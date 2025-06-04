<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EvaluationService;
use App\Models\LeadershipCertificateRequest;
use App\Models\Council;

class DashboardController extends Controller
{
    /**
     * Display the student dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(EvaluationService $evaluationService)
    {
        // Get the authenticated student
        $student = auth()->user();

        // Get current council (active council for current academic year)
        $currentCouncil = null;
        $currentOfficer = $student->councilOfficers()
            ->whereHas('council', function($query) {
                $query->where('status', 'active');
            })
            ->with(['council.department', 'council.adviser'])
            ->first();

        if ($currentOfficer) {
            $currentCouncil = $currentOfficer->council;
        }

        // Get pending evaluations for this student (only unfinished ones)
        $pendingEvaluations = $evaluationService->getPendingEvaluationsForStudent($student->id);

        // Group evaluations by type
        $selfEvaluations = $pendingEvaluations->where('evaluator_type', 'self');
        $peerEvaluations = $pendingEvaluations->where('evaluator_type', 'peer');

        // Get completed councils count for portfolio preview
        $completedCouncilsCount = $student->councilOfficers()
            ->whereNotNull('completed_at')
            ->whereNotNull('final_score')
            ->count();

        return view('student.dashboard', compact(
            'student',
            'currentCouncil',
            'currentOfficer',
            'selfEvaluations',
            'peerEvaluations',
            'completedCouncilsCount'
        ));
    }

    /**
     * Show the form for creating a new leadership certificate request.
     */
    public function createCertificateRequest()
    {
        $student = auth()->user();

        // Get all council officers where the student has completed service with their rank information
        $completedOfficers = $student->councilOfficers()
            ->with(['council.department'])
            ->whereNotNull('completed_at')
            ->whereNotNull('final_score')
            ->get();

        // Group by council and include rank information
        $councilsWithRanks = $completedOfficers->groupBy('council_id')->map(function ($officers) {
            $council = $officers->first()->council;
            $studentOfficer = $officers->first(); // Student's officer record

            return [
                'council' => $council,
                'student_rank' => $studentOfficer->rank,
                'student_final_score' => $studentOfficer->final_score,
                'student_position' => $studentOfficer->position_title,
                'student_self_score' => $studentOfficer->self_score,
                'student_peer_score' => $studentOfficer->peer_score,
                'student_adviser_score' => $studentOfficer->adviser_score,
            ];
        });

        // Separate UNIWIDE (campus) and departmental councils
        $campusCouncils = $councilsWithRanks->filter(function ($item) {
            return $item['council']->department->abbreviation === 'UNIWIDE';
        });

        $departmentalCouncils = $councilsWithRanks->filter(function ($item) {
            return $item['council']->department->abbreviation !== 'UNIWIDE';
        });

        return view('student.leadership_certificate.create', compact(
            'campusCouncils',
            'departmentalCouncils'
        ));
    }

    /**
     * Store a newly created leadership certificate request.
     */
    public function storeCertificateRequest(Request $request)
    {
        $student = auth()->user();

        $validated = $request->validate([
            'certificate_type' => 'required|in:campus,departmental',
            'council_id' => 'required|exists:councils,id',
            'is_graduating' => 'required|boolean',
        ]);

        // Verify that the student actually served in the selected council
        $councilOfficer = $student->councilOfficers()
            ->where('council_id', $validated['council_id'])
            ->whereNotNull('completed_at')
            ->whereNotNull('final_score')
            ->first();

        if (!$councilOfficer) {
            return back()->withErrors(['council_id' => 'You did not complete service in the selected council.']);
        }

        // Check if request already exists for this council
        $existingRequest = LeadershipCertificateRequest::where('student_id', $student->id)
            ->where('council_id', $validated['council_id'])
            ->first();

        if ($existingRequest) {
            return back()->withErrors(['council_id' => 'You have already requested a certificate for this council.']);
        }

        // Create the request
        LeadershipCertificateRequest::create([
            'student_id' => $student->id,
            'council_id' => $validated['council_id'],
            'certificate_type' => $validated['certificate_type'],
            'is_graduating' => $validated['is_graduating'],
            'status' => 'pending',
        ]);

        return redirect()->route('student.dashboard')
            ->with('success', 'Leadership certificate request submitted successfully! Your adviser will be notified.');
    }

    /**
     * Display the student's certificate requests.
     */
    public function certificateRequests()
    {
        $student = auth()->user();

        $requests = LeadershipCertificateRequest::where('student_id', $student->id)
            ->with(['council.department', 'council.adviser'])
            ->orderBy('requested_at', 'desc')
            ->get();

        return view('student.leadership_certificate.index', compact('requests'));
    }
}
