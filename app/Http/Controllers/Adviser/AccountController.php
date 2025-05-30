<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function index()
    {
        $adviser = Auth::user();
        return view('adviser.account.index', compact('adviser'));
    }

    public function edit()
    {
        $adviser = Auth::user();
        return view('adviser.account.edit', compact('adviser'));
    }

    public function update(Request $request)
    {
        $adviser = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:advisers,email,' . $adviser->id,
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($adviser->profile_picture) {
                Storage::delete('public/' . $adviser->profile_picture);
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile_pictures/advisers', 'public');
            $validated['profile_picture'] = $path;
        }

        $adviser->update($validated);

        return redirect()->route('adviser.account.index')->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $adviser = Auth::user();

        // Check current password
        if (!Hash::check($validated['current_password'], $adviser->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $adviser->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('adviser.account.index')->with('success', 'Password updated successfully!');
    }
}


