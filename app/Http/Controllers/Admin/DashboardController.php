<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EvaluationService;
use App\Models\Council;
use App\Models\Student;
use App\Models\Adviser;
use App\Models\Admin;

class DashboardController extends Controller
{
    public function index(EvaluationService $evaluationService)
    {
        // Get user counts
        $userCounts = [
            'students' => Student::count(),
            'advisers' => Adviser::count(),
            'admins' => Admin::count(),
            'total_users' => Student::count() + Adviser::count() + Admin::count(),
        ];

        // Get council counts
        $councilCounts = [
            'total_councils' => Council::count(),
            'active_councils' => Council::where('status', 'active')->count(),
            'completed_councils' => Council::where('status', 'completed')->count(),
        ];

        // Get all active councils with evaluation progress
        $activeCouncils = Council::where('status', 'active')
            ->with(['department', 'adviser', 'councilOfficers.student'])
            ->orderBy('created_at', 'desc')
            ->get();

        $evaluationProgress = [];
        foreach ($activeCouncils as $council) {
            // Only include active councils with evaluations in progress
            if ($council->status === 'active' && $council->hasEvaluations()) {
                $progress = $evaluationService->getEvaluationProgress($council);
                $evaluationProgress[] = [
                    'council' => $council,
                    'progress' => $progress
                ];
            }
        }

        return view('admin.dashboard', compact(
            'userCounts',
            'councilCounts',
            'activeCouncils',
            'evaluationProgress'
        ));
    }
}
