<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Description;
use App\Models\Interpreter;
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

    public function indexPaid()
    {
        return Invoice::where('status', 'paid')->get()->load('agency', 'interpreter', 'coordinator', 'invoiceDetails');
    }

    /**
     * Display the specified resource.
     */
    public function validateAssignmentNumber($assignmentNumber)
    {
        $invoiceDetail = InvoiceDetail::where('assignment_number', $assignmentNumber)->first();
        if($invoiceDetail != null){
            if($invoiceDetail->invoice->status != 'cancelled'){
                return response()->json([
                    'message' => 'The assignment number is already in use',
                    'status' => 'error',
                ], 400);
            }
            else{
                return response()->json([
                    'message' => 'The assignment number is available',
                    'status' => 'success',
                ], 200);
            }
        }
        else{
            return response()->json([
                'message' => 'The assignment number is available',
                'status' => 'success',
            ], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Obtener entidades utilizadas en el request
        $interpreter_id = $request->has('interpreter_id') ? $request->interpreter_id : null;
        $address_id = $request->has('address_id') ? $request->address_id : null;
        $description_id = $request->has('description_id') ? $request->description_id : null;

        //FindOrFails Interpreter, address and description

        if (empty($interpreter_id)) {
            $interpreter = Interpreter::create([
                'full_name' => $request->interpreterName,
                'ssn' => $request->interpreterSSN,
                'phone_number' => $request->interpreterPhoneNum,
                'email' => $request->interpreterEmail,
                'address' => $request->interpreterAddress,
                'city' => $request->interpreterCity,
                'state' => $request->interpreterState,
                'zip_code' => $request->interpreterZipCode,
                'lenguage_id' => $request->interpreterLenguageId,
            ]);

            $interpreter_id = $interpreter->id;
        }
        else{
            $interpreter = Interpreter::where('id', $interpreter_id)->first();
        }

        if (empty($address_id)) {
            $validateAddress = Address::where('address', $request->address)
                ->where('city', $request->city)
                ->where('state', $request->state)
                ->where('state_abbr', $request->state_abbr)
                ->where('zip_code', $request->zip_code)
                ->first();

            if($validateAddress != null){
                $address_id = $validateAddress->id;
            }
            else{
                $address = Address::create([
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'state_abbr' => $request->state_abbr,
                    'zip_code' => $request->zip_code,
                ]);
    
                $address_id = $address->id;
            }
        }

        if (empty($description_id)) {
            $description = Description::where('title', $request->description)->first();
            if($description == null){
                $description = Description::create([
                    'title' => $request->description,
                ]);
    
                $description_id = $description->id;
            }
            else{
                $description_id = $description->id;
            }
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
            'invoice_number' => $request->assignment_number.'-'.strtoupper(substr($interpreter->full_name, 0, 1).substr($interpreter->full_name, strpos($interpreter->full_name, ' ')+1, 1)),
            'assignment_number' => $request->assignment_number,
            'description_id' => $description_id,
            'date_of_service_provided' => $request->date_of_service_provided,
            'date_of_completion_service' => $request->date_of_completion_service,
            'arrival_time' => $request->arrival_time,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'travel_time_to_assignment' => $request->travel_time_to_assignment,
            'time_back_from_assignment' => $request->time_back_from_assignment,
            'miscellaneous' => $request->miscellaneous,
            'travel_mileage' => $request->travel_mileage,
            'cost_per_mile' => $request->cost_per_mile,
            'total_amount_miles' => $request->total_amount_miles,
            'total_amount_hours' => $request->total_amount_hours,
            'total_interpreter' => $request->total_interpreter,
            'total_coordinator' => $request->total_coordinator,
            'address_id' => $address_id,
            'comments' => $request->comments
        ]);

        // validate if the invoice was created
        if (!$invoice) {
            return response()->json([
                'message' => 'Invoice not created',
                'status' => 'error',
            ], 400);
        }
        // validate if the invoice detail was created
        if (!$invoice->invoiceDetails) {
            return response()->json([
                'message' => 'Invoice detail not created',
                'status' => 'error',
            ], 400);
        }

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
        return response()->json([
            'status' => 'success',
            'invoice' => $invoice,
            'interpreter' => $invoice->interpreter,
            'details' => $invoice->invoiceDetails[0],
            'agency' => $invoice->agency,
            'description' => $invoice->invoiceDetails()->first()->description,
            'address' => $invoice->invoiceDetails()->first()->address,
            'lenguage' => $invoice->interpreter->lenguage,
        ], 201);
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
                //Obtener el interprete que viene en el request
        //Si el valor es null, entonces hay que crearlo
        $interpreter_id = $request->has('interpreter_id') ? $request->interpreter_id : null;
        $address_id = $request->has('address_id') ? $request->address_id : null;
        $description_id = $request->has('description_id') ? $request->description_id : null;

        //FindOrFails interprete

        if (empty($interpreter_id)) {
            $interpreter = Interpreter::create([
                'full_name' => $request->interpreterName,
                'ssn' => $request->interpreterSSN,
                'phone_number' => $request->interpreterPhoneNum,
                'email' => $request->interpreterEmail,
                'address' => $request->interpreterAddress,
                'city' => $request->interpreterCity,
                'state' => $request->interpreterState,
                'zip_code' => $request->interpreterZipCode,
                'lenguage_id' => $request->interpreterLenguageId,
            ]);

            $interpreter_id = $interpreter->id;
        }
        else{
            $interpreter = Interpreter::where('id', $interpreter_id)->first();
        }

        if (empty($address_id)) {
            $validateAddress = Address::where('address', $request->address)
                ->where('city', $request->city)
                ->where('state', $request->state)
                ->where('state_abbr', $request->state_abbr)
                ->where('zip_code', $request->zip_code)
                ->first();

            if($validateAddress != null){
                $address_id = $validateAddress->id;
            }
            else{
                $address = Address::create([
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'state_abbr' => $request->state_abbr,
                    'zip_code' => $request->zip_code,
                ]);
    
                $address_id = $address->id;
            }
        }

        if (empty($description_id)) {
            $description = Description::where('title', $request->description)->first();
            if($description == null){
                $description = Description::create([
                    'title' => $request->description,
                ]);
    
                $description_id = $description->id;
            }
            else{
                $description_id = $description->id;
            }
        }

        $invoice = Invoice::where('id', $invoice->id)->get()->first();
        $invoice->update([
            'user_id' => $request->user_id,
            'agency_id' => $request->agency_id,
            'interpreter_id' => $interpreter_id,
            'coordinator_id' => $request->coordinator_id,
            'total_amount' => $request->total_amount,
        ]);

        $invoiceDetail = InvoiceDetail::where('invoice_id', $invoice->id)->get()->first();
        $invoiceDetail->update([
            'invoice_number' => $request->assignment_number.'-'.strtoupper(substr($interpreter->full_name, 0, 1).substr($interpreter->full_name, strpos($interpreter->full_name, ' ')+1, 1)),
            'assignment_number' => $request->assignment_number,
            'description_id' => $description_id,
            'date_of_service_provided' => $request->date_of_service_provided,
            'date_of_completion_service' => $request->date_of_completion_service,
            'arrival_time' => $request->arrival_time,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'travel_time_to_assignment' => $request->travel_time_to_assignment,
            'time_back_from_assignment' => $request->time_back_from_assignment,
            'miscellaneous' => $request->miscellaneous,
            'travel_mileage' => $request->travel_mileage,
            'cost_per_mile' => $request->cost_per_mile,
            'total_amount_miles' => $request->total_amount_miles,
            'total_amount_hours' => $request->total_amount_hours,
            'total_interpreter' => $request->total_interpreter,
            'total_coordinator' => $request->total_coordinator,
            'address_id' => $address_id,
            'comments' => $request->comments
        ]);

        return response()->json([
            'message' => 'Invoice updated successfully',
            'status' => 'success',
            'invoice' => $invoice->id
        ], 201);
    }

    public function validatePayroll(Invoice $invoice)
    {
        //Find invoice and validate if id_payroll is not null
        
        try {
            $invoiceRequest = Invoice::with('payroll')->where('id', $invoice->id)->first();

            if ($invoiceRequest->payroll_id != null && $invoiceRequest->payroll->status == 'created') {
                return response()->json([
                    'message' => 'Service already has a payroll',
                    'status' => 'error',
                ], 400);
            }
        } catch (\Throwable $th) {
            if ($invoiceRequest->payroll_id != null) {
                return response()->json([
                    'message' => 'Service already has a payroll',
                    'status' => 'error',
                ], 400);
            }
        }


        $invoiceRequest = Invoice::with('request')->where('id', $invoice->id)->first();

        if($invoiceRequest->request_id != null && ($invoiceRequest->request->status == 'completed' || $invoiceRequest->request->status == 'pending')){
            return response()->json([
                'message' => 'Service already has a request',
                'status' => 'error',
            ], 400);
        }
    }

    public function newStatus(Request $request, Invoice $invoice)
    {
        //Find invoice and validate if id_payroll is not null

        $invoiceRequest = Invoice::with('payroll')->where('id', $invoice->id)->first();

        if ($invoice->payroll_id != null && $invoiceRequest->payroll->status == 'created' && $request->status == 'cancelled') {
            return response()->json([
                'message' => 'Service already has a payroll',
                'status' => 'error',
            ], 400);
        }

        $invoiceRequest = Invoice::with('request')->where('id', $invoice->id)->first();

        if($invoiceRequest->request_id != null && ($invoiceRequest->request->status == 'completed' || $invoiceRequest->request->status == 'pending') && $request->status == 'cancelled'){
            return response()->json([
                'message' => 'Service already has a request',
                'status' => 'error',
            ], 400);
        }

        Invoice::where('id', $invoice->id)->update(['status' => $request->status]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
