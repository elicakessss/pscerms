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

        // Check if student has completed service in any council
        $hasCompletedService = $student->councilOfficers()
            ->whereNotNull('completed_at')
            ->whereNotNull('final_score')
            ->exists();

        if (!$hasCompletedService) {
            return redirect()->route('student.dashboard')
                ->with('error', 'You need to complete service in at least one council to request a leadership certificate.');
        }

        // Get latest council terms for each type
        $latestUniwideOfficer = $student->getLatestUniwideCouncilOfficer();
        $latestDepartmentalOfficer = $student->getLatestDepartmentalCouncilOfficer();

        // Determine available certificate types
        $canRequestCampus = $latestUniwideOfficer !== null;
        $canRequestDepartmental = $latestDepartmentalOfficer !== null;

        // If student has no completed service, redirect
        if (!$canRequestCampus && !$canRequestDepartmental) {
            return redirect()->route('student.dashboard')
                ->with('error', 'You need to complete service in at least one council to request a leadership certificate.');
        }

        // Ensure we pass null if the officer doesn't exist
        $latestUniwideOfficer = $canRequestCampus ? $latestUniwideOfficer : null;
        $latestDepartmentalOfficer = $canRequestDepartmental ? $latestDepartmentalOfficer : null;

        return view('student.leadership_certificate.create', compact(
            'latestUniwideOfficer',
            'latestDepartmentalOfficer',
            'canRequestCampus',
            'canRequestDepartmental'
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
            'is_graduating' => 'required|boolean',
        ]);

        // Determine the council based on certificate type and latest term served
        if ($validated['certificate_type'] === 'campus') {
            $councilOfficer = $student->getLatestUniwideCouncilOfficer();
            if (!$councilOfficer) {
                return back()->withErrors(['certificate_type' => 'You have not completed service in any UNIWIDE council.']);
            }
        } else {
            $councilOfficer = $student->getLatestDepartmentalCouncilOfficer();
            if (!$councilOfficer) {
                return back()->withErrors(['certificate_type' => 'You have not completed service in any departmental council.']);
            }
        }

        $councilId = $councilOfficer->council_id;

        // Check if request already exists for this certificate type
        $existingRequest = LeadershipCertificateRequest::where('student_id', $student->id)
            ->where('certificate_type', $validated['certificate_type'])
            ->first();

        if ($existingRequest) {
            return back()->withErrors(['certificate_type' => 'You have already requested a ' . $validated['certificate_type'] . ' leadership certificate.']);
        }

        // Create the request
        LeadershipCertificateRequest::create([
            'student_id' => $student->id,
            'council_id' => $councilId,
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
            ->whereHas('council') // Only include requests where council still exists
            ->orderBy('requested_at', 'desc')
            ->get();

        return view('student.leadership_certificate.index', compact('requests'));
    }
}
