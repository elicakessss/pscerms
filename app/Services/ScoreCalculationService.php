<?php

namespace App\Services;

use App\Models\Council;
use App\Models\CouncilOfficer;
use App\Models\Evaluation;
use App\Models\EvaluationForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScoreCalculationService
{
    /**
     * Calculate scores for all officers in a council
     */
    public function calculateCouncilScores(Council $council)
    {
        $officers = $council->councilOfficers()->with('student')->get();

        foreach ($officers as $officer) {
            $this->calculateOfficerScore($officer);
        }

        // After calculating all scores, assign rankings
        $this->assignRankings($council);

        return true;
    }

    /**
     * Calculate individual officer score
     */
    public function calculateOfficerScore(CouncilOfficer $officer)
    {
        try {
            DB::transaction(function () use ($officer) {
                // Step 3.1: Calculate individual averages
                $selfScore = $this->calculateSelfEvaluationAverage($officer);
                $peerScore = $this->calculatePeerEvaluationAverage($officer);
                $adviserScore = $this->calculateAdviserEvaluationAverage($officer);

                // Step 3.2: Apply weighted formula
                $finalScore = $this->calculateWeightedScore($selfScore, $peerScore, $adviserScore);

                // Update the council officer record
                $officer->update([
                    'self_score' => $selfScore,
                    'peer_score' => $peerScore,
                    'adviser_score' => $adviserScore,
                    'final_score' => $finalScore,
                ]);

                Log::info("Calculated scores for officer {$officer->id}: Self={$selfScore}, Peer={$peerScore}, Adviser={$adviserScore}, Final={$finalScore}");
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Error calculating score for officer {$officer->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate self-evaluation average
     */
    private function calculateSelfEvaluationAverage(CouncilOfficer $officer)
    {
        $evaluation = Evaluation::where('council_id', $officer->council_id)
            ->where('evaluator_id', $officer->student_id)
            ->where('evaluator_type', 'self')
            ->where('evaluated_student_id', $officer->student_id)
            ->where('status', 'completed')
            ->first();

        if (!$evaluation) {
            return null;
        }

        return $this->calculateEvaluationAverage($evaluation);
    }

    /**
     * Calculate peer evaluation average
     */
    private function calculatePeerEvaluationAverage(CouncilOfficer $officer)
    {
        $evaluations = Evaluation::where('council_id', $officer->council_id)
            ->where('evaluator_type', 'peer')
            ->where('evaluated_student_id', $officer->student_id)
            ->where('status', 'completed')
            ->get();

        if ($evaluations->isEmpty()) {
            return null;
        }

        $totalScore = 0;
        $validEvaluations = 0;

        foreach ($evaluations as $evaluation) {
            $score = $this->calculateEvaluationAverage($evaluation);
            if ($score !== null) {
                $totalScore += $score;
                $validEvaluations++;
            }
        }

        return $validEvaluations > 0 ? round($totalScore / $validEvaluations, 2) : null;
    }

    /**
     * Calculate adviser evaluation average
     */
    private function calculateAdviserEvaluationAverage(CouncilOfficer $officer)
    {
        $evaluation = Evaluation::where('council_id', $officer->council_id)
            ->where('evaluator_type', 'adviser')
            ->where('evaluated_student_id', $officer->student_id)
            ->where('status', 'completed')
            ->first();

        if (!$evaluation) {
            return null;
        }

        return $this->calculateEvaluationAverage($evaluation);
    }

    /**
     * Calculate average score from evaluation forms
     */
    private function calculateEvaluationAverage(Evaluation $evaluation)
    {
        $forms = $evaluation->evaluationForms;

        if ($forms->isEmpty()) {
            return null;
        }

        $totalScore = 0;
        $questionCount = 0;

        foreach ($forms as $form) {
            // Convert answer to numeric value
            $score = (float) $form->answer;
            $totalScore += $score;
            $questionCount++;
        }

        return $questionCount > 0 ? round($totalScore / $questionCount, 2) : null;
    }

    /**
     * Calculate weighted final score
     * Formula: (Self×10%) + (Peer×25%) + (Adviser×50%) + (LOS×15%)
     */
    private function calculateWeightedScore($selfScore, $peerScore, $adviserScore)
    {
        // Handle cases where some scores might be missing
        $weights = [
            'self' => 0.10,
            'peer' => 0.25,
            'adviser' => 0.50,
            'los' => 0.15  // Length of Service is included in each evaluation
        ];

        $finalScore = 0;
        $totalWeight = 0;

        // Self evaluation (10%)
        if ($selfScore !== null) {
            $finalScore += $selfScore * $weights['self'];
            $totalWeight += $weights['self'];
        }

        // Peer evaluation (25%)
        if ($peerScore !== null) {
            $finalScore += $peerScore * $weights['peer'];
            $totalWeight += $weights['peer'];
        } else {
            // If no peer evaluation, redistribute weight to adviser
            $weights['adviser'] += $weights['peer'];
        }

        // Adviser evaluation (50% + redistributed peer weight if needed)
        if ($adviserScore !== null) {
            $finalScore += $adviserScore * $weights['adviser'];
            $totalWeight += $weights['adviser'];
        }

        // Note: Length of Service is already included in individual evaluations
        // so we don't need to add it separately

        return $totalWeight > 0 ? round($finalScore, 2) : null;
    }

    /**
     * Assign award rankings based on final scores
     */
    public function assignRankings(Council $council)
    {
        $officers = $council->councilOfficers()
            ->whereNotNull('final_score')
            ->get();

        foreach ($officers as $officer) {
            $awardCategory = $this->getRankingCategory($officer->final_score);
            $officer->update(['rank' => $awardCategory]);
        }
    }

    /**
     * Get ranking category based on final score
     * Gold: 2.41-3.00, Silver: 1.81-2.40, Bronze: 1.21-1.80, No Award: Below 1.21
     */
    public function getRankingCategory($finalScore)
    {
        if ($finalScore >= 2.41) {
            return 'Gold';
        } elseif ($finalScore >= 1.81) {
            return 'Silver';
        } elseif ($finalScore >= 1.21) {
            return 'Bronze';
        } else {
            return 'No Award';
        }
    }

    /**
     * Check if all required evaluations are completed for score calculation
     */
    public function canCalculateScores(Council $council)
    {
        $officers = $council->councilOfficers;

        foreach ($officers as $officer) {
            // Check if self evaluation is completed
            $selfEvaluation = Evaluation::where('council_id', $council->id)
                ->where('evaluator_id', $officer->student_id)
                ->where('evaluator_type', 'self')
                ->where('evaluated_student_id', $officer->student_id)
                ->where('status', 'completed')
                ->exists();

            // Check if adviser evaluation is completed
            $adviserEvaluation = Evaluation::where('council_id', $council->id)
                ->where('evaluator_type', 'adviser')
                ->where('evaluated_student_id', $officer->student_id)
                ->where('status', 'completed')
                ->exists();

            if (!$selfEvaluation || !$adviserEvaluation) {
                return false;
            }
        }

        return true;
    }
}
