<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\Payment;
use App\Models\GardenReport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Show admin dashboard.
     */
    public function index()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $next7Days = $now->copy()->addDays(7);
        $next30Days = $now->copy()->addDays(30);

        // Métricas básicas de usuarios
        $totalUsers = User::where('role', 'client')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $newUsersThisMonth = User::where('role', 'client')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Métricas de suscripciones
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $expiringSoon = Subscription::where('status', 'active')
            ->whereBetween('end_date', [$now, $next30Days])
            ->count();
        $expiringUrgent = Subscription::where('status', 'active')
            ->whereBetween('end_date', [$now, $next7Days])
            ->count();
        $recentlyExpired = Subscription::where('status', 'expired')
            ->whereBetween('end_date', [$now->copy()->subDays(30), $now])
            ->count();

        // Métricas financieras del mes actual
        $monthlyRevenue = Payment::where('status', 'approved')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Ganancias por plan del mes actual
        $revenueByPlan = Payment::where('status', 'approved')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->with('subscription.plan')
            ->get()
            ->groupBy(function ($payment) {
                return $payment->subscription->plan->name ?? 'Sin plan';
            })
            ->map(function ($payments) {
                return $payments->sum('amount');
            });

        // Pagos pendientes
        $pendingPayments = Payment::where('status', 'pending')->count();
        $pendingPaymentsAmount = Payment::where('status', 'pending')->sum('amount');

        // Ingresos Recurrentes Mensuales (MRR)
        $mrr = Subscription::where('status', 'active')
            ->with('plan')
            ->get()
            ->sum(function ($subscription) {
                $plan = $subscription->plan;
                if ($plan && $plan->duration_months > 0) {
                    return $plan->price / $plan->duration_months;
                }
                return 0;
            });

        // Métricas de reportes
        $totalReports = GardenReport::count();

        $stats = [
            // Métricas básicas
            'total_users' => $totalUsers,
            'total_admins' => $totalAdmins,
            'active_subscriptions' => $activeSubscriptions,
            'total_reports' => $totalReports,
            
            // Métricas financieras
            'monthly_revenue' => $monthlyRevenue,
            'revenue_by_plan' => $revenueByPlan,
            'pending_payments' => $pendingPayments,
            'pending_payments_amount' => $pendingPaymentsAmount,
            'mrr' => $mrr,
            
            // Métricas de usuarios
            'new_users_this_month' => $newUsersThisMonth,
            
            // Métricas de suscripciones
            'expiring_soon' => $expiringSoon,
            'expiring_urgent' => $expiringUrgent,
            'recently_expired' => $recentlyExpired,
        ];

        return response()
            ->view('admin.index', compact('stats'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}

