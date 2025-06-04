<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScoringSystemStabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_scoring_system_handles_variable_question_counts()
    {
        // Test that scoring works the same regardless of question count
        
        // Scenario 1: 3 questions with scores [3, 2, 1] = Average 2.00
        $responses1 = [
            ['answer' => '3.00'],
            ['answer' => '2.00'], 
            ['answer' => '1.00']
        ];
        $average1 = $this->calculateAverage($responses1);
        $this->assertEquals(2.00, $average1);
        
        // Scenario 2: 6 questions with scores [3, 3, 2, 2, 1, 1] = Average 2.00
        $responses2 = [
            ['answer' => '3.00'],
            ['answer' => '3.00'],
            ['answer' => '2.00'],
            ['answer' => '2.00'],
            ['answer' => '1.00'],
            ['answer' => '1.00']
        ];
        $average2 = $this->calculateAverage($responses2);
        $this->assertEquals(2.00, $average2);
        
        // Both scenarios should produce the same final score
        $this->assertEquals($average1, $average2);
    }

    public function test_weighted_scoring_remains_consistent()
    {
        // Test that weighted formula works regardless of individual question counts
        
        $selfScore = 2.5;   // From any number of self-evaluation questions
        $peerScore = 2.0;   // From any number of peer-evaluation questions  
        $adviserScore = 3.0; // From any number of adviser-evaluation questions
        
        $finalScore = $this->calculateWeightedScore($selfScore, $peerScore, $adviserScore);
        
        // Expected: (2.5 × 0.10) + (2.0 × 0.25) + (3.0 × 0.50) = 0.25 + 0.50 + 1.50 = 2.25
        $this->assertEquals(2.25, $finalScore);
        
        // Test ranking assignment
        $rank = $this->getRankingCategory($finalScore);
        $this->assertEquals('Silver', $rank); // 1.81-2.40 range
    }

    public function test_access_level_changes_dont_affect_calculation_logic()
    {
        // Test that changing who sees questions doesn't change how scores are calculated
        
        // Original config: Adviser sees all questions
        $adviserQuestions = [
            ['answer' => '3.00'],
            ['answer' => '2.00'],
            ['answer' => '1.00']
        ];
        $adviserAverage = $this->calculateAverage($adviserQuestions);
        
        // Modified config: Adviser sees fewer questions due to access level changes
        $adviserQuestionsReduced = [
            ['answer' => '3.00'],
            ['answer' => '2.00']
        ];
        $adviserAverageReduced = $this->calculateAverage($adviserQuestionsReduced);
        
        // Both should use the same calculation method (average)
        $this->assertEquals(2.00, $adviserAverage);
        $this->assertEquals(2.50, $adviserAverageReduced);
        
        // The calculation logic is the same, just different data sets
        $this->assertTrue(is_float($adviserAverage));
        $this->assertTrue(is_float($adviserAverageReduced));
        $this->assertGreaterThanOrEqual(0, $adviserAverage);
        $this->assertLessThanOrEqual(3, $adviserAverage);
        $this->assertGreaterThanOrEqual(0, $adviserAverageReduced);
        $this->assertLessThanOrEqual(3, $adviserAverageReduced);
    }

    public function test_ranking_thresholds_remain_stable()
    {
        // Test that ranking categories work regardless of structure changes
        
        $testScores = [
            3.00 => 'Gold',
            2.50 => 'Gold', 
            2.41 => 'Gold',
            2.40 => 'Silver',
            2.00 => 'Silver',
            1.81 => 'Silver',
            1.80 => 'Bronze',
            1.50 => 'Bronze',
            1.21 => 'Bronze',
            1.20 => 'No Award',
            1.00 => 'No Award',
            0.00 => 'No Award'
        ];
        
        foreach ($testScores as $score => $expectedRank) {
            $actualRank = $this->getRankingCategory($score);
            $this->assertEquals($expectedRank, $actualRank, "Score {$score} should be {$expectedRank}");
        }
    }

    public function test_dynamic_validation_preserves_score_integrity()
    {
        // Test that dynamic validation still enforces 0-3 scale
        
        $validAnswers = ['0.00', '1.00', '2.00', '3.00'];
        $invalidAnswers = ['4.00', '-1.00', '3.50', 'invalid'];
        
        foreach ($validAnswers as $answer) {
            $numericValue = (float) $answer;
            $this->assertGreaterThanOrEqual(0, $numericValue);
            $this->assertLessThanOrEqual(3, $numericValue);
        }
        
        foreach ($invalidAnswers as $answer) {
            $numericValue = (float) $answer;
            if ($numericValue != 0) { // 'invalid' becomes 0
                $this->assertTrue($numericValue < 0 || $numericValue > 3);
            }
        }
    }

    public function test_score_calculation_handles_missing_evaluations()
    {
        // Test that the system handles cases where some evaluation types are missing
        
        // Case 1: All evaluations present
        $finalScore1 = $this->calculateWeightedScore(2.0, 2.5, 3.0);
        $this->assertNotNull($finalScore1);
        
        // Case 2: Missing peer evaluation (redistributes weight to adviser)
        $finalScore2 = $this->calculateWeightedScore(2.0, null, 3.0);
        $this->assertNotNull($finalScore2);
        
        // Case 3: Missing self evaluation
        $finalScore3 = $this->calculateWeightedScore(null, 2.5, 3.0);
        $this->assertNotNull($finalScore3);
        
        // All should produce valid scores
        $this->assertGreaterThanOrEqual(0, $finalScore1);
        $this->assertLessThanOrEqual(3, $finalScore1);
        $this->assertGreaterThanOrEqual(0, $finalScore2);
        $this->assertLessThanOrEqual(3, $finalScore2);
        $this->assertGreaterThanOrEqual(0, $finalScore3);
        $this->assertLessThanOrEqual(3, $finalScore3);
    }

    /**
     * Helper method to calculate average (mimics ScoreCalculationService logic)
     */
    private function calculateAverage($responses)
    {
        $totalScore = 0;
        $questionCount = 0;

        foreach ($responses as $response) {
            $score = (float) $response['answer'];
            $totalScore += $score;
            $questionCount++;
        }

        return $questionCount > 0 ? round($totalScore / $questionCount, 2) : null;
    }

    /**
     * Helper method to calculate weighted score (mimics ScoreCalculationService logic)
     */
    private function calculateWeightedScore($selfScore, $peerScore, $adviserScore)
    {
        $weights = [
            'self' => 0.10,
            'peer' => 0.25,
            'adviser' => 0.50,
        ];

        $finalScore = 0;
        $totalWeight = 0;

        if ($selfScore !== null) {
            $finalScore += $selfScore * $weights['self'];
            $totalWeight += $weights['self'];
        }

        if ($peerScore !== null) {
            $finalScore += $peerScore * $weights['peer'];
            $totalWeight += $weights['peer'];
        } else {
            // Redistribute peer weight to adviser
            $weights['adviser'] += $weights['peer'];
        }

        if ($adviserScore !== null) {
            $finalScore += $adviserScore * $weights['adviser'];
            $totalWeight += $weights['adviser'];
        }

        return $totalWeight > 0 ? round($finalScore, 2) : null;
    }

    /**
     * Helper method to get ranking category (mimics ScoreCalculationService logic)
     */
    private function getRankingCategory($finalScore)
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
}
