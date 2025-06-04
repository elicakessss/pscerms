<?php

namespace Database\Seeders;

use App\Models\Council;
use App\Models\CouncilOfficer;
use App\Models\Department;
use App\Models\Adviser;
use App\Models\Student;
use Illuminate\Database\Seeder;

class CouncilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing departments and advisers
        $departments = Department::all();

        if ($departments->isEmpty()) {
            $this->command->info('No departments found. Please run the department seeder first.');
            return;
        }

        foreach ($departments as $department) {
            // Get an adviser from this department
            $adviser = Adviser::where('department_id', $department->id)->first();

            if (!$adviser) {
                $this->command->info("No adviser found for department: {$department->name}");
                continue;
            }

            // Create a council for this department
            $council = Council::create([
                'name' => 'Paulinian Student Government - ' . $department->abbreviation,
                'academic_year' => '2024-2025',
                'status' => 'active',
                'adviser_id' => $adviser->id,
                'department_id' => $department->id,
            ]);

            $this->command->info("Created council: {$council->name} (empty - ready for adviser to assign positions)");

            // Note: Councils are created empty. Advisers will assign students to positions through the interface.
        }
    }

    /**
     * Get position templates based on department type
     */
    private function getPositionTemplates($departmentAbbreviation)
    {
        if ($departmentAbbreviation === 'UNIWIDE') {
            return $this->getUniversityWidePositions();
        } else {
            return $this->getDepartmentalPositions();
        }
    }

    /**
     * Get position templates for departmental councils
     */
    private function getDepartmentalPositions()
    {
        return [
            // Executive Branch
            ['title' => 'Governor', 'level' => 'Executive'],
            ['title' => 'Vice Governor', 'level' => 'Executive'],
            ['title' => 'Secretary', 'level' => 'Officer'],
            ['title' => 'Assistant Secretary', 'level' => 'Officer'],
            ['title' => 'Treasurer', 'level' => 'Officer'],
            ['title' => 'Assistant Treasurer', 'level' => 'Officer'],
            ['title' => 'Auditor', 'level' => 'Officer'],
            ['title' => 'Public Relations Officer', 'level' => 'Officer'],
            ['title' => 'Assistant Public Relations Officer', 'level' => 'Officer'],

            // Legislative Branch
            ['title' => 'Councilor 1', 'level' => 'Officer'],
            ['title' => 'Councilor 2', 'level' => 'Officer'],
            ['title' => 'Councilor 3', 'level' => 'Officer'],
            ['title' => 'Councilor 4', 'level' => 'Officer'],
            ['title' => 'Councilor 5', 'level' => 'Officer'],
            ['title' => 'Councilor 6', 'level' => 'Officer'],
            ['title' => 'Councilor 7', 'level' => 'Officer'],
            ['title' => 'Councilor 8', 'level' => 'Officer'],

            // Mayoral Branch
            ['title' => '1st Year Mayor', 'level' => 'Officer'],
            ['title' => '2nd Year Mayor', 'level' => 'Officer'],
            ['title' => '3rd Year Mayor', 'level' => 'Officer'],
            ['title' => '4th Year Mayor', 'level' => 'Officer'],
        ];
    }

    /**
     * Get position templates for university-wide councils
     */
    private function getUniversityWidePositions()
    {
        return [
            // Executive Branch
            ['title' => 'President', 'level' => 'Executive'],
            ['title' => 'Vice President', 'level' => 'Executive'],
            ['title' => 'Secretary', 'level' => 'Officer'],
            ['title' => 'Assistant Secretary', 'level' => 'Officer'],
            ['title' => 'Treasurer', 'level' => 'Officer'],
            ['title' => 'Assistant Treasurer', 'level' => 'Officer'],
            ['title' => 'Auditor', 'level' => 'Officer'],
            ['title' => 'Public Relations Officer', 'level' => 'Officer'],
            ['title' => 'Assistant Public Relations Officer', 'level' => 'Officer'],


        ];
    }
}
