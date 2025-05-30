<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'id_number' => 'required',
            'password' => 'required',
            'role' => 'required|in:student,adviser,admin',
        ]);

        $role = $credentials['role'];
        unset($credentials['role']);

        // Attempt to authenticate based on role
        if ($role === 'student') {
            $guard = 'student';
        } elseif ($role === 'adviser') {
            $guard = 'adviser';
        } else {
            $guard = 'admin';
        }

        if (Auth::guard($guard)->attempt($credentials)) {
            $request->session()->regenerate();

            // Debug information
            \Log::info('User authenticated with guard: ' . $guard);
            \Log::info('User role: ' . $role);

            // Redirect based on role
            if ($role === 'student') {
                return redirect()->intended(route('student.dashboard'));
            } elseif ($role === 'adviser') {
                return redirect()->intended(route('adviser.dashboard'));
            } else {
                return redirect()->intended(route('admin.dashboard'));
            }
        }

        return back()->withErrors([
            'id_number' => 'The provided credentials do not match our records.',
        ])->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}




