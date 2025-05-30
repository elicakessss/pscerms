<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default admin account for testing
        Admin::create([
            'id_number' => '1234',
            'first_name' => 'System',
            'last_name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('123456'),
        ]);
    }
}
