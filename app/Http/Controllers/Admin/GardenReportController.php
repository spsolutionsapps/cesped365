<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GardenReport;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GardenReportController extends Controller
{
    /**
     * Display a listing of garden reports.
     */
    public function index()
    {
        $reports = GardenReport::with(['user', 'subscription', 'images'])
            ->orderBy('report_date', 'desc')
            ->paginate(15);
        
        return view('admin.garden-reports.index', compact('reports'));
    }

    /**
     * Show the form for creating a new garden report.
     */
    public function create()
    {
        $users = User::where('role', 'client')->get();
        
        return view('admin.garden-reports.create', compact('users'));
    }

    /**
     * Store a newly created garden report.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'report_date' => 'required|date',
            'general_status' => 'required|in:good,regular,improve',
            'grass_even' => 'nullable|boolean',
            'grass_color' => 'required|in:ok,regular,bad',
            'grass_spots' => 'nullable|boolean',
            'worn_areas' => 'nullable|boolean',
            'visible_weeds' => 'nullable|boolean',
            'grass_note' => 'nullable|string',
            'growth_cm' => 'required|numeric|min:0',
            'growth_category' => 'required|in:low,normal,high',
            'growth_estimated' => 'nullable|numeric|min:0',
            'growth_note' => 'nullable|string',
            'soil_condition' => 'required|in:loose,compact',
            'aeration_recommended' => 'nullable|boolean',
            'soil_note' => 'nullable|string',
            'humidity_status' => 'required|in:dry,correct,excess',
            'humidity_note' => 'nullable|string',
            'pests_status' => 'required|in:none,mild,observe',
            'pests_note' => 'nullable|string',
            'flowerbeds_status' => 'required|in:clean,weeds,maintenance',
            'flowerbeds_note' => 'nullable|string',
            'seasonal_recommendations' => 'nullable|string',
            'general_observations' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
        ]);

        // Get active subscription for the user
        $user = User::findOrFail($validated['user_id']);
        $activeSubscription = $user->subscriptions()
            ->where('status', 'active')
            ->latest('end_date')
            ->first();

        if (!$activeSubscription) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['user_id' => 'El usuario seleccionado no tiene una suscripción activa.']);
        }

        // Set subscription_id automatically
        $validated['subscription_id'] = $activeSubscription->id;

        // Set default values for boolean fields
        $validated['grass_even'] = $request->has('grass_even') ? true : false;
        $validated['grass_spots'] = $request->has('grass_spots') ? true : false;
        $validated['worn_areas'] = $request->has('worn_areas') ? true : false;
        $validated['visible_weeds'] = $request->has('visible_weeds') ? true : false;
        $validated['aeration_recommended'] = $request->has('aeration_recommended') ? true : false;

        $report = GardenReport::create($validated);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('garden-reports', 'public');
                $report->images()->create([
                    'image_path' => $path,
                    'image_date' => $validated['report_date'],
                ]);
            }
        }

        return redirect()->route('admin.garden-reports.index')
            ->with('success', 'Reporte creado exitosamente.');
    }

    /**
     * Display the specified garden report.
     */
    public function show(GardenReport $gardenReport)
    {
        $gardenReport->load(['user', 'subscription', 'images']);
        return view('admin.garden-reports.show', compact('gardenReport'));
    }

    /**
     * Show the form for editing the specified garden report.
     */
    public function edit(GardenReport $gardenReport)
    {
        $users = User::where('role', 'client')->get();
        $gardenReport->load('images');
        
        return view('admin.garden-reports.edit', compact('gardenReport', 'users'));
    }

    /**
     * Update the specified garden report.
     */
    public function update(Request $request, GardenReport $gardenReport)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'report_date' => 'required|date',
            'general_status' => 'required|in:good,regular,improve',
            'grass_even' => 'boolean',
            'grass_color' => 'required|in:ok,regular,bad',
            'grass_spots' => 'boolean',
            'worn_areas' => 'boolean',
            'visible_weeds' => 'boolean',
            'grass_note' => 'nullable|string',
            'growth_cm' => 'required|numeric|min:0',
            'growth_category' => 'required|in:low,normal,high',
            'growth_estimated' => 'nullable|numeric|min:0',
            'growth_note' => 'nullable|string',
            'soil_condition' => 'required|in:loose,compact',
            'aeration_recommended' => 'boolean',
            'soil_note' => 'nullable|string',
            'humidity_status' => 'required|in:dry,correct,excess',
            'humidity_note' => 'nullable|string',
            'pests_status' => 'required|in:none,mild,observe',
            'pests_note' => 'nullable|string',
            'flowerbeds_status' => 'required|in:clean,weeds,maintenance',
            'flowerbeds_note' => 'nullable|string',
            'seasonal_recommendations' => 'nullable|string',
            'general_observations' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
        ]);

        // Get active subscription for the user (or keep current if user hasn't changed)
        if ($validated['user_id'] != $gardenReport->user_id) {
            $user = User::findOrFail($validated['user_id']);
            $activeSubscription = $user->subscriptions()
                ->where('status', 'active')
                ->latest('end_date')
                ->first();

            if (!$activeSubscription) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['user_id' => 'El usuario seleccionado no tiene una suscripción activa.']);
            }

            $validated['subscription_id'] = $activeSubscription->id;
        } else {
            // Keep the current subscription if user hasn't changed
            $validated['subscription_id'] = $gardenReport->subscription_id;
        }

        $gardenReport->update($validated);

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('garden-reports', 'public');
                $gardenReport->images()->create([
                    'image_path' => $path,
                    'image_date' => $validated['report_date'],
                ]);
            }
        }

        return redirect()->route('admin.garden-reports.index')
            ->with('success', 'Reporte actualizado exitosamente.');
    }

    /**
     * Remove the specified garden report.
     */
    public function destroy(GardenReport $gardenReport)
    {
        // Delete associated images
        foreach ($gardenReport->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $gardenReport->delete();

        return redirect()->route('admin.garden-reports.index')
            ->with('success', 'Reporte eliminado exitosamente.');
    }
}

