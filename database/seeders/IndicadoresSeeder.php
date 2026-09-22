<?php

namespace Database\Seeders;

use App\Models\IndicadorMaestro;
use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;
use Illuminate\Database\Seeder;

class IndicadoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProgramaEstudio::updateOrCreate(
            ['codigo' => 'UNT-EP'],
            ['nombre' => 'Escuela Profesional UNT', 'activo' => true],
        );

        foreach (range(2024, 2026) as $anio) {
            foreach (['I', 'II'] as $semestre) {
                PeriodoAcademico::updateOrCreate(
                    ['codigo' => "{$anio}-{$semestre}"],
                    [
                        'fecha_inicio' => null,
                        'fecha_fin' => null,
                        'activo' => true,
                    ],
                );
            }
        }

        $indicadores = [
            [
                'codigo' => 'I-M01.01-DPA-004',
                'nombre' => 'Sílabos visados antes del inicio del semestre',
                'proceso' => 'Gestión Curricular',
                'macro_proceso' => 'Gestión Curricular',
                'finalidad' => 'Evidenciar que los sílabos sean visados antes del primer día de clases.',
                'formula_texto' => '(N° de sílabos visados / N° total de asignaturas implementadas en el semestre académico) * 100',
                'meta_institucional' => 80,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'N° de sílabos visados',
                        'denominador_label' => 'N° total de asignaturas implementadas',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.01.02.02-FI-001',
                'nombre' => 'Tasa de retención por programa de estudios',
                'proceso' => 'Matrícula',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Medir el porcentaje de estudiantes que permanecen inscritos.',
                'formula_texto' => '(Matriculados periodo actual / Matriculados periodo anterior) * 100',
                'meta_institucional' => 90,
                'nivel_critico' => 85,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
            ],
            [
                'codigo' => 'M01.01.02.02-FI-002',
                'nombre' => 'Porcentaje de estudiantes que repiten una asignatura',
                'proceso' => 'Matrícula',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Identificar asignaturas con altos índices de repetición.',
                'formula_texto' => '(Estudiantes en segunda o mayor matrícula / Total matriculados) * 100',
                'meta_institucional' => 10,
                'nivel_critico' => 15,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MENOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
            ],
            [
                'codigo' => 'M01.01.02.02-FI-003',
                'nombre' => 'Resolución de problemas en matrícula',
                'proceso' => 'Matrícula',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Medir la atención oportuna de incidencias durante la matrícula.',
                'formula_texto' => '(Incidencias resueltas / Total incidencias reportadas) * 100',
                'meta_institucional' => 95,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
            ],
            [
                'codigo' => 'M01.01.03.01-F-013',
                'nombre' => 'Cumplimiento de avance silábico',
                'proceso' => 'Desarrollo Académico',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Controlar el avance de los temas programados en cada asignatura.',
                'formula_texto' => 'Promedio del porcentaje de avance reportado por asignatura',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
                'configuracion' => [
                    'formatos_fuente' => ['M01.01.03.01-F-013', 'M01.01.03.01-F-005'],
                    'campo_porcentaje' => 'porcentaje_avance',
                ],
            ],
            [
                'codigo' => 'M01.04-DDA-FI-001',
                'nombre' => 'Eficacia de sesiones de tutoría',
                'proceso' => 'Tutoría',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Medir la ejecución de las sesiones de tutoría programadas.',
                'formula_texto' => '(Sesiones realizadas / Sesiones programadas) * 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
            ],
            [
                'codigo' => 'M01.05-DCU-FI-001',
                'nombre' => 'Egresados titulados',
                'proceso' => 'Seguimiento al Egresado',
                'macro_proceso' => 'Resultados de la Formación',
                'finalidad' => 'Medir la proporción de egresados que obtuvieron el título profesional.',
                'formula_texto' => '(Egresados titulados / Total egresados registrados) * 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'ANUAL',
            ],
            [
                'codigo' => 'M01.05-DCU-FI-002',
                'nombre' => 'Egresados laborando',
                'proceso' => 'Seguimiento al Egresado',
                'macro_proceso' => 'Resultados de la Formación',
                'finalidad' => 'Medir la inserción laboral pertinente de los egresados.',
                'formula_texto' => '(Egresados que laboran / Total egresados registrados) * 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'ANUAL',
            ],
        ];

        foreach ($indicadores as $indicador) {
            IndicadorMaestro::updateOrCreate(
                ['codigo' => $indicador['codigo']],
                $indicador + [
                    'unidad_medida' => 'PORCENTAJE',
                    'responsable' => 'Director de Escuela',
                ],
            );
        }
    }
}
