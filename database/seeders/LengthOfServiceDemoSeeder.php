<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Council;
use App\Models\CouncilOfficer;
use App\Models\Department;
use App\Models\Adviser;

class LengthOfServiceDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo department if it doesn't exist
        $department = Department::firstOrCreate([
            'abbreviation' => 'DEMO'
        ], [
            'name' => 'Demo Department'
        ]);

        // Create demo adviser if it doesn't exist
        $adviser = Adviser::firstOrCreate([
            'id_number' => 'DEMO001'
        ], [
            'first_name' => 'Demo',
            'last_name' => 'Adviser',
            'email' => 'demo.adviser@example.com',
            'password' => bcrypt('123456'),
            'department_id' => $department->id
        ]);

        // Create demo students with different length of service scenarios
        $students = [
            [
                'id_number' => 'DEMO001',
                'first_name' => 'Alice',
                'last_name' => 'Newbie',
                'email' => 'alice.newbie@example.com',
                'completed_councils' => 0, // Did not finish their term
                'description' => 'New student with no completed council terms'
            ],
            [
                'id_number' => 'DEMO002',
                'first_name' => 'Bob',
                'last_name' => 'Freshman',
                'email' => 'bob.freshman@example.com',
                'completed_councils' => 1, // Finished one term
                'description' => 'Student who completed one council term'
            ],
            [
                'id_number' => 'DEMO003',
                'first_name' => 'Carol',
                'last_name' => 'Experienced',
                'email' => 'carol.experienced@example.com',
                'completed_councils' => 2, // Finished two terms
                'description' => 'Student who completed two council terms'
            ],
            [
                'id_number' => 'DEMO004',
                'first_name' => 'David',
                'last_name' => 'Veteran',
                'email' => 'david.veteran@example.com',
                'completed_councils' => 4, // Finished 3+ terms
                'description' => 'Veteran student who completed multiple council terms'
            ]
        ];

        foreach ($students as $studentData) {
            // Create or update student
            $student = Student::updateOrCreate([
                'id_number' => $studentData['id_number']
            ], [
                'first_name' => $studentData['first_name'],
                'last_name' => $studentData['last_name'],
                'email' => $studentData['email'],
                'password' => bcrypt('123456'),
                'department_id' => $department->id,
                'description' => $studentData['description']
            ]);

            // Create completed councils for this student
            for ($i = 1; $i <= $studentData['completed_councils']; $i++) {
                $academicYear = (2020 + $i) . '-' . (2021 + $i);
                
                $council = Council::firstOrCreate([
                    'academic_year' => $academicYear,
                    'department_id' => $department->id
                ], [
                    'name' => "Demo Council {$academicYear}",
                    'status' => 'completed',
                    'adviser_id' => $adviser->id
                ]);

                // Add student as officer in this council
                CouncilOfficer::firstOrCreate([
                    'council_id' => $council->id,
                    'student_id' => $student->id
                ], [
                    'position_title' => 'President',
                    'position_level' => '1',
                    'completed_at' => now()->subYears($studentData['completed_councils'] - $i + 1)
                ]);
            }

            // Create one active council for demonstration
            $currentYear = date('Y');
            $activeCouncil = Council::firstOrCreate([
                'academic_year' => $currentYear . '-' . ($currentYear + 1),
                'department_id' => $department->id
            ], [
                'name' => "Demo Council {$currentYear}-" . ($currentYear + 1),
                'status' => 'active',
                'adviser_id' => $adviser->id
            ]);

            // Add all students to the current active council
            CouncilOfficer::firstOrCreate([
                'council_id' => $activeCouncil->id,
                'student_id' => $student->id
            ], [
                'position_title' => $studentData['first_name'] === 'Alice' ? 'President' : 
                                  ($studentData['first_name'] === 'Bob' ? 'Vice President' : 
                                  ($studentData['first_name'] === 'Carol' ? 'Secretary' : 'Treasurer')),
                'position_level' => $studentData['first_name'] === 'Alice' ? '1' : '10',
                'completed_at' => null // Active council, not completed yet
            ]);
        }

        $this->command->info('Length of Service demo data created successfully!');
        $this->command->info('Demo students created with different length of service scenarios:');
        $this->command->info('- Alice Newbie: 0 completed terms (0.00)');
        $this->command->info('- Bob Freshman: 1 completed term (1.00)');
        $this->command->info('- Carol Experienced: 2 completed terms (2.00)');
        $this->command->info('- David Veteran: 4 completed terms (3.00)');
        $this->command->info('All students are also in the current active council.');
    }
}
