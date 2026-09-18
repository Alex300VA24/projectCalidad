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
        Schema::create('educational_objective_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('academic_period');
            $table->json('stakeholders_survey_data')->nullable();
            $table->json('competency_level_report')->nullable();
            $table->json('curriculum_feedback_actions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educational_objective_evaluations');
    }
};
