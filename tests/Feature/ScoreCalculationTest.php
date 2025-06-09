<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Council;
use App\Models\CouncilOfficer;
use App\Models\Student;
use App\Models\Adviser;
use App\Models\Department;
use App\Models\Evaluation;
use App\Models\EvaluationForm;
use App\Services\ScoreCalculationService;

class ScoreCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected $council;
    protected $officer;
    protected $student;
    protected $adviser;
    protected $scoreService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data manually (following existing test patterns)
        $department = Department::create([
            'name' => 'School of Information Technology and Engineering',
            'abbreviation' => 'SITE'
        ]);

        $this->adviser = Adviser::create([
            'id_number' => 'ADV001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'adviser@test.com',
            'password' => bcrypt('password'),
            'department_id' => $department->id
        ]);

        $this->student = Student::create([
            'id_number' => 'STU001',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'student@test.com',
            'password' => bcrypt('password'),
            'department_id' => $department->id
        ]);

        $this->council = Council::create([
            'name' => 'Student Council 2024',
            'academic_year' => '2024-2025',
            'status' => 'active',
            'adviser_id' => $this->adviser->id,
            'department_id' => $department->id
        ]);

        $this->officer = CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $this->student->id,
            'position_title' => 'President',
            'position_level' => 'Executive'
        ]);

        $this->scoreService = new ScoreCalculationService();
    }

    /** @test */
    public function it_calculates_self_evaluation_average()
    {
        // Create self evaluation
        $evaluation = Evaluation::create([
            'council_id' => $this->council->id,
            'evaluator_id' => $this->student->id,
            'evaluator_type' => 'self',
            'evaluated_student_id' => $this->student->id,
            'evaluation_type' => 'rating',
            'status' => 'completed',
        ]);

        // Create evaluation forms with scores
        EvaluationForm::create([
            'evaluation_id' => $evaluation->id,
            'section_name' => 'Domain 1',
            'question' => 'Question 1',
            'answer' => '3.00',
        ]);

        EvaluationForm::create([
            'evaluation_id' => $evaluation->id,
            'section_name' => 'Domain 1',
            'question' => 'Question 2',
            'answer' => '2.00',
        ]);

        // Calculate score
        $this->scoreService->calculateOfficerScore($this->officer);

        $this->officer->refresh();
        $this->assertEquals(2.50, $this->officer->self_score);
    }

    /** @test */
    public function it_calculates_peer_evaluation_average()
    {
        // Create peer evaluator
        $peerStudent = Student::create([
            'id_number' => 'STU002',
            'first_name' => 'Peer',
            'last_name' => 'Student',
            'email' => 'peer@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->council->department_id
        ]);

        $peerOfficer = CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $peerStudent->id,
            'position_title' => 'Vice President',
            'position_level' => 'Executive'
        ]);

        // Create peer evaluation
        $evaluation = Evaluation::create([
            'council_id' => $this->council->id,
            'evaluator_id' => $peerStudent->id,
            'evaluator_type' => 'peer',
            'evaluated_student_id' => $this->student->id,
            'evaluation_type' => 'rating',
            'status' => 'completed',
        ]);

        // Create evaluation forms
        EvaluationForm::create([
            'evaluation_id' => $evaluation->id,
            'section_name' => 'Domain 1',
            'question' => 'Question 1',
            'answer' => '2.00',
        ]);

        EvaluationForm::create([
            'evaluation_id' => $evaluation->id,
            'section_name' => 'Domain 2',
            'question' => 'Question 2',
            'answer' => '3.00',
        ]);

        // Calculate score
        $this->scoreService->calculateOfficerScore($this->officer);

        $this->officer->refresh();
        $this->assertEquals(2.50, $this->officer->peer_score);
    }

    /** @test */
    public function it_calculates_adviser_evaluation_average()
    {
        // Create adviser evaluation
        $evaluation = Evaluation::create([
            'council_id' => $this->council->id,
            'evaluator_id' => $this->adviser->id,
            'evaluator_type' => 'adviser',
            'evaluated_student_id' => $this->student->id,
            'evaluation_type' => 'rating',
            'status' => 'completed',
        ]);

        // Create evaluation forms
        EvaluationForm::create([
            'evaluation_id' => $evaluation->id,
            'section_name' => 'Domain 1',
            'question' => 'Question 1',
            'answer' => '1.00',
        ]);

        EvaluationForm::create([
            'evaluation_id' => $evaluation->id,
            'section_name' => 'Domain 2',
            'question' => 'Question 2',
            'answer' => '3.00',
        ]);

        // Calculate score
        $this->scoreService->calculateOfficerScore($this->officer);

        $this->officer->refresh();
        $this->assertEquals(2.00, $this->officer->adviser_score);
    }

    /** @test */
    public function it_calculates_weighted_final_score()
    {
        // Create all evaluations
        $this->createCompleteEvaluations();

        // Calculate score
        $this->scoreService->calculateOfficerScore($this->officer);

        $this->officer->refresh();

        // Expected calculation:
        // Self: 2.50 × 10% = 0.25
        // Peer: 2.00 × 25% = 0.50
        // Adviser: 3.00 × 50% = 1.50
        // Total: 2.25
        $this->assertEquals(2.25, $this->officer->final_score);
    }

    /** @test */
    public function it_assigns_rankings_correctly()
    {
        // Create multiple officers
        $student2 = Student::create([
            'id_number' => 'STU003',
            'first_name' => 'Second',
            'last_name' => 'Officer',
            'email' => 'officer2@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->council->department_id
        ]);

        $officer2 = CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $student2->id,
            'position_title' => 'Secretary',
            'position_level' => 'Executive'
        ]);

        // Set different final scores
        $this->officer->update(['final_score' => 2.50]);
        $officer2->update(['final_score' => 2.75]);

        // Assign rankings based on final scores
        $this->scoreService->assignRankings($this->council);

        $this->officer->refresh();
        $officer2->refresh();

        $this->assertEquals('Gold', $this->officer->rank);
        $this->assertEquals('Gold', $officer2->rank);
    }

    /** @test */
    public function it_returns_correct_ranking_categories()
    {
        $this->assertEquals('Gold', $this->scoreService->getRankingCategory(2.50));
        $this->assertEquals('Silver', $this->scoreService->getRankingCategory(2.00));
        $this->assertEquals('Bronze', $this->scoreService->getRankingCategory(1.50));
        $this->assertEquals('Certificate', $this->scoreService->getRankingCategory(1.00));
        $this->assertEquals('No Award', $this->scoreService->getRankingCategory(0.50));
    }

    /** @test */
    public function it_handles_missing_peer_evaluations()
    {
        // Create only self and adviser evaluations
        $this->createSelfEvaluation(2.50);
        $this->createAdviserEvaluation(3.00);

        // Calculate score
        $this->scoreService->calculateOfficerScore($this->officer);

        $this->officer->refresh();

        // Should redistribute peer weight to adviser
        // Self: 2.50 × 10% = 0.25
        // Adviser: 3.00 × 75% = 2.25 (50% + 25% redistributed)
        // Total: 2.50
        $this->assertEquals(2.50, $this->officer->final_score);
        $this->assertNull($this->officer->peer_score);
    }

    private function createCompleteEvaluations()
    {
        $this->createSelfEvaluation(2.50);
        $this->createPeerEvaluation(2.00);
        $this->createAdviserEvaluation(3.00);
    }

    private function createSelfEvaluation($averageScore)
    {
        $evaluation = Evaluation::create([
            'council_id' => $this->council->id,
            'evaluator_id' => $this->student->id,
            'evaluator_type' => 'self',
            'evaluated_student_id' => $this->student->id,
            'evaluation_type' => 'rating',
            'status' => 'completed',
        ]);

        EvaluationForm::create([
            'evaluation_id' => $evaluation->id,
            'section_name' => 'Domain 1',
            'question' => 'Question 1',
            'answer' => (string) $averageScore,
        ]);
    }

    private function createPeerEvaluation($averageScore)
    {
        $peerStudent = Student::create([
            'id_number' => 'STU004',
            'first_name' => 'Peer',
            'last_name' => 'Evaluator',
            'email' => 'peerevaluator@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->council->department_id
        ]);

        CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $peerStudent->id,
            'position_title' => 'Treasurer',
            'position_level' => 'Executive'
        ]);

        $evaluation = Evaluation::create([
            'council_id' => $this->council->id,
            'evaluator_id' => $peerStudent->id,
            'evaluator_type' => 'peer',
            'evaluated_student_id' => $this->student->id,
            'evaluation_type' => 'rating',
            'status' => 'completed',
        ]);

        EvaluationForm::create([
            'evaluation_id' => $evaluation->id,
            'section_name' => 'Domain 1',
            'question' => 'Question 1',
            'answer' => (string) $averageScore,
        ]);
    }

    private function createAdviserEvaluation($averageScore)
    {
        $evaluation = Evaluation::create([
            'council_id' => $this->council->id,
            'evaluator_id' => $this->adviser->id,
            'evaluator_type' => 'adviser',
            'evaluated_student_id' => $this->student->id,
            'evaluation_type' => 'rating',
            'status' => 'completed',
        ]);

        EvaluationForm::create([
            'evaluation_id' => $evaluation->id,
            'section_name' => 'Domain 1',
            'question' => 'Question 1',
            'answer' => (string) $averageScore,
        ]);
    }
}
