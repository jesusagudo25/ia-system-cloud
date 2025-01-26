<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Coordinator;
use PDF;
use DateTime;
use DatePeriod;
use DateInterval;
use Carbon\Carbon;
use App\Models\Report;
use App\Models\Invoice;
use App\Models\Interpreter;
use App\Models\Lenguage;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Report::all()->load('user');
    }

    public function indexDashboards()
    {
        /* Get total agencies */
        $totalAgencies = Agency::count();

        /* Get total interpreters */
        $totalInterpreters = Interpreter::count();

        /* get current monthly income (coordinators) and expenses (interpreters) */

        $start_date = Carbon::now()->startOfMonth();
        $end_date = Carbon::now()->endOfMonth();

        $totalCoordinator = Payroll::whereBetween('payrolls.created_at', [$start_date, $end_date])
            ->join('invoices', 'payrolls.id', '=', 'invoices.payroll_id')
            ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->sum('invoice_details.total_coordinator');

        //15 (AnnaBelle Tomlinson)
        $totalInterpreter = Payroll::whereBetween('payrolls.created_at', [$start_date, $end_date])
            ->join('invoices', 'payrolls.id', '=', 'invoices.payroll_id')
            ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->whereIn('invoices.interpreter_id', [15])
            ->sum('invoice_details.total_interpreter');

        $totalIncome = $totalCoordinator + $totalInterpreter;

        $totalExpenses = Payroll::whereBetween('payrolls.created_at', [$start_date, $end_date])
            ->join('invoices', 'payrolls.id', '=', 'invoices.payroll_id')
            ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->whereNotIn('invoices.interpreter_id', [15])
            ->sum('invoice_details.total_interpreter');
        return [
            'total_agencies' => $totalAgencies,
            'total_interpreters' => $totalInterpreters,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return Report::create($request->all());
    }

    /**
     * Generate a report based on the filters and store it in a file.
     */

    public function generate(Request $request)
    {
        $params = [];
        //Report information
        $reportId = $request->report_id;
        $userId = $request->user_id;

        //Filter information
        $filters = $request->filters;
        $startDate = isset($filters['start_date']) ? Carbon::parse($filters['start_date']) : null;
        $endDate = isset($filters['end_date']) ? Carbon::parse($filters['end_date']) : null;
        $typeOfPerson = $filters['type_of_person'] ?? null;
        $nameOfinterpreter = $filters['name_of_interpreter'] ?? null;
        $languageId = $filters['language_id'] ?? null;

        //Get Models
        $report = Report::find($reportId);
        $user = User::find($userId);

        $params = [
            'report' => $report,
            'user' => $user,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'typeOfPerson' => $typeOfPerson,
            'language' => $languageId,
            'interpreter' => $nameOfinterpreter,
        ];

        /* 
            1- Monthly Service Report
            2- Annual Service Report
            3- Cumulative Service Report by Year
            4- Monthly Service Report by Payment Period
        */
        return match ($reportId) {
            1 => $this->monthlyServiceReport($params),
            2 => $this->annualServiceReport($params),
            3 => $this->cumulativeServiceReport($params),
            4 => $this->monthlyServiceReportByPaymentPeriod($params),
            default => response()->json(['error' => 'Case not found'], 404),
        };
    }

    /**
     * Generate a report based on the filters and store it in a file.
     */

    public function monthlyServiceReport(array $params = [])
    {
        DB::enableQueryLog(); // Activa el registro de consultas

        $report = [
            'title' => $params['report']->title,
            'start_date' => $params['startDate'],
            'end_date' => $params['endDate'],
        ];

        // Filtros dinámicos
        $interpretersQuery = Interpreter::query()->where('status', 1);
        $coordinatorsQuery = Coordinator::query()->where('status', 1);

        if ($params['interpreter']) {
            $interpretersQuery->where('full_name', 'like', '%' . $params['interpreter'] . '%');
        }

        if ($params['language']) {
            $interpretersQuery->where('lenguage_id', $params['language']);
        }

        switch ($params['typeOfPerson']) {
            case 'All':
                $interpreters = $interpretersQuery->get();
                $coordinators = $coordinatorsQuery->get();
                break;
            case 'Interpreter':
                $interpreters = $interpretersQuery->get();
                $coordinators = collect();
                break;
            case 'Coordinator':
                $coordinators = $coordinatorsQuery->get();
                $interpreters = collect();
                break;
            default:
                $interpreters = collect();
                $coordinators = collect();
                break;
        }

        $months = ReportController::getMeses($params['startDate'], $params['endDate']);

        //Debe haber minimamente un mes y maximo 12 meses
        if (count($months) < 1 || count($months) > 13) {
            return response()->json(['status' => 'error', 'message' => 'Invalid date range'], 400);
        }

        /* Get reports interpreters */
        $reportsInterpreters = [];

        foreach ($interpreters as $interpreter) {
            $row = [
                'name' => $interpreter->full_name,
                'ssn' => $interpreter->ssn,
                'address' => $interpreter->address,
                'city' => $interpreter->city,
                'state' => $interpreter->state,
                'zip_code' => $interpreter->zip_code,
            ];

            $total = 0;
            
            foreach ($months as $month) {
                $range = ReportController::startAndEndDateForMonth(explode('_', $month)[0], explode('_', $month)[1]);
                $start_date = $range['start_date'];
                $end_date = $range['end_date'];

                //Search invoices from interpreter, with payroll_id related
                $details = Invoice::where('interpreter_id', $interpreter->id)
                    ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                    ->join('payrolls', 'invoices.payroll_id', '=', 'payrolls.id')
                    ->whereBetween('payrolls.start_date', [$start_date, $end_date]) // Filtra por el periodo de pago
                    ->select('invoice_details.total_interpreter'); // Selecciona solo la columna necesaria

                $salary = $details->sum('total_interpreter');
                $total += $salary;
                $row[$month] = $salary ? number_format($salary, 2) : '0.00';
            }
            
            
            $row['total'] = $total ? number_format($total, 2) : '0.00';
            $reportsInterpreters[] = $row;
        }

        /* Get reports coordinators */

        $reportsCoordinators = [];

        foreach ($coordinators as $coordinator) {
            $row = [
                'name' => $coordinator->full_name,
                'ssn' => $coordinator->ssn,
                'address' => $coordinator->address,
                'city' => $coordinator->city,
                'state' => $coordinator->state,
                'zip_code' => $coordinator->zip_code,
            ];

            $total = 0;
            foreach ($months as $month) {
                $range = ReportController::startAndEndDateForMonth(explode('_', $month)[0], explode('_', $month)[1]);
                $start_date = $range['start_date'];
                $end_date = $range['end_date'];

                //Search invoices from coordinator, with payroll_id related
                $details = Invoice::where('coordinator_id', $coordinator->id)
                    ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                    ->join('payrolls', 'invoices.payroll_id', '=', 'payrolls.id')
                    ->whereBetween('payrolls.start_date', [$start_date, $end_date]) // Filtra por el periodo de pago
                    ->select('invoice_details.total_coordinator'); // Selecciona solo la columna necesaria

                $salary = $details->sum('total_coordinator');
                $total += $salary;
                $row[$month] = $salary ? number_format($salary, 2) : '0.00';
            }
            $row['total'] = $total ? number_format($total, 2) : '0.00';
            $reportsCoordinators[] = $row;
        }
        Log::debug(DB::getQueryLog()); // Registra las consultas ejecutadas en el archivo de logs

        //Validate if invoice_details is empty
        if (empty($reportsInterpreters) && empty($reportsCoordinators)) {
            return response()->json(['status' => 'error', 'message' => 'No data found'], 404);
        }

        // Generate PDF AND SAVE IT
        $pdf = PDF::loadView('pdf.monthly', compact('report', 'reportsCoordinators', 'reportsInterpreters', 'months'));
        $file_name = 'monthly_service_report_' . Carbon::parse($params['startDate'])->format('m-d-Y') . '_' . Carbon::parse($params['endDate'])->format('m-d-Y') . '.pdf';
        $pdf->save(storage_path('app/public/reports/' . $file_name));

        return response()->json([
            'file_name' => $file_name,
            'file_path' => url('api/reports/' . $file_name.'/download'),
            'status' => 'success',
            'message' => 'Report generated successfully',
        ]);

    }

    public function annualServiceReport(array $params = [])
    {
        $report = [
            'title' => $params['report']->title,
            'start_date' => $params['startDate'],
            'end_date' => $params['endDate'],
        ];

        $year = Carbon::parse($params['startDate'])->format('Y');

        // Filtros dinámicos
        $interpretersQuery = Interpreter::query()->where('status', 1);
        $coordinatorsQuery = Coordinator::query()->where('status', 1);

        if ($params['interpreter']) {
            $interpretersQuery->where('full_name', 'like', '%' . $params['interpreter'] . '%');
        }

        if ($params['language']) {
            $interpretersQuery->where('lenguage_id', $params['language']);
        }

        switch ($params['typeOfPerson']) {
            case 'All':
                $interpreters = $interpretersQuery->get();
                $coordinators = $coordinatorsQuery->get();
                break;
            case 'Interpreter':
                $interpreters = $interpretersQuery->get();
                $coordinators = collect();
                break;
            case 'Coordinator':
                $coordinators = $coordinatorsQuery->get();
                $interpreters = collect();
                break;
            default:
                $interpreters = collect();
                $coordinators = collect();
                break;
        }

        $years = ReportController::getYears($params['startDate'], $params['endDate']);

        /* Get reports interpreters */
        $reportsInterpreters = [];

        foreach ($years as $year) {
            foreach ($interpreters as $interpreter) {
                $row = [
                    'name' => $interpreter->full_name,
                    'ssn' => $interpreter->ssn,
                    'address' => $interpreter->address,
                    'city' => $interpreter->city,
                    'state' => $interpreter->state,
                    'zip_code' => $interpreter->zip_code,
                ];

                $range = ReportController::startAndEndDateForYear($year);
                $start_date = $range['start_date'];
                $end_date = $range['end_date'];

                //Search invoices from interpreter, with payroll_id related
                $details = Invoice::where('interpreter_id', $interpreter->id)
                    ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                    ->join('payrolls', 'invoices.payroll_id', '=', 'payrolls.id')
                    ->whereBetween('payrolls.start_date', [$start_date, $end_date]) // Filtra por el periodo de pago
                    ->select('invoice_details.total_interpreter'); // Selecciona solo la columna necesaria

                $salary = $details->sum('total_interpreter');
                $row['total'] = $salary ? number_format($salary,2) : '0.00';
                $reportsInterpreters[$year]['interpreters'][] = $row;
            }
        }

        /* Get reports coordinators */

        $reportsCoordinators = [];

        foreach ($years as $year) {
            foreach ($coordinators as $coordinator) {
                $row = [
                    'name' => $coordinator->full_name,
                    'ssn' => $coordinator->ssn,
                    'address' => $coordinator->address,
                    'city' => $coordinator->city,
                    'state' => $coordinator->state,
                    'zip_code' => $coordinator->zip_code,
                ];

                $range = ReportController::startAndEndDateForYear($year);
                $start_date = $range['start_date'];
                $end_date = $range['end_date'];

                //Search invoices from coordinator, with payroll_id related
                $details = Invoice::where('coordinator_id', $coordinator->id)
                    ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
                    ->join('payrolls', 'invoices.payroll_id', '=', 'payrolls.id')
                    ->whereBetween('payrolls.start_date', [$start_date, $end_date]) // Filtra por el periodo de pago
                    ->select('invoice_details.total_coordinator'); // Selecciona solo la columna necesaria

                $salary = $details->sum('total_coordinator');
                $row['total'] = $salary ? number_format($salary,2) : '0.00';
                $reportsCoordinators[$year]['coordinators'][] = $row;
            }
        }

        Log::debug(DB::getQueryLog()); // Registra las consultas ejecutadas en el archivo de logs

        //Validate if invoice_details is empty
        if (empty($reportsInterpreters) && empty($reportsCoordinators)) {
            return response()->json(['status' => 'error', 'message' => 'No data found'], 404);
        }

        // Generate PDF AND SAVE IT
        $pdf = PDF::loadView('pdf.annual', compact('report', 'reportsCoordinators', 'reportsInterpreters', 'years'));
        $file_name = 'annual_service_report_' . Carbon::parse($params['startDate'])->format('m-d-Y') . '_' . Carbon::parse($params['endDate'])->format('m-d-Y') . '.pdf';
        $pdf->save(storage_path('app/public/reports/' . $file_name));

        return response()->json([
            'file_name' => $file_name,
            'file_path' => url('api/reports/' . $file_name.'/download'),
            'status' => 'success',
            'message' => 'Report generated successfully',
        ]);


        //$pdf = \PDF::loadView('pdf.annual', compact('report', 'reportsCoordinators', 'reportsInterpreters', 'years'));
    }

    public function cumulativeServiceReport(array $params = []) {}

    public function monthlyServiceReportByPaymentPeriod(array $params = []) {}

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        //
    }

    public function pdf($file_name)
    {
        //Search file name in storage, and return it
        $path = storage_path('app/public/reports/' . $file_name);
        return response()->file($path)->deleteFileAfterSend(true);
    }

    public function getMeses($fechaInicio, $fechaFin)
    {
        $meses = [];
        $inicio = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);
        $fin->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($inicio, $interval, $fin);

        foreach ($period as $mes) {
            $meses[] = $mes->format('M_Y');
        }

        return $meses;
    }

    public function getYears($fechaInicio, $fechaFin)
    {
        /* Obtener los años del intervalo */
        $years = [];
        $inicio = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);
        $fin->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 year');
        $period = new DatePeriod($inicio, $interval, $fin);

        foreach ($period as $year) {
            $years[] = $year->format('Y');
        }

        return $years;
    }

    public function startAndEndDateForMonth($month, $year)
    {
        $start_date = date('Y-m-d', strtotime("first day of $month $year"));
        $end_date = date('Y-m-d', strtotime("last day of $month $year"));

        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];
    }

    public function startAndEndDateForYear($year)
    {
        $start_date = date('Y-m-d', strtotime("first day of January $year"));
        $end_date = date('Y-m-d', strtotime("last day of December $year"));

        return [
            'start_date' => $start_date,
            'end_date' => $end_date,
        ];
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
