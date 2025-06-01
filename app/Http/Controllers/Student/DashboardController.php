<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\EvaluationService;

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
}
