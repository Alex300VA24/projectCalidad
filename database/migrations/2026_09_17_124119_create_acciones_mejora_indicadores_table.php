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
        Schema::create('acciones_mejora_indicadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicador_medicion_id')->constrained('indicadores_mediciones')->cascadeOnDelete();
            $table->text('descripcion');
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_limite')->nullable();
            $table->string('estado', 20)->default('PENDIENTE');
            $table->text('seguimiento')->nullable();
            $table->string('evidencia_url')->nullable();
            $table->foreignId('verificada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cerrada_en')->nullable();
            $table->timestamps();

            $table->index(['indicador_medicion_id', 'estado'], 'accion_indicador_estado_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acciones_mejora_indicadores');
    }
};
