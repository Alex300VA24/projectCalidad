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
        Schema::create('curriculum_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->nullable()->constrained('procedures')->nullOnDelete();
            $table->foreignId('curriculum_id')->constrained('curricula')->cascadeOnDelete();
            $table->foreignId('coteccu_user_id')->constrained('users')->cascadeOnDelete();
            $table->json('review_checklist_data')->nullable();
            $table->string('technical_report_path')->nullable();
            $table->string('decision')->nullable();
            $table->string('state')->default('en_revision');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_reviews');
    }
};
