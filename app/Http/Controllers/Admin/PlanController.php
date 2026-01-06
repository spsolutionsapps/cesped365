<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of plans.
     */
    public function index()
    {
        $plans = Plan::orderBy('created_at', 'desc')->get();
        return view('admin.plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create()
    {
        return view('admin.plans.create');
    }

    /**
     * Store a newly created plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:monthly,yearly',
            'price' => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:1',
            'active' => 'boolean',
        ]);

        Plan::create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan creado exitosamente.');
    }

    /**
     * Show the form for editing the specified plan.
     */
    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update the specified plan.
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:monthly,yearly',
            'price' => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:1',
            'active' => 'boolean',
        ]);

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan actualizado exitosamente.');
    }

    /**
     * Remove the specified plan.
     */
    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan eliminado exitosamente.');
    }
}

