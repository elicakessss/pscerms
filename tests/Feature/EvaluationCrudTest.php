<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Council;
use App\Models\CouncilOfficer;
use App\Models\Department;
use App\Models\Adviser;
use App\Models\Evaluation;
use App\Models\EvaluationForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluationCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $student;
    protected $adviser;
    protected $council;
    protected $department;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->department = Department::create([
            'name' => 'Test Department',
            'abbreviation' => 'TEST'
        ]);

        $this->adviser = Adviser::create([
            'id_number' => 'ADV001',
            'first_name' => 'Test',
            'last_name' => 'Adviser',
            'email' => 'adviser@test.com',
            'password' => bcrypt('123456'),
            'department_id' => $this->department->id
        ]);

        $this->student = Student::create([
            'id_number' => 'STU001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@test.com',
            'password' => bcrypt('123456'),
            'department_id' => $this->department->id
        ]);

        $this->council = Council::create([
            'name' => 'Test Council',
            'academic_year' => '2024-2025',
            'status' => 'active',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ]);

        // Add student as officer
        CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $this->student->id,
            'position_title' => 'President',
            'position_level' => '1'
        ]);
    }

    public function test_evaluation_can_be_viewed()
    {
        // Create a completed evaluation
        $evaluation = Evaluation::create([
            'council_id' => $this->council->id,
            'evaluator_id' => $this->adviser->id,
            'evaluator_type' => 'adviser',
            'evaluated_student_id' => $this->student->id,
            'evaluation_type' => 'rating',
            'status' => 'completed',
            'submitted_at' => now()
        ]);

        // Add some evaluation forms
        EvaluationForm::create([
            'evaluation_id' => $evaluation->id,
            'section_name' => 'Domain 1 - Strand 1',
            'question' => 'Test question',
            'answer' => '3.00'
        ]);

        // Test viewing as adviser
        $response = $this->actingAs($this->adviser, 'adviser')
            ->get(route('adviser.evaluation.view', $evaluation));

        $response->assertStatus(200);
        $response->assertSee('View Adviser Evaluation');
        $response->assertSee('Test question');
        $response->assertSee('Outstanding (3.00)');
    }

    public function test_pending_evaluation_can_be_edited()
    {
        // Create a pending evaluation
        $evaluation = Evaluation::create([
            'council_id' => $this->council->id,
            'evaluator_id' => $this->adviser->id,
            'evaluator_type' => 'adviser',
            'evaluated_student_id' => $this->student->id,
            'evaluation_type' => 'rating',
            'status' => 'pending'
        ]);

        // Test editing as adviser
        $response = $this->actingAs($this->adviser, 'adviser')
            ->get(route('adviser.evaluation.edit', $evaluation));

        $response->assertStatus(200);
        $response->assertSee('Save Draft');
        $response->assertSee('Update & Submit');
    }

    public function test_completed_evaluation_cannot_be_edited()
    {
        // Create a completed evaluation
        $evaluation = Evaluation::create([
            'council_id' => $this->council->id,
            'evaluator_id' => $this->adviser->id,
            'evaluator_type' => 'adviser',
            'evaluated_student_id' => $this->student->id,
            'evaluation_type' => 'rating',
            'status' => 'completed',
            'submitted_at' => now()
        ]);

        // Test editing as adviser should redirect with error
        $response = $this->actingAs($this->adviser, 'adviser')
            ->get(route('adviser.evaluation.edit', $evaluation));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_pending_evaluation_can_be_deleted()
    {
        // Create a pending evaluation
        $evaluation = Evaluation::create([
            'council_id' => $this->council->id,
            'evaluator_id' => $this->adviser->id,
            'evaluator_type' => 'adviser',
            'evaluated_student_id' => $this->student->id,
            'evaluation_type' => 'rating',
            'status' => 'pending'
        ]);

        // Add some evaluation forms
        EvaluationForm::create([
            'evaluation_id' => $evaluation->id,
            'section_name' => 'Domain 1 - Strand 1',
            'question' => 'Test question',
            'answer' => '3.00'
        ]);

        // Test deleting as adviser
        $response = $this->actingAs($this->adviser, 'adviser')
            ->delete(route('adviser.evaluation.destroy', $evaluation));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify evaluation and forms are deleted
        $this->assertDatabaseMissing('evaluations', ['id' => $evaluation->id]);
        $this->assertDatabaseMissing('evaluation_forms', ['evaluation_id' => $evaluation->id]);
    }

    public function test_completed_evaluation_cannot_be_deleted()
    {
        // Create a completed evaluation
        $evaluation = Evaluation::create([
            'council_id' => $this->council->id,
            'evaluator_id' => $this->adviser->id,
            'evaluator_type' => 'adviser',
            'evaluated_student_id' => $this->student->id,
            'evaluation_type' => 'rating',
            'status' => 'completed',
            'submitted_at' => now()
        ]);

        // Test deleting as adviser should redirect with error
        $response = $this->actingAs($this->adviser, 'adviser')
            ->delete(route('adviser.evaluation.destroy', $evaluation));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Verify evaluation still exists
        $this->assertDatabaseHas('evaluations', ['id' => $evaluation->id]);
    }

    public function test_unauthorized_user_cannot_access_evaluation()
    {
        // Create another adviser
        $otherAdviser = Adviser::create([
            'id_number' => 'ADV002',
            'first_name' => 'Other',
            'last_name' => 'Adviser',
            'email' => 'other@test.com',
            'password' => bcrypt('123456'),
            'department_id' => $this->department->id
        ]);

        // Create evaluation by first adviser
        $evaluation = Evaluation::create([
            'council_id' => $this->council->id,
            'evaluator_id' => $this->adviser->id,
            'evaluator_type' => 'adviser',
            'evaluated_student_id' => $this->student->id,
            'evaluation_type' => 'rating',
            'status' => 'pending'
        ]);

        // Test accessing as other adviser should be forbidden
        $response = $this->actingAs($otherAdviser, 'adviser')
            ->get(route('adviser.evaluation.view', $evaluation));

        $response->assertStatus(403);
    }

    public function test_evaluation_model_helper_methods()
    {
        // Test pending evaluation
        $pendingEvaluation = Evaluation::create([
            'council_id' => $this->council->id,
            'evaluator_id' => $this->adviser->id,
            'evaluator_type' => 'adviser',
            'evaluated_student_id' => $this->student->id,
            'evaluation_type' => 'rating',
            'status' => 'pending'
        ]);

        $this->assertTrue($pendingEvaluation->canBeEdited());
        $this->assertTrue($pendingEvaluation->canBeDeleted());
        $this->assertFalse($pendingEvaluation->isCompleted());
        $this->assertEquals('bg-yellow-100 text-yellow-800', $pendingEvaluation->getStatusBadgeClass());
        $this->assertEquals('In Progress', $pendingEvaluation->getStatusDisplayText());

        // Test completed evaluation
        $completedEvaluation = Evaluation::create([
            'council_id' => $this->council->id,
            'evaluator_id' => $this->adviser->id,
            'evaluator_type' => 'adviser',
            'evaluated_student_id' => $this->student->id,
            'evaluation_type' => 'rating',
            'status' => 'completed',
            'submitted_at' => now()
        ]);

        $this->assertFalse($completedEvaluation->canBeEdited());
        $this->assertFalse($completedEvaluation->canBeDeleted());
        $this->assertTrue($completedEvaluation->isCompleted());
        $this->assertEquals('bg-green-100 text-green-800', $completedEvaluation->getStatusBadgeClass());
        $this->assertEquals('Completed', $completedEvaluation->getStatusDisplayText());
    }
}
