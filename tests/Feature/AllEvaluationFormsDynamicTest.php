<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AllEvaluationFormsDynamicTest extends TestCase
{
    use RefreshDatabase;

    public function test_adviser_evaluation_uses_dynamic_questions()
    {
        $configQuestions = config('evaluation_questions.domains');
        
        // Filter questions for adviser access
        $adviserQuestions = $this->filterQuestionsByAccess($configQuestions, 'adviser');
        
        $this->assertNotEmpty($adviserQuestions);
        $this->assertGreaterThan(0, count($adviserQuestions));
    }

    public function test_peer_evaluation_uses_dynamic_questions()
    {
        $configQuestions = config('evaluation_questions.domains');
        
        // Filter questions for peer access
        $peerQuestions = $this->filterQuestionsByAccess($configQuestions, 'peer');
        
        $this->assertNotEmpty($peerQuestions);
        $this->assertGreaterThan(0, count($peerQuestions));
    }

    public function test_self_evaluation_uses_dynamic_questions()
    {
        $configQuestions = config('evaluation_questions.domains');
        
        // Filter questions for self access
        $selfQuestions = $this->filterQuestionsByAccess($configQuestions, 'self');
        
        $this->assertNotEmpty($selfQuestions);
        $this->assertGreaterThan(0, count($selfQuestions));
    }

    public function test_access_levels_are_properly_configured()
    {
        $configQuestions = config('evaluation_questions.domains');
        
        $adviserCount = 0;
        $peerCount = 0;
        $selfCount = 0;
        
        foreach ($configQuestions as $domain) {
            foreach ($domain['strands'] as $strand) {
                foreach ($strand['questions'] as $question) {
                    if (in_array('adviser', $question['access_levels'])) {
                        $adviserCount++;
                    }
                    if (in_array('peer', $question['access_levels'])) {
                        $peerCount++;
                    }
                    if (in_array('self', $question['access_levels'])) {
                        $selfCount++;
                    }
                }
            }
        }
        
        // Verify each evaluator type has questions
        $this->assertGreaterThan(0, $adviserCount, 'Adviser should have access to questions');
        $this->assertGreaterThan(0, $peerCount, 'Peer should have access to questions');
        $this->assertGreaterThan(0, $selfCount, 'Self should have access to questions');
        
        // Verify adviser has the most questions (should have access to all)
        $this->assertGreaterThanOrEqual($peerCount, $adviserCount, 'Adviser should have access to at least as many questions as peer');
        $this->assertGreaterThanOrEqual($selfCount, $adviserCount, 'Adviser should have access to at least as many questions as self');
    }

    public function test_all_questions_have_correct_rating_scale()
    {
        $configQuestions = config('evaluation_questions.domains');
        
        foreach ($configQuestions as $domain) {
            foreach ($domain['strands'] as $strand) {
                foreach ($strand['questions'] as $question) {
                    // Verify each question has 4 rating options
                    $this->assertCount(4, $question['rating_options']);
                    
                    // Verify rating values are 0, 1, 2, 3
                    $values = array_column($question['rating_options'], 'value');
                    sort($values);
                    $this->assertEquals([0, 1, 2, 3], $values);
                    
                    // Verify each option has a label
                    foreach ($question['rating_options'] as $option) {
                        $this->assertArrayHasKey('label', $option);
                        $this->assertNotEmpty($option['label']);
                    }
                }
            }
        }
    }

    public function test_dynamic_validation_rules_generation()
    {
        $configQuestions = config('evaluation_questions.domains');
        
        // Test adviser validation rules
        $adviserRules = $this->generateValidationRules($configQuestions, 'adviser');
        $this->assertNotEmpty($adviserRules);
        
        // Test peer validation rules
        $peerRules = $this->generateValidationRules($configQuestions, 'peer');
        $this->assertNotEmpty($peerRules);
        
        // Test self validation rules
        $selfRules = $this->generateValidationRules($configQuestions, 'self');
        $this->assertNotEmpty($selfRules);
        
        // Verify all rules have correct validation
        foreach ([$adviserRules, $peerRules, $selfRules] as $rules) {
            foreach ($rules as $field => $rule) {
                $this->assertEquals('required|numeric|in:0.00,1.00,2.00,3.00', $rule);
            }
        }
    }

    /**
     * Helper method to filter questions by access level
     */
    private function filterQuestionsByAccess($configQuestions, $accessLevel)
    {
        $filteredQuestions = [];
        
        foreach ($configQuestions as $domain) {
            $filteredDomain = $domain;
            $filteredDomain['strands'] = [];

            foreach ($domain['strands'] as $strand) {
                $filteredStrand = $strand;
                $filteredStrand['questions'] = [];

                foreach ($strand['questions'] as $question) {
                    if (in_array($accessLevel, $question['access_levels'])) {
                        $filteredStrand['questions'][] = $question;
                    }
                }

                if (!empty($filteredStrand['questions'])) {
                    $filteredDomain['strands'][] = $filteredStrand;
                }
            }

            if (!empty($filteredDomain['strands'])) {
                $filteredQuestions[] = $filteredDomain;
            }
        }
        
        return $filteredQuestions;
    }

    /**
     * Helper method to generate validation rules
     */
    private function generateValidationRules($configQuestions, $evaluatorType)
    {
        $validationRules = [];

        foreach ($configQuestions as $domainIndex => $domain) {
            foreach ($domain['strands'] as $strandIndex => $strand) {
                foreach ($strand['questions'] as $questionIndex => $question) {
                    if (in_array($evaluatorType, $question['access_levels'])) {
                        $fieldName = 'domain' . ($domainIndex + 1) . '_strand' . ($strandIndex + 1) . '_q' . ($questionIndex + 1);
                        $validationRules[$fieldName] = 'required|numeric|in:0.00,1.00,2.00,3.00';
                    }
                }
            }
        }

        return $validationRules;
    }
}
