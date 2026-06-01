<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                
                return back()->withErrors([
                    'email' => 'User is not active.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            if ($user->isSuperAdmin()) 
            {
                return redirect()->intended('/superAdmin');
            }
             elseif ($user->isMentor()) 
            {
                return redirect()->intended('/mentor');
            }
            elseif ($user->isAdmin())
            {
                return redirect()->intended('/admin');
            }
            return redirect()->intended('/student');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
