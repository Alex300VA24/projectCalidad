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
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programa_estudio_id')->constrained('programas_estudio')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained('courses')->cascadeOnDelete();
            $table->string('periodo_academico', 10);
            $table->unsignedTinyInteger('ciclo_academico');
            $table->unsignedTinyInteger('numero_matricula')->default(1);
            $table->enum('estado_resultado', ['CURSANDO', 'APROBADO', 'DESAPROBADO', 'INHABILITADO'])->default('CURSANDO');
            $table->timestamps();

            $table->unique(['programa_estudio_id', 'estudiante_id', 'curso_id', 'periodo_academico'], 'matricula_estudiante_curso_periodo_unique');
            $table->index(['programa_estudio_id', 'periodo_academico', 'estado_resultado'], 'matricula_programa_periodo_estado_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
