<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GardenReport;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class GardenReportController extends Controller
{
    /**
     * Display a listing of garden reports.
     */
    public function index(Request $request)
    {
        $query = GardenReport::with(['user', 'subscription', 'images']);

        // Filtro por usuario
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtro por fecha desde
        if ($request->filled('date_from')) {
            $query->whereDate('report_date', '>=', $request->date_from);
        }

        // Filtro por fecha hasta
        if ($request->filled('date_to')) {
            $query->whereDate('report_date', '<=', $request->date_to);
        }

        $reports = $query->orderBy('report_date', 'desc')->paginate(15);

        // Obtener usuarios para el filtro
        $users = User::where('role', 'client')->orderBy('name')->get();

        return view('admin.garden-reports.index', compact('reports', 'users'));
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
            // Prefer saving under /public/storage so Apache can serve files without relying on symlinks.
            // On some shared hostings, creating symlinks is not possible, so we ensure the directory exists.
            $preferredDisk = config('filesystems.disks.public_uploads') ? 'public_uploads' : null;
            if ($preferredDisk) {
                try {
                    File::ensureDirectoryExists(public_path('storage/garden-reports'));
                } catch (\Throwable $e) {
                    // If we can't create/write in /public/storage, fallback to the standard disk.
                    $preferredDisk = null;
                }
            }

            foreach ($request->file('images') as $image) {
                // Shared-hosting friendly: store directly under /public/storage (no symlink needed).
                // Fallback to the standard "public" disk if the preferred disk isn't available or writable.
                try {
                    $path = $preferredDisk
                        ? $image->store('garden-reports', $preferredDisk)
                        : $image->store('garden-reports', 'public');
                } catch (\Throwable $e) {
                    $path = $image->store('garden-reports', 'public');
                }

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

        // IMPORTANT:
        // This edit form only updates user/date/status and uploads new images.
        // Don't validate/overwrite the rest of the report fields here.
        $gardenReport->update([
            'user_id' => $validated['user_id'],
            'subscription_id' => $validated['subscription_id'],
            'report_date' => $validated['report_date'],
            'general_status' => $validated['general_status'],
        ]);

        // Handle new image uploads
        $hasAttemptedUpload = $request->files->has('images');
        $files = $request->file('images', []);
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }
        $files = array_values(array_filter((array) $files, fn ($f) => $f instanceof UploadedFile && $f->isValid()));

        if (count($files) > 0) {
            // With a proper storage link, always store on the standard "public" disk:
            // storage/app/public/garden-reports/*
            foreach ($files as $image) {
                $path = $image->store('garden-reports', 'public');
                $gardenReport->images()->create([
                    'image_path' => $path,
                    'image_date' => $validated['report_date'],
                ]);
            }
        } elseif ($hasAttemptedUpload) {
            // User selected files but none arrived as valid uploads (usually size/server limits/WAF).
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'images' => 'No se pudieron subir las imágenes. Probá con fotos más livianas (<= 2MB) o revisá el límite de subida del servidor.',
                ]);
        }

        // Redirect back to edit so the admin can immediately see new images
        return redirect()->route('admin.garden-reports.edit', $gardenReport)
            ->with('success', 'Reporte actualizado exitosamente.');
    }

    /**
     * POST alternative to update() for shared hostings that break uploads with _method=PUT.
     */
    public function updatePost(Request $request, GardenReport $gardenReport)
    {
        return $this->update($request, $gardenReport);
    }

    /**
     * Remove the specified garden report.
     */
    public function destroy(GardenReport $gardenReport)
    {
        // Delete associated images
        foreach ($gardenReport->images as $image) {
            // Prefer deleting from /public/storage, but keep fallback for older files
            Storage::disk('public_uploads')->delete($image->image_path);
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $gardenReport->delete();

        return redirect()->route('admin.garden-reports.index')
            ->with('success', 'Reporte eliminado exitosamente.');
    }

    /**
     * Delete a specific image from a garden report.
     */
    public function deleteImage($reportId, $imageId)
    {
        $gardenReport = GardenReport::findOrFail($reportId);
        $image = $gardenReport->images()->findOrFail($imageId);

        // Delete the file from storage
        // Prefer deleting from /public/storage, but keep fallback for older files
        Storage::disk('public_uploads')->delete($image->image_path);
        Storage::disk('public')->delete($image->image_path);

        // Delete the database record
        $image->delete();

        return response()->json(['success' => true, 'message' => 'Imagen eliminada exitosamente.']);
    }
}

