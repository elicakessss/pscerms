<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Adviser;
use App\Models\Admin;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\SystemLogService;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $userType = $request->get('type', 'all');
        $department = $request->get('department');
        $search = $request->get('search');

        // Get all departments for filter dropdown
        $departments = Department::all();

        // Initialize users collection
        $users = collect();

        // Build query based on user type filter
        if ($userType === 'all' || $userType === 'students') {
            $studentQuery = Student::with('department');

            if ($department) {
                $studentQuery->where('department_id', $department);
            }

            if ($search) {
                $studentQuery->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('id_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $students = $studentQuery->get()->map(function($student) {
                $student->user_type = 'Student';
                $student->full_name = $student->first_name . ' ' . $student->last_name;
                return $student;
            });

            $users = $users->merge($students);
        }

        if ($userType === 'all' || $userType === 'advisers') {
            $adviserQuery = Adviser::with('department');

            if ($department) {
                $adviserQuery->where('department_id', $department);
            }

            if ($search) {
                $adviserQuery->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('id_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $advisers = $adviserQuery->get()->map(function($adviser) {
                $adviser->user_type = 'Adviser';
                $adviser->full_name = $adviser->first_name . ' ' . $adviser->last_name;
                return $adviser;
            });

            $users = $users->merge($advisers);
        }

        if ($userType === 'all' || $userType === 'admins') {
            $adminQuery = Admin::query();

            if ($search) {
                $adminQuery->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('id_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $admins = $adminQuery->get()->map(function($admin) {
                $admin->user_type = 'Administrator';
                $admin->full_name = $admin->first_name . ' ' . $admin->last_name;
                $admin->department = null; // Admins don't have departments
                return $admin;
            });

            $users = $users->merge($admins);
        }

        // Sort users by name
        $users = $users->sortBy('full_name');

        // Get total counts for summary cards (unfiltered)
        $totalCounts = [
            'total_users' => Student::count() + Adviser::count() + Admin::count(),
            'students' => Student::count(),
            'advisers' => Adviser::count(),
            'administrators' => Admin::count(),
        ];

        return view('admin.user_management.index', compact('users', 'userType', 'department', 'search', 'departments', 'totalCounts'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('admin.user_management.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $rules = [
            'user_type' => 'required|in:student,adviser,admin',
            'id_number' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'nullable|digits:6',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ];

        // Add department validation for students and advisers
        if (in_array($request->user_type, ['student', 'adviser'])) {
            $rules['department_id'] = 'required|exists:departments,id';
        }

        $validated = $request->validate($rules);

        // Set default password if not provided
        if (empty($validated['password'])) {
            $validated['password'] = '123456';
        }

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures/' . $request->user_type . 's', 'public');
            $validated['profile_picture'] = $path;
        }

        // Create user based on type
        $user = null;
        switch ($request->user_type) {
            case 'student':
                // Check if ID number is unique for students
                if (Student::where('id_number', $validated['id_number'])->exists()) {
                    return back()->withErrors(['id_number' => 'This ID number is already taken by another student.'])->withInput();
                }

                // Check if email is unique for students
                if (Student::where('email', $validated['email'])->exists()) {
                    return back()->withErrors(['email' => 'This email is already taken by another student.'])->withInput();
                }

                $user = Student::create($validated);
                break;

            case 'adviser':
                // Check if ID number is unique for advisers
                if (Adviser::where('id_number', $validated['id_number'])->exists()) {
                    return back()->withErrors(['id_number' => 'This ID number is already taken by another adviser.'])->withInput();
                }

                // Check if email is unique for advisers
                if (Adviser::where('email', $validated['email'])->exists()) {
                    return back()->withErrors(['email' => 'This email is already taken by another adviser.'])->withInput();
                }

                $user = Adviser::create($validated);
                break;

            case 'admin':
                // Check if ID number is unique for admins
                if (Admin::where('id_number', $validated['id_number'])->exists()) {
                    return back()->withErrors(['id_number' => 'This ID number is already taken by another admin.'])->withInput();
                }

                // Check if email is unique for admins
                if (Admin::where('email', $validated['email'])->exists()) {
                    return back()->withErrors(['email' => 'This email is already taken by another admin.'])->withInput();
                }

                $user = Admin::create($validated);
                break;
        }

        // Log the user creation
        if ($user) {
            SystemLogService::logCreate($request->user_type, $user, $validated, $request);
        }

        return redirect()->route('admin.user_management.index')
            ->with('success', ucfirst($request->user_type) . ' created successfully!');
    }

    public function show($type, $id)
    {
        $user = $this->getUserByTypeAndId($type, $id);

        if (!$user) {
            return redirect()->route('admin.user_management.index')
                           ->with('error', 'User not found.');
        }

        // Get completed councils for portfolio if user is a student
        $completedCouncils = collect();
        if ($type === 'student') {
            $completedCouncils = $user->councilOfficers()
                ->with(['council.department'])
                ->whereNotNull('completed_at')
                ->whereNotNull('final_score')
                ->orderBy('completed_at', 'desc')
                ->get();
        }

        return view('admin.user_management.show', compact('user', 'type', 'completedCouncils'));
    }

    public function edit($type, $id)
    {
        $user = $this->getUserByTypeAndId($type, $id);

        if (!$user) {
            return redirect()->route('admin.user_management.index')
                           ->with('error', 'User not found.');
        }

        $departments = Department::all();
        return view('admin.user_management.edit', compact('user', 'type', 'departments'));
    }

    public function update(Request $request, $type, $id)
    {
        $user = $this->getUserByTypeAndId($type, $id);

        if (!$user) {
            return redirect()->route('admin.user_management.index')
                           ->with('error', 'User not found.');
        }

        $rules = [
            'id_number' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'nullable|string|digits:6',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
        ];

        // Add department validation for students and advisers
        if (in_array($type, ['student', 'adviser'])) {
            $rules['department_id'] = 'required|exists:departments,id';
        }

        // Check for unique ID number (excluding current user)
        if ($request->id_number !== $user->id_number) {
            $existingIdNumber = $this->checkIdNumberExists($request->id_number);
            if ($existingIdNumber) {
                return back()->withErrors(['id_number' => 'ID number already exists.'])->withInput();
            }
        }

        // Check for unique email (excluding current user)
        if ($request->email !== $user->email) {
            $existingEmail = $this->checkEmailExists($request->email);
            if ($existingEmail) {
                return back()->withErrors(['email' => 'Email already exists.'])->withInput();
            }
        }

        $validated = $request->validate($rules);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture) {
                \Storage::disk('public')->delete($user->profile_picture);
            }
            $validated['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        // Store old values for logging
        $oldValues = $user->toArray();

        // Update password only if provided
        if (!empty($request->password)) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        // Log the user update
        SystemLogService::logUpdate($type, $user, $oldValues, $validated, $request);

        // If admin is editing their own account, redirect to dashboard with a personalized message
        if ($type === 'admin' && $id == auth('admin')->id()) {
            return redirect()->route('admin.dashboard')
                            ->with('success', 'Your account information has been updated successfully!');
        }

        return redirect()->route('admin.user_management.show', [$type, $id])
                        ->with('success', 'User updated successfully!');
    }

    public function destroy(Request $request, $type, $id)
{
    $user = $this->getUserByTypeAndId($type, $id);

    if (!$user) {
        return redirect()->route('admin.user_management.index')
                       ->with('error', 'User not found.');
    }

    // Prevent admin from deleting their own account
    if ($type === 'admin' && auth('admin')->check() && auth('admin')->id() == $id) {
        return redirect()->route('admin.user_management.index')
                       ->with('error', 'You cannot delete your own account.');
    }

    // Store user data for logging before deletion
    $userData = $user->toArray();

    // Delete profile picture if exists
    if (isset($user->profile_picture) && $user->profile_picture) {
        \Storage::disk('public')->delete($user->profile_picture);
    }

    $user->delete();

    // Log the user deletion
    SystemLogService::logDelete($type, $user, $userData, $request);

    return redirect()->route('admin.user_management.index')
                    ->with('success', 'User deleted successfully!');
}

    private function getUserByTypeAndId($type, $id)
    {
        switch ($type) {
            case 'student':
                return Student::with('department')->find($id);
            case 'adviser':
                return Adviser::with('department')->find($id);
            case 'admin':
                return Admin::find($id);
            default:
                return null;
        }
    }

    private function checkIdNumberExists($idNumber)
    {
        return Student::where('id_number', $idNumber)->exists() ||
               Adviser::where('id_number', $idNumber)->exists() ||
               Admin::where('id_number', $idNumber)->exists();
    }

    private function checkEmailExists($email)
    {
        return Student::where('email', $email)->exists() ||
               Adviser::where('email', $email)->exists() ||
               Admin::where('email', $email)->exists();
    }
}




