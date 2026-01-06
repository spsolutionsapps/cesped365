<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Plan;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of subscriptions.
     */
    public function index()
    {
        $subscriptions = Subscription::with(['user', 'plan'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    /**
     * Show the form for creating a new subscription.
     */
    public function create()
    {
        $users = User::where('role', 'client')->get();
        $plans = Plan::where('active', true)->get();
        
        return view('admin.subscriptions.create', compact('users', 'plans'));
    }

    /**
     * Store a newly created subscription.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:plans,id',
            'start_date' => 'required|date',
            'status' => 'required|in:active,suspended,expired,cancelled',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = $startDate->copy()->addMonths($plan->duration_months);

        $validated['end_date'] = $endDate->format('Y-m-d');

        Subscription::create($validated);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Suscripción creada exitosamente.');
    }

    /**
     * Show the form for editing the specified subscription.
     */
    public function edit(Subscription $subscription)
    {
        $users = User::where('role', 'client')->get();
        $plans = Plan::where('active', true)->get();
        
        return view('admin.subscriptions.edit', compact('subscription', 'users', 'plans'));
    }

    /**
     * Update the specified subscription.
     */
    public function update(Request $request, Subscription $subscription)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:plans,id',
            'start_date' => 'required|date',
            'status' => 'required|in:active,suspended,expired,cancelled',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = $startDate->copy()->addMonths($plan->duration_months);

        $validated['end_date'] = $endDate->format('Y-m-d');

        $subscription->update($validated);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Suscripción actualizada exitosamente.');
    }

    /**
     * Remove the specified subscription.
     */
    public function destroy(Subscription $subscription)
    {
        $subscription->delete();

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Suscripción eliminada exitosamente.');
    }
}

