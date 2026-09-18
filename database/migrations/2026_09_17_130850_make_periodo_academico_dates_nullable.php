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
        Schema::table('periodos_academicos', function (Blueprint $table) {
            $table->date('fecha_inicio')->nullable()->change();
            $table->date('fecha_fin')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Las fechas oficiales desconocidas no deben rellenarse con valores inventados.
    }
};
