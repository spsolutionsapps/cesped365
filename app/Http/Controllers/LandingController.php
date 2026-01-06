<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Show the landing page.
     */
    public function home()
    {
        return view('landing.home');
    }

    /**
     * Show the plans page (redirects to home with anchor).
     */
    public function plans()
    {
        return redirect()->route('landing.home') . '#planes';
    }

    /**
     * Show the contact page (redirects to home with anchor).
     */
    public function contact()
    {
        return redirect()->route('landing.home') . '#contacto';
    }
}

