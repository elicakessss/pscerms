<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert the predefined departments
        $departments = [
            ['name' => 'University Wide', 'abbreviation' => 'UNIWIDE'],
            ['name' => 'School of Art Sciences and Teacher Education', 'abbreviation' => 'SASTE'],
            ['name' => 'School of Business, Accountancy and Hospitality Management', 'abbreviation' => 'SBAHM'],
            ['name' => 'School of Information Technology and Engineering', 'abbreviation' => 'SITE'],
            ['name' => 'School of Nursing and Allied Health Sciences', 'abbreviation' => 'SNAHS'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['abbreviation' => $department['abbreviation']],
                $department
            );
        }

        $this->command->info('Default departments created successfully.');
    }
}
