<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Council;
use App\Models\Department;
use App\Models\Adviser;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class CouncilManagementController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $department = $request->get('department');
        $status = $request->get('status');
        $search = $request->get('search');

        // Get all departments for filter dropdown
        $departments = Department::all();

        // Build query for councils
        $query = Council::with(['department', 'adviser', 'councilOfficers.student']);

        // Apply filters
        if ($department) {
            $query->where('department_id', $department);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('academic_year', 'like', "%{$search}%")
                  ->orWhereHas('adviser', function($adviserQuery) use ($search) {
                      $adviserQuery->where('first_name', 'like', "%{$search}%")
                                   ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $councils = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get statistics for overview
        $stats = [
            'total_councils' => Council::count(),
            'active_councils' => Council::where('status', 'active')->count(),
            'completed_councils' => Council::where('status', 'completed')->count(),
            'total_officers' => DB::table('council_officers')->count(),
        ];

        // Get current academic year (from the most recent council or default)
        $currentAcademicYear = Council::latest()->value('academic_year') ?? date('Y') . '-' . (date('Y') + 1);

        return view('admin.council_management.index', compact(
            'councils',
            'departments',
            'stats',
            'currentAcademicYear'
        ));
    }

    public function create()
    {
        $departments = Department::all();
        $advisers = Adviser::with('department')->get();

        return view('admin.council_management.create', compact('departments', 'advisers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'academic_year' => 'required|string|max:255',
            'status' => 'required|in:active,completed',
            'adviser_id' => 'required|exists:advisers,id',
            'department_id' => 'required|exists:departments,id',
        ]);

        // Validate that adviser belongs to the selected department
        $adviser = Adviser::find($validated['adviser_id']);
        if ($adviser->department_id != $validated['department_id']) {
            return back()->withErrors(['adviser_id' => 'The selected adviser must belong to the selected department.'])->withInput();
        }

        // Ensure the name follows the correct format
        $department = Department::find($validated['department_id']);
        $validated['name'] = 'Paulinian Student Government - ' . $department->abbreviation;

        Council::create($validated);

        return redirect()->route('admin.council_management.index')
            ->with('success', 'Council created successfully!');
    }

    public function show(Council $council)
    {
        $council->load(['department', 'adviser', 'councilOfficers.student']);

        return view('admin.council_management.show', compact('council'));
    }

    public function edit(Council $council)
    {
        $departments = Department::all();
        $advisers = Adviser::with('department')->get();

        return view('admin.council_management.edit', compact('council', 'departments', 'advisers'));
    }

    public function update(Request $request, Council $council)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'academic_year' => 'required|string|max:255',
            'status' => 'required|in:active,completed',
            'adviser_id' => 'required|exists:advisers,id',
            'department_id' => 'required|exists:departments,id',
        ]);

        // Validate that adviser belongs to the selected department
        $adviser = Adviser::find($validated['adviser_id']);
        if ($adviser->department_id != $validated['department_id']) {
            return back()->withErrors(['adviser_id' => 'The selected adviser must belong to the selected department.'])->withInput();
        }

        // Ensure the name follows the correct format
        $department = Department::find($validated['department_id']);
        $validated['name'] = 'Paulinian Student Government - ' . $department->abbreviation;

        $council->update($validated);

        return redirect()->route('admin.council_management.index')
            ->with('success', 'Council updated successfully!');
    }

    public function destroy(Council $council)
    {
        $council->delete();

        return redirect()->route('admin.council_management.index')
            ->with('success', 'Council deleted successfully!');
    }

    public function updateAcademicYear(Request $request)
    {
        $validated = $request->validate([
            'academic_year' => 'required|string|max:255',
        ]);

        // Update all active councils to the new academic year
        Council::where('status', 'active')->update([
            'academic_year' => $validated['academic_year']
        ]);

        return redirect()->route('admin.council_management.index')
            ->with('success', 'Academic year updated for all active councils!');
    }
}
