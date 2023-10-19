<?php

namespace App\Http\Controllers;


use App\Models\Invoice;

use App\Models\Payroll;

use Illuminate\Http\Request;


class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Payroll::all()->load('user')->load('request');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     */
    public function show(Payroll $payroll)
    {
        return $payroll->load('user')->load('request')->load('bankChecks')->load('invoices');
    }

    public function lastPayroll()
    {
        //return last in array
        return Payroll::where('status', '!=', 'cancelled')->orderBy('id', 'desc')->first();
    }

    public function wizard(Payroll $payroll)
    {

        $review = Invoice::whereIn('invoices.status', ['paid', 'pending'])
            ->where(function ($query) use ($payroll) {
                $query->where('invoices.payroll_id', $payroll->id)
                    ->orWhere('invoices.payroll_id', null);
            })
            ->where(function ($query) use ($payroll) {
                $query->where('invoices.request_id', $payroll->request_id)
                    ->orWhere('invoices.request_id', null);
            })
            ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->select(
                'invoices.id',
                'invoices.agency_id',
                'invoices.interpreter_id',
                'invoices.coordinator_id',
                'invoices.status',
                'invoices.total_amount',
                'invoices.created_at',
                'invoices.updated_at',
                'invoices.payroll_id',
                'invoice_details.id as detail_id',
                'invoice_details.invoice_id',
                'invoice_details.address_id',
                'invoice_details.description_id',
                'invoice_details.invoice_number',
                'invoice_details.assignment_number',
                'invoice_details.date_of_service_provided',
                'invoice_details.arrival_time',
                'invoice_details.start_time',
                'invoice_details.end_time',
                'invoice_details.travel_time_to_assignment',
                'invoice_details.time_back_from_assignment',
                'invoice_details.travel_mileage',
                'invoice_details.cost_per_mile',
                'invoice_details.total_amount_miles',
                'invoice_details.total_amount_hours',
                'invoice_details.total_interpreter',
                'invoice_details.total_coordinator',
                'invoice_details.comments',
            )
            ->get()
            ->load('agency', 'interpreter', 'coordinator');

        return response()->json([
            'message' => 'Review created successfully',
            'success' => true,
            'review' => $review
        ], 201);
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
                ->join('addresses', 'invoice_details.address_id', '=', 'addresses.id')
                ->join('interpreters', 'invoices.interpreter_id', '=', 'interpreters.id')
                ->select('interpreters.*', 'addresses.address as locationAddress', 'addresses.city as locationCity', 'addresses.state as locationState', 'addresses.zip_code as locationZipCode', 'invoice_details.*', 'invoices.*')
                ->get()
                ->groupBy('interpreter_id');

            $coordinator = Invoice::where('invoices.payroll_id', $payroll->id)
                ->whereIn('invoices.status', ['paid', 'pending'])
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('addresses', 'invoice_details.address_id', '=', 'addresses.id')
                ->join('coordinators', 'invoices.coordinator_id', '=', 'coordinators.id')
                ->select('coordinators.*', 'addresses.address as locationAddress', 'addresses.city as locationCity', 'addresses.state as locationState', 'addresses.zip_code as locationZipCode', 'invoice_details.*', 'invoices.*')
                ->get();

            // find services by miscellaneous != 0
            $miscellaneous = Invoice::where('invoices.payroll_id', $payroll->id)
                ->whereIn('invoices.status', ['paid', 'pending'])
                ->where('invoice_details.miscellaneous', '!=', 0)
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('addresses', 'invoice_details.address_id', '=', 'addresses.id')
                ->join('coordinators', 'invoices.coordinator_id', '=', 'coordinators.id')
                ->select('coordinators.*', 'addresses.address as locationAddress', 'addresses.city as locationCity', 'addresses.state as locationState', 'addresses.zip_code as locationZipCode', 'invoice_details.*', 'invoices.*')
                ->get();


            $pdf = \PDF::loadView('pdf.fortnightly', [
                'payroll' => $payroll,
                'interpreters' => array_values($interpreters->toArray()),
                'coordinator' => $coordinator,
                'miscellaneous' => $miscellaneous,
                'preview' => false
            ]);
            return $pdf->stream('fortnightly.pdf');
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
        Invoice::where('payroll_id', $payroll->id)->update(['payroll_id' => null]);
        return $payroll->delete();
    }
}
