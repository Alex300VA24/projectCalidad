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
        Schema::create('curriculum_redesigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->nullable()->constrained('procedures')->nullOnDelete();
            $table->foreignId('curriculum_id')->constrained('curricula')->cascadeOnDelete();
            $table->json('work_plan_data')->nullable();
            $table->json('structure_format_data')->nullable();
            $table->json('validation_stakeholders_data')->nullable();
            $table->string('state')->default('en_elaboracion');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curriculum_redesigns');
    }
};
