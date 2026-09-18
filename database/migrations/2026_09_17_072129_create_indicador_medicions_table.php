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
        Schema::create('indicadores_mediciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicador_id')->constrained('indicadores_maestros')->cascadeOnDelete();
            $table->foreignId('programa_estudio_id')->constrained('programas_estudio')->cascadeOnDelete();
            $table->string('periodo_academico', 10);
            $table->decimal('valor_medido', 8, 2);
            $table->decimal('meta_programada', 8, 2);
            $table->enum('estado_cumplimiento', ['CONFORME', 'OBSERVADO', 'CRITICO']);
            $table->text('analisis_causas')->nullable();
            $table->text('acciones_mejora')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['indicador_id', 'programa_estudio_id', 'periodo_academico'], 'indicador_medicion_periodo_unique');
            $table->index(['programa_estudio_id', 'periodo_academico', 'estado_cumplimiento'], 'medicion_programa_periodo_estado_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indicadores_mediciones');
    }
};
