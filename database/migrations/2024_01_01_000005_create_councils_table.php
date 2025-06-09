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
        Schema::create('councils', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('academic_year');
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->enum('evaluation_instance_status', ['not_started', 'active', 'finalized'])->default('not_started');
            $table->timestamp('evaluation_instance_started_at')->nullable();
            $table->timestamp('evaluation_instance_finalized_at')->nullable();
            $table->foreignId('adviser_id')->constrained('advisers');
            $table->foreignId('department_id')->constrained();
            $table->timestamps();

            // Add unique constraint for department_id and academic_year combination
            $table->unique(['department_id', 'academic_year'], 'councils_department_academic_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('councils');
    }
};
