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
        Schema::table('syllabi', function (Blueprint $table) {
            $table->string('tipo_silabo')->nullable()->after('academic_period');
            $table->string('modalidad')->nullable()->after('tipo_silabo');
            $table->string('seccion')->nullable()->after('modalidad');
            $table->text('fundamentacion')->nullable()->after('seccion');
            $table->text('aprendizajes_esperados')->nullable()->after('fundamentacion');
            $table->json('unidades')->nullable()->after('aprendizajes_esperados');
            $table->json('sesiones_no_presenciales')->nullable()->after('unidades');
            $table->text('guias_aprendizaje')->nullable()->after('sesiones_no_presenciales');
            $table->text('material_trabajo_distancia')->nullable()->after('guias_aprendizaje');
            $table->string('tutoria_dia')->nullable()->after('material_trabajo_distancia');
            $table->string('tutoria_medio')->nullable()->after('tutoria_dia');
            $table->string('tutoria_horario')->nullable()->after('tutoria_medio');
            $table->text('bibliografia')->nullable()->after('tutoria_horario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('syllabi', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_silabo', 'modalidad', 'seccion', 'fundamentacion', 'aprendizajes_esperados',
                'unidades', 'sesiones_no_presenciales', 'guias_aprendizaje', 'material_trabajo_distancia',
                'tutoria_dia', 'tutoria_medio', 'tutoria_horario', 'bibliografia',
            ]);
        });
    }
};
