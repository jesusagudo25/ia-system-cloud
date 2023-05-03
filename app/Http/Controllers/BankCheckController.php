<?php

namespace App\Http\Controllers;

use App\Models\BankCheck;
use App\Models\Invoice;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use NumberFormatter;
use PDF;

class BankCheckController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(BankCheck $bankCheck)
    {
        //
    }

    /**
     * Show PDF
     */

    public function pdf(Payroll $payroll)
    {

        $interpreters = Invoice::where('invoices.payroll_id', $payroll->id)
            ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->join('interpreters', 'invoices.interpreter_id', '=', 'interpreters.id')
            ->select('interpreters.*', 'invoice_details.*', 'invoices.*', \DB::raw('sum(invoice_details.total_interpreter) as checks'))
            ->groupBy('interpreter_id', 'invoice_details.id')
            ->get();

        $coordinator = Invoice::where('invoices.payroll_id', $payroll->id)
            ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->join('coordinators', 'invoices.coordinator_id', '=', 'coordinators.id')
            ->select('coordinators.*', 'invoice_details.*', 'invoices.*', \DB::raw('sum(invoice_details.total_coordinator) as checks'))
            ->groupBy('coordinator_id', 'invoice_details.id')
            ->get();

        $pdf = PDF::loadView('pdf.bank-checks', [
            'checks' => $payroll->bankChecks()->get(),
            'interpreters' => collect($interpreters)->groupBy('full_name'),
            'coordinator' => collect($coordinator)->groupBy('full_name')
        ]);
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOptions([
            'isRemoteEnabled' => true,
            'fontCache' => storage_path('fonts/'),
            'pdfBackend' => 'CPDF',
            'chroot' => [
                'resources/views/',
                storage_path('fonts/')
            ]
        ]);
        return $pdf->stream('bank-checks.pdf');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankCheck $bankCheck)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankCheck $bankCheck)
    {
        //
    }
}
