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
        Schema::create('sufficiency_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->nullable()->constrained('procedures')->nullOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->boolean('director_approval')->default(false);
            $table->json('jury_members')->nullable();
            $table->string('resolution_number')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->string('act_number')->nullable();
            $table->string('status')->default('solicitado');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sufficiency_exams');
    }
};
