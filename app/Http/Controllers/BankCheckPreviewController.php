<?php

namespace App\Http\Controllers;

use App\Models\BankCheckPreview;
use Illuminate\Http\Request as HttpRequest;

use App\Models\Invoice;
use App\Models\Request;
use App\Models\Request as ModelsRequest;
use Illuminate\Support\Facades\DB;
use NumberFormatter;
use PDF;

class BankCheckPreviewController extends Controller
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
    public function store(HttpRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(BankCheckPreview $bankCheckPreview)
    {
        //
    }

    public function pdf(Request $request)
    {
        $pdf = PDF::loadView('pdf.bank-checks', [
            'checks' => $request->bankCheckPreviews()->get()->load('checkDetailPreviews'),
            'preview' => true
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
    public function update(HttpR $request, BankCheckPreview $bankCheckPreview)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankCheckPreview $bankCheckPreview)
    {
        //
    }
}
