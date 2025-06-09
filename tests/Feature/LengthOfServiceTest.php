<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\Council;
use App\Models\CouncilOfficer;
use App\Models\Department;
use App\Models\Adviser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LengthOfServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $student;
    protected $department;
    protected $adviser;

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
    }

    public function test_student_with_no_completed_councils_has_zero_length_of_service()
    {
        $lengthOfService = $this->student->calculateLengthOfService();
        $this->assertEquals(0.00, $lengthOfService);
        
        $description = $this->student->getLengthOfServiceDescription();
        $this->assertEquals('Did not finish their term (0.00)', $description);
    }

    public function test_student_with_one_completed_council_has_one_length_of_service()
    {
        // Create a completed council
        $council = Council::create([
            'name' => 'Test Council 2023',
            'academic_year' => '2023-2024',
            'status' => 'completed',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ]);

        // Add student as officer with completed_at timestamp
        CouncilOfficer::create([
            'council_id' => $council->id,
            'student_id' => $this->student->id,
            'position_title' => 'President',
            'position_level' => '1',
            'completed_at' => now()
        ]);

        $lengthOfService = $this->student->calculateLengthOfService();
        $this->assertEquals(1.00, $lengthOfService);
        
        $description = $this->student->getLengthOfServiceDescription();
        $this->assertEquals('Finished one term (1.00)', $description);
    }

    public function test_student_with_two_completed_councils_has_two_length_of_service()
    {
        // Create two completed councils
        $council1 = Council::create([
            'name' => 'Test Council 2023',
            'academic_year' => '2023-2024',
            'status' => 'completed',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ]);

        $council2 = Council::create([
            'name' => 'Test Council 2024',
            'academic_year' => '2024-2025',
            'status' => 'completed',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ]);

        // Add student as officer in both councils with completed_at timestamps
        CouncilOfficer::create([
            'council_id' => $council1->id,
            'student_id' => $this->student->id,
            'position_title' => 'President',
            'position_level' => '1',
            'completed_at' => now()->subYear()
        ]);

        CouncilOfficer::create([
            'council_id' => $council2->id,
            'student_id' => $this->student->id,
            'position_title' => 'Vice President',
            'position_level' => '2',
            'completed_at' => now()
        ]);

        $lengthOfService = $this->student->calculateLengthOfService();
        $this->assertEquals(2.00, $lengthOfService);
        
        $description = $this->student->getLengthOfServiceDescription();
        $this->assertEquals('Finished two terms (2.00)', $description);
    }

    public function test_student_with_three_or_more_completed_councils_has_three_length_of_service()
    {
        // Create four completed councils to test 3+ scenario
        for ($i = 1; $i <= 4; $i++) {
            $council = Council::create([
                'name' => "Test Council 202{$i}",
                'academic_year' => "202{$i}-202" . ($i + 1),
                'status' => 'completed',
                'adviser_id' => $this->adviser->id,
                'department_id' => $this->department->id
            ]);

            CouncilOfficer::create([
                'council_id' => $council->id,
                'student_id' => $this->student->id,
                'position_title' => 'President',
                'position_level' => '1',
                'completed_at' => now()->subYears(4 - $i)
            ]);
        }

        $lengthOfService = $this->student->calculateLengthOfService();
        $this->assertEquals(3.00, $lengthOfService);
        
        $description = $this->student->getLengthOfServiceDescription();
        $this->assertEquals('Finished 3 or more terms (3.00)', $description);
    }

    public function test_student_with_active_councils_not_counted_in_length_of_service()
    {
        // Create one completed council and one active council
        $completedCouncil = Council::create([
            'name' => 'Completed Council',
            'academic_year' => '2023-2024',
            'status' => 'completed',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ]);

        $activeCouncil = Council::create([
            'name' => 'Active Council',
            'academic_year' => '2024-2025',
            'status' => 'active',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ]);

        // Add student as officer in both councils
        CouncilOfficer::create([
            'council_id' => $completedCouncil->id,
            'student_id' => $this->student->id,
            'position_title' => 'President',
            'position_level' => '1',
            'completed_at' => now()->subYear()
        ]);

        CouncilOfficer::create([
            'council_id' => $activeCouncil->id,
            'student_id' => $this->student->id,
            'position_title' => 'President',
            'position_level' => '1',
            'completed_at' => null // Active council, no completion timestamp
        ]);

        // Should only count the completed council
        $lengthOfService = $this->student->calculateLengthOfService();
        $this->assertEquals(1.00, $lengthOfService);
        
        $completedCount = $this->student->getCompletedCouncilsCount();
        $this->assertEquals(1, $completedCount);
    }

    public function test_student_without_completed_at_timestamp_not_counted()
    {
        // Create a completed council but without completed_at timestamp for the officer
        $council = Council::create([
            'name' => 'Test Council',
            'academic_year' => '2023-2024',
            'status' => 'completed',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ]);

        CouncilOfficer::create([
            'council_id' => $council->id,
            'student_id' => $this->student->id,
            'position_title' => 'President',
            'position_level' => '1',
            'completed_at' => null // No completion timestamp
        ]);

        // Should not count this council
        $lengthOfService = $this->student->calculateLengthOfService();
        $this->assertEquals(0.00, $lengthOfService);
    }
}
