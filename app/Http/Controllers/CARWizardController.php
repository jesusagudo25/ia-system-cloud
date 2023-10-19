<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\BankCheck;
use App\Models\BankCheckPreview;
use App\Models\CARWizard;
use App\Models\CheckDetail;
use App\Models\Invoice;
use App\Models\Payroll;
use App\Models\Request;
use App\Models\WizardView;
use Carbon\Carbon;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use NumberFormatter;

class CARWizardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CARWizard::all()->load(['user', 'payroll', 'request']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HttpRequest $request)
    {
        Invoice::where('payroll_id', $request->payroll)->update(['payroll_id' => null, 'request_id' => null]);

        //search payroll
        $payrollCancel = Payroll::find($request->payroll);
        $payrollCancel->status = 'cancelled';
        $payrollCancel->save();

        //Search first bank_check where payroll_id = $payroll->id and get first autoincrement
        $bankCheck = BankCheck::where('payroll_id', $request->payroll)->first();
        $autoincrement = $bankCheck->id;

        //search bank_checks and delete
        $bankChecks = BankCheck::where('payroll_id', $request->payroll)->get();
        foreach ($bankChecks as $bankCheck) {
            $bankCheck->delete();
        }

        if ($payrollCancel->request_id != null) {
            $requestUpdate = Request::find($payrollCancel->request_id);
            $requestUpdate->status = 'cancelled';
            $requestUpdate->save();

            //search bank_check_previews and delete
            $bankCheckPreviews = BankCheckPreview::where('request_id', $payrollCancel->request_id)->get();
            foreach ($bankCheckPreviews as $bankCheckPreview) {
                $bankCheckPreview->delete();
            }
        }

        //set autoincrement
        DB::statement("ALTER TABLE bank_checks AUTO_INCREMENT = $autoincrement");

        $commentIAJROBOT = 'IAJ ROBOT: AUTO INCREMENT SET TO ' . $autoincrement;

        //Falta lo importante, setear los autoincrement de las tablas bank_checks

        if ($request->actionRequest == 'Regenerate') {

            //search payroll
            $payrollSearch = Payroll::find($request->payroll);

            //get last payroll id
            $lastPayroll = Payroll::all()->last()->id;

            //create suffix_id
            $suffix_id = 'P-' . str_pad($lastPayroll + 1, 5, '0', STR_PAD_LEFT);

            /* Se obtiene el total de la planilla para asignarlo al payroll */
            $total = Invoice::whereIn('invoices.id', $request->invoices)
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->selectRaw('sum(invoices.total_amount) as total_amount')
                ->get();


            $newPayroll = Payroll::create([
                'user_id' => $request->user_id,
                'suffix_id' => $suffix_id,
                'request_id' => null,
                'month' => $payrollSearch->month,
                'start_date' => $payrollSearch->start_date,
                'end_date' => $payrollSearch->end_date,
                'total_amount' =>  $total[0]['total_amount'] ? $total[0]['total_amount'] : 0
            ]);

            $commentIAJROBOT.= ' | IAJ ROBOT: NEW PAYROLL CREATED WITH ID ' . $newPayroll->suffix_id;

            //search services by request_id from invoices table

            Invoice::whereIn('id', $request->invoices)->update(['payroll_id' => $newPayroll->id]);

            /* Se obtiene los servicios proporcionados por los interpretes de acuerdo a la lista de servicios (facturas) seleccionados */
            $interpreters = Invoice::whereIn('invoices.id', $request->invoices)
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('interpreters', 'invoices.interpreter_id', '=', 'interpreters.id')
                ->selectRaw('sum(invoice_details.total_interpreter) as total_amount, interpreters.*')
                ->groupBy('interpreters.id')
                ->get();

            $interpreterDetails = Invoice::whereIn('invoices.id', $request->invoices)
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('interpreters', 'invoices.interpreter_id', '=', 'interpreters.id')
                ->selectRaw('sum(invoice_details.total_interpreter) as total_amount, interpreters.id AS interpreter_id, invoice_details.*')
                ->groupBy('interpreters.id', 'invoice_details.id')
                ->get();

            $interpreters = collect($interpreters)->each(function ($item, $key) use ($interpreterDetails) {
                $item->details = $interpreterDetails->where('interpreter_id', $item->id);
            });

            /* Se obtiene los servicios proporcionados por los coordinadores de acuerdo a la lista de servicios seleccionados */
            $coordinator = Invoice::whereIn('invoices.id', $request->invoices)
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('coordinators', 'invoices.coordinator_id', '=', 'coordinators.id')
                ->selectRaw('sum(invoice_details.total_coordinator) as total_amount, coordinators.*')
                ->groupBy('coordinators.id')
                ->get();

            $coordinatorDetails = Invoice::whereIn('invoices.id', $request->invoices)
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('coordinators', 'invoices.coordinator_id', '=', 'coordinators.id')
                ->selectRaw('sum(invoice_details.total_coordinator) as total_amount, coordinators.id AS coordinator_id, invoice_details.*')
                ->groupBy('coordinators.id', 'invoice_details.id')
                ->get();

            $coordinator = collect($coordinator)->each(function ($item, $key) use ($coordinatorDetails) {
                $item->details = $coordinatorDetails->where('coordinator_id', $item->id);
            });

            /* Se recorren los interpretes y se crea su respectivo cheque */
            foreach ($interpreters as $interpreter) {

                $totalAmount = explode('.', $interpreter->total_amount);
                $decimal = $totalAmount[1] . '/' . 100;
                $amountInWords = ucwords((new NumberFormatter('en_IN', NumberFormatter::SPELLOUT))->format($totalAmount[0]));
                $amountInWords = $amountInWords . ' ' . 'and' . ' ' . $decimal;

                $bankCheck = BankCheck::create([
                    'payroll_id' => $newPayroll->id,
                    'date' => Carbon::now(),
                    'amount' => $interpreter->total_amount,
                    'amount_in_words' => $amountInWords,
                    'ssn' => $interpreter->ssn,
                    'pay_to' => $interpreter->full_name,
                    'for' => 'Interpreting services provided from ' . Carbon::parse($request->start_date)->format('m-d-Y') . ' to ' . Carbon::parse($request->end_date)->format('m-d-Y'),
                    'address' => $interpreter->address,
                    'city' => $interpreter->city,
                    'state' => RequestController::getAbbrState($interpreter->state),
                    'zip' => $interpreter->zip_code,
                ]);

                foreach ($interpreter->details as $detail) {
                    //Find address of the service detail
                    $address = Address::where('id', $detail->address_id)->first();
                    CheckDetail::create([
                        'bank_check_id' => $bankCheck->id,
                        'assignment_number' => $detail->assignment_number,
                        'date_of_service_provided' => $detail->date_of_service_provided,
                        'location' => $address->address . ', ' . $address->city . ', ' . $address->state . ', ' . $address->zip_code,
                        'total_amount' => $detail->total_interpreter,
                    ]);
                }
            }

            /* Se reccoren los coordinadores y se crea su respectivo cheque */
            foreach ($coordinator as $coordinator) {
                $totalAmount = explode('.', $coordinator->total_amount);
                $decimal = $totalAmount[1] . '/' . 100;
                $amountInWords = ucwords((new NumberFormatter('en_IN', NumberFormatter::SPELLOUT))->format($totalAmount[0]));
                $amountInWords = $amountInWords . ' ' . 'and' . ' ' . $decimal;
                $bankCheck = BankCheck::create([
                    'payroll_id' => $newPayroll->id,
                    'date' => Carbon::now(),
                    'amount' => $coordinator->total_amount,
                    'amount_in_words' => $amountInWords,
                    'ssn' => $coordinator->ssn, // '123-45-6789
                    'pay_to' => $coordinator->full_name,
                    'for' => 'Interpreting services provided from ' . Carbon::parse($request->start_date)->format('m-d-Y') . ' to ' . Carbon::parse($request->end_date)->format('m-d-Y'),
                    'address' => $coordinator->address,
                    'city' => $coordinator->city,
                    'state' => RequestController::getAbbrState($coordinator->state),
                    'zip' => $coordinator->zip_code,
                ]);

                foreach ($coordinator->details as $detail) {
                    $address = Address::where('id', $detail->address_id)->first();
                    CheckDetail::create([
                        'bank_check_id' => $bankCheck->id,
                        'assignment_number' => $detail->assignment_number,
                        'date_of_service_provided' => $detail->date_of_service_provided,
                        'location' => $address->address . ', ' . $address->city . ', ' . $address->state . ', ' . $address->zip_code,
                        'total_amount' => $detail->total_coordinator,
                    ]);
                }
            }
        }

        //insert into car_wizard log --------------------
        $carWizard = new CARWizard();
        $carWizard->payroll_id = $payrollCancel->id;
        $carWizard->request_id = $request->id;
        $carWizard->user_id = $request->user_id;
        $carWizard->comment = empty($request->comments) ?
            $commentIAJROBOT :
            $commentIAJROBOT . ' | User: ' . $request->comments;

        $carWizard->action = $request->actionRequest;
        $carWizard->save();

        return response()->json([
            'message' => 'Payroll cancelled successfully',
            'payroll' => $payrollCancel,
            'request' => $request,
            'carWizard' => $carWizard
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(CARWizard $cARWizard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CARWizard $cARWizard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CARWizard $cARWizard)
    {
        //
    }
}
