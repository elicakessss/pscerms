<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Adviser;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'id_number' => 'required|string',
            'role' => 'required|in:student,adviser,admin',
        ]);

        // Find user based on role and ID number
        $user = $this->findUserByRoleAndId($request->role, $request->id_number);

        if (!$user) {
            return back()->withErrors([
                'id_number' => 'No user found with this ID number and role.',
            ]);
        }

        // For now, we'll redirect to a password reset form
        // In a real application, you would send an email with a reset link
        return redirect()->route('password.reset.form', [
            'role' => $request->role,
            'id_number' => $request->id_number
        ])->with('status', 'Password reset form is ready. Please set your new password.');
    }

    public function showResetForm(Request $request)
    {
        $role = $request->get('role');
        $idNumber = $request->get('id_number');

        // Verify user exists
        $user = $this->findUserByRoleAndId($role, $idNumber);

        if (!$user) {
            return redirect()->route('password.request')->withErrors([
                'id_number' => 'Invalid reset request.',
            ]);
        }

        return view('auth.reset-password', compact('role', 'idNumber', 'user'));
    }

    public function reset(Request $request)
    {
        $request->validate([
            'role' => 'required|in:student,adviser,admin',
            'id_number' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Find user based on role and ID number
        $user = $this->findUserByRoleAndId($request->role, $request->id_number);

        if (!$user) {
            return back()->withErrors([
                'id_number' => 'No user found with this ID number and role.',
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login')->with('status', 'Password has been reset successfully! You can now login with your new password.');
    }

    private function findUserByRoleAndId($role, $idNumber)
    {
        switch ($role) {
            case 'student':
                return Student::where('id_number', $idNumber)->first();
            case 'adviser':
                return Adviser::where('id_number', $idNumber)->first();
            case 'admin':
                return Admin::where('id_number', $idNumber)->first();
            default:
                return null;
        }
    }
}
