<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\GardenReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
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
     * Show the client dashboard.
     */
    public function index()
    {
        // Si el usuario es admin, redirigir al dashboard de admin
        if(Auth::user()->isAdmin()) {
            return redirect()->route('admin.index');
        }
        
        $user = Auth::user();
        $activeSubscription = $user->subscriptions()
            ->where('status', 'active')
            ->with('plan')
            ->latest()
            ->first();
        
        // Último reporte con imágenes
        $latestReport = $user->gardenReports()
            ->with('images')
            ->latest('report_date')
            ->first();

        // Agrupar reportes por mes para el resumen mensual
        $monthlyReports = $user->gardenReports()
            ->orderBy('report_date', 'desc')
            ->get()
            ->groupBy(function ($report) {
                return Carbon::parse($report->report_date)->format('Y-m');
            })
            ->take(6) // Solo últimos 6 meses
            ->map(function ($monthReports) {
                $lastReport = $monthReports->first();
                return [
                    'year' => Carbon::parse($lastReport->report_date)->year,
                    'month' => Carbon::parse($lastReport->report_date)->month,
                    'month_name' => $this->getMonthName(Carbon::parse($lastReport->report_date)->month),
                    'status' => $lastReport->general_status,
                    'count' => $monthReports->count(),
                ];
            })
            ->values();

        return view('dashboard.client.index', compact('activeSubscription', 'latestReport', 'monthlyReports'));
    }

    /**
     * Show the subscription page.
     */
    public function subscription()
    {
        $user = Auth::user();
        $subscriptions = $user->subscriptions()
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.subscription', compact('subscriptions'));
    }

    /**
     * Show the garden reports page.
     */
    public function gardenReports()
    {
        $user = Auth::user();
        $reports = $user->gardenReports()
            ->with('images')
            ->orderBy('report_date', 'desc')
            ->get();

        return view('dashboard.garden-reports', compact('reports'));
    }
}

