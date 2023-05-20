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

    public function indexDashboards()
    {
        /* Get total agencies */
        $totalAgencies = Agency::count();

        /* Get total interpreters */
        $totalInterpreters = Interpreter::count();

        /* get curent monthly income (coordinators) and expenses (interpreters) */

        $currentMonth = date('m');
        $currentYear = date('Y');
        $start_date = date('Y-m-d', strtotime("first day of $currentMonth $currentYear"));
        $end_date = date('Y-m-d', strtotime("last day of $currentMonth $currentYear"));
        $invoices = Invoice::whereBetween('updated_at', [$start_date, $end_date])->get()->load('invoiceDetails')->where('payroll_id', '!=', null)->pluck('invoiceDetails')->flatten();
        $totalIncome = $invoices->sum('total_coordinator');
        $totalExpenses = $invoices->sum('total_interpreter');

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
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        //
    }

    public function pdf(Report $report)
    {
        if ($report->type == 'a') {
            $year = Carbon::parse($report->start_date)->format('Y');

            $interpreters = Interpreter::with(['invoices' => function ($query) use ($report) {
                $query->whereBetween('invoices.updated_at', [$report->start_date, $report->end_date]);
            }])->get();

            $coordinators = Coordinator::with(['invoices' => function ($query) use ($report) {
                $query->whereBetween('invoices.updated_at', [$report->start_date, $report->end_date]);
            }])->get();

            $years = ReportController::getYears($report->start_date, $report->end_date);

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
                    $details = $interpreter->invoices
                        ->load('invoiceDetails')
                        ->whereBetween('updated_at', [$start_date, $end_date])
                        ->where('payroll_id', '!=', null)
                        ->pluck('invoiceDetails')
                        ->flatten();
                    $salary = $details->sum('total_interpreter');
                    $row['total'] = $salary ? $salary : '0.00';
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
                    $details = $coordinator->invoices
                        ->load('invoiceDetails')
                        ->whereBetween('updated_at', [$start_date, $end_date])
                        ->where('payroll_id', '!=', null)
                        ->pluck('invoiceDetails')
                        ->flatten();
                    $salary = $details->sum('total_coordinator');
                    $row['total'] = $salary ? $salary : '0.00';
                    $reportsCoordinators[$year]['coordinators'][] = $row;
                }
            }


            $pdf = \PDF::loadView('pdf.annual', compact('report', 'reportsCoordinators', 'reportsInterpreters', 'years'));

            return $pdf->stream('annual.pdf');
        } else {
            $year = Carbon::parse($report->start_date)->format('Y');

            $interpreters = Interpreter::with(['invoices' => function ($query) use ($report) {
                $query->whereBetween('invoices.updated_at', [$report->start_date, $report->end_date]);
            }])->get();

            $coordinators = Coordinator::with(['invoices' => function ($query) use ($report) {
                $query->whereBetween('invoices.updated_at', [$report->start_date, $report->end_date]);
            }])->get();

            $months = ReportController::getMeses($report->start_date, $report->end_date);

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
                    $range = ReportController::startAndEndDateForMonth($month, $year);
                    $start_date = $range['start_date'];
                    $end_date = $range['end_date'];
                    $details = $interpreter->invoices
                        ->load('invoiceDetails')
                        ->whereBetween('updated_at', [$start_date, $end_date])
                        ->where('payroll_id', '!=', null)
                        ->pluck('invoiceDetails')
                        ->flatten();

                    $salary = $details->sum('total_interpreter');
                    $total += $salary;
                    $row[$month] = $salary ? $salary : '0.00';
                }
                $row['total'] = $total ? $total : '0.00';
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
                    $range = ReportController::startAndEndDateForMonth($month, $year);
                    $start_date = $range['start_date'];
                    $end_date = $range['end_date'];
                    $details = $coordinator->invoices
                        ->load('invoiceDetails')
                        ->whereBetween('updated_at', [$start_date, $end_date])
                        ->where('payroll_id', '!=', null)
                        ->pluck('invoiceDetails')
                        ->flatten();
                    $salary = $details->sum('total_coordinator');
                    $total += $salary;
                    $row[$month] = $salary ? $salary : '0.00';
                }
                $row['total'] = $total ? $total : '0.00';
                $reportsCoordinators[] = $row;
            }

            $pdf = \PDF::loadView('pdf.monthly', compact('report', 'reportsCoordinators', 'reportsInterpreters', 'months'));

            return $pdf->stream('monthly.pdf');
        }
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
            $meses[] = $mes->format('F');
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
