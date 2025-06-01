<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EvaluationService;

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
            if ($council->hasEvaluations()) {
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

        return view('adviser.dashboard', compact(
            'adviser',
            'councils',
            'studentCount',
            'isUniwideAdviser',
            'pendingEvaluations',
            'evaluationProgress',
            'overallProgress',
            'totalEvaluations',
            'completedEvaluations'
        ));
    }
}
