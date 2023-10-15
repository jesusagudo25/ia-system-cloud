<?php

namespace App\Http\Controllers;

use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;

use App\Models\Address;
use App\Models\BankCheck;
use App\Models\BankCheckPreview;
use App\Models\CheckDetail;
use App\Models\CheckDetailPreview;
use App\Models\Interpreter;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Payroll;
use Carbon\Carbon;
use NumberFormatter;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Request::all()->load('user');
    }


    public function getAbbrState($state)
    {
        $statesUSA = array(
            'Alabama' => 'AL',
            'Alaska' => 'AK',
            'American Samoa' => 'AS',
            'Arizona' => 'AZ',
            'Arkansas' => 'AR',
            'Baker Island' => 'UM-81',
            'California' => 'CA',
            'Colorado' => 'CO',
            'Connecticut' => 'CT',
            'Delaware' => 'DE',
            'District of Columbia' => 'DC',
            'Florida' => 'FL',
            'Georgia' => 'GA',
            'Guam' => 'GU',
            'Hawaii' => 'HI',
            'Howland Island' => 'UM-84',
            'Idaho' => 'ID',
            'Illinois' => 'IL',
            'Indiana' => 'IN',
            'Iowa' => 'IA',
            'Jarvis Island' => 'UM-86',
            'Johnston Atoll' => 'UM-67',
            'Kansas' => 'KS',
            'Kentucky' => 'KY',
            'Kingman Reef' => 'UM-89',
            'Louisiana' => 'LA',
            'Maine' => 'ME',
            'Maryland' => 'MD',
            'Massachusetts' => 'MA',
            'Michigan' => 'MI',
            'Midway Atoll' => 'UM-71',
            'Minnesota' => 'MN',
            'Mississippi' => 'MS',
            'Missouri' => 'MO',
            'Montana' => 'MT',
            'Navassa Island' => 'UM-76',
            'Nebraska' => 'NE',
            'Nevada' => 'NV',
            'New Hampshire' => 'NH',
            'New Jersey' => 'NJ',
            'New Mexico' => 'NM',
            'New York' => 'NY',
            'North Carolina' => 'NC',
            'North Dakota' => 'ND',
            'Northern Mariana Islands' => 'MP',
            'Ohio' => 'OH',
            'Oklahoma' => 'OK',
            'Oregon' => 'OR',
            'Palmyra Atoll' => 'UM-95',
            'Pennsylvania' => 'PA',
            'Puerto Rico' => 'PR',
            'Rhode Island' => 'RI',
            'South Carolina' => 'SC',
            'South Dakota' => 'SD',
            'Tennessee' => 'TN',
            'Texas' => 'TX',
            'United States Minor Outlying Islands' => 'UM',
            'United States Virgin Islands' => 'VI',
            'Utah' => 'UT',
            'Vermont' => 'VT',
            'Virginia' => 'VA',
            'Wake Island' => 'UM-79',
            'Washington' => 'WA',
            'West Virginia' => 'WV',
            'Wisconsin' => 'WI',
            'Wyoming' => 'WY'
        );

        return $statesUSA[$state];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HttpRequest $request)
    {
        /* Se debe trabajar en base a la lista de servicios seleccionados por el usuario */

        /* Se obtiene el total de la planilla para asignarlo al payroll */
        $total = Invoice::whereIn('invoices.id', $request->services)
            ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->selectRaw('sum(invoices.total_amount) as total_amount')
            ->get();

        //get last request id
        $lastRequest = Request::orderBy('id', 'desc')->first();

        //create suffix_id
        $suffix_id = 'PR-' . str_pad($lastRequest->id + 1, 5, '0', STR_PAD_LEFT);

        $requestPayroll = Request::create([
            'user_id' => $request->user_id,
            'suffix_id' => $suffix_id,
            'month' => Carbon::parse($request->start_date)->format('F'),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_amount' => $total[0]['total_amount'] ? $total[0]['total_amount'] : 0,
        ]);

        Invoice::whereIn('id', $request->services)->update(['request_id' => $requestPayroll->id]);

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

            $bankCheck = BankCheckPreview::create([
                'request_id' => $requestPayroll->id,
                'date' => Carbon::now(),
                'amount' => $interpreter->total_amount,
                'amount_in_words' => $amountInWords,
                'ssn' => $interpreter->ssn,
                'pay_to' => $interpreter->full_name,
                'for' => 'Interpreting services provided from ' . Carbon::parse($request->start_date)->format('m-d-Y') . ' to ' . Carbon::parse($request->end_date)->format('m-d-Y'),
                'address' => $interpreter->address,
                'city' => $interpreter->city,
                'state' => RequestController::getAbbrState($interpreter->state),
                'zip' => $interpreter->zip_code,
            ]);

            foreach ($interpreter->details as $detail) {
                //Find address of the service detail
                $address = Address::where('id', $detail->address_id)->first();
                CheckDetailPreview::create([
                    'bank_check_preview_id' => $bankCheck->id,
                    'assignment_number' => $detail->assignment_number,
                    'date_of_service_provided' => $detail->date_of_service_provided,
                    'location' => $address->address . ', ' . $address->city . ', ' . $address->state . ', ' . $address->zip_code,
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
            $bankCheck = BankCheckPreview::create([
                'request_id' => $requestPayroll->id,
                'date' => Carbon::now(),
                'amount' => $coordinator->total_amount,
                'amount_in_words' => $amountInWords,
                'ssn' => $coordinator->ssn, // '123-45-6789
                'pay_to' => $coordinator->full_name,
                'for' => 'Interpreting services provided from ' . Carbon::parse($request->start_date)->format('m-d-Y') . ' to ' . Carbon::parse($request->end_date)->format('m-d-Y'),
                'address' => $coordinator->address,
                'city' => $coordinator->city,
                'state' => RequestController::getAbbrState($coordinator->state),
                'zip' => $coordinator->zip_code,
            ]);

            foreach ($coordinator->details as $detail) {
                $address = Address::where('id', $detail->address_id)->first();
                CheckDetailPreview::create([
                    'bank_check_preview_id' => $bankCheck->id,
                    'assignment_number' => $detail->assignment_number,
                    'date_of_service_provided' => $detail->date_of_service_provided,
                    'location' => $address->address . ', ' . $address->city . ', ' . $address->state . ', ' . $address->zip_code,
                    'total_amount' => $detail->total_coordinator,
                ]);
            }
        }

        return response()->json([
            'message' => 'Request created successfully',
            'success' => true,
            'request' => $requestPayroll
        ], 201);
    }

    public function review($startDate, $endDate)
    {

        /* Se debe validar que el rango de fecha sea entre 1 - 15 */

        /* Se consulta a la BD la lista de servicios que llevan mas de 30 dias y no se les ha pagado...
            **De lo que puedo analizar, en ningun caso se estaria pagando dentro de la quincena legalmente, sino siempre se
            pagan servicios atrazados.
        */

        $review = Invoice::whereIn('invoices.status', ['paid', 'pending'])
            ->where('invoices.request_id', null)
            ->where('invoices.payroll_id', null)
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
            'startDate' => $startDate,
            'endDate' => $endDate,
            'success' => true,
            'review' => $review
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        //
    }

    public function pdf(Request $request)
    {
        //Si el rango de fecha corresponde a una quincena o mes
        $dateTimeStart = new \DateTime($request->start_date);
        $dateTimeEnd = new \DateTime($request->end_date);
        $interval = $dateTimeStart->diff($dateTimeEnd);
        $days = $interval->format('%a');

        if ($days <= 15) {
            $interpreters = Invoice::where('invoices.request_id', $request->id)
                ->whereIn('invoices.status', ['paid', 'pending'])
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('addresses', 'invoice_details.address_id', '=', 'addresses.id')
                ->join('interpreters', 'invoices.interpreter_id', '=', 'interpreters.id')
                ->select('interpreters.*', 'addresses.address as locationAddress', 'addresses.city as locationCity', 'addresses.state as locationState', 'addresses.zip_code as locationZipCode', 'invoice_details.*', 'invoices.*')
                ->get()
                ->groupBy('interpreter_id');

            $coordinator = Invoice::where('invoices.request_id', $request->id)
                ->whereIn('invoices.status', ['paid', 'pending'])
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('addresses', 'invoice_details.address_id', '=', 'addresses.id')
                ->join('coordinators', 'invoices.coordinator_id', '=', 'coordinators.id')
                ->select('coordinators.*', 'addresses.address as locationAddress', 'addresses.city as locationCity', 'addresses.state as locationState', 'addresses.zip_code as locationZipCode', 'invoice_details.*', 'invoices.*')
                ->get();

            // find services by miscellaneous != 0
            $miscellaneous = Invoice::where('invoices.request_id', $request->id)
                ->whereIn('invoices.status', ['paid', 'pending'])
                ->where('invoice_details.miscellaneous', '!=', 0)
                ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                ->join('addresses', 'invoice_details.address_id', '=', 'addresses.id')
                ->join('coordinators', 'invoices.coordinator_id', '=', 'coordinators.id')
                ->select('coordinators.*', 'addresses.address as locationAddress', 'addresses.city as locationCity', 'addresses.state as locationState', 'addresses.zip_code as locationZipCode', 'invoice_details.*', 'invoices.*')
                ->get();


            $pdf = \PDF::loadView('pdf.fortnightly', [
                'payroll' => $request, //payroll for no change the view
                'interpreters' => array_values($interpreters->toArray()),
                'coordinator' => $coordinator,
                'miscellaneous' => $miscellaneous,
                'preview' => true
            ]);
            return $pdf->stream('fortnightly.pdf');
        } else {
            return response()->json([
                'message' => 'El rango de fecha no corresponde a una quincena',
                'success' => false,
            ], 201);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HttpRequest $request, Request $requestUpdate)
    {
        //
    }

    public function newStatus(HttpRequest $request, Request $requestUpdate)
    {
        /*transfers data from request to payroll
            List table from:
                -requests
                -bank_check_previews
                -check_detail_previews

            List table to:
                - bank_checks
                - bank_check_details
                - payrolls
        */

        if ($request->status == 'completed') {
            //search if exist a payroll with the same request_id
            $payroll = Payroll::where('request_id', $requestUpdate->id)->first();

            if ($payroll) {
                return response()->json([
                    'message' => 'Request already has a payroll',
                    'success' => false,
                ], 201);
            }

            $requestPayroll = Request::where('id', $requestUpdate->id)->first();

            //get last payroll id
            $lastPayroll = Payroll::orderBy('id', 'desc')->first();

            //create suffix_id
            $suffix_id = 'P-' . str_pad($lastPayroll->id + 1, 5, '0', STR_PAD_LEFT);

            $payroll = Payroll::create([
                'user_id' => $requestPayroll->user_id,
                'suffix_id' => $suffix_id,
                'request_id' => $requestPayroll->id, // '123-45-6789
                'month' => $requestPayroll->month,
                'start_date' => $requestPayroll->start_date,
                'end_date' => $requestPayroll->end_date,
                'total_amount' => $requestPayroll->total_amount,
            ]);

            //search services by request_id from invoices table

            $invoices = Invoice::where('request_id', $requestPayroll->id)->get();

            foreach ($invoices as $invoice) {
                $invoice = Invoice::where('id', $invoice->id)->update(['payroll_id' => $payroll->id]);
            }

            //search bank_check_previews by request_id from bank_check_previews table

            $bankChecks = BankCheckPreview::where('request_id', $requestPayroll->id)->get();

            foreach ($bankChecks as $bankCheck) {
                $bankCheck = BankCheck::create([
                    'payroll_id' => $payroll->id,
                    'date' => $bankCheck->date,
                    'amount' => $bankCheck->amount,
                    'amount_in_words' => $bankCheck->amount_in_words,
                    'ssn' => $bankCheck->ssn,
                    'pay_to' => $bankCheck->pay_to,
                    'for' => $bankCheck->for,
                    'address' => $bankCheck->address,
                    'city' => $bankCheck->city,
                    'state' => $bankCheck->state,
                    'zip' => $bankCheck->zip,
                ]);

                $checkDetails = CheckDetailPreview::where('bank_check_preview_id', $bankCheck->id)->get();

                foreach ($checkDetails as $checkDetail) {
                    $checkDetail = CheckDetail::create([
                        'bank_check_id' => $bankCheck->id,
                        'assignment_number' => $checkDetail->assignment_number,
                        'date_of_service_provided' => $checkDetail->date_of_service_provided,
                        'location' => $checkDetail->location,
                        'total_amount' => $checkDetail->total_amount,
                    ]);
                }
            }
        }

        if($request->status == 'cancelled'){
            //search if exist a payroll with the same request_id
            $payroll = Payroll::where('request_id', $requestUpdate->id)->first();

            if ($payroll) {
                return response()->json([
                    'message' => 'Request already has a payroll',
                    'success' => false,
                ], 201);
            }

            //search services by request_id from invoices table and update request_id to null
            Invoice::where('request_id', $requestUpdate->id)->update(['request_id' => null]);
        }

        Request::where('id', $requestUpdate->id)->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Request updated successfully',
            'success' => true,
            'request' => $requestUpdate
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        //
    }
}
