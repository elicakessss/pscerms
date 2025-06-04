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
        Schema::create('leadership_certificate_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('council_id')->constrained('councils')->onDelete('cascade');
            $table->enum('certificate_type', ['campus', 'departmental']);
            $table->boolean('is_graduating')->default(false);
            $table->enum('status', ['pending', 'approved', 'dismissed'])->default('pending');
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->text('adviser_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leadership_certificate_requests');
    }
};
