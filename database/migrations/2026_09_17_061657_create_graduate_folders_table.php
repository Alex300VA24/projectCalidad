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
        Schema::create('graduate_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->nullable()->constrained('procedures')->nullOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('stu_registration_code')->nullable();
            $table->boolean('egresado_condition_validated')->default(false);
            $table->boolean('approval_constancy')->default(false);
            $table->boolean('expedito_constancy')->default(false);
            $table->boolean('no_adeudo_constancy')->default(false);
            $table->json('sunedu_data_payload')->nullable();
            $table->string('status')->default('en_verificacion');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduate_folders');
    }
};
