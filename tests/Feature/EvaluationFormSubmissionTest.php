<?php

namespace Tests\Feature;

use App\Models\Council;
use App\Models\CouncilOfficer;
use App\Models\Department;
use App\Models\Adviser;
use App\Models\Student;
use App\Models\Evaluation;
use App\Models\EvaluationForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluationFormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected $council;
    protected $department;
    protected $adviser;
    protected $student1;
    protected $student2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->department = Department::create([
            'name' => 'School of Information Technology and Engineering',
            'abbreviation' => 'SITE'
        ]);

        $this->adviser = Adviser::create([
            'id_number' => 'ADV001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'adviser@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id
        ]);

        $this->council = Council::create([
            'name' => 'Test Council',
            'academic_year' => '2024-2025',
            'status' => 'active',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ]);

        $this->student1 = Student::create([
            'id_number' => 'STU001',
            'first_name' => 'Student',
            'last_name' => 'One',
            'email' => 'student1@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id
        ]);

        $this->student2 = Student::create([
            'id_number' => 'STU002',
            'first_name' => 'Student',
            'last_name' => 'Two',
            'email' => 'student2@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id
        ]);

        // Create council officers
        CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $this->student1->id,
            'position_title' => 'President',
            'position_level' => 1
        ]);

        CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $this->student2->id,
            'position_title' => 'Vice President',
            'position_level' => 2
        ]);
    }

    /** @test */
    public function self_evaluation_form_submits_correctly()
    {
        $this->actingAs($this->student1, 'student');

        $formData = [
            'council_id' => $this->council->id,
            'evaluated_student_id' => $this->student1->id,
            'evaluator_type' => 'self',
            // Domain 2 fields
            'domain2_strand1_q1' => '3.00',
            'domain2_strand3_q1' => '2.00',
            'domain2_strand3_q2' => '3.00',
            // Domain 3 fields
            'domain3_strand1_q1' => '2.00',
            'domain3_strand1_q2' => '3.00',
            'domain3_strand2_q1' => '2.00',
        ];

        $response = $this->post(route('student.evaluation.store'), $formData);

        $response->assertRedirect(route('student.dashboard'));
        $response->assertSessionHas('success');

        // Verify evaluation was created
        $this->assertDatabaseHas('evaluations', [
            'council_id' => $this->council->id,
            'evaluator_id' => $this->student1->id,
            'evaluator_type' => 'self',
            'evaluated_student_id' => $this->student1->id,
            'status' => 'completed'
        ]);

        // Verify evaluation forms were created with correct data
        $evaluation = Evaluation::where('evaluator_id', $this->student1->id)
            ->where('evaluator_type', 'self')
            ->first();

        $this->assertEquals(6, EvaluationForm::where('evaluation_id', $evaluation->id)->count());
        
        // Check specific form entries
        $this->assertDatabaseHas('evaluation_forms', [
            'evaluation_id' => $evaluation->id,
            'section_name' => 'Domain 2 - Strand 1',
            'answer' => '3.00'
        ]);
    }

    /** @test */
    public function peer_evaluation_form_submits_correctly()
    {
        $this->actingAs($this->student1, 'student');

        $formData = [
            'council_id' => $this->council->id,
            'evaluated_student_id' => $this->student2->id,
            'evaluator_type' => 'peer',
            // Domain 2 fields
            'domain2_strand1_q1' => '3.00',
            'domain2_strand2_q1' => '2.00',
            'domain2_strand2_q2' => '3.00',
            'domain2_strand3_q1' => '2.00',
            'domain2_strand3_q2' => '3.00',
            // Domain 3 fields
            'domain3_strand1_q1' => '2.00',
            'domain3_strand1_q2' => '3.00',
            'domain3_strand2_q1' => '2.00',
        ];

        $response = $this->post(route('student.evaluation.store'), $formData);

        $response->assertRedirect(route('student.dashboard'));
        $response->assertSessionHas('success');

        // Verify evaluation was created
        $this->assertDatabaseHas('evaluations', [
            'council_id' => $this->council->id,
            'evaluator_id' => $this->student1->id,
            'evaluator_type' => 'peer',
            'evaluated_student_id' => $this->student2->id,
            'status' => 'completed'
        ]);

        // Verify evaluation forms were created with correct data
        $evaluation = Evaluation::where('evaluator_id', $this->student1->id)
            ->where('evaluator_type', 'peer')
            ->first();

        $this->assertEquals(8, EvaluationForm::where('evaluation_id', $evaluation->id)->count());
        
        // Check specific form entries
        $this->assertDatabaseHas('evaluation_forms', [
            'evaluation_id' => $evaluation->id,
            'section_name' => 'Domain 2 - Strand 2',
            'question' => 'Shares in organization management and evaluation',
            'answer' => '2.00'
        ]);
    }

    /** @test */
    public function validation_fails_with_missing_required_fields()
    {
        $this->actingAs($this->student1, 'student');

        // Missing required fields
        $formData = [
            'council_id' => $this->council->id,
            'evaluated_student_id' => $this->student1->id,
            'evaluator_type' => 'self',
            'domain2_strand1_q1' => '3.00',
            // Missing other required fields
        ];

        $response = $this->post(route('student.evaluation.store'), $formData);

        $response->assertSessionHasErrors();
    }
}
