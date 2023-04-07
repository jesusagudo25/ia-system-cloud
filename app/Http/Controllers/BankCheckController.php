<?php

namespace App\Http\Controllers;

use App\Models\BankCheck;
use App\Models\Invoice;
use App\Models\Payroll;
use Illuminate\Http\Request;
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

    public function pdf(Payroll $payroll){
        
        $pdf = PDF::loadView('pdf.bank-checks', [ 'checks' => $payroll->bankChecks()->get() ]);
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
