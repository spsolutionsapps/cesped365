<?php

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientReportsController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\GardenReportController;
use App\Models\GardenReportImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public Landing Routes
Route::get('/', [LandingController::class, 'home'])->name('landing.home');
Route::get('/planes', [LandingController::class, 'plans'])->name('landing.plans');
Route::get('/contacto', [LandingController::class, 'contact'])->name('landing.contact');

/**
 * Fallback public image serving for shared hosting (no symlink required).
 *
 * If /public/storage symlink is not available or the FTP deploy can't create it,
 * this route serves garden report images from storage/app/public.
 *
 * Note: images are already treated as public assets in this app.
 */
Route::get('/storage/garden-reports/{filename}', function (string $filename) {
    if (str_contains($filename, '..') || str_contains($filename, '/')) {
        abort(404);
    }

    $relativePath = 'garden-reports/' . $filename;

    // 1) Prefer /public/storage (public_uploads disk) if present (fast static-like path).
    if (config('filesystems.disks.public_uploads') && Storage::disk('public_uploads')->exists($relativePath)) {
        return response()->file(Storage::disk('public_uploads')->path($relativePath), [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    // 2) Fallback: storage/app/public (public disk), served via Laravel when symlink isn't available.
    if (Storage::disk('public')->exists($relativePath)) {
        return response()->file(Storage::disk('public')->path($relativePath), [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    abort(404);
})->where('filename', '[^/]+')->name('storage.garden-reports.show');

// Auth Routes
Route::group(['middleware' => 'guest'], function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/login', [SessionsController::class, 'create'])->name('login');
    Route::post('/session', [SessionsController::class, 'store'])->name('login.store');
    Route::get('/login/forgot-password', [ResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ResetController::class, 'sendEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetController::class, 'resetPass'])->name('password.reset');
    Route::post('/reset-password', [ChangePasswordController::class, 'changePassword'])->name('password.update');
});

// Authenticated Routes
Route::group(['middleware' => 'auth'], function () {
    /**
     * Definitive garden report image serving (doesn't depend on /storage rewrites or symlinks).
     * Uses the image id and serves from either disk.
     */
    Route::get('/media/garden-report-images/{image}', function (GardenReportImage $image) {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        // Authorization: admin can view, otherwise only the owner of the report.
        $report = $image->gardenReport;
        if (!$report) {
            abort(404);
        }

        if (!$user->isAdmin() && (int) $report->user_id !== (int) $user->id) {
            abort(403);
        }

        $relativePath = (string) $image->image_path;

        if (config('filesystems.disks.public_uploads') && Storage::disk('public_uploads')->exists($relativePath)) {
            return response()->file(Storage::disk('public_uploads')->path($relativePath), [
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        if (Storage::disk('public')->exists($relativePath)) {
            return response()->file(Storage::disk('public')->path($relativePath), [
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        abort(404);
    })->name('garden-report-images.show');

    Route::get('/logout', [SessionsController::class, 'destroy'])->name('logout');
    Route::get('/user-profile', [InfoUserController::class, 'create']);
    Route::post('/user-profile', [InfoUserController::class, 'store']);
    
    // Dashboard Routes (Client)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/subscription', [DashboardController::class, 'subscription'])->name('dashboard.subscription');
    
    // Client Reports Routes (New UX)
    Route::get('/dashboard/reports', [ClientReportsController::class, 'index'])->name('dashboard.reports');
    Route::get('/dashboard/reports/{year}/{month}', [ClientReportsController::class, 'month'])->name('dashboard.reports.month');
    Route::get('/dashboard/garden-reports/{id}', [ClientReportsController::class, 'show'])->name('dashboard.garden-reports.show');
    
    // Legacy route (mantener por compatibilidad)
    Route::get('/dashboard/garden-reports', [DashboardController::class, 'gardenReports'])->name('dashboard.garden-reports');
    
    // Admin Routes
    Route::group(['middleware' => 'admin', 'prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        
        // Users
        Route::resource('users', UserController::class);
        Route::get('users/{user}/subscriptions', [UserController::class, 'getSubscriptions'])->name('users.subscriptions');
        
        // Plans
        Route::resource('plans', PlanController::class);
        
        // Subscriptions
        Route::resource('subscriptions', SubscriptionController::class);
        
        // Garden Reports
        Route::resource('garden-reports', GardenReportController::class);
        Route::delete('garden-reports/{reportId}/images/{imageId}', [GardenReportController::class, 'deleteImage'])->name('garden-reports.images.delete');
    });
    
    // Legacy routes (keeping for compatibility)
    Route::get('billing', function () {
        return view('billing');
    })->name('billing');
    
    Route::get('profile', function () {
        return view('profile');
    })->name('profile');
    
    // Removed routes: rtl, virtual-reality
});