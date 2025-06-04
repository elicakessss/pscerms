<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EvaluationService;
use App\Models\LeadershipCertificateRequest;

class DashboardController extends Controller
{
    /**
     * Display the adviser dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(EvaluationService $evaluationService)
    {
        // Get the authenticated adviser
        $adviser = auth()->user();
        $isUniwideAdviser = $adviser->department->abbreviation === 'UNIWIDE';

        // Get adviser's councils
        $councils = \App\Models\Council::where('adviser_id', $adviser->id)
            ->with(['department', 'councilOfficers.student'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get student count based on adviser type
        if ($isUniwideAdviser) {
            $studentCount = \App\Models\Student::count();
        } else {
            $studentCount = \App\Models\Student::where('department_id', $adviser->department_id)->count();
        }

        // Get pending evaluations for this adviser
        $pendingEvaluations = $evaluationService->getPendingEvaluationsForAdviser($adviser->id);

        // Calculate overall evaluation progress
        $totalEvaluations = 0;
        $completedEvaluations = 0;
        $evaluationProgress = [];

        foreach ($councils as $council) {
            // Only include active councils in progress calculations
            if ($council->status === 'active' && $council->hasEvaluations()) {
                $progress = $evaluationService->getEvaluationProgress($council);
                $evaluationProgress[] = [
                    'council' => $council,
                    'progress' => $progress
                ];
                $totalEvaluations += $progress['evaluations_total'];
                $completedEvaluations += $progress['evaluations_completed'];
            }
        }

        $overallProgress = $totalEvaluations > 0 ? round(($completedEvaluations / $totalEvaluations) * 100, 1) : 0;

        // Get pending leadership certificate requests for this adviser's councils
        $pendingCertificateRequests = LeadershipCertificateRequest::whereHas('council', function($query) use ($adviser) {
                $query->where('adviser_id', $adviser->id);
            })
            ->where('status', 'pending')
            ->with(['student', 'council.department'])
            ->orderBy('requested_at', 'desc')
            ->get();

        return view('adviser.dashboard', compact(
            'adviser',
            'councils',
            'studentCount',
            'isUniwideAdviser',
            'pendingEvaluations',
            'evaluationProgress',
            'overallProgress',
            'totalEvaluations',
            'completedEvaluations',
            'pendingCertificateRequests'
        ));
    }

    /**
     * Approve a leadership certificate request.
     */
    public function approveCertificateRequest(LeadershipCertificateRequest $request)
    {
        $adviser = auth()->user();

        // Verify the request belongs to this adviser's council
        if ($request->council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->update([
            'status' => 'approved',
            'responded_at' => now(),
            'adviser_response' => 'Certificate request approved by adviser.'
        ]);

        return redirect()->route('adviser.dashboard')
            ->with('success', 'Certificate request approved successfully!');
    }

    /**
     * Dismiss a leadership certificate request.
     */
    public function dismissCertificateRequest(Request $httpRequest, LeadershipCertificateRequest $request)
    {
        $adviser = auth()->user();

        // Verify the request belongs to this adviser's council
        if ($request->council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $httpRequest->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $request->update([
            'status' => 'dismissed',
            'responded_at' => now(),
            'adviser_response' => $validated['reason'] ?? 'Certificate request dismissed by adviser.'
        ]);

        return redirect()->route('adviser.dashboard')
            ->with('success', 'Certificate request dismissed.');
    }

    /**
     * View details of a leadership certificate request.
     */
    public function viewCertificateRequest(LeadershipCertificateRequest $request)
    {
        $adviser = auth()->user();

        // Verify the request belongs to this adviser's council
        if ($request->council->adviser_id !== $adviser->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->load(['student', 'council.department']);

        // Get the student's performance data for this council
        $studentOfficer = $request->student->councilOfficers()
            ->where('council_id', $request->council_id)
            ->whereNotNull('final_score')
            ->first();

        return view('adviser.leadership_certificate.show', compact('request', 'studentOfficer'));
    }
}
