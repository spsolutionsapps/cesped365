<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    public function create()
    {
        return view('session.login-session');
    }

    public function store()
    {
        $attributes = request()->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = (bool) request()->boolean('remember');

        if (!Auth::attempt($attributes, $remember)) {
            return back()
                ->withInput(['email' => $attributes['email'], 'remember' => $remember])
                ->withErrors(['email' => 'Email or password invalid.']);
        }

        // Regenerar sesión para prevenir fijación de sesión
        request()->session()->regenerate();

        $authUser = Auth::user();
        if ($authUser) {
            $authUser->refresh();
        }

        // Redirect admins to admin dashboard, clients to client dashboard
        if ($authUser && $authUser->isAdmin()) {
            return redirect()->route('admin.index')
                ->with(['success' => 'You are logged in.'])
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }

        return redirect('dashboard')
            ->with(['success' => 'You are logged in.'])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
    
    public function destroy()
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/login')->with(['success' => 'You\'ve been logged out.']);
    }
}
