<?php

namespace App\Http\Controllers;

use App\Models\GardenReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClientReportsController extends Controller
{
    /**
     * Nombres de meses en español
     */
    private function getMonthName($month)
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $months[$month] ?? '';
    }

    /**
     * Listado de meses con reportes (agrupados por año/mes)
     */
    public function index()
    {
        $user = Auth::user();
        
        // Obtener todos los reportes del usuario agrupados por año y mes
        $reports = $user->gardenReports()
            ->orderBy('report_date', 'desc')
            ->get()
            ->groupBy(function ($report) {
                return Carbon::parse($report->report_date)->format('Y-m');
            })
            ->map(function ($monthReports) {
                // Usar el último reporte del mes como referencia del estado
                $lastReport = $monthReports->first();
                return [
                    'year' => Carbon::parse($lastReport->report_date)->year,
                    'month' => Carbon::parse($lastReport->report_date)->month,
                    'month_name' => $this->getMonthName(Carbon::parse($lastReport->report_date)->month),
                    'status' => $lastReport->general_status,
                    'count' => $monthReports->count(),
                    'last_date' => $lastReport->report_date,
                ];
            })
            ->values();

        return view('dashboard.client.reports', compact('reports'));
    }

    /**
     * Detalle de un mes específico
     */
    public function month($year, $month)
    {
        $user = Auth::user();
        
        // Validar que el mes y año sean válidos
        $year = (int) $year;
        $month = (int) $month;
        
        if (!checkdate($month, 1, $year)) {
            abort(404);
        }

        $reports = $user->gardenReports()
            ->whereYear('report_date', $year)
            ->whereMonth('report_date', $month)
            ->orderBy('report_date', 'desc')
            ->get();

        if ($reports->isEmpty()) {
            abort(404);
        }

        $monthName = $this->getMonthName($month);

        return view('dashboard.client.month', compact('reports', 'year', 'month', 'monthName'));
    }

    /**
     * Mostrar un reporte completo
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $report = GardenReport::where('user_id', $user->id)
            ->with(['images', 'subscription.plan'])
            ->findOrFail($id);

        return view('dashboard.client.show', compact('report'));
    }
}

