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
        // #region agent log
        $logPath = base_path('.cursor/debug.log');
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'DashboardController.php:29','message'=>'DashboardController index() entry','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
        // #endregion
        try {
            // #region agent log
            $authCheck = Auth::check();
            $authUser = Auth::user();
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'DashboardController.php:34','message'=>'Auth check before isAdmin()','data'=>['auth_check'=>$authCheck,'user_id'=>$authUser?->id,'user_email'=>$authUser?->email],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            // Si el usuario es admin, redirigir al dashboard de admin
            if($authUser && $authUser->isAdmin()) {
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'DashboardController.php:37','message'=>'User is admin, redirecting','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
                return redirect()->route('admin.index');
            }
            
            $user = $authUser;
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C','location'=>'DashboardController.php:42','message'=>'Before subscriptions query','data'=>['user_id'=>$user?->id],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            $activeSubscription = $user->subscriptions()
                ->where('status', 'active')
                ->with('plan')
                ->latest()
                ->first();
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C','location'=>'DashboardController.php:48','message'=>'After subscriptions query','data'=>['subscription_id'=>$activeSubscription?->id],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            
            // Último reporte con imágenes
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C','location'=>'DashboardController.php:51','message'=>'Before gardenReports query','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            $latestReport = $user->gardenReports()
                ->with('images')
                ->latest('report_date')
                ->first();
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C','location'=>'DashboardController.php:56','message'=>'After gardenReports query','data'=>['report_id'=>$latestReport?->id],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion

            // Agrupar reportes por mes para el resumen mensual
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C','location'=>'DashboardController.php:60','message'=>'Before monthlyReports query','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
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
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'C','location'=>'DashboardController.php:78','message'=>'After monthlyReports processing','data'=>['count'=>$monthlyReports->count()],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion

            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'DashboardController.php:81','message'=>'Returning view','data'=>[],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            return view('dashboard.client.index', compact('activeSubscription', 'latestReport', 'monthlyReports'));
        } catch (\Exception $e) {
            // #region agent log
            file_put_contents($logPath, json_encode(['sessionId'=>'debug-session','runId'=>'run1','hypothesisId'=>'B','location'=>'DashboardController.php:85','message'=>'Exception in index()','data'=>['error'=>$e->getMessage(),'file'=>$e->getFile(),'line'=>$e->getLine()],'timestamp'=>time()*1000])."\n", FILE_APPEND);
            // #endregion
            throw $e;
        }
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

