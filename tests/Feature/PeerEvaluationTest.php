<?php

namespace Tests\Feature;

use App\Models\Council;
use App\Models\CouncilOfficer;
use App\Models\Department;
use App\Models\Adviser;
use App\Models\Student;
use App\Models\Evaluation;
use App\Services\EvaluationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeerEvaluationTest extends TestCase
{
    use RefreshDatabase;

    protected $council;
    protected $department;
    protected $adviser;
    protected $evaluationService;

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

        $this->evaluationService = new EvaluationService();
    }

    /** @test */
    public function level_1_officers_evaluate_all_members()
    {
        // Create students
        $president = Student::create([
            'id_number' => 'STU001',
            'first_name' => 'President',
            'last_name' => 'Student',
            'email' => 'president@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id
        ]);

        $vicePresident = Student::create([
            'id_number' => 'STU002',
            'first_name' => 'Vice',
            'last_name' => 'President',
            'email' => 'vp@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id
        ]);

        $secretary = Student::create([
            'id_number' => 'STU003',
            'first_name' => 'Secretary',
            'last_name' => 'Student',
            'email' => 'secretary@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id
        ]);

        // Create officers
        $presidentOfficer = CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $president->id,
            'position_title' => 'President',
            'position_level' => 'Executive'
        ]);

        $vpOfficer = CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $vicePresident->id,
            'position_title' => 'Vice President',
            'position_level' => 'Vice Executive'
        ]);

        $secretaryOfficer = CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $secretary->id,
            'position_title' => 'Secretary',
            'position_level' => 'Officer'
        ]);

        // Create evaluations
        $this->evaluationService->startEvaluations($this->council);

        // Check that President (Level 1) evaluates all other members
        $presidentPeerEvaluations = Evaluation::where('council_id', $this->council->id)
            ->where('evaluator_id', $president->id)
            ->where('evaluator_type', 'peer')
            ->get();

        $this->assertEquals(2, $presidentPeerEvaluations->count()); // VP and Secretary
        $this->assertTrue($presidentPeerEvaluations->pluck('evaluated_student_id')->contains($vicePresident->id));
        $this->assertTrue($presidentPeerEvaluations->pluck('evaluated_student_id')->contains($secretary->id));
    }

    /** @test */
    public function level_2_officers_evaluate_only_level_1()
    {
        // Create students
        $president = Student::create([
            'id_number' => 'STU001',
            'first_name' => 'President',
            'last_name' => 'Student',
            'email' => 'president@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id
        ]);

        $vicePresident = Student::create([
            'id_number' => 'STU002',
            'first_name' => 'Vice',
            'last_name' => 'President',
            'email' => 'vp@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id
        ]);

        $secretary = Student::create([
            'id_number' => 'STU003',
            'first_name' => 'Secretary',
            'last_name' => 'Student',
            'email' => 'secretary@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id
        ]);

        // Create officers
        CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $president->id,
            'position_title' => 'President',
            'position_level' => 'Executive'
        ]);

        CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $vicePresident->id,
            'position_title' => 'Vice President',
            'position_level' => 'Vice Executive'
        ]);

        CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $secretary->id,
            'position_title' => 'Secretary',
            'position_level' => 'Officer'
        ]);

        // Create evaluations
        $this->evaluationService->startEvaluations($this->council);

        // Check that Vice President (Level 2) evaluates only President (Level 1)
        $vpPeerEvaluations = Evaluation::where('council_id', $this->council->id)
            ->where('evaluator_id', $vicePresident->id)
            ->where('evaluator_type', 'peer')
            ->get();

        $this->assertEquals(1, $vpPeerEvaluations->count()); // Only President
        $this->assertEquals($president->id, $vpPeerEvaluations->first()->evaluated_student_id);
    }

    /** @test */
    public function other_officers_do_not_evaluate_peers()
    {
        // Create students
        $president = Student::create([
            'id_number' => 'STU001',
            'first_name' => 'President',
            'last_name' => 'Student',
            'email' => 'president@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id
        ]);

        $secretary = Student::create([
            'id_number' => 'STU003',
            'first_name' => 'Secretary',
            'last_name' => 'Student',
            'email' => 'secretary@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id
        ]);

        // Create officers
        CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $president->id,
            'position_title' => 'President',
            'position_level' => 'Executive'
        ]);

        CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $secretary->id,
            'position_title' => 'Secretary',
            'position_level' => 'Officer'
        ]);

        // Create evaluations
        $this->evaluationService->startEvaluations($this->council);

        // Check that Secretary (Level 10) does not evaluate peers
        $secretaryPeerEvaluations = Evaluation::where('council_id', $this->council->id)
            ->where('evaluator_id', $secretary->id)
            ->where('evaluator_type', 'peer')
            ->get();

        $this->assertEquals(0, $secretaryPeerEvaluations->count());
    }
}
