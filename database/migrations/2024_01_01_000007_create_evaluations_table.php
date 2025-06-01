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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('council_id')->constrained();
            $table->unsignedBigInteger('evaluator_id'); // No foreign key constraint since it can be student or adviser
            $table->enum('evaluator_type', ['self', 'peer', 'adviser']);
            $table->foreignId('evaluated_student_id')->constrained('students');
            $table->enum('evaluation_type', ['rating', 'completed']);
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
