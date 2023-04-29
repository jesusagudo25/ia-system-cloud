<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use Illuminate\Http\Request;
use PDF;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Invoice::all()->load('agency', 'interpreter', 'coordinator', 'invoiceDetails');
    }

    public function closed()
    {
        return Invoice::where('status', 'closed')->get()->load('agency', 'interpreter', 'coordinator', 'invoiceDetails');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Obtener el interprete que viene en el request
        //Si el valor es null, entonces hay que crearlo
        $interpreter_id = $request->has('interpreter_id') ? $request->interpreter_id : null;

        if (empty($interpreter_id)) {
            //Crear el interprete con los datos del request
            //Obtener el id del interprete creado
        }

        $invoice = Invoice::create([
            'user_id' => $request->user_id,
            'agency_id' => $request->agency_id,
            'interpreter_id' => $interpreter_id,
            'coordinator_id' => $request->coordinator_id,
            'total_amount' => $request->total_amount,
        ]);

        InvoiceDetail::create([
            'invoice_id' => $invoice->id,
            'invoice_number' => sprintf('%06d', $invoice->id).'-JA',
            'assignment_number' => sprintf('%06d', $invoice->id),
            'description_id' => $request->description_id,
            'date_of_service_provided' => $request->date_of_service_provided,
            'arrival_time' => $request->arrival_time,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'travel_time_to_assignment' => $request->travel_time_to_assignment,
            'time_back_from_assignment' => $request->time_back_from_assignment,
            'travel_mileage' => $request->travel_mileage,
            'cost_per_mile' => $request->cost_per_mile,
            'total_amount_miles' => $request->total_amount_miles,
            'total_amount_hours' => $request->total_amount_hours,
            'total_interpreter' => $request->total_interpreter,
            'total_coordinator' => $request->total_coordinator,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'zip_code' => $request->zip_code,
            'comments' => $request->comments
        ]);

        return response()->json([
            'message' => 'Invoice created successfully',
            'status' => 'success',
            'invoice' => $invoice->id
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        //
    }

    public function pdf(Invoice $invoice)
    {
        /*         return response()->json([
            'message' => 'Invoice created successfully',
            'status' => 'success',
            'invoice' => $invoice,

        ], 201); */
        $pdf = PDF::loadView(
            'pdf.invoice',
            [
                'invoice' => $invoice,
                'details' => $invoice->invoiceDetails[0],
                'agency' => $invoice->agency,
                'description' => $invoice->invoiceDetails()->first()->description,
                'lenguage' => $invoice->interpreter->lenguage,
            ]
        );
        return $pdf->stream('invoice_' . $invoice->id . '.pdf');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
