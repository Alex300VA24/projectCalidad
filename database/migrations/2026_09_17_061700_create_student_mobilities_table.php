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
        Schema::create('student_mobilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->nullable()->constrained('procedures')->nullOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('destination_university');
            $table->string('call_type');
            $table->boolean('is_interareas_view')->default(true);
            $table->string('orni_registration_code')->nullable();
            $table->boolean('fee_exemption')->default(false);
            $table->string('subvention_status')->nullable();
            $table->string('convalidation_resolution_number')->nullable();
            $table->json('equivalent_grades_data')->nullable();
            $table->string('status')->default('postulado');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_mobilities');
    }
};
