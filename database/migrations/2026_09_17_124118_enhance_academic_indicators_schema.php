<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('periodos_academicos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::table('indicadores_maestros', function (Blueprint $table) {
            $table->decimal('meta_institucional', 12, 4)->nullable()->change();
            $table->decimal('nivel_advertencia', 12, 4)->nullable()->after('meta_institucional');
            $table->decimal('nivel_critico', 12, 4)->nullable()->change();
            $table->string('regla_cumplimiento', 40)->nullable()->after('sentido_meta');
            $table->json('configuracion')->nullable()->after('regla_cumplimiento');
        });

        Schema::table('indicadores_mediciones', function (Blueprint $table) {
            $table->foreignId('periodo_academico_id')->nullable()->after('programa_estudio_id')->constrained('periodos_academicos')->restrictOnDelete();
            $table->decimal('valor_medido', 12, 4)->change();
            $table->decimal('meta_programada', 12, 4)->nullable()->change();
            $table->string('estado_cumplimiento', 24)->change();
            $table->timestamp('consolidada_en')->nullable()->after('registrado_por');
            $table->json('datos_fuente')->nullable()->after('consolidada_en');
        });

        Schema::table('matriculas', function (Blueprint $table) {
            $table->foreignId('periodo_academico_id')->nullable()->after('curso_id')->constrained('periodos_academicos')->restrictOnDelete();
            $table->string('estado_matricula', 20)->default('CONFIRMADA')->after('numero_matricula');
            $table->index(['programa_estudio_id', 'periodo_academico_id', 'estado_matricula'], 'matricula_programa_periodo_confirmacion_index');
        });

        Schema::table('incidencias_matricula', function (Blueprint $table) {
            $table->string('estado', 20)->default('REPORTADA')->change();
        });

        Schema::table('syllabi', function (Blueprint $table) {
            $table->foreignId('programa_estudio_id')->nullable()->after('procedure_id')->constrained('programas_estudio')->restrictOnDelete();
            $table->foreignId('periodo_academico_id')->nullable()->after('teacher_id')->constrained('periodos_academicos')->restrictOnDelete();
            $table->index(['programa_estudio_id', 'periodo_academico_id', 'status'], 'syllabi_programa_periodo_estado_index');
        });

        Schema::table('course_execution_reports', function (Blueprint $table) {
            $table->foreignId('programa_estudio_id')->nullable()->after('procedure_id')->constrained('programas_estudio')->restrictOnDelete();
            $table->foreignId('periodo_academico_id')->nullable()->after('teacher_id')->constrained('periodos_academicos')->restrictOnDelete();
            $table->string('academic_period', 10)->nullable()->after('periodo_academico_id');
            $table->string('status', 20)->default('BORRADOR')->after('academic_period');
            $table->index(['programa_estudio_id', 'periodo_academico_id', 'status'], 'execution_programa_periodo_estado_index');
        });

        Schema::table('tutoring_sessions', function (Blueprint $table) {
            $table->foreignId('programa_estudio_id')->nullable()->after('procedure_id')->constrained('programas_estudio')->restrictOnDelete();
            $table->foreignId('periodo_academico_id')->nullable()->after('tutor_id')->constrained('periodos_academicos')->restrictOnDelete();
            $table->index(['programa_estudio_id', 'periodo_academico_id', 'status'], 'tutoring_programa_periodo_estado_index');
        });

        Schema::table('student_referrals', function (Blueprint $table) {
            $table->foreignId('programa_estudio_id')->nullable()->after('procedure_id')->constrained('programas_estudio')->restrictOnDelete();
            $table->foreignId('periodo_academico_id')->nullable()->after('referrer_id')->constrained('periodos_academicos')->restrictOnDelete();
            $table->index(['programa_estudio_id', 'periodo_academico_id'], 'referral_programa_periodo_index');
        });

        Schema::table('graduate_registries', function (Blueprint $table) {
            $table->foreignId('programa_estudio_id')->nullable()->after('student_id')->constrained('programas_estudio')->restrictOnDelete();
            $table->foreignId('periodo_academico_id')->nullable()->after('programa_estudio_id')->constrained('periodos_academicos')->restrictOnDelete();
            $table->boolean('titulado')->nullable()->after('apt_list_number');
            $table->string('condicion_laboral', 24)->nullable()->after('titulado');
            $table->boolean('labora_especialidad')->nullable()->after('condicion_laboral');
            $table->index(['programa_estudio_id', 'periodo_academico_id'], 'graduate_programa_periodo_index');
        });

        $this->crearPeriodosDesdeDatosExistentes();
        $this->asociarDatosExistentes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('graduate_registries', function (Blueprint $table) {
            $table->dropForeign(['programa_estudio_id']);
            $table->dropForeign(['periodo_academico_id']);
            $table->dropColumn(['programa_estudio_id', 'periodo_academico_id', 'titulado', 'condicion_laboral', 'labora_especialidad']);
        });
        Schema::table('student_referrals', function (Blueprint $table) {
            $table->dropForeign(['programa_estudio_id']);
            $table->dropForeign(['periodo_academico_id']);
            $table->dropColumn(['programa_estudio_id', 'periodo_academico_id']);
        });
        Schema::table('tutoring_sessions', function (Blueprint $table) {
            $table->dropForeign(['programa_estudio_id']);
            $table->dropForeign(['periodo_academico_id']);
            $table->dropColumn(['programa_estudio_id', 'periodo_academico_id']);
        });
        Schema::table('course_execution_reports', function (Blueprint $table) {
            $table->dropForeign(['programa_estudio_id']);
            $table->dropForeign(['periodo_academico_id']);
            $table->dropColumn(['programa_estudio_id', 'periodo_academico_id', 'academic_period', 'status']);
        });
        Schema::table('syllabi', function (Blueprint $table) {
            $table->dropForeign(['programa_estudio_id']);
            $table->dropForeign(['periodo_academico_id']);
            $table->dropColumn(['programa_estudio_id', 'periodo_academico_id']);
        });
        Schema::table('matriculas', function (Blueprint $table) {
            $table->dropForeign(['periodo_academico_id']);
            $table->dropColumn(['periodo_academico_id', 'estado_matricula']);
        });
        Schema::table('indicadores_mediciones', function (Blueprint $table) {
            $table->dropForeign(['periodo_academico_id']);
            $table->dropColumn(['periodo_academico_id', 'consolidada_en', 'datos_fuente']);
        });
        Schema::table('indicadores_maestros', function (Blueprint $table) {
            $table->dropColumn(['nivel_advertencia', 'regla_cumplimiento', 'configuracion']);
        });
        Schema::dropIfExists('periodos_academicos');
    }

    private function crearPeriodosDesdeDatosExistentes(): void
    {
        $codigos = collect(['2024-I', '2024-II', '2025-I', '2025-II', '2026-I', '2026-II']);

        foreach (['indicadores_mediciones' => 'periodo_academico', 'matriculas' => 'periodo_academico', 'syllabi' => 'academic_period'] as $tabla => $columna) {
            $codigos = $codigos->merge(DB::table($tabla)->whereNotNull($columna)->pluck($columna));
        }

        foreach ($codigos->filter(fn (mixed $codigo): bool => is_string($codigo) && preg_match('/^\d{4}-(I|II)$/', $codigo) === 1)->unique() as $codigo) {
            [$anio, $semestre] = explode('-', $codigo);
            DB::table('periodos_academicos')->updateOrInsert(
                ['codigo' => $codigo],
                [
                    'fecha_inicio' => null,
                    'fecha_fin' => null,
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    private function asociarDatosExistentes(): void
    {
        $programaId = DB::table('programas_estudio')->where('activo', true)->orderBy('id')->value('id');
        $periodos = DB::table('periodos_academicos')->pluck('id', 'codigo');

        foreach ($periodos as $codigo => $periodoId) {
            DB::table('indicadores_mediciones')->where('periodo_academico', $codigo)->update(['periodo_academico_id' => $periodoId]);
            DB::table('matriculas')->where('periodo_academico', $codigo)->update(['periodo_academico_id' => $periodoId]);
            DB::table('syllabi')->where('academic_period', $codigo)->update(['periodo_academico_id' => $periodoId]);
        }

        if ($programaId !== null) {
            foreach (['syllabi', 'course_execution_reports', 'tutoring_sessions', 'student_referrals', 'graduate_registries'] as $tabla) {
                DB::table($tabla)->whereNull('programa_estudio_id')->update(['programa_estudio_id' => $programaId]);
            }
        }
    }
};
