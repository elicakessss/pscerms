<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Council;
use App\Models\Student;
use App\Models\CouncilOfficer;
use App\Services\EvaluationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CouncilController extends Controller
{
    /**
     * Display a listing of the adviser's councils.
     */
    public function index(EvaluationService $evaluationService)
    {
        $adviser = Auth::user();

        $councils = Council::where('adviser_id', $adviser->id)
            ->with(['department', 'councilOfficers.student'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get pending evaluations for this adviser
        $pendingEvaluations = $evaluationService->getPendingEvaluationsForAdviser($adviser->id);

        // Calculate statistics for summary cards
        $stats = [
            'total_councils' => $councils->count(),
            'active_councils' => $councils->where('status', 'active')->count(),
            'completed_councils' => $councils->where('status', 'completed')->count(),
            'pending_evaluations' => $pendingEvaluations->count(),
        ];

        return view('adviser.my_councils.index', compact('councils', 'stats'));
    }

    /**
     * Show the form for creating a new council.
     */
    public function create()
    {
        $adviser = Auth::user();
        return view('adviser.my_councils.create', compact('adviser'));
    }

    /**
     * Store a newly created council in storage.
     */
    public function store(Request $request)
    {
        $adviser = Auth::user();

        $validated = $request->validate([
            'academic_year' => [
                'required',
                'string',
                'regex:/^\d{4}-\d{4}$/',
                function ($attribute, $value, $fail) {
                    $years = explode('-', $value);
                    if (count($years) !== 2) {
                        $fail('The academic year must be in YYYY-YYYY format.');
                        return;
                    }

                    $startYear = (int) $years[0];
                    $endYear = (int) $years[1];

                    if ($endYear !== $startYear + 1) {
                        $fail('The academic year must be consecutive years (e.g., 2024-2025).');
                    }
                }
            ],
        ]);

        // Check if adviser already has a council for this academic year and department
        $existingCouncil = Council::where('adviser_id', $adviser->id)
            ->where('department_id', $adviser->department_id)
            ->where('academic_year', $validated['academic_year'])
            ->first();

        if ($existingCouncil) {
            return back()->withErrors([
                'academic_year' => 'You already have a council for this academic year in your department.'
            ])->withInput();
        }

        // Check if department already has a council for this academic year
        $departmentCouncil = Council::where('department_id', $adviser->department_id)
            ->where('academic_year', $validated['academic_year'])
            ->first();

        if ($departmentCouncil) {
            return back()->withErrors([
                'academic_year' => 'Your department already has a council for this academic year.'
            ])->withInput();
        }

        // Create council name based on department
        $councilName = 'Paulinian Student Government - ' . $adviser->department->abbreviation;

        // Create the council
        Council::create([
            'name' => $councilName,
            'academic_year' => $validated['academic_year'],
            'status' => 'active',
            'adviser_id' => $adviser->id,
            'department_id' => $adviser->department_id,
        ]);

        return redirect()->route('adviser.councils.index')
            ->with('success', 'Council created successfully! You can now start assigning officers.');
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

        // Get all positions for this council type and merge with existing officers
        $allPositions = $this->getAllPositionsForCouncil($council);

        // Get evaluation progress if evaluations have been started
        $evaluationProgress = null;
        if ($council->hasEvaluations()) {
            $evaluationProgress = $evaluationService->getEvaluationProgress($council);
        }

        return view('adviser.my_councils.show', compact('council', 'allPositions', 'evaluationProgress'));
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

        // Clean up the coordinator title and append "Coordinator" if not already present
        $coordinatorTitle = trim($validated['coordinator_title']);
        if (!str_ends_with(strtolower($coordinatorTitle), 'coordinator')) {
            $coordinatorTitle .= ' Coordinator';
        }

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
     * Add a senator position to the council.
     */
    public function addSenator(Request $request, Council $council)
    {
        return $this->addDynamicPosition($request, $council, 'Senator', 'Officer');
    }

    /**
     * Add a congressman position to the council.
     */
    public function addCongressman(Request $request, Council $council)
    {
        return $this->addDynamicPosition($request, $council, 'Congressman', 'Officer');
    }

    /**
     * Add a justice position to the council.
     */
    public function addJustice(Request $request, Council $council)
    {
        return $this->addDynamicPosition($request, $council, 'Associate Justice', 'Officer');
    }

    /**
     * Generic method to add dynamic positions
     */
    private function addDynamicPosition(Request $request, Council $council, $positionType, $positionLevel)
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
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::with('department')->find($validated['student_id']);

        // Check if student is already in a council for this academic year
        $existingCouncilMembership = CouncilOfficer::whereHas('council', function($query) use ($council) {
            $query->where('academic_year', $council->academic_year);
        })->where('student_id', $validated['student_id'])->first();

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

        // Check if this position already exists
        $existingPosition = CouncilOfficer::where('council_id', $council->id)
            ->where('position_title', $validated['position_title'])
            ->first();

        if ($existingPosition) {
            return back()->withErrors(['position_title' => 'This position already exists.']);
        }

        // Create the position with assigned student
        CouncilOfficer::create([
            'council_id' => $council->id,
            'student_id' => $validated['student_id'],
            'position_title' => $validated['position_title'],
            'position_level' => $positionLevel,
        ]);

        return redirect()->route('adviser.councils.show', $council)
            ->with('success', ucfirst($positionType) . ' position created and student assigned successfully!');
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
     * Delete a council.
     */
    public function destroy(Council $council)
    {
        $adviser = Auth::user();

        // Check if the council belongs to this adviser
        if ($council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if council has any evaluations - prevent deletion if evaluations exist
        if ($council->hasEvaluations()) {
            return back()->withErrors([
                'error' => 'Cannot delete council with existing evaluations. Please clear evaluations first.'
            ]);
        }

        // Store council name for success message
        $councilName = $council->name;

        // Delete all associated council officers first
        $council->councilOfficers()->delete();

        // Delete the council
        $council->delete();

        return redirect()->route('adviser.councils.index')
            ->with('success', "Council '{$councilName}' has been deleted successfully!");
    }

    /**
     * Search for available students for assignment
     */
    public function searchStudents(Request $request, Council $council)
    {
        $adviser = Auth::user();

        // Check if the council belongs to this adviser
        if ($council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        $search = $request->get('search', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        // Get available students based on council type
        if ($council->department->abbreviation === 'UNIWIDE') {
            // For Uniwide councils, get students from all departments except UNIWIDE
            $students = Student::whereHas('department', function($query) {
                $query->where('abbreviation', '!=', 'UNIWIDE');
            })
            ->whereDoesntHave('councilOfficers', function($query) use ($council) {
                $query->whereHas('council', function($subQuery) use ($council) {
                    $subQuery->where('academic_year', $council->academic_year);
                });
            })
            ->where(function($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('id_number', 'like', "%{$search}%");
            })
            ->with('department')
            ->orderBy('last_name')
            ->limit(10)
            ->get();
        } else {
            // For departmental councils, get students from the same department
            $students = Student::where('department_id', $council->department_id)
                ->whereDoesntHave('councilOfficers', function($query) use ($council) {
                    $query->whereHas('council', function($subQuery) use ($council) {
                        $subQuery->where('academic_year', $council->academic_year);
                    });
                })
                ->where(function($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('id_number', 'like', "%{$search}%");
                })
                ->orderBy('last_name')
                ->limit(10)
                ->get();
        }

        return response()->json($students->map(function($student) use ($council) {
            return [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'id_number' => $student->id_number,
                'department' => $council->department->abbreviation === 'UNIWIDE' ? $student->department->abbreviation : null,
            ];
        }));
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

        // Add any dynamic positions that exist but aren't in base positions
        $dynamicOfficers = $council->councilOfficers->filter(function($officer) use ($basePositions) {
            return !collect($basePositions)->pluck('title')->contains($officer->position_title);
        });

        foreach ($dynamicOfficers as $officer) {
            // Determine the branch based on position title
            $branch = 'Coordinator'; // Default
            if (str_contains(strtolower($officer->position_title), 'senator')) {
                $branch = 'Senate';
            } elseif (str_contains(strtolower($officer->position_title), 'representative')) {
                $branch = 'House of Representatives';
            } elseif (str_contains(strtolower($officer->position_title), 'justice')) {
                $branch = 'Judicial';
            }

            $allPositions->push([
                'title' => $officer->position_title,
                'branch' => $branch,
                'level' => $officer->position_level,
                'officer' => $officer,
                'is_filled' => true,
            ]);
        }

        // Sort positions according to the required order for UNIWIDE councils
        if ($council->department->abbreviation === 'UNIWIDE') {
            $allPositions = $allPositions->sortBy(function($position) {
                $branch = $position['branch'];
                $title = $position['title'];

                // Define order: Executive, Senate, House of Representatives, Judicial, Coordinator
                $branchOrder = [
                    'Executive' => 1,
                    'Senate' => 2,
                    'House of Representatives' => 3,
                    'Judicial' => 4,
                    'Coordinator' => 5
                ];

                $order = $branchOrder[$branch] ?? 6;

                // Within Executive branch, maintain hierarchy
                if ($branch === 'Executive') {
                    $execOrder = [
                        'President' => 1,
                        'Vice President' => 2,
                        'Secretary' => 3,
                        'Assistant Secretary' => 4,
                        'Treasurer' => 5,
                        'Assistant Treasurer' => 6,
                        'Auditor' => 7,
                        'Public Relations Officer' => 8,
                        'Assistant Public Relations Officer' => 9,
                    ];
                    return ($order * 100) + ($execOrder[$title] ?? 99);
                }

                return $order * 100;
            });
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

            // Note: Senators, Representatives, and Associate Justices are now added manually by advisers
            // They will appear in the list only after being created through the dynamic position forms
        ];
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

    /**
     * Assign an officer as a peer evaluator
     */
    public function assignPeerEvaluator(Request $request, Council $council, CouncilOfficer $officer)
    {
        $adviser = Auth::user();

        // Check if the council belongs to this adviser
        if ($council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if the officer belongs to this council
        if ($officer->council_id !== $council->id) {
            abort(404, 'Officer not found in this council.');
        }

        // Check if evaluations have already started
        if ($council->hasEvaluations()) {
            return redirect()->route('adviser.councils.show', $council)
                ->with('error', 'Cannot assign peer evaluators after evaluations have started.');
        }

        $validated = $request->validate([
            'peer_evaluator_level' => 'required|integer|in:1,2'
        ]);

        try {
            DB::transaction(function () use ($council, $officer, $validated) {
                // Check if there's already a peer evaluator at this level
                $existingPeerEvaluator = $council->councilOfficers()
                    ->where('is_peer_evaluator', true)
                    ->where('peer_evaluator_level', $validated['peer_evaluator_level'])
                    ->first();

                if ($existingPeerEvaluator) {
                    // Remove the existing peer evaluator at this level
                    $existingPeerEvaluator->update([
                        'is_peer_evaluator' => false,
                        'peer_evaluator_level' => null,
                    ]);
                }

                // Assign the new peer evaluator
                $officer->update([
                    'is_peer_evaluator' => true,
                    'peer_evaluator_level' => $validated['peer_evaluator_level'],
                ]);
            });

            $levelText = $validated['peer_evaluator_level'] === 1 ? 'Level 1' : 'Level 2';
            return redirect()->route('adviser.councils.show', $council)
                ->with('success', "Successfully assigned {$officer->student->first_name} {$officer->student->last_name} as {$levelText} peer evaluator.");

        } catch (\Exception $e) {
            return redirect()->route('adviser.councils.show', $council)
                ->with('error', 'Failed to assign peer evaluator: ' . $e->getMessage());
        }
    }

    /**
     * Remove an officer as a peer evaluator
     */
    public function removePeerEvaluator(Council $council, CouncilOfficer $officer)
    {
        $adviser = Auth::user();

        // Check if the council belongs to this adviser
        if ($council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if the officer belongs to this council
        if ($officer->council_id !== $council->id) {
            abort(404, 'Officer not found in this council.');
        }

        // Check if evaluations have already started
        if ($council->hasEvaluations()) {
            return redirect()->route('adviser.councils.show', $council)
                ->with('error', 'Cannot remove peer evaluators after evaluations have started.');
        }

        try {
            $officer->update([
                'is_peer_evaluator' => false,
                'peer_evaluator_level' => null,
            ]);

            return redirect()->route('adviser.councils.show', $council)
                ->with('success', "Successfully removed {$officer->student->first_name} {$officer->student->last_name} as peer evaluator.");

        } catch (\Exception $e) {
            return redirect()->route('adviser.councils.show', $council)
                ->with('error', 'Failed to remove peer evaluator: ' . $e->getMessage());
        }
    }
}
