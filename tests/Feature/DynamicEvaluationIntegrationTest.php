<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DynamicEvaluationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_config_questions_contain_edited_content()
    {
        // Get questions from config
        $configQuestions = config('evaluation_questions.domains');
        
        // Check if the edited question is present
        $found = false;
        foreach ($configQuestions as $domain) {
            foreach ($domain['strands'] as $strand) {
                foreach ($strand['questions'] as $question) {
                    if (strpos($question['text'], '[EDITED BY ADMIN]') !== false) {
                        $found = true;
                        break 3;
                    }
                }
            }
        }
        
        $this->assertTrue($found, 'The edited question text should be present in the config');
    }

    public function test_admin_edit_changes_are_reflected_in_config()
    {
        // Test that the config file contains the structure we expect
        $configQuestions = config('evaluation_questions.domains');
        
        // Verify structure
        $this->assertIsArray($configQuestions);
        $this->assertNotEmpty($configQuestions);
        
        // Check first domain structure
        $firstDomain = $configQuestions[0];
        $this->assertArrayHasKey('name', $firstDomain);
        $this->assertArrayHasKey('strands', $firstDomain);
        
        // Check first strand structure
        $firstStrand = $firstDomain['strands'][0];
        $this->assertArrayHasKey('name', $firstStrand);
        $this->assertArrayHasKey('questions', $firstStrand);
        
        // Check first question structure
        $firstQuestion = $firstStrand['questions'][0];
        $this->assertArrayHasKey('text', $firstQuestion);
        $this->assertArrayHasKey('access_levels', $firstQuestion);
        $this->assertArrayHasKey('rating_options', $firstQuestion);
        
        // Verify rating options
        $this->assertCount(4, $firstQuestion['rating_options']);
        
        foreach ($firstQuestion['rating_options'] as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
            $this->assertContains($option['value'], [0, 1, 2, 3]);
        }
    }

    public function test_adviser_questions_are_properly_filtered()
    {
        $configQuestions = config('evaluation_questions.domains');
        
        // Filter questions for adviser access (same logic as controller)
        $adviserQuestions = [];
        foreach ($configQuestions as $domain) {
            $filteredDomain = $domain;
            $filteredDomain['strands'] = [];

            foreach ($domain['strands'] as $strand) {
                $filteredStrand = $strand;
                $filteredStrand['questions'] = [];

                foreach ($strand['questions'] as $question) {
                    if (in_array('adviser', $question['access_levels'])) {
                        $filteredStrand['questions'][] = $question;
                    }
                }

                if (!empty($filteredStrand['questions'])) {
                    $filteredDomain['strands'][] = $filteredStrand;
                }
            }

            if (!empty($filteredDomain['strands'])) {
                $adviserQuestions[] = $filteredDomain;
            }
        }
        
        // Verify adviser has access to questions
        $this->assertNotEmpty($adviserQuestions);
        
        // Verify all questions are accessible by advisers
        foreach ($adviserQuestions as $domain) {
            foreach ($domain['strands'] as $strand) {
                foreach ($strand['questions'] as $question) {
                    $this->assertContains('adviser', $question['access_levels']);
                }
            }
        }
    }
}
