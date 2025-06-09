<?php

namespace App\Services;

use App\Models\Council;
use App\Models\Evaluation;
use App\Models\CouncilOfficer;
use Illuminate\Support\Facades\DB;

class EvaluationService
{
    /**
     * Start evaluations for a council
     * Creates all required evaluation instances and starts the evaluation instance
     */
    public function startEvaluations(Council $council)
    {
        if (!$council->canStartEvaluationInstance()) {
            throw new \Exception('Cannot start evaluations for this council.');
        }

        DB::transaction(function () use ($council) {
            // Update council evaluation instance status
            $council->update([
                'evaluation_instance_status' => 'active',
                'evaluation_instance_started_at' => now(),
            ]);

            // Get all council officers
            $officers = $council->councilOfficers()->with('student')->get();

            if ($officers->isEmpty()) {
                throw new \Exception('No officers found in this council.');
            }

            // Create self-evaluations for all council members
            $this->createSelfEvaluations($council, $officers);

            // Create peer evaluations from executives to all members
            $this->createPeerEvaluations($council, $officers);

            // Create adviser evaluations from adviser to all members
            $this->createAdviserEvaluations($council, $officers);
        });

        return true;
    }

    /**
     * Finalize evaluation instance for a council
     * Locks all evaluations and calculates final scores
     */
    public function finalizeEvaluationInstance(Council $council)
    {
        if (!$council->canFinalizeEvaluationInstance()) {
            throw new \Exception('Cannot finalize evaluation instance. Not all evaluations are completed.');
        }

        DB::transaction(function () use ($council) {
            // Update council evaluation instance status
            $council->update([
                'evaluation_instance_status' => 'finalized',
                'evaluation_instance_finalized_at' => now(),
            ]);

            // Calculate final scores for all officers
            $scoreService = new ScoreCalculationService();
            $scoreService->calculateCouncilScores($council);

            // Mark council as completed if all scores are calculated
            $this->markCouncilAsCompletedIfReady($council);
        });

        return true;
    }

