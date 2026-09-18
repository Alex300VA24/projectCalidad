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
        Schema::create('incidencias_matricula', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programa_estudio_id')->constrained('programas_estudio')->cascadeOnDelete();
            $table->string('periodo_academico', 10);
            $table->text('descripcion');
            $table->enum('estado', ['REPORTADA', 'EN_PROCESO', 'RESUELTA'])->default('REPORTADA');
            $table->timestamp('resuelta_en')->nullable();
            $table->timestamps();

            $table->index(['programa_estudio_id', 'periodo_academico', 'estado'], 'incidencia_programa_periodo_estado_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidencias_matricula');
    }
};
