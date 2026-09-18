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
        Schema::create('indicadores_maestros', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 200);
            $table->string('proceso', 100);
            $table->text('finalidad');
            $table->text('formula_texto');
            $table->enum('unidad_medida', ['PORCENTAJE', 'NUMERO', 'INDICE'])->default('PORCENTAJE');
            $table->decimal('meta_institucional', 8, 2);
            $table->decimal('nivel_critico', 8, 2)->nullable();
            $table->enum('sentido_meta', ['MAYOR_IGUAL', 'MENOR_IGUAL'])->default('MAYOR_IGUAL');
            $table->enum('frecuencia', ['SEMESTRAL', 'ANUAL', 'TRIENAL'])->default('SEMESTRAL');
            $table->string('responsable', 100)->default('Director de Escuela');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indicadores_maestros');
    }
};
