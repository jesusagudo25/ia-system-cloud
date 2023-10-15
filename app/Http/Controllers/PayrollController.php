<?php

namespace App\Http\Controllers;

use App\Models\Address;
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
