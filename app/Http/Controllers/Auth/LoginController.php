<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SystemLogService;

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
            'password' => 'required|digits:6',
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

            // Log successful login
            $user = Auth::guard($guard)->user();
            SystemLogService::logLogin($role, $user, $request);

            // Redirect based on role
            if ($role === 'student') {
                return redirect()->route('student.dashboard');
            } elseif ($role === 'adviser') {
                return redirect()->route('adviser.dashboard');
            } else {
                return redirect()->route('admin.dashboard');
            }
        }

        return back()->withErrors([
            'id_number' => 'The provided credentials do not match our records.',
        ])->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        // Log logout before actually logging out
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            SystemLogService::logLogout('admin', $user, $request);
        } elseif (Auth::guard('adviser')->check()) {
            $user = Auth::guard('adviser')->user();
            SystemLogService::logLogout('adviser', $user, $request);
        } elseif (Auth::guard('student')->check()) {
            $user = Auth::guard('student')->user();
            SystemLogService::logLogout('student', $user, $request);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
