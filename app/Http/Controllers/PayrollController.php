<?php

namespace App\Http\Controllers;

use App\Models\BankCheck;
use App\Models\Interpreter;
use App\Models\Invoice;
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
        $total = Invoice::whereBetween('date_of_service_provided', [$request->start_date, $request->end_date])
        ->where('invoices.status', 'closed')
        ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
        ->selectRaw('sum(invoices.total_amount) as total_amount')
        ->get();

        $payroll = Payroll::create([
            'user_id' => $request->user_id,
            'month' => Carbon::parse($request->start_date)->format('m'),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_amount' => $total[0]['total_amount']
        ]);

        $interpreters = Invoice::whereBetween('date_of_service_provided', [$payroll->start_date, $payroll->end_date])
        ->where('invoices.status', 'closed')
        ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
        ->join('interpreters', 'invoices.interpreter_id', '=', 'interpreters.id')
        ->selectRaw('sum(invoice_details.total_interpreter) as total_amount, interpreters.*')
        ->groupBy('interpreters.id')
        ->get();

        $coordinator = Invoice::whereBetween('date_of_service_provided', [$payroll->start_date, $payroll->end_date])
        ->where('invoices.status', 'closed')
        ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
        ->join('coordinators', 'invoices.coordinator_id', '=', 'coordinators.id')
        ->selectRaw('sum(invoice_details.total_coordinator) as total_amount, coordinators.*')
        ->groupBy('coordinators.id')
        ->get();

        foreach ($interpreters as $interpreter) {

            $totalAmount = explode('.', $interpreter->total_amount);
            $decimal = $totalAmount[1] . '/' . 100;
            $amountInWords = ucwords((new NumberFormatter('en_IN', NumberFormatter::SPELLOUT))->format($totalAmount[0]));
            $amountInWords = $amountInWords . ' ' . 'and' . ' ' . $decimal;
    
            BankCheck::create([
                'payroll_id' => $payroll->id,
                'date' => Carbon::now(),
                'amount' => $interpreter->total_amount,
                'amount_in_words' => $amountInWords,
                'pay_to' => $interpreter->full_name,
                'for' => 'Interpreter service',
                'routing_number' => 'C1111C',
                'account_number' => 'A123456789A',
            ]);
        }

        foreach ($coordinator as $coordinator) {
            $totalAmount = explode('.', $coordinator->total_amount);
            $decimal = $totalAmount[1] . '/' . 100;
            $amountInWords = ucwords((new NumberFormatter('en_IN', NumberFormatter::SPELLOUT))->format($totalAmount[0]));
            $amountInWords = $amountInWords . ' ' . 'and' . ' ' . $decimal;
            BankCheck::create([
                'payroll_id' => $payroll->id,
                'date' => Carbon::now(),
                'amount' => $coordinator->total_amount,
                'amount_in_words' => $amountInWords,
                'pay_to' => $coordinator->full_name,
                'for' => 'Coordinador of Interprete',
                'routing_number' => 'C1111C',
                'account_number' => 'A123456789A',
            ]);
        }

        return response()->json([
            'message' => 'Payroll created successfully',
            'success' => true,
            'payroll' => $payroll
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
            $interpreters = Invoice::whereBetween('date_of_service_provided', [$payroll->start_date, $payroll->end_date])
                ->where('invoices.status', 'closed')
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('descriptions', 'invoice_details.description_id', '=', 'descriptions.id')
                ->join('interpreters', 'invoices.interpreter_id', '=', 'interpreters.id')
                ->select('interpreters.*', 'descriptions.*', 'invoice_details.*', 'invoices.*')
                ->get()
                ->groupBy('interpreter_id');

            $coordinator = Invoice::whereBetween('date_of_service_provided', [$payroll->start_date, $payroll->end_date])
                ->where('invoices.status', 'closed')
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
        } else if ($days <= 30) {

            $intermediaDate = new \DateTime($payroll->start_date);
            $intermediaDate->modify('+14 day');
            $intermediaDateString = $intermediaDate->format('Y-m-d');

            $coordinator_first = Invoice::whereBetween('date_of_service_provided', [$payroll->start_date, $intermediaDateString])
                ->where('invoices.status', 'closed')
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('descriptions', 'invoice_details.description_id', '=', 'descriptions.id')
                ->join('coordinators', 'invoices.coordinator_id', '=', 'coordinators.id')
                ->get();

            $interpreters_first = Invoice::whereBetween('date_of_service_provided', [$payroll->start_date, $intermediaDateString])
                ->where('invoices.status', 'closed')
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('descriptions', 'invoice_details.description_id', '=', 'descriptions.id')
                ->join('interpreters', 'invoices.interpreter_id', '=', 'interpreters.id')
                ->select('interpreters.*', 'descriptions.*', 'invoice_details.*', 'invoices.*')
                ->get()
                ->groupBy('interpreter_id');

            $intermediaDate->modify('+1 day');
            $intermediaDateString = $intermediaDate->format('Y-m-d');

            $coordinator_second = Invoice::whereBetween('date_of_service_provided', [$intermediaDateString, $payroll->end_date])
                ->where('invoices.status', 'closed')
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('descriptions', 'invoice_details.description_id', '=', 'descriptions.id')
                ->join('coordinators', 'invoices.coordinator_id', '=', 'coordinators.id')
                ->get();

            $interpreters_second = Invoice::whereBetween('date_of_service_provided', [$intermediaDateString, $payroll->end_date])
                ->where('invoices.status', 'closed')
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('descriptions', 'invoice_details.description_id', '=', 'descriptions.id')
                ->join('interpreters', 'invoices.interpreter_id', '=', 'interpreters.id')
                ->select('interpreters.*', 'descriptions.*', 'invoice_details.*', 'invoices.*')
                ->get()
                ->groupBy('interpreter_id');

            $interpretersNotShowFirst = Interpreter::whereNotIn('id', $interpreters_first->keys())->get();
            $interpretersNotShowSecond = Interpreter::whereNotIn('id', $interpreters_second->keys())->get();

            foreach ($interpretersNotShowFirst as $interpreter) {
                $interpreters_first->put($interpreter->id, []);
            }

            foreach ($interpretersNotShowSecond as $interpreter) {
                $interpreters_second->put($interpreter->id, []);
            }

            $interpreters = $interpreters_first->map(function ($item, $key) use ($interpreters_second) {
                return [
                    'details' => count($item) > 0 ? $item[0]->interpreter : $interpreters_second[$key][0]->interpreter,
                    'first' => collect($item)->map(function ($item) {
                        return [
                            'date_of_service_provided' => $item->date_of_service_provided,
                            'title' => $item->title,
                            'invoice_id' => $item->invoice_id,
                            'total_coordinator' => $item->total_coordinator,
                            'total_interpreter' => $item->total_interpreter,
                        ];
                    }),
                    'second' => collect($interpreters_second[$key])->map(function ($item) {
                        return [
                            'date_of_service_provided' => $item->date_of_service_provided,
                            'title' => $item->title,
                            'invoice_id' => $item->invoice_id,
                            'total_coordinator' => $item->total_coordinator,
                            'total_interpreter' => $item->total_interpreter,
                        ];
                    }),
                ];
            });

            $pdf = \PDF::loadView('pdf.monthly', [
                'payroll' => $payroll,
                'end_date_first' => $intermediaDate->modify('-1 day')->format('Y-m-d'),
                'start_date_second' => $intermediaDate->modify('+1 day')->format('Y-m-d'),
                'interpreters' => $interpreters,
                'coordinator_first' => $coordinator_first,
                'coordinator_second' => $coordinator_second,
            ]);

            return $pdf->stream('monthly.pdf');
        } else {
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
        return $payroll->delete();
    }


}
