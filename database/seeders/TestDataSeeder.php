<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Adviser;
use App\Models\Student;
use App\Models\Council;
use App\Models\CouncilOfficer;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test adviser
        $adviser = Adviser::create([
            'id_number' => 'ADV-001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'adviser@test.com',
            'password' => Hash::make('123456'),
            'department_id' => Department::where('abbreviation', 'SITE')->first()->id,
        ]);

        // Create test students
        $student1 = Student::create([
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => 'alice@test.com',
            'password' => Hash::make('123456'),
            'id_number' => '2024-001',
            'department_id' => Department::where('abbreviation', 'SITE')->first()->id,
        ]);

        $student2 = Student::create([
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'email' => 'bob@test.com',
            'password' => Hash::make('123456'),
            'id_number' => '2024-002',
            'department_id' => Department::where('abbreviation', 'SITE')->first()->id,
        ]);

        $student3 = Student::create([
            'first_name' => 'Carol',
            'last_name' => 'Williams',
            'email' => 'carol@test.com',
            'password' => Hash::make('123456'),
            'id_number' => '2024-003',
            'department_id' => Department::where('abbreviation', 'SITE')->first()->id,
        ]);

        // Create test council
        $council = Council::create([
            'name' => 'Paulinian Student Government - SITE',
            'academic_year' => '2024-2025',
            'status' => 'active',
            'evaluation_instance_status' => 'not_started',
            'adviser_id' => $adviser->id,
            'department_id' => Department::where('abbreviation', 'SITE')->first()->id,
        ]);

        // Create council officers
        CouncilOfficer::create([
            'council_id' => $council->id,
            'student_id' => $student1->id,
            'position_title' => 'President',
            'position_level' => 'Executive',
            'is_peer_evaluator' => true,
            'peer_evaluator_level' => 1,
        ]);

        CouncilOfficer::create([
            'council_id' => $council->id,
            'student_id' => $student2->id,
            'position_title' => 'Vice President',
            'position_level' => 'Executive',
            'is_peer_evaluator' => true,
            'peer_evaluator_level' => 2,
        ]);

        CouncilOfficer::create([
            'council_id' => $council->id,
            'student_id' => $student3->id,
            'position_title' => 'Secretary',
            'position_level' => 'Officer',
        ]);

        echo "Test data created successfully!\n";
        echo "Adviser: adviser@test.com / 123456\n";
        echo "Students: alice@test.com, bob@test.com, carol@test.com / 123456\n";
        echo "Council: {$council->name} with 3 officers and 2 peer evaluators assigned\n";
    }
}
