<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Council;
use App\Models\Student;
use App\Models\CouncilOfficer;
use App\Services\EvaluationService;
use Illuminate\Support\Facades\Auth;

class CouncilController extends Controller
{
    /**
     * Display a listing of the adviser's councils.
     */
    public function index()
    {
        $adviser = Auth::user();

        $councils = Council::where('adviser_id', $adviser->id)
            ->with(['department', 'councilOfficers.student'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('adviser.my_councils.index', compact('councils'));
    }

    /**
     * Display the specified council.
     */
    public function show(Council $council, EvaluationService $evaluationService)
    {
        $adviser = Auth::user();

        // Check if the council belongs to this adviser
        if ($council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        $council->load(['department', 'councilOfficers.student']);

        // Get available students based on council type
        if ($council->department->abbreviation === 'UNIWIDE') {
            // For Uniwide councils, get students from all departments except UNIWIDE
            $availableStudents = Student::whereHas('department', function($query) {
                $query->where('abbreviation', '!=', 'UNIWIDE');
            })
            ->with('department')
            ->orderBy('last_name')
            ->get();
        } else {
            // For departmental councils, get students from the same department
            $availableStudents = Student::where('department_id', $council->department_id)
                ->orderBy('last_name')
                ->get();
        }

        // Get all positions for this council type and merge with existing officers
        $allPositions = $this->getAllPositionsForCouncil($council);

        // Get evaluation progress if evaluations have been started
        $evaluationProgress = null;
        if ($council->hasEvaluations()) {
            $evaluationProgress = $evaluationService->getEvaluationProgress($council);
        }

        return view('adviser.my_councils.show', compact('council', 'availableStudents', 'allPositions', 'evaluationProgress'));
    }

    /**
     * Assign a student to a position in the council.
     */
    public function assignOfficer(Request $request, Council $council)
    {
        $adviser = Auth::user();

        // Check if the council belongs to this adviser
        if ($council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if council is completed (closed)
        if ($council->status === 'completed') {
            return back()->withErrors(['error' => 'Cannot modify officers in a completed council.']);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'position_title' => 'required|string|max:255',
        ]);

        // Get student and validate
        $student = Student::with('department')->find($validated['student_id']);

        // Check if student is already in any council for the same academic year
        $existingCouncilMembership = CouncilOfficer::whereHas('council', function($query) use ($council) {
            $query->where('academic_year', $council->academic_year);
        })
        ->where('student_id', $validated['student_id'])
        ->first();

        if ($existingCouncilMembership) {
            return back()->withErrors(['student_id' => 'Student is already a member of another council in the same academic year.']);
        }

        // Validate department constraints based on council type
        if ($council->department->abbreviation === 'UNIWIDE') {
            // For Uniwide councils, validate department constraints for specific positions
            $positionValidation = $this->validateUniwidePositionConstraints($council, $validated['position_title'], $student);
            if ($positionValidation !== true) {
                return back()->withErrors(['student_id' => $positionValidation]);
            }
        } else {
            // For departmental councils, student must belong to the same department
            if ($student->department_id !== $council->department_id) {
                return back()->withErrors(['student_id' => 'Student must belong to the same department as the council.']);
            }
        }

        // Check if student is already assigned to this council
        $existingAssignment = CouncilOfficer::where('council_id', $council->id)
            ->where('student_id', $validated['student_id'])
            ->first();

        if ($existingAssignment) {
            return back()->withErrors(['student_id' => 'Student is already assigned to this council.']);
        }

        // Check if position is already filled
        $existingPosition = CouncilOfficer::where('council_id', $council->id)
            ->where('position_title', $validated['position_title'])
            ->first();

        if ($existingPosition) {
            return back()->withErrors(['position_title' => 'This position is already filled.']);
        }

        // Determine position level based on title
        $positionLevel = $this->getPositionLevel($validated['position_title']);

        CouncilOfficer::create([
            'council_id' => $council->id,
            'student_id' => $validated['student_id'],
            'position_title' => $validated['position_title'],
            'position_level' => $positionLevel,
        ]);

        return redirect()->route('adviser.councils.show', $council)
            ->with('success', 'Student assigned to position successfully!');
    }

    /**
     * Add a coordinator position to the council.
     */
    public function addCoordinator(Request $request, Council $council)
    {
        $adviser = Auth::user();

        // Check if the council belongs to this adviser
        if ($council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if council is completed (closed)
        if ($council->status === 'completed') {
            return back()->withErrors(['error' => 'Cannot modify officers in a completed council.']);
        }

        $validated = $request->validate([
            'coordinator_title' => 'required|string|max:255',
            'student_id' => 'required|exists:students,id',
        ]);

        // Clean up the coordinator title
        $coordinatorTitle = trim($validated['coordinator_title']);

        // Get student and validate
        $student = Student::with('department')->find($validated['student_id']);

        // Check if student is already in any council for the same academic year
        $existingCouncilMembership = CouncilOfficer::whereHas('council', function($query) use ($council) {
            $query->where('academic_year', $council->academic_year);
        })
        ->where('student_id', $validated['student_id'])
        ->first();

        if ($existingCouncilMembership) {
            return back()->withErrors(['student_id' => 'Student is already a member of another council in the same academic year.']);
        }

        // Validate department constraints based on council type
        if ($council->department->abbreviation === 'UNIWIDE') {
            // For Uniwide councils, students can be from any department except UNIWIDE
            if ($student->department->abbreviation === 'UNIWIDE') {
                return back()->withErrors(['student_id' => 'Students from UNIWIDE department cannot be assigned to council positions.']);
            }
        } else {
            // For departmental councils, student must belong to the same department
            if ($student->department_id !== $council->department_id) {
                return back()->withErrors(['student_id' => 'Student must belong to the same department as the council.']);
            }
        }

        // Check if student is already assigned to this council
        $existingAssignment = CouncilOfficer::where('council_id', $council->id)
            ->where('student_id', $validated['student_id'])
            ->first();

        if ($existingAssignment) {
            return back()->withErrors(['student_id' => 'Student is already assigned to this council.']);
        }

        // Check if this coordinator position already exists
        $existingCoordinator = CouncilOfficer::where('council_id', $council->id)
            ->where('position_title', $coordinatorTitle)
            ->first();

        if ($existingCoordinator) {
            return back()->withErrors(['coordinator_title' => 'This coordinator position already exists.']);
        }

        // Create the coordinator position with assigned student
        CouncilOfficer::create([
            'council_id' => $council->id,
            'student_id' => $validated['student_id'],
            'position_title' => $coordinatorTitle,
            'position_level' => 'Officer',
        ]);

        return redirect()->route('adviser.councils.show', $council)
            ->with('success', 'Coordinator position created and student assigned successfully!');
    }

    /**
     * Update an officer's position.
     */
    public function updateOfficer(Request $request, Council $council, CouncilOfficer $officer)
    {
        $adviser = Auth::user();

        // Check if the council belongs to this adviser
        if ($council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if council is completed (closed)
        if ($council->status === 'completed') {
            return back()->withErrors(['error' => 'Cannot modify officers in a completed council.']);
        }

        $validated = $request->validate([
            'position_title' => 'required|string|max:255',
            'position_level' => 'required|string|max:255',
        ]);

        $officer->update($validated);

        return redirect()->route('adviser.councils.show', $council)
            ->with('success', 'Officer position updated successfully!');
    }

    /**
     * Remove an officer from the council.
     */
    public function removeOfficer(Council $council, CouncilOfficer $officer)
    {
        $adviser = Auth::user();

        // Check if the council belongs to this adviser
        if ($council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if council is completed (closed)
        if ($council->status === 'completed') {
            return back()->withErrors(['error' => 'Cannot modify officers in a completed council.']);
        }

        $officer->delete();

        return redirect()->route('adviser.councils.show', $council)
            ->with('success', 'Officer removed successfully!');
    }

    /**
     * Get all positions for a council (existing officers + empty positions)
     */
    private function getAllPositionsForCouncil($council)
    {
        // Get base positions for this council type
        $basePositions = $this->getBasePositions($council->department->abbreviation);

        // Get existing officers
        $existingOfficers = $council->councilOfficers->keyBy('position_title');

        // Merge base positions with existing officers
        $allPositions = collect();

        foreach ($basePositions as $position) {
            if ($existingOfficers->has($position['title'])) {
                // Position is filled
                $officer = $existingOfficers[$position['title']];
                $allPositions->push([
                    'title' => $position['title'],
                    'branch' => $position['branch'],
                    'level' => $position['level'],
                    'officer' => $officer,
                    'is_filled' => true,
                ]);
            } else {
                // Position is empty
                $allPositions->push([
                    'title' => $position['title'],
                    'branch' => $position['branch'],
                    'level' => $position['level'],
                    'officer' => null,
                    'is_filled' => false,
                ]);
            }
        }

        // Add any coordinator positions that exist but aren't in base positions
        $coordinatorOfficers = $council->councilOfficers->filter(function($officer) use ($basePositions) {
            return !collect($basePositions)->pluck('title')->contains($officer->position_title);
        });

        foreach ($coordinatorOfficers as $officer) {
            $allPositions->push([
                'title' => $officer->position_title,
                'branch' => 'Coordinator',
                'level' => $officer->position_level,
                'officer' => $officer,
                'is_filled' => true,
            ]);
        }

        return $allPositions;
    }

    /**
     * Get base positions for department type
     */
    private function getBasePositions($departmentAbbreviation)
    {
        if ($departmentAbbreviation === 'UNIWIDE') {
            return $this->getUniversityWidePositions();
        } else {
            return $this->getDepartmentalPositions();
        }
    }

    /**
     * Get position level based on title
     */
    private function getPositionLevel($positionTitle)
    {
        $executivePositions = ['President', 'Governor', 'Vice President', 'Vice Governor'];

        foreach ($executivePositions as $execPos) {
            if (stripos($positionTitle, $execPos) !== false) {
                return 'Executive';
            }
        }

        return 'Officer';
    }



    /**
     * Get position templates for departmental councils
     */
    private function getDepartmentalPositions()
    {
        return [
            // Executive Branch
            ['title' => 'Governor', 'level' => 'Executive', 'branch' => 'Executive', 'display_title' => 'Governor'],
            ['title' => 'Vice Governor', 'level' => 'Executive', 'branch' => 'Executive', 'display_title' => 'Vice Governor'],
            ['title' => 'Secretary', 'level' => 'Officer', 'branch' => 'Executive', 'display_title' => 'Secretary'],
            ['title' => 'Assistant Secretary', 'level' => 'Officer', 'branch' => 'Executive', 'display_title' => 'Assistant Secretary'],
            ['title' => 'Treasurer', 'level' => 'Officer', 'branch' => 'Executive', 'display_title' => 'Treasurer'],
            ['title' => 'Assistant Treasurer', 'level' => 'Officer', 'branch' => 'Executive', 'display_title' => 'Assistant Treasurer'],
            ['title' => 'Auditor', 'level' => 'Officer', 'branch' => 'Executive', 'display_title' => 'Auditor'],
            ['title' => 'Public Relations Officer', 'level' => 'Officer', 'branch' => 'Executive', 'display_title' => 'Public Relations Officer'],
            ['title' => 'Assistant Public Relations Officer', 'level' => 'Officer', 'branch' => 'Executive', 'display_title' => 'Assistant Public Relations Officer'],

            // Legislative Branch
            ['title' => 'Councilor 1', 'level' => 'Officer', 'branch' => 'Legislative', 'display_title' => 'Councilor'],
            ['title' => 'Councilor 2', 'level' => 'Officer', 'branch' => 'Legislative', 'display_title' => 'Councilor'],
            ['title' => 'Councilor 3', 'level' => 'Officer', 'branch' => 'Legislative', 'display_title' => 'Councilor'],
            ['title' => 'Councilor 4', 'level' => 'Officer', 'branch' => 'Legislative', 'display_title' => 'Councilor'],
            ['title' => 'Councilor 5', 'level' => 'Officer', 'branch' => 'Legislative', 'display_title' => 'Councilor'],
            ['title' => 'Councilor 6', 'level' => 'Officer', 'branch' => 'Legislative', 'display_title' => 'Councilor'],
            ['title' => 'Councilor 7', 'level' => 'Officer', 'branch' => 'Legislative', 'display_title' => 'Councilor'],
            ['title' => 'Councilor 8', 'level' => 'Officer', 'branch' => 'Legislative', 'display_title' => 'Councilor'],

            // Mayoral Branch
            ['title' => '1st Year Mayor', 'level' => 'Officer', 'branch' => 'Mayoral', 'display_title' => '1st Year Mayor'],
            ['title' => '2nd Year Mayor', 'level' => 'Officer', 'branch' => 'Mayoral', 'display_title' => '2nd Year Mayor'],
            ['title' => '3rd Year Mayor', 'level' => 'Officer', 'branch' => 'Mayoral', 'display_title' => '3rd Year Mayor'],
            ['title' => '4th Year Mayor', 'level' => 'Officer', 'branch' => 'Mayoral', 'display_title' => '4th Year Mayor'],
        ];
    }

    /**
     * Get position templates for university-wide councils
     */
    private function getUniversityWidePositions()
    {
        return [
            // Executive Branch
            ['title' => 'President', 'level' => 'Executive', 'branch' => 'Executive', 'display_title' => 'President'],
            ['title' => 'Vice President', 'level' => 'Executive', 'branch' => 'Executive', 'display_title' => 'Vice President'],
            ['title' => 'Secretary', 'level' => 'Officer', 'branch' => 'Executive', 'display_title' => 'Secretary'],
            ['title' => 'Assistant Secretary', 'level' => 'Officer', 'branch' => 'Executive', 'display_title' => 'Assistant Secretary'],
            ['title' => 'Treasurer', 'level' => 'Officer', 'branch' => 'Executive', 'display_title' => 'Treasurer'],
            ['title' => 'Assistant Treasurer', 'level' => 'Officer', 'branch' => 'Executive', 'display_title' => 'Assistant Treasurer'],
            ['title' => 'Auditor', 'level' => 'Officer', 'branch' => 'Executive', 'display_title' => 'Auditor'],
            ['title' => 'Public Relations Officer', 'level' => 'Officer', 'branch' => 'Executive', 'display_title' => 'Public Relations Officer'],
            ['title' => 'Assistant Public Relations Officer', 'level' => 'Officer', 'branch' => 'Executive', 'display_title' => 'Assistant Public Relations Officer'],

            // Senate - Senators (3 from each department)
            ['title' => 'SASTE Senator 1', 'level' => 'Officer', 'branch' => 'Senate', 'display_title' => 'Senator', 'department_constraint' => 'SASTE'],
            ['title' => 'SASTE Senator 2', 'level' => 'Officer', 'branch' => 'Senate', 'display_title' => 'Senator', 'department_constraint' => 'SASTE'],
            ['title' => 'SASTE Senator 3', 'level' => 'Officer', 'branch' => 'Senate', 'display_title' => 'Senator', 'department_constraint' => 'SASTE'],
            ['title' => 'SBAHM Senator 1', 'level' => 'Officer', 'branch' => 'Senate', 'display_title' => 'Senator', 'department_constraint' => 'SBAHM'],
            ['title' => 'SBAHM Senator 2', 'level' => 'Officer', 'branch' => 'Senate', 'display_title' => 'Senator', 'department_constraint' => 'SBAHM'],
            ['title' => 'SBAHM Senator 3', 'level' => 'Officer', 'branch' => 'Senate', 'display_title' => 'Senator', 'department_constraint' => 'SBAHM'],
            ['title' => 'SITE Senator 1', 'level' => 'Officer', 'branch' => 'Senate', 'display_title' => 'Senator', 'department_constraint' => 'SITE'],
            ['title' => 'SITE Senator 2', 'level' => 'Officer', 'branch' => 'Senate', 'display_title' => 'Senator', 'department_constraint' => 'SITE'],
            ['title' => 'SITE Senator 3', 'level' => 'Officer', 'branch' => 'Senate', 'display_title' => 'Senator', 'department_constraint' => 'SITE'],
            ['title' => 'SNAHS Senator 1', 'level' => 'Officer', 'branch' => 'Senate', 'display_title' => 'Senator', 'department_constraint' => 'SNAHS'],
            ['title' => 'SNAHS Senator 2', 'level' => 'Officer', 'branch' => 'Senate', 'display_title' => 'Senator', 'department_constraint' => 'SNAHS'],
            ['title' => 'SNAHS Senator 3', 'level' => 'Officer', 'branch' => 'Senate', 'display_title' => 'Senator', 'department_constraint' => 'SNAHS'],

            // House of Representatives - Congressman (2 from each department)
            ['title' => 'SASTE Congressman 1', 'level' => 'Officer', 'branch' => 'House of Representatives', 'display_title' => 'Congressman', 'department_constraint' => 'SASTE'],
            ['title' => 'SASTE Congressman 2', 'level' => 'Officer', 'branch' => 'House of Representatives', 'display_title' => 'Congressman', 'department_constraint' => 'SASTE'],
            ['title' => 'SBAHM Congressman 1', 'level' => 'Officer', 'branch' => 'House of Representatives', 'display_title' => 'Congressman', 'department_constraint' => 'SBAHM'],
            ['title' => 'SBAHM Congressman 2', 'level' => 'Officer', 'branch' => 'House of Representatives', 'display_title' => 'Congressman', 'department_constraint' => 'SBAHM'],
            ['title' => 'SITE Congressman 1', 'level' => 'Officer', 'branch' => 'House of Representatives', 'display_title' => 'Congressman', 'department_constraint' => 'SITE'],
            ['title' => 'SITE Congressman 2', 'level' => 'Officer', 'branch' => 'House of Representatives', 'display_title' => 'Congressman', 'department_constraint' => 'SITE'],
            ['title' => 'SNAHS Congressman 1', 'level' => 'Officer', 'branch' => 'House of Representatives', 'display_title' => 'Congressman', 'department_constraint' => 'SNAHS'],
            ['title' => 'SNAHS Congressman 2', 'level' => 'Officer', 'branch' => 'House of Representatives', 'display_title' => 'Congressman', 'department_constraint' => 'SNAHS'],

            // Judiciary Branch - Associate Justices (2 from each department)
            ['title' => 'SASTE Justice 1', 'level' => 'Officer', 'branch' => 'Judiciary', 'display_title' => 'Associate Justice', 'department_constraint' => 'SASTE'],
            ['title' => 'SASTE Justice 2', 'level' => 'Officer', 'branch' => 'Judiciary', 'display_title' => 'Associate Justice', 'department_constraint' => 'SASTE'],
            ['title' => 'SBAHM Justice 1', 'level' => 'Officer', 'branch' => 'Judiciary', 'display_title' => 'Associate Justice', 'department_constraint' => 'SBAHM'],
            ['title' => 'SBAHM Justice 2', 'level' => 'Officer', 'branch' => 'Judiciary', 'display_title' => 'Associate Justice', 'department_constraint' => 'SBAHM'],
            ['title' => 'SITE Justice 1', 'level' => 'Officer', 'branch' => 'Judiciary', 'display_title' => 'Associate Justice', 'department_constraint' => 'SITE'],
            ['title' => 'SITE Justice 2', 'level' => 'Officer', 'branch' => 'Judiciary', 'display_title' => 'Associate Justice', 'department_constraint' => 'SITE'],
            ['title' => 'SNAHS Justice 1', 'level' => 'Officer', 'branch' => 'Judiciary', 'display_title' => 'Associate Justice', 'department_constraint' => 'SNAHS'],
            ['title' => 'SNAHS Justice 2', 'level' => 'Officer', 'branch' => 'Judiciary', 'display_title' => 'Associate Justice', 'department_constraint' => 'SNAHS'],
        ];
    }

    /**
     * Validate department constraints for Uniwide council positions
     */
    private function validateUniwidePositionConstraints($council, $positionTitle, $student)
    {
        // Get the position definition to check for department constraints
        $positions = $this->getUniversityWidePositions();
        $position = collect($positions)->firstWhere('title', $positionTitle);

        // If position has no department constraint, any student can be assigned
        if (!isset($position['department_constraint'])) {
            return true;
        }

        $requiredDepartment = $position['department_constraint'];

        // Check if student belongs to the required department
        if ($student->department->abbreviation !== $requiredDepartment) {
            return "This position requires a student from {$requiredDepartment} department.";
        }

        return true;
    }

    /**
     * Start evaluations for a council
     */
    public function startEvaluations(Council $council, EvaluationService $evaluationService)
    {
        $adviser = Auth::user();

        // Check if the council belongs to this adviser
        if ($council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $evaluationService->startEvaluations($council);

            return redirect()->route('adviser.councils.show', $council)
                ->with('success', 'Evaluations started successfully! All evaluation instances have been created.');
        } catch (\Exception $e) {
            return redirect()->route('adviser.councils.show', $council)
                ->with('error', 'Failed to start evaluations: ' . $e->getMessage());
        }
    }

    /**
     * Clear evaluations for a council (reset evaluation phase)
     */
    public function clearEvaluations(Council $council, EvaluationService $evaluationService)
    {
        $adviser = Auth::user();

        // Check if the council belongs to this adviser
        if ($council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $evaluationService->clearEvaluations($council);

            return redirect()->route('adviser.councils.show', $council)
                ->with('success', 'All evaluations have been cleared. You can now start evaluations again.');
        } catch (\Exception $e) {
            return redirect()->route('adviser.councils.show', $council)
                ->with('error', 'Failed to clear evaluations: ' . $e->getMessage());
        }
    }
}
