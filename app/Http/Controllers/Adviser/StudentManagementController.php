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
    public function index()
    {
        $adviser = Auth::user();
        $students = Student::where('department_id', $adviser->department_id)->paginate(10);

        return view('adviser.student_management.index', compact('students'));
    }

    public function create()
    {
        $adviser = Auth::user();
        $departments = Department::all();
        $isUniwideAdviser = $adviser->department->abbreviation === 'UNIWIDE';

        return view('adviser.student_management.create', compact('departments', 'isUniwideAdviser'));
    }

    public function store(Request $request)
    {
        $adviser = Auth::user();

        $validated = $request->validate([
            'id_number' => 'required|string|max:255|unique:students,id_number',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:students,email',
            'password' => 'nullable|string|min:6',
            'profile_picture' => 'nullable|image|max:2048',
            'description' => 'nullable|string',
        ]);

        // Set default password if not provided
        if (empty($validated['password'])) {
            $validated['password'] = '123456';
        }

        // Hash password
        $validated['password'] = Hash::make($validated['password']);

        // Set department_id to adviser's department
        $validated['department_id'] = $adviser->department_id;

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

        // Check if student belongs to adviser's department
        if ($student->department_id !== $adviser->department_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('adviser.student_management.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $adviser = Auth::user();

        // Check if student belongs to adviser's department
        if ($student->department_id !== $adviser->department_id) {
            abort(403, 'Unauthorized action.');
        }

        $departments = Department::all();
        $isUniwideAdviser = $adviser->department->abbreviation === 'UNIWIDE';

        return view('adviser.student_management.edit', compact('student', 'departments', 'isUniwideAdviser'));
    }

    public function update(Request $request, Student $student)
    {
        $adviser = Auth::user();

        // Check if student belongs to adviser's department
        if ($student->department_id !== $adviser->department_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:students,email,' . $student->id,
            'password' => 'nullable|string|min:6',
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

        // Check if student belongs to adviser's department
        if ($student->department_id !== $adviser->department_id) {
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
