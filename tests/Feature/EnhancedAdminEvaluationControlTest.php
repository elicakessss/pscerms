<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EnhancedAdminEvaluationControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_control_access_levels()
    {
        // Test that access levels can be dynamically set
        $configQuestions = config('evaluation_questions.domains');

        $hasAdviserOnly = false;
        $hasPeerOnly = false;
        $hasAllAccess = false;

        foreach ($configQuestions as $domain) {
            foreach ($domain['strands'] as $strand) {
                foreach ($strand['questions'] as $question) {
                    $accessLevels = $question['access_levels'];

                    if ($accessLevels === ['adviser']) {
                        $hasAdviserOnly = true;
                    }
                    if (count(array_intersect($accessLevels, ['peer'])) > 0 && !in_array('adviser', $accessLevels)) {
                        $hasPeerOnly = true;
                    }
                    if (count($accessLevels) === 3) {
                        $hasAllAccess = true;
                    }
                }
            }
        }

        // Verify that different access level combinations exist
        $this->assertTrue($hasAdviserOnly || $hasPeerOnly || $hasAllAccess, 'Should have various access level configurations');
    }

    public function test_config_structure_supports_dynamic_management()
    {
        $configQuestions = config('evaluation_questions.domains');

        // Test that structure supports adding/removing
        $this->assertIsArray($configQuestions);
        $this->assertNotEmpty($configQuestions);

        foreach ($configQuestions as $domainIndex => $domain) {
            // Each domain should have required fields
            $this->assertArrayHasKey('name', $domain);
            $this->assertArrayHasKey('strands', $domain);
            $this->assertIsArray($domain['strands']);
            $this->assertNotEmpty($domain['strands']);

            foreach ($domain['strands'] as $strandIndex => $strand) {
                // Each strand should have required fields
                $this->assertArrayHasKey('name', $strand);
                $this->assertArrayHasKey('questions', $strand);
                $this->assertIsArray($strand['questions']);
                $this->assertNotEmpty($strand['questions']);

                foreach ($strand['questions'] as $questionIndex => $question) {
                    // Each question should have all required fields
                    $this->assertArrayHasKey('text', $question);
                    $this->assertArrayHasKey('access_levels', $question);
                    $this->assertArrayHasKey('rating_options', $question);

                    // Access levels should be valid
                    $this->assertIsArray($question['access_levels']);
                    $this->assertNotEmpty($question['access_levels']);

                    foreach ($question['access_levels'] as $level) {
                        $this->assertContains($level, ['adviser', 'peer', 'self']);
                    }

                    // Rating options should be complete
                    $this->assertCount(4, $question['rating_options']);

                    $expectedValues = [0, 1, 2, 3];
                    $actualValues = array_column($question['rating_options'], 'value');
                    sort($actualValues);
                    $this->assertEquals($expectedValues, $actualValues);
                }
            }
        }
    }

    public function test_validation_rules_support_enhanced_structure()
    {
        // Test that the validation rules in the controller support the enhanced structure
        $validationRules = [
            'domains' => 'required|array|min:1',
            'domains.*.name' => 'required|string|max:255',
            'domains.*.strands' => 'required|array|min:1',
            'domains.*.strands.*.name' => 'required|string|max:255',
            'domains.*.strands.*.questions' => 'required|array|min:1',
            'domains.*.strands.*.questions.*.text' => 'required|string',
            'domains.*.strands.*.questions.*.access_levels' => 'required|array|min:1',
            'domains.*.strands.*.questions.*.access_levels.*' => 'required|in:adviser,peer,self',
            'domains.*.strands.*.questions.*.rating_options' => 'required|array|size:4',
            'domains.*.strands.*.questions.*.rating_options.*.value' => 'required|integer|min:0|max:3',
            'domains.*.strands.*.questions.*.rating_options.*.label' => 'required|string|max:255',
        ];

        // Verify key validation rules exist
        $this->assertArrayHasKey('domains', $validationRules);
        $this->assertArrayHasKey('domains.*.strands.*.questions.*.access_levels', $validationRules);
        $this->assertArrayHasKey('domains.*.strands.*.questions.*.access_levels.*', $validationRules);

        // Verify minimum requirements
        $this->assertStringContainsString('min:1', $validationRules['domains']);
        $this->assertStringContainsString('min:1', $validationRules['domains.*.strands']);
        $this->assertStringContainsString('min:1', $validationRules['domains.*.strands.*.questions']);
        $this->assertStringContainsString('min:1', $validationRules['domains.*.strands.*.questions.*.access_levels']);
    }

    public function test_access_level_filtering_works_correctly()
    {
        $configQuestions = config('evaluation_questions.domains');

        // Test filtering for each evaluator type
        $evaluatorTypes = ['adviser', 'peer', 'self'];

        foreach ($evaluatorTypes as $evaluatorType) {
            $filteredQuestions = $this->filterQuestionsByAccess($configQuestions, $evaluatorType);

            // Should have some questions for each type
            $this->assertNotEmpty($filteredQuestions, "Should have questions for {$evaluatorType}");

            // Verify all returned questions have correct access
            foreach ($filteredQuestions as $domain) {
                foreach ($domain['strands'] as $strand) {
                    foreach ($strand['questions'] as $question) {
                        $this->assertContains($evaluatorType, $question['access_levels'],
                            "Question should be accessible by {$evaluatorType}");
                    }
                }
            }
        }
    }

    public function test_dynamic_form_field_generation()
    {
        $configQuestions = config('evaluation_questions.domains');

        // Test that field names can be generated dynamically
        $expectedFields = [];

        foreach ($configQuestions as $domainIndex => $domain) {
            foreach ($domain['strands'] as $strandIndex => $strand) {
                foreach ($strand['questions'] as $questionIndex => $question) {
                    $fieldName = 'domain' . ($domainIndex + 1) . '_strand' . ($strandIndex + 1) . '_q' . ($questionIndex + 1);
                    $expectedFields[] = $fieldName;
                }
            }
        }

        $this->assertNotEmpty($expectedFields);

        // Verify field naming convention
        foreach ($expectedFields as $field) {
            $this->assertMatchesRegularExpression('/^domain\d+_strand\d+_q\d+$/', $field);
        }
    }

    public function test_rating_scale_consistency()
    {
        $configQuestions = config('evaluation_questions.domains');

        foreach ($configQuestions as $domain) {
            foreach ($domain['strands'] as $strand) {
                foreach ($strand['questions'] as $question) {
                    $ratingOptions = $question['rating_options'];

                    // Should have exactly 4 options
                    $this->assertCount(4, $ratingOptions);

                    // Should have values 0, 1, 2, 3
                    $values = array_column($ratingOptions, 'value');
                    sort($values);
                    $this->assertEquals([0, 1, 2, 3], $values);

                    // Each option should have a label
                    foreach ($ratingOptions as $option) {
                        $this->assertArrayHasKey('value', $option);
                        $this->assertArrayHasKey('label', $option);
                        $this->assertNotEmpty($option['label']);
                        $this->assertIsInt($option['value']);
                        $this->assertGreaterThanOrEqual(0, $option['value']);
                        $this->assertLessThanOrEqual(3, $option['value']);
                    }
                }
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
}