    /**
     * Create self-evaluation instances for all council members
     */
    private function createSelfEvaluations(Council $council, $officers)
    {
        foreach ($officers as $officer) {
            Evaluation::firstOrCreate([
                'council_id' => $council->id,
                'evaluator_id' => $officer->student_id,
                'evaluator_type' => 'self',
                'evaluated_student_id' => $officer->student_id,
            ], [
                'evaluation_type' => 'rating',
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Create peer evaluation instances based on assigned peer evaluators
     * Level 1 peer evaluator: Evaluates all members
     * Level 2 peer evaluator: Evaluates all members
     */
    private function createPeerEvaluations(Council $council, $officers)
    {
        // Get assigned peer evaluators
        $peerEvaluators = $officers->filter(function ($officer) {
            return $officer->is_peer_evaluator;
        });

        // Check if we have the required peer evaluators
        $level1PeerEvaluator = $peerEvaluators->where('peer_evaluator_level', 1)->first();
        $level2PeerEvaluator = $peerEvaluators->where('peer_evaluator_level', 2)->first();

        if (!$level1PeerEvaluator || !$level2PeerEvaluator) {
            throw new \Exception('Both Level 1 and Level 2 peer evaluators must be assigned before starting evaluations.');
        }

        // Both peer evaluators evaluate ALL members (except themselves)
        foreach ([$level1PeerEvaluator, $level2PeerEvaluator] as $peerEvaluator) {
            foreach ($officers as $officer) {
                // Skip self-evaluation (that's handled separately)
                if ($peerEvaluator->student_id === $officer->student_id) {
                    continue;
                }

                Evaluation::firstOrCreate([
                    'council_id' => $council->id,
                    'evaluator_id' => $peerEvaluator->student_id,
                    'evaluator_type' => 'peer',
                    'evaluated_student_id' => $officer->student_id,
                ], [
                    'evaluation_type' => 'rating',
                    'status' => 'pending',
                ]);
            }
        }
    }

    /**
     * Create adviser evaluation instances from adviser to all members
     */
    private function createAdviserEvaluations(Council $council, $officers)
    {
        foreach ($officers as $officer) {
            Evaluation::firstOrCreate([
                'council_id' => $council->id,
                'evaluator_id' => $council->adviser_id,
                'evaluator_type' => 'adviser',
                'evaluated_student_id' => $officer->student_id,
            ], [
                'evaluation_type' => 'rating',
                'status' => 'pending',
            ]);
        }
    }



    /**
     * Get pending evaluations for a student
     */
    public function getPendingEvaluationsForStudent($studentId)
    {
        return Evaluation::where('evaluator_id', $studentId)
            ->whereIn('evaluator_type', ['self', 'peer'])
            ->where('status', 'pending')
            ->with(['council.department', 'evaluatedStudent'])
            ->get();
    }

    /**
     * Get pending evaluations for an adviser
     */
    public function getPendingEvaluationsForAdviser($adviserId)
    {
        return Evaluation::where('evaluator_id', $adviserId)
            ->where('evaluator_type', 'adviser')
            ->where('status', 'pending')
            ->with(['council.department', 'evaluatedStudent'])
            ->get();
    }

    /**
     * Clear all evaluations for a council (reset evaluation phase)
     */
    public function clearEvaluations(Council $council)
    {
        if (!$council->hasEvaluations()) {
            throw new \Exception('No evaluations found for this council.');
        }

        DB::transaction(function () use ($council) {
            // Delete all evaluation forms first (due to foreign key constraints)
            $evaluationIds = $council->evaluations()->pluck('id');
            \App\Models\EvaluationForm::whereIn('evaluation_id', $evaluationIds)->delete();

            // Delete all evaluations
            $council->evaluations()->delete();

            // Reset evaluation instance status
            $council->update([
                'evaluation_instance_status' => 'not_started',
                'evaluation_instance_started_at' => null,
                'evaluation_instance_finalized_at' => null,
            ]);

            // Clear scores from council officers
            $council->councilOfficers()->update([
                'self_score' => null,
                'peer_score' => null,
                'adviser_score' => null,
                'final_score' => null,
                'rank' => null,
                'completed_at' => null,
            ]);
        });

        return true;
    }

    /**
     * Calculate scores for a council when evaluations are completed
     */
    public function calculateScoresIfReady(Council $council)
    {
        $scoreService = new ScoreCalculationService();

        if ($scoreService->canCalculateScores($council)) {
            $scoreService->calculateCouncilScores($council);

            // Mark council as completed if all scores are calculated
            $this->markCouncilAsCompletedIfReady($council);

            return true;
        }

        return false;
    }

    /**
     * Mark council as completed if all evaluations and scores are done
     */
    private function markCouncilAsCompletedIfReady(Council $council)
    {
        $allOfficersHaveScores = $council->councilOfficers()
            ->whereNull('final_score')
            ->count() === 0;

        if ($allOfficersHaveScores && $council->status === 'active') {
            $council->update([
                'status' => 'completed'
            ]);

            // Update completed_at timestamp for all officers
            $council->councilOfficers()->update([
                'completed_at' => now()
            ]);

            // Log the completion
            \Log::info("Council {$council->name} (ID: {$council->id}) has been automatically completed. All evaluations and score calculations are finished.");
        }
    }

    /**
     * Get detailed evaluation progress for a council
     */
    public function getEvaluationProgress(Council $council)
    {
        $officers = $council->councilOfficers()->with('student')->get();
        $totalOfficers = $officers->count();

        if ($totalOfficers === 0) {
            return [
                'total_officers' => 0,
                'evaluations_completed' => 0,
                'evaluations_total' => 0,
                'scores_calculated' => 0,
                'completion_percentage' => 0,
                'is_ready_for_completion' => false,
                'missing_evaluations' => []
            ];
        }

        $evaluationsCompleted = 0;
        $evaluationsTotal = 0;
        $scoresCalculated = 0;
        $missingEvaluations = [];

        foreach ($officers as $officer) {
            // Self evaluation
            $selfEval = $council->evaluations()
                ->where('evaluator_id', $officer->student_id)
                ->where('evaluator_type', 'self')
                ->where('evaluated_student_id', $officer->student_id)
                ->first();

            $evaluationsTotal++;
            if ($selfEval && $selfEval->status === 'completed') {
                $evaluationsCompleted++;
            } else {
                $missingEvaluations[] = [
                    'type' => 'self',
                    'evaluator' => $officer->student->first_name . ' ' . $officer->student->last_name,
                    'evaluated' => $officer->student->first_name . ' ' . $officer->student->last_name
                ];
            }

            // Adviser evaluation
            $adviserEval = $council->evaluations()
                ->where('evaluator_type', 'adviser')
                ->where('evaluated_student_id', $officer->student_id)
                ->first();

            $evaluationsTotal++;
            if ($adviserEval && $adviserEval->status === 'completed') {
                $evaluationsCompleted++;
            } else {
                $missingEvaluations[] = [
                    'type' => 'adviser',
                    'evaluator' => $council->adviser->first_name . ' ' . $council->adviser->last_name,
                    'evaluated' => $officer->student->first_name . ' ' . $officer->student->last_name
                ];
            }

            // Check if scores are calculated
            if ($officer->final_score !== null) {
                $scoresCalculated++;
            }
        }

        // Add peer evaluations based on assigned peer evaluators
        $peerEvaluators = $officers->filter(function ($officer) {
            return $officer->is_peer_evaluator;
        });

        // Both peer evaluators evaluate all members (except themselves)
        foreach ($peerEvaluators as $peerEvaluator) {
            foreach ($officers as $officer) {
                if ($peerEvaluator->student_id !== $officer->student_id) {
                    $peerEval = $council->evaluations()
                        ->where('evaluator_id', $peerEvaluator->student_id)
                        ->where('evaluator_type', 'peer')
                        ->where('evaluated_student_id', $officer->student_id)
                        ->first();

                    $evaluationsTotal++;
                    if ($peerEval && $peerEval->status === 'completed') {
                        $evaluationsCompleted++;
                    } else {
                        $levelText = $peerEvaluator->peer_evaluator_level === 1 ? 'Level 1' : 'Level 2';
                        $missingEvaluations[] = [
                            'type' => 'peer',
                            'evaluator' => $peerEvaluator->student->first_name . ' ' . $peerEvaluator->student->last_name . " ({$levelText})",
                            'evaluated' => $officer->student->first_name . ' ' . $officer->student->last_name
                        ];
                    }
                }
            }
        }

        $completionPercentage = $evaluationsTotal > 0 ? round(($evaluationsCompleted / $evaluationsTotal) * 100, 1) : 0;
        $isReadyForCompletion = $scoresCalculated === $totalOfficers && $scoresCalculated > 0;

        return [
            'total_officers' => $totalOfficers,
            'evaluations_completed' => $evaluationsCompleted,
            'evaluations_total' => $evaluationsTotal,
            'scores_calculated' => $scoresCalculated,
            'completion_percentage' => $completionPercentage,
            'is_ready_for_completion' => $isReadyForCompletion,
            'missing_evaluations' => $missingEvaluations
        ];
    }

    /**
     * Recalculate scores for a specific officer
     */
    public function recalculateOfficerScore(CouncilOfficer $officer)
    {
        $scoreService = new ScoreCalculationService();
        return $scoreService->calculateOfficerScore($officer);
    }

    /**
     * Get position level based on title
     * 1 = President/Governor
     * 2 = Vice President/Vice Governor
     * 10 = Others
     */
    private function getPositionLevel($positionTitle)
    {
        // Check for Vice positions first (more specific)
        $level2Positions = ['Vice President', 'Vice Governor'];
        foreach ($level2Positions as $position) {
            if (stripos($positionTitle, $position) !== false) {
                return 2;
            }
        }

        // Then check for main positions
        $level1Positions = ['President', 'Governor'];
        foreach ($level1Positions as $position) {
            if (stripos($positionTitle, $position) !== false) {
                return 1;
            }
        }

        return 10;
    }

    /**
     * Force calculate scores for a council (even if not all evaluations are complete)
     */
    public function forceCalculateScores(Council $council)
    {
        $scoreService = new ScoreCalculationService();
        return $scoreService->calculateCouncilScores($council);
    }
}
