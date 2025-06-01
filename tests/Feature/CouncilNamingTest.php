<?php

namespace Tests\Feature;

use App\Models\Council;
use App\Models\Department;
use App\Models\Adviser;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouncilNamingTest extends TestCase
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

    public function test_council_name_follows_paulinian_student_government_format()
    {
        $councilData = [
            'name' => 'Paulinian Student Government - SITE',
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

    public function test_admin_can_access_council_show_page()
    {
        $council = Council::create([
            'name' => 'Paulinian Student Government - SITE',
            'academic_year' => '2024-2025',
            'status' => 'active',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ]);

        $response = $this->actingAs($this->admin, 'admin')
                         ->get(route('admin.council_management.show', $council));

        $response->assertStatus(200);
        $response->assertSee('Paulinian Student Government - SITE');
    }

    public function test_admin_can_access_council_edit_page()
    {
        $council = Council::create([
            'name' => 'Paulinian Student Government - SITE',
            'academic_year' => '2024-2025',
            'status' => 'active',
            'adviser_id' => $this->adviser->id,
            'department_id' => $this->department->id
        ]);

        $response = $this->actingAs($this->admin, 'admin')
                         ->get(route('admin.council_management.edit', $council));

        $response->assertStatus(200);
        $response->assertSee('Edit Council');
        $response->assertSee('Paulinian Student Government - SITE');
    }

    public function test_adviser_must_belong_to_same_department_as_council()
    {
        // Create another department and adviser
        $otherDepartment = Department::create([
            'name' => 'School of Art Sciences and Teacher Education',
            'abbreviation' => 'SASTE'
        ]);

        $otherAdviser = Adviser::create([
            'id_number' => 'ADV002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'adviser2@test.com',
            'password' => bcrypt('password'),
            'department_id' => $otherDepartment->id
        ]);

        // Try to create a council with mismatched department and adviser
        $councilData = [
            'name' => 'Paulinian Student Government - SITE',
            'academic_year' => '2024-2025',
            'status' => 'active',
            'adviser_id' => $otherAdviser->id, // This adviser is from SASTE
            'department_id' => $this->department->id, // But council is for SITE
        ];

        $response = $this->actingAs($this->admin, 'admin')
                         ->post(route('admin.council_management.store'), $councilData);

        $response->assertSessionHasErrors(['adviser_id']);
        $response->assertRedirect();
    }
}
