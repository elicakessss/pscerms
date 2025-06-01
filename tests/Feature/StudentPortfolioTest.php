<?php

namespace Tests\Feature;

use App\Models\Council;
use App\Models\CouncilOfficer;
use App\Models\Department;
use App\Models\Adviser;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortfolioTest extends TestCase
{
    use RefreshDatabase;

    protected $student;
    protected $department;
    protected $adviser;
    protected $council;

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

        $this->student = Student::create([
            'id_number' => 'STU001',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'student@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id
        ]);

        $this->council = Council::create([
            'name' => 'Paulinian Student Government - SITE',
            'academic_year' => '2024-2025',
            'status' => 'completed',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id,
        ]);
    }

    public function test_student_can_view_account_page_with_empty_portfolio()
    {
        $response = $this->actingAs($this->student, 'student')
            ->get(route('student.account.index'));

        $response->assertStatus(200);
        $response->assertSee('Portfolio');
        $response->assertSee('No Completed Councils');
    }

    public function test_student_can_view_completed_councils_in_portfolio()
    {
        // Create a completed council officer record
        $councilOfficer = CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $this->student->id,
            'position_title' => 'President',
            'position_level' => 'Executive',
            'self_score' => 85.50,
            'peer_score' => 88.75,
            'adviser_score' => 90.00,
            'final_score' => 88.08,
            'rank' => 1,
            'completed_at' => now()
        ]);

        $response = $this->actingAs($this->student, 'student')
            ->get(route('student.account.index'));

        $response->assertStatus(200);
        $response->assertSee('Portfolio');
        $response->assertSee('Paulinian Student Government - SITE');
        $response->assertSee('President');
        $response->assertSee('88.08');
        $response->assertSee('#1');
        $response->assertSee('Gold'); // Should show Gold ranking for score 88.08
    }

    public function test_portfolio_shows_correct_ranking_categories()
    {
        // Test different ranking categories
        $testCases = [
            ['score' => 2.50, 'expected' => 'Gold'],
            ['score' => 2.00, 'expected' => 'Silver'],
            ['score' => 1.50, 'expected' => 'Bronze'],
            ['score' => 1.00, 'expected' => 'Needs Improvement'],
        ];

        foreach ($testCases as $index => $testCase) {
            CouncilOfficer::create([
                'council_id' => $this->council->id,
                'student_id' => $this->student->id,
                'position_title' => 'Test Position ' . ($index + 1),
                'position_level' => 'Officer',
                'final_score' => $testCase['score'],
                'rank' => $index + 1,
                'completed_at' => now()
            ]);
        }

        $response = $this->actingAs($this->student, 'student')
            ->get(route('student.account.index'));

        $response->assertStatus(200);
        foreach ($testCases as $testCase) {
            $response->assertSee($testCase['expected']);
        }
    }

    public function test_portfolio_only_shows_completed_councils()
    {
        // Create an incomplete council officer (no completed_at)
        CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $this->student->id,
            'position_title' => 'Incomplete Position',
            'position_level' => 'Officer',
            'final_score' => null,
            'completed_at' => null
        ]);

        // Create a completed council officer
        CouncilOfficer::create([
            'council_id' => $this->council->id,
            'student_id' => $this->student->id,
            'position_title' => 'Completed Position',
            'position_level' => 'Officer',
            'final_score' => 85.00,
            'rank' => 1,
            'completed_at' => now()
        ]);

        $response = $this->actingAs($this->student, 'student')
            ->get(route('student.account.index'));

        $response->assertStatus(200);
        $response->assertSee('Completed Position');
        $response->assertDontSee('Incomplete Position');
    }
}
