<?php

namespace App\Http\Controllers;

use App\Models\Interpreter;
use App\Models\Invoice;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Report::all()->load('user');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return Report::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        //
    }

    public function pdf(Report $report)
    {
        if($report->type == 'a'){
            $pdf = \PDF::loadView('pdf.annual', compact('report'));
            return $pdf->stream('annual.pdf');
        }else{
            $intermediaDate = new \DateTime($report->start_date);
            $intermediaDate->modify('+14 day');
            $intermediaDateString = $intermediaDate->format('Y-m-d');

            $coordinator_first = Invoice::whereBetween('invoices.updated_at', [$report->start_date, $intermediaDateString])
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('descriptions', 'invoice_details.description_id', '=', 'descriptions.id')
                ->join('coordinators', 'invoices.coordinator_id', '=', 'coordinators.id')
                ->get();

            $interpreters_first = Invoice::whereBetween('invoices.updated_at', [$report->start_date, $intermediaDateString])
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('descriptions', 'invoice_details.description_id', '=', 'descriptions.id')
                ->join('interpreters', 'invoices.interpreter_id', '=', 'interpreters.id')
                ->select('interpreters.*', 'descriptions.*', 'invoice_details.*', 'invoices.*')
                ->get()
                ->groupBy('interpreter_id');

            $intermediaDate->modify('+1 day');
            $intermediaDateString = $intermediaDate->format('Y-m-d');

            $coordinator_second = Invoice::whereBetween('invoices.updated_at', [$intermediaDateString, $report->end_date])
                ->where('invoices.status', 'closed')
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('descriptions', 'invoice_details.description_id', '=', 'descriptions.id')
                ->join('coordinators', 'invoices.coordinator_id', '=', 'coordinators.id')
                ->get();

            $interpreters_second = Invoice::whereBetween('invoices.updated_at', [$intermediaDateString, $report->end_date])
                ->where('invoices.status', 'closed')
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('descriptions', 'invoice_details.description_id', '=', 'descriptions.id')
                ->join('interpreters', 'invoices.interpreter_id', '=', 'interpreters.id')
                ->select('interpreters.*', 'descriptions.*', 'invoice_details.*', 'invoices.*')
                ->get()
                ->groupBy('interpreter_id');

/*             $interpretersNotShowFirst = Interpreter::whereNotIn('id', $interpreters_first->keys())->get();
            $interpretersNotShowSecond = Interpreter::whereNotIn('id', $interpreters_second->keys())->get();

            foreach ($interpretersNotShowFirst as $interpreter) {
                $interpreters_first->put($interpreter->id, []);
            }

            foreach ($interpretersNotShowSecond as $interpreter) {
                $interpreters_second->put($interpreter->id, []);
            } */

            /* What keys are missing from the first collection */

            $missing = $interpreters_second->keys()->diff($interpreters_first->keys());

            /* Add the missing keys to the first collection */

            foreach ($missing as $key) {
                $interpreters_first->put($key, []);
            }

            /* What keys are missing from the second collection */

            $missing = $interpreters_first->keys()->diff($interpreters_second->keys());

            /* Add the missing keys to the second collection */

            foreach ($missing as $key) {
                $interpreters_second->put($key, []);
            }
            
            /* What keys are missing from the first collection coordinator */

            $missing = $coordinator_second->keys()->diff($coordinator_first->keys());

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
                'report' => $report,
                'end_date_first' => $intermediaDate->modify('-1 day')->format('Y-m-d'),
                'start_date_second' => $intermediaDate->modify('+1 day')->format('Y-m-d'),
                'interpreters' => $interpreters,
                'coordinator_first' => $coordinator_first,
                'coordinator_second' => $coordinator_second,
            ]);

            return $pdf->stream('monthly.pdf');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        return $report->delete();
    }
}
