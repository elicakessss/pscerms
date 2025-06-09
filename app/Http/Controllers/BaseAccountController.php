<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

abstract class BaseAccountController extends Controller
{
    /**
     * Get the authenticated user
     */
    protected function getAuthenticatedUser()
    {
        return Auth::user();
    }

    /**
     * Get the user type for validation rules
     */
    abstract protected function getUserType(): string;

    /**
     * Get the route name for redirecting after updates
     */
    abstract protected function getAccountIndexRoute(): string;

    /**
     * Get validation rules for profile update
     */
    protected function getProfileValidationRules($userId): array
    {
        $userType = $this->getUserType();

        $rules = [
            'id_number' => "required|string|max:255|unique:{$userType}s,id_number,{$userId}",
            'email' => "required|email|max:255|unique:{$userType}s,email,{$userId}",
        ];

        // Add name fields for admin and adviser
        if (in_array($userType, ['admin', 'adviser'])) {
            $rules['first_name'] = 'required|string|max:255';
            $rules['last_name'] = 'required|string|max:255';
        }

        // Add profile picture for student and adviser
        if (in_array($userType, ['student', 'adviser'])) {
            $rules['profile_picture'] = 'nullable|image|max:2048';
        }

        return $rules;
    }

    /**
     * Handle profile picture upload
     */
    protected function handleProfilePictureUpload(Request $request, $user): ?string
    {
        if (!$request->hasFile('profile_picture')) {
            return null;
        }

        // Delete old profile picture if exists
        if ($user->profile_picture) {
            Storage::delete('public/' . $user->profile_picture);
        }

        // Store new profile picture
        $userType = $this->getUserType();
        $path = $request->file('profile_picture')->store("profile_pictures/{$userType}s", 'public');

        return $path;
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|digits:6',
            'password' => 'required|digits:6|confirmed',
        ]);

        $user = $this->getAuthenticatedUser();

        // Check current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route($this->getAccountIndexRoute())
            ->with('success', 'Password updated successfully!');
    }

    /**
     * Common update logic
     */
    protected function updateProfile(Request $request)
    {
        $user = $this->getAuthenticatedUser();
        $validated = $request->validate($this->getProfileValidationRules($user->id));

        // Handle profile picture upload
        $profilePicturePath = $this->handleProfilePictureUpload($request, $user);
        if ($profilePicturePath) {
            $validated['profile_picture'] = $profilePicturePath;
        }

        $user->update($validated);

        return redirect()->route($this->getAccountIndexRoute())
            ->with('success', 'Profile updated successfully!');
    }
}
