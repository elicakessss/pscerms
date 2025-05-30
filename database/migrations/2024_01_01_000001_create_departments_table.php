<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('abbreviation', ['SASTE', 'SBAHM', 'SITE', 'SNAHS', 'UNIWIDE']);
            $table->timestamps();
        });

        // Insert the predefined departments
        DB::table('departments')->insert([
            ['name' => 'University Wide', 'abbreviation' => 'UNIWIDE', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'School of Art Sciences and Teacher Education', 'abbreviation' => 'SASTE', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'School of Business, Accountancy and Hospitality Management', 'abbreviation' => 'SBAHM', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'School of Information Technology and Engineering', 'abbreviation' => 'SITE', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'School of Nursing and Allied Health Sciences', 'abbreviation' => 'SNAHS', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};