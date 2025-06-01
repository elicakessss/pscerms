<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Adviser;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DefaultPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $department;
    protected $adviser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test department
        $this->department = Department::create([
            'name' => 'School of Information Technology and Engineering',
            'abbreviation' => 'SITE'
        ]);

        // Create test adviser
        $this->adviser = Adviser::create([
            'id_number' => 'ADV001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'adviser@test.com',
            'password' => bcrypt('password'),
            'department_id' => $this->department->id
        ]);

        // Create test admin
        $this->admin = Admin::create([
            'id_number' => 'ADMIN001',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password')
        ]);
    }

    public function test_adviser_creates_student_with_default_password_when_field_is_empty()
    {
        $response = $this->actingAs($this->adviser, 'adviser')
            ->post(route('adviser.student_management.store'), [
                'id_number' => 'STU001',
                'first_name' => 'Test',
                'last_name' => 'Student',
                'email' => 'test.student@example.com',
                'password' => '', // Empty password
                'description' => 'Test student'
            ]);

        $response->assertRedirect(route('adviser.student_management.index'));
        $response->assertSessionHas('success');

        // Check that student was created
        $student = Student::where('email', 'test.student@example.com')->first();
        $this->assertNotNull($student);

        // Check that the default password (123456) works
        $this->assertTrue(Hash::check('123456', $student->password));
    }

    public function test_adviser_creates_student_with_custom_password_when_field_is_provided()
    {
        $response = $this->actingAs($this->adviser, 'adviser')
            ->post(route('adviser.student_management.store'), [
                'id_number' => 'STU002',
                'first_name' => 'Test',
                'last_name' => 'Student',
                'email' => 'test.student2@example.com',
                'password' => '654321', // Custom password
                'description' => 'Test student'
            ]);

        $response->assertRedirect(route('adviser.student_management.index'));
        $response->assertSessionHas('success');

        // Check that student was created
        $student = Student::where('email', 'test.student2@example.com')->first();
        $this->assertNotNull($student);

        // Check that the custom password works
        $this->assertTrue(Hash::check('654321', $student->password));
        $this->assertFalse(Hash::check('123456', $student->password));
    }

    public function test_admin_creates_user_with_default_password_when_field_is_empty()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.user_management.store'), [
                'user_type' => 'student',
                'id_number' => 'STU003',
                'first_name' => 'Admin',
                'last_name' => 'Created',
                'email' => 'admin.created@example.com',
                'password' => '', // Empty password
                'department_id' => $this->department->id,
                'description' => 'Admin created student'
            ]);

        $response->assertRedirect(route('admin.user_management.index'));
        $response->assertSessionHas('success');

        // Check that student was created
        $student = Student::where('email', 'admin.created@example.com')->first();
        $this->assertNotNull($student);

        // Check that the default password (123456) works
        $this->assertTrue(Hash::check('123456', $student->password));
    }

    public function test_admin_creates_user_with_custom_password_when_field_is_provided()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.user_management.store'), [
                'user_type' => 'adviser',
                'id_number' => 'ADV002',
                'first_name' => 'Admin',
                'last_name' => 'Created',
                'email' => 'admin.created.adviser@example.com',
                'password' => '987654', // Custom password
                'department_id' => $this->department->id,
                'description' => 'Admin created adviser'
            ]);

        $response->assertRedirect(route('admin.user_management.index'));
        $response->assertSessionHas('success');

        // Check that adviser was created
        $adviser = Adviser::where('email', 'admin.created.adviser@example.com')->first();
        $this->assertNotNull($adviser);

        // Check that the custom password works
        $this->assertTrue(Hash::check('987654', $adviser->password));
        $this->assertFalse(Hash::check('123456', $adviser->password));
    }
}
