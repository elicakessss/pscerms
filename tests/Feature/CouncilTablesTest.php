<?php

namespace Tests\Feature;

use App\Models\Council;
use App\Models\CouncilOfficer;
use App\Models\Evaluation;
use App\Models\EvaluationForm;
use App\Models\Department;
use App\Models\Adviser;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouncilTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_council_with_relationships()
    {
        // Create a department
        $department = Department::create([
            'name' => 'School of Information Technology and Engineering',
            'abbreviation' => 'SITE'
        ]);

        // Create an adviser
        $adviser = Adviser::create([
            'id_number' => 'ADV001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'adviser@test.com',
            'password' => bcrypt('password'),
            'department_id' => $department->id
        ]);

        // Create a council
        $council = Council::create([
            'name' => 'Student Council 2024',
            'academic_year' => '2024-2025',
            'status' => 'active',
            'adviser_id' => $adviser->id,
            'department_id' => $department->id
        ]);

        // Create a student
        $student = Student::create([
            'id_number' => 'STU001',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'student@test.com',
            'password' => bcrypt('password'),
            'department_id' => $department->id
        ]);

        // Create a council officer
        $councilOfficer = CouncilOfficer::create([
            'council_id' => $council->id,
            'student_id' => $student->id,
            'position_title' => 'President',
            'position_level' => 'Executive',
            'self_score' => 85.50,
            'peer_score' => 88.75,
            'adviser_score' => 90.00,
            'final_score' => 88.08,
            'rank' => 1
        ]);

        // Create an evaluation
        $evaluation = Evaluation::create([
            'council_id' => $council->id,
            'evaluator_id' => $student->id,
            'evaluator_type' => 'self',
            'evaluated_student_id' => $student->id,
            'evaluation_type' => 'rating',
            'status' => 'completed'
        ]);

        // Create evaluation form
        $evaluationForm = EvaluationForm::create([
            'evaluation_id' => $evaluation->id,
            'section_name' => 'Leadership',
            'question' => 'How would you rate your leadership skills?',
            'answer' => 'Excellent'
        ]);

        // Test relationships
        $this->assertEquals($department->id, $council->department->id);
        $this->assertEquals($adviser->id, $council->adviser->id);
        $this->assertEquals($council->id, $councilOfficer->council->id);
        $this->assertEquals($student->id, $councilOfficer->student->id);
        $this->assertEquals($evaluation->id, $evaluationForm->evaluation->id);

        // Test reverse relationships
        $this->assertTrue($department->councils->contains($council));
        $this->assertTrue($adviser->councils->contains($council));
        $this->assertTrue($student->councilOfficers->contains($councilOfficer));
        $this->assertTrue($student->evaluationsAsEvaluator->contains($evaluation));
        $this->assertTrue($student->evaluationsAsEvaluated->contains($evaluation));

        $this->assertDatabaseHas('councils', [
            'name' => 'Student Council 2024',
            'academic_year' => '2024-2025'
        ]);

        $this->assertDatabaseHas('council_officers', [
            'position_title' => 'President',
            'final_score' => 88.08
        ]);

        $this->assertDatabaseHas('evaluations', [
            'evaluator_type' => 'self',
            'status' => 'completed'
        ]);

        $this->assertDatabaseHas('evaluation_forms', [
            'section_name' => 'Leadership',
            'question' => 'How would you rate your leadership skills?'
        ]);
    }
}
