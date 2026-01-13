<?php

namespace App\Http\Controllers;

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

