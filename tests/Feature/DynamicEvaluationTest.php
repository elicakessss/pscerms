<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Adviser;
use App\Models\Council;
use App\Models\Student;
use App\Models\CouncilOfficer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DynamicEvaluationTest extends TestCase
{
    use RefreshDatabase;

    public function test_adviser_can_access_dynamic_evaluation_form()
    {
        // Create test data
        $adviser = Adviser::factory()->create();
        $student = Student::factory()->create();
        $council = Council::factory()->create(['adviser_id' => $adviser->id]);
        $officer = CouncilOfficer::factory()->create([
            'council_id' => $council->id,
            'student_id' => $student->id,
            'position_title' => 'President'
        ]);

        // Act as adviser
        $this->actingAs($adviser);

        // Access the evaluation form
        $response = $this->get(route('adviser.evaluation.show', [$council, $student]));

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert the view contains dynamic questions
        $response->assertViewHas('questions');

        // Assert the view is using the dynamic template
        $response->assertViewIs('evaluation.adviser_dynamic');
    }

    public function test_evaluation_questions_are_filtered_by_access_level()
    {
        // Create test data
        $adviser = Adviser::factory()->create();
        $student = Student::factory()->create();
        $council = Council::factory()->create(['adviser_id' => $adviser->id]);
        $officer = CouncilOfficer::factory()->create([
            'council_id' => $council->id,
            'student_id' => $student->id,
            'position_title' => 'President'
        ]);

        // Act as adviser
        $this->actingAs($adviser);

        // Access the evaluation form
        $response = $this->get(route('adviser.evaluation.show', [$council, $student]));

        // Get the questions from the view
        $questions = $response->viewData('questions');

        // Assert that all questions are accessible by advisers
        foreach ($questions as $domain) {
            foreach ($domain['strands'] as $strand) {
                foreach ($strand['questions'] as $question) {
                    $this->assertContains('adviser', $question['access_levels']);
                }
            }
        }
    }

    public function test_config_questions_are_loaded_correctly()
    {
        // Get questions from config
        $configQuestions = config('evaluation_questions.domains');

        // Assert config is loaded
        $this->assertNotEmpty($configQuestions);

        // Assert structure is correct
        $this->assertIsArray($configQuestions);

        foreach ($configQuestions as $domain) {
            $this->assertArrayHasKey('name', $domain);
            $this->assertArrayHasKey('strands', $domain);

            foreach ($domain['strands'] as $strand) {
                $this->assertArrayHasKey('name', $strand);
                $this->assertArrayHasKey('questions', $strand);

                foreach ($strand['questions'] as $question) {
                    $this->assertArrayHasKey('text', $question);
                    $this->assertArrayHasKey('access_levels', $question);
                    $this->assertArrayHasKey('rating_options', $question);

                    // Assert rating options have correct structure
                    $this->assertCount(4, $question['rating_options']);

                    foreach ($question['rating_options'] as $option) {
                        $this->assertArrayHasKey('value', $option);
                        $this->assertArrayHasKey('label', $option);
                        $this->assertContains($option['value'], [0, 1, 2, 3]);
                    }
                }
            }
        }
    }
}
