<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function index()
    {
        $student = Auth::user();
        return view('student.account.index', compact('student'));
    }

    public function edit()
    {
        $student = Auth::user();
        return view('student.account.edit', compact('student'));
    }

    public function update(Request $request)
    {
        $student = Auth::user();

        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:students,email,' . $student->id,
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($student->profile_picture) {
                Storage::delete('public/' . $student->profile_picture);
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile_pictures/students', 'public');
            $validated['profile_picture'] = $path;
        }

        $student->update($validated);

        return redirect()->route('student.account.index')->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $student = Auth::user();

        // Check current password
        if (!Hash::check($validated['current_password'], $student->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $student->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('student.account.index')->with('success', 'Password updated successfully!');
    }
}

