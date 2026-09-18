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
        Schema::create('teacher_performance_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('academic_period');
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->json('student_survey_results')->nullable();
            $table->json('matrix_consolidation')->nullable();
            $table->text('improvement_plan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_performance_evaluations');
    }
};
