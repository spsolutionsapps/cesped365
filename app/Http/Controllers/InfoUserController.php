<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InfoUserController extends Controller
{

    public function create()
    {
        return view('laravel-examples/user-profile');
    }

    public function store(Request $request)
    {
        $authUser = Auth::user();
        $attributes = request()->validate([
            'name' => ['required', 'max:50'],
            'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore($authUser?->id)],
            'phone'     => ['max:50'],
            'location' => ['max:70'],
            'about_me'    => ['max:150'],
        ]);

        $attribute = null;
        if ($request->get('email') != $authUser->email) {
            if (env('IS_DEMO') && $authUser->id == 1) {
                return redirect()->back()->withErrors(['msg2' => 'You are in a demo version, you can\'t change the email address.']);
            }
            $attribute = request()->validate([
                'email' => ['required', 'email', 'max:50', Rule::unique('users')->ignore($authUser->id)],
            ]);
        } else {
            $attribute = ['email' => $attributes['email']];
        }

        User::where('id', $authUser->id)
            ->update([
                'name'    => $attributes['name'],
                'email' => $attribute['email'],
                'phone'     => $attributes['phone'] ?? null,
                'location' => $attributes['location'] ?? null,
                'about_me'    => $attributes['about_me'] ?? null,
            ]);

        return redirect('/user-profile')->with('success', 'Profile updated successfully');
    }
}
