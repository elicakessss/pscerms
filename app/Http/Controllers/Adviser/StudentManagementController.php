<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentManagementController extends Controller
{
    public function index(Request $request)
    {
        $adviser = Auth::user();
        $isUniwideAdviser = $adviser->department->abbreviation === 'UNIWIDE';

        // Build query based on adviser type
        $query = Student::with('department');

        if (!$isUniwideAdviser) {
            // Regular advisers can only see students from their department
            $query->where('department_id', $adviser->department_id);
        } else {
            // Uniwide advisers can see all students, with optional department filter
            if ($request->filled('department_filter') && $request->department_filter !== 'all') {
                $query->where('department_id', $request->department_filter);
            }
        }

        // Add search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('id_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(10);

        // Get departments for filter dropdown (uniwide advisers only)
        $departments = $isUniwideAdviser ? Department::where('abbreviation', '!=', 'UNIWIDE')->get() : collect();

        return view('adviser.student_management.index', compact('students', 'isUniwideAdviser', 'departments'));
    }

    public function create()
    {
        $adviser = Auth::user()->load('department');
        $departments = Department::all();
        $isUniwideAdviser = $adviser->department->abbreviation === 'UNIWIDE';

        return view('adviser.student_management.create', compact('departments', 'isUniwideAdviser'));
    }

    public function store(Request $request)
    {
        $adviser = Auth::user();
        $isUniwideAdviser = $adviser->department->abbreviation === 'UNIWIDE';

        $rules = [
            'id_number' => 'required|string|max:255|unique:students,id_number',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:students,email',
            'password' => 'nullable|digits:6',
            'profile_picture' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
        ];

        // Add department validation for uniwide advisers
        if ($isUniwideAdviser) {
            $rules['department_id'] = 'required|exists:departments,id';
        }

        $validated = $request->validate($rules);

        // Set default password if not provided
        if (empty($validated['password'])) {
            $validated['password'] = '123456';
        }

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        // Set department_id based on adviser type
        if (!$isUniwideAdviser) {
            // Regular advisers can only create students in their department
            $validated['department_id'] = $adviser->department_id;
        }
        // For uniwide advisers, department_id comes from the form and is already validated

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures/students', 'public');
            $validated['profile_picture'] = $path;
        }

        Student::create($validated);

        return redirect()->route('adviser.student_management.index')
            ->with('success', 'Student created successfully!');
    }

    public function show(Student $student)
    {
        $adviser = Auth::user();
        $isUniwideAdviser = $adviser->department->abbreviation === 'UNIWIDE';

        // Check if student belongs to adviser's department (unless uniwide adviser)
        if (!$isUniwideAdviser && $student->department_id !== $adviser->department_id) {
            abort(403, 'Unauthorized action.');
        }

        // Get completed councils for portfolio
        $completedCouncils = $student->councilOfficers()
            ->with(['council.department'])
            ->whereNotNull('completed_at')
            ->whereNotNull('final_score')
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('adviser.student_management.show', compact('student', 'completedCouncils'));
    }

    public function edit(Student $student)
    {
        $adviser = Auth::user();
        $isUniwideAdviser = $adviser->department->abbreviation === 'UNIWIDE';

        // Check if student belongs to adviser's department (unless uniwide adviser)
        if (!$isUniwideAdviser && $student->department_id !== $adviser->department_id) {
            abort(403, 'Unauthorized action.');
        }

        $departments = Department::all();

        return view('adviser.student_management.edit', compact('student', 'departments', 'isUniwideAdviser'));
    }

    public function update(Request $request, Student $student)
    {
        $adviser = Auth::user();
        $isUniwideAdviser = $adviser->department->abbreviation === 'UNIWIDE';

        // Check if student belongs to adviser's department (unless uniwide adviser)
        if (!$isUniwideAdviser && $student->department_id !== $adviser->department_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'id_number' => 'required|string|max:255|unique:students,id_number,' . $student->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:students,email,' . $student->id,
            'password' => 'nullable|digits:6',
            'profile_picture' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
        ]);

        // Only update password if provided
        if (isset($validated['password']) && $validated['password']) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($student->profile_picture) {
                Storage::delete('public/' . $student->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profile_pictures/students', 'public');
            $validated['profile_picture'] = $path;
        }

        $student->update($validated);

        return redirect()->route('adviser.student_management.index')
            ->with('success', 'Student updated successfully!');
    }

    public function destroy(Student $student)
    {
        $adviser = Auth::user();
        $isUniwideAdviser = $adviser->department->abbreviation === 'UNIWIDE';

        // Check if student belongs to adviser's department (unless uniwide adviser)
        if (!$isUniwideAdviser && $student->department_id !== $adviser->department_id) {
            abort(403, 'Unauthorized action.');
        }

        // Delete profile picture if exists
        if ($student->profile_picture) {
            Storage::delete('public/' . $student->profile_picture);
        }

        $student->delete();

        return redirect()->route('adviser.student_management.index')
            ->with('success', 'Student deleted successfully!');
    }
}
