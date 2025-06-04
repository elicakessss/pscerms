<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request)
    {
        $query = Department::withCount(['students', 'advisers', 'councils']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('abbreviation', 'like', "%{$search}%");
            });
        }

        $departments = $query->orderBy('name')->paginate(10);

        // Calculate statistics
        $stats = [
            'total_departments' => Department::count(),
            'departments_with_students' => Department::whereHas('students')->count(),
            'departments_with_advisers' => Department::whereHas('advisers')->count(),
            'departments_with_councils' => Department::whereHas('councils')->count(),
        ];

        return view('admin.departments.index', compact('departments', 'stats'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        return view('admin.departments.create');
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'abbreviation' => 'required|string|max:20|unique:departments,abbreviation',
        ]);

        // Convert abbreviation to uppercase
        $validated['abbreviation'] = strtoupper($validated['abbreviation']);

        Department::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully!');
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
        $department->load(['students', 'advisers', 'councils']);

        return view('admin.departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('departments')->ignore($department->id)],
            'abbreviation' => ['required', 'string', 'max:20', Rule::unique('departments')->ignore($department->id)],
        ]);

        // Convert abbreviation to uppercase
        $validated['abbreviation'] = strtoupper($validated['abbreviation']);

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully!');
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(Department $department)
    {
        // Check if department has associated records
        $hasStudents = $department->students()->exists();
        $hasAdvisers = $department->advisers()->exists();
        $hasCouncils = $department->councils()->exists();

        if ($hasStudents || $hasAdvisers || $hasCouncils) {
            return back()->withErrors([
                'error' => 'Cannot delete department. It has associated students, advisers, or councils.'
            ]);
        }

        // Prevent deletion of UNIWIDE department
        if ($department->abbreviation === 'UNIWIDE') {
            return back()->withErrors([
                'error' => 'Cannot delete the UNIWIDE department as it has special system functions.'
            ]);
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully!');
    }
}
