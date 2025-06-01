<?php

namespace Tests\Feature;

use App\Models\Council;
use App\Models\CouncilOfficer;
use App\Models\Department;
use App\Models\Adviser;
use App\Models\Student;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCouncilManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $department;
    protected $adviser;

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

        $this->admin = Admin::create([
            'id_number' => 'ADMIN001',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password')
        ]);
    }

    public function test_admin_can_view_council_index()
    {
        // Create a council
        $council = Council::create([
            'name' => 'Student Council 2024',
            'academic_year' => '2024-2025',
            'status' => 'active',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ]);

        $response = $this->actingAs($this->admin, 'admin')
                         ->get(route('admin.council_management.index'));

        $response->assertStatus(200);
        $response->assertSee('Council Management');
        $response->assertSee('Student Council 2024');
    }

    public function test_admin_can_create_council()
    {
        $councilData = [
            'name' => 'New Student Council',
            'academic_year' => '2024-2025',
            'status' => 'active',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ];

        $response = $this->actingAs($this->admin, 'admin')
                         ->post(route('admin.council_management.store'), $councilData);

        $response->assertRedirect(route('admin.council_management.index'));
        $this->assertDatabaseHas('councils', [
            'name' => 'Paulinian Student Government - SITE',
            'academic_year' => '2024-2025'
        ]);
    }

    public function test_admin_can_view_council_details()
    {
        $council = Council::create([
            'name' => 'Student Council 2024',
            'academic_year' => '2024-2025',
            'status' => 'active',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ]);

        $response = $this->actingAs($this->admin, 'admin')
                         ->get(route('admin.council_management.show', $council));

        $response->assertStatus(200);
        $response->assertSee('Council Details');
        $response->assertSee('Student Council 2024');
        $response->assertSee($this->adviser->first_name);
        $response->assertSee($this->department->name);
    }

    public function test_admin_can_update_academic_year()
    {
        $council = Council::create([
            'name' => 'Student Council 2024',
            'academic_year' => '2024-2025',
            'status' => 'active',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ]);

        $response = $this->actingAs($this->admin, 'admin')
                         ->put(route('admin.council_management.update_academic_year'), [
                             'academic_year' => '2025-2026'
                         ]);

        $response->assertRedirect(route('admin.council_management.index'));
        $this->assertDatabaseHas('councils', [
            'id' => $council->id,
            'academic_year' => '2025-2026'
        ]);
    }

    public function test_adviser_cannot_edit_completed_council()
    {
        // Create a completed council
        $council = Council::create([
            'name' => 'Completed Council',
            'academic_year' => '2024-2025',
            'status' => 'completed',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ]);

        // Create a student
        $student = Student::create([
            'id_number' => 'STU001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id
        ]);

        // Try to assign an officer to the completed council
        $response = $this->actingAs($this->adviser, 'adviser')
                         ->post(route('adviser.councils.assign_officer', $council), [
                             'student_id' => $student->id,
                             'position_title' => 'President',
                             'position_level' => 'Executive'
                         ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['error' => 'Cannot modify officers in a completed council.']);
    }
}
