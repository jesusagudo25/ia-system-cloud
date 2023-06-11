<?php

namespace App\Http\Controllers;

use App\Models\BankCheck;
use App\Models\CheckDetail;
use App\Models\Interpreter;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use NumberFormatter;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Payroll::all()->load('user');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /* Se debe trabajar en base a la lista de servicios seleccionados por el usuario */

        /* Se obtiene el total de la planilla para asignarlo al payroll */
        $total = Invoice::whereIn('invoices.id', $request->services)
        ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
        ->selectRaw('sum(invoices.total_amount) as total_amount')
        ->get();

        //Find payrroll with start_date and end_date equal to the request
        $payroll = Payroll::where('start_date', $request->start_date)->where('end_date', $request->end_date)->first();

        //If payroll exists, return error
        if ($payroll) {
            return response()->json([
                'message' => 'Already exists a payroll with the same start date and end date',
            ], 400);
        }

        $payroll = Payroll::create([
            'user_id' => $request->user_id,
            'month' => Carbon::parse($request->start_date)->format('F'),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_amount' => $total[0]['total_amount'] ? $total[0]['total_amount'] : 0,
        ]);

        Invoice::whereIn('id', $request->services)->update(['payroll_id' => $payroll->id]);

        /* Se obtiene los servicios proporcionados por los interpretes de acuerdo a la lista de servicios (facturas) seleccionados */
        $interpreters = Invoice::whereIn('invoices.id', $request->services)
        ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
        ->join('interpreters', 'invoices.interpreter_id', '=', 'interpreters.id')
        ->selectRaw('sum(invoice_details.total_interpreter) as total_amount, interpreters.*')
        ->groupBy('interpreters.id')
        ->get();

        $interpreterDetails = Invoice::whereIn('invoices.id', $request->services)
        ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
        ->join('interpreters', 'invoices.interpreter_id', '=', 'interpreters.id')
        ->selectRaw('sum(invoice_details.total_interpreter) as total_amount, interpreters.id AS interpreter_id, invoice_details.*')
        ->groupBy('interpreters.id', 'invoice_details.id')
        ->get();
        
        $interpreters = collect($interpreters)->each(function ($item, $key) use ($interpreterDetails) {
            $item->details = $interpreterDetails->where('interpreter_id', $item->id);
        });

        /* Se obtiene los servicios proporcionados por los coordinadores de acuerdo a la lista de servicios seleccionados */
        $coordinator = Invoice::whereIn('invoices.id', $request->services)
        ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
        ->join('coordinators', 'invoices.coordinator_id', '=', 'coordinators.id')
        ->selectRaw('sum(invoice_details.total_coordinator) as total_amount, coordinators.*')
        ->groupBy('coordinators.id')
        ->get();

        $coordinatorDetails = Invoice::whereIn('invoices.id', $request->services)
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
                'payroll_id' => $payroll->id,
                'date' => Carbon::now(),
                'amount' => $interpreter->total_amount,
                'amount_in_words' => $amountInWords,
                'ssn' => $interpreter->ssn,
                'pay_to' => $interpreter->full_name,
                'for' => 'Interpreting services provided from '. Carbon::parse($request->start_date)->format('m-d-Y') .' to '. Carbon::parse($request->end_date)->format('m-d-Y'),
            ]);

            foreach ($interpreter->details as $detail) {
                CheckDetail::create([
                    'bank_check_id' => $bankCheck->id,
                    'assignment_number' => $detail->assignment_number,
                    'date_of_service_provided' => $detail->date_of_service_provided,
                    'location' => $interpreter->address.', '.$interpreter->city.', '.$interpreter->state.', '.$interpreter->zip_code,
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
                'payroll_id' => $payroll->id,
                'date' => Carbon::now(),
                'amount' => $coordinator->total_amount,
                'amount_in_words' => $amountInWords,
                'ssn' => $coordinator->ssn, // '123-45-6789
                'pay_to' => $coordinator->full_name,
                'for' => 'Interpreting services provided from '. Carbon::parse($request->start_date)->format('m-d-Y') .' to '. Carbon::parse($request->end_date)->format('m-d-Y'),
            ]);

            foreach ($coordinator->details as $detail) {
                CheckDetail::create([
                    'bank_check_id' => $bankCheck->id,
                    'assignment_number' => $detail->assignment_number,
                    'date_of_service_provided' => $detail->date_of_service_provided,
                    'location' => $interpreter->address.', '.$interpreter->city.', '.$interpreter->state.', '.$interpreter->zip_code,
                    'total_amount' => $detail->total_coordinator,
                ]);
            }
        }

        return response()->json([
            'message' => 'Payroll created successfully',
            'success' => true,
            'payroll' => $payroll
        ], 201);
    }

    public function review(Request $request){
        $request->validate([
            'start_date' => 'required | date',
            'end_date' => 'required  | date',
        ]);

        /* Se debe validar que el rango de fecha sea entre 1 - 15 */

        /* Se consulta a la BD la lista de servicios que llevan mas de 30 dias y no se les ha pagado...
            **De lo que puedo analizar, en ningun caso se estaria pagando dentro de la quincena legalmente, sino siempre se
            pagan servicios atrazados.
        */

        $review = Invoice::whereIn('invoices.status', ['paid', 'pending'])
        ->where('invoices.payroll_id', null)
        ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
        ->get()
        ->load('agency', 'interpreter', 'coordinator', 'invoiceDetails');

        return response()->json([
            'message' => 'Review created successfully',
            'success' => true,
            'review' => $review
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payroll $payroll)
    {
        //
    }

    public function pdf(Payroll $payroll)
    {
        //Si el rango de fecha corresponde a una quincena o mes
        $dateTimeStart = new \DateTime($payroll->start_date);
        $dateTimeEnd = new \DateTime($payroll->end_date);
        $interval = $dateTimeStart->diff($dateTimeEnd);
        $days = $interval->format('%a');

        if ($days <= 15) {
            $interpreters = Invoice::where('invoices.payroll_id', $payroll->id)
            ->whereIn('invoices.status', ['paid', 'pending'])
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('descriptions', 'invoice_details.description_id', '=', 'descriptions.id')
                ->join('interpreters', 'invoices.interpreter_id', '=', 'interpreters.id')
                ->select('interpreters.*', 'descriptions.*', 'invoice_details.*', 'invoices.*')
                ->get()
                ->groupBy('interpreter_id');

            $coordinator = Invoice::where('invoices.payroll_id', $payroll->id)
            ->whereIn('invoices.status', ['paid', 'pending'])
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('descriptions', 'invoice_details.description_id', '=', 'descriptions.id')
                ->join('coordinators', 'invoices.coordinator_id', '=', 'coordinators.id')
                ->get();

            $pdf = \PDF::loadView('pdf.fortnightly', [
                'payroll' => $payroll,
                'interpreters' => array_values($interpreters->toArray()),
                'coordinator' => $coordinator,
            ]);
            return $pdf->stream('fortnightly.pdf');
        }else {
            return response()->json([
                'message' => 'El rango de fecha no corresponde a una quincena o mes',
                'success' => false,
            ], 201);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payroll $payroll)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payroll $payroll)
    {
        Invoice::where('payroll_id', $payroll->id)->update(['payroll_id' => null]);
        return $payroll->delete();
    }


}
