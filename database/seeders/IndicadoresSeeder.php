<?php

namespace Database\Seeders;

use App\Models\IndicadorMaestro;
use App\Models\IndicadorMedicion;
use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

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
                'macro_proceso' => 'Gestión del Ingreso',
                'finalidad' => 'Medir el porcentaje de estudiantes que permanecen inscritos de un período académico al siguiente en cada programa de estudios, con el fin de identificar programas con altas tasas de deserción y diseñar estrategias de mejora específicas.',
                'formula_texto' => "(Número de estudiantes que continúan inscritos en el programa en el período actual / Número de estudiantes inscritos en el programa en el período anterior) * 100\nNota: La Tasa de Deserción por programa puede obtenerse restando la Tasa de Retención del 100%.",
                'meta_institucional' => 90,
                'nivel_critico' => 80,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'Número de estudiantes que continúan inscritos en el programa en el período actual',
                        'denominador_label' => 'Número de estudiantes inscritos en el programa en el período anterior',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.01.02.02-FI-002',
                'nombre' => 'Porcentaje de estudiantes matriculados que repiten una misma experiencia curricular por programa de estudios',
                'proceso' => 'Matrícula',
                'macro_proceso' => 'Gestión del Ingreso',
                'finalidad' => 'Identificar experiencias curriculares con altos índices de repetición, para analizar y mejorar la calidad académica, el diseño del curso y el apoyo académico a los estudiantes.',
                'formula_texto' => '(Número de Estudiantes matriculados dos o más veces en una misma experiencia curricular / Total de estudiantes matriculados en la experiencia curricular) * 100',
                'meta_institucional' => 10,
                'nivel_critico' => 15,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MENOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'Número de estudiantes matriculados dos o más veces en una misma experiencia curricular',
                        'denominador_label' => 'Total de estudiantes matriculados en la experiencia curricular',
                    ],
                ],
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
                'codigo' => 'M01.04-PG-I1',
                'nombre' => 'Ingresantes que cumplen con el perfil de ingreso',
                'proceso' => 'Seguimiento al Desempeño de los Estudiantes',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Identificar brechas en el cumplimiento del perfil del ingresante y generar programas de nivelación.',
                'formula_texto' => '(N° de ingresantes que cumplen con las competencias del perfil del ingresante / N° total de ingresantes) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'ANUAL',
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'N° de ingresantes que cumplen con las competencias del perfil del ingresante',
                        'denominador_label' => 'N° total de ingresantes',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.04-PG-I2',
                'nombre' => 'Estudiantes que logran las competencias generales esperadas',
                'proceso' => 'Seguimiento al Desempeño de los Estudiantes',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Evaluar la eficacia de la formación en la etapa de Estudios Generales.',
                'formula_texto' => '(N° de estudiantes que logran el nivel esperado de desarrollo de COMPETENCIAS GENERALES / N° total de estudiantes por promoción o cohorte) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'ANUAL',
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'N° de estudiantes que logran el nivel esperado de desarrollo de COMPETENCIAS GENERALES',
                        'denominador_label' => 'N° total de estudiantes por promoción o cohorte',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.04-PG-I3',
                'nombre' => 'Estudiantes que logran las competencias específicas esperadas',
                'proceso' => 'Seguimiento al Desempeño de los Estudiantes',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Evaluar la eficacia de la formación.',
                'formula_texto' => '(N° de estudiantes que logran el nivel esperado de desarrollo de COMPETENCIAS ESPECÍFICAS / N° total de estudiantes por promoción o cohorte) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'ANUAL',
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'N° de estudiantes que logran el nivel esperado de desarrollo de COMPETENCIAS ESPECÍFICAS',
                        'denominador_label' => 'N° total de estudiantes por promoción o cohorte',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.04-PG-I8',
                'nombre' => 'Satisfacción del estudiante con los programas de consejería académica y tutoría',
                'proceso' => 'Seguimiento al Desempeño de los Estudiantes',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Evaluar el nivel de satisfacción de los estudiantes con el programa de consejería académica y tutoría.',
                'formula_texto' => '(N° de estudiantes satisfechos + muy satisfechos / N° total de estudiantes encuestados que participan en el programa de consejería académica y tutoría) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'N° de estudiantes satisfechos + muy satisfechos',
                        'denominador_label' => 'N° total de estudiantes encuestados que participan en el programa de consejería académica y tutoría',
                    ],
                ],
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
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'Egresados titulados',
                        'denominador_label' => 'Total egresados registrados',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.03.02.02-PG-I1',
                'nombre' => 'Estudiantes desaprobados en cada experiencia curricular',
                'proceso' => 'Evaluación del Estudiante',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Identificar estudiantes con problemas de rendimiento académico para planificar actividades de apoyo.',
                'formula_texto' => '(N° de estudiantes desaprobados / N° total de estudiantes matriculados en cada experiencia curricular) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MENOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'N° de estudiantes desaprobados',
                        'denominador_label' => 'N° total de estudiantes matriculados en cada experiencia curricular',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.03.02.02-PG-I2',
                'nombre' => 'Estudiantes desaprobados dos o más veces en una experiencia curricular',
                'proceso' => 'Evaluación del Estudiante',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Identificar a los estudiantes con problemas de rendimiento académico para planificar actividades de apoyo o toma de decisiones en el marco del Estatuto de la UNT.',
                'formula_texto' => '(N° de alumnos desaprobados DOS, TRES o CUATRO veces / N° total de estudiantes matriculados en la experiencia curricular) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MENOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'N° de alumnos desaprobados DOS, TRES o CUATRO veces',
                        'denominador_label' => 'N° total de estudiantes matriculados en la experiencia curricular',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.03.02.02-PG-I3',
                'nombre' => 'Nivel de logro de las competencias del perfil de egreso',
                'proceso' => 'Evaluación del Estudiante',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Evaluar el nivel de logro de las competencias definidas en el perfil de egreso del programa de estudios.',
                'formula_texto' => 'No requiere',
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
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'Egresados que laboran',
                        'denominador_label' => 'Total egresados registrados',
                    ],
                ],
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

        $this->llenarIndicadorGestionCurricular();
        $this->llenarIndicadorRetencion();
        $this->llenarIndicadorRepitencia();
    }

    private function llenarIndicadorGestionCurricular(): void
    {
        $path = database_path('data/indicador_gestion_curricular.json');

        if (! File::exists($path)) {
            return;
        }

        $periodos = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($periodos)) {
            return;
        }

        $programa = ProgramaEstudio::query()->where('codigo', 'UNT-EP')->firstOrFail();
        $indicador = IndicadorMaestro::query()->where('codigo', 'I-M01.01-DPA-004')->first();

        if ($indicador === null) {
            return;
        }

        foreach ($periodos as $registro) {
            $periodo = trim((string) ($registro['periodo'] ?? ''));
            $numerador = (int) ($registro['numerador'] ?? 0);
            $denominador = (int) ($registro['denominador'] ?? 0);

            if ($periodo === '' || $denominador === 0) {
                continue;
            }

            $valor = round(($numerador / $denominador) * 100, 2);

            IndicadorMedicion::query()->updateOrCreate(
                [
                    'indicador_id' => $indicador->id,
                    'programa_estudio_id' => $programa->id,
                    'periodo_academico' => $periodo,
                ],
                [
                    'valor_medido' => $valor,
                    'meta_programada' => $indicador->meta_institucional,
                    'estado_cumplimiento' => $indicador->clasificar($valor),
                    'datos_fuente' => [
                        'origen' => 'json',
                        'numerador' => $numerador,
                        'denominador' => $denominador,
                    ],
                ],
            );
        }
    }

    private function llenarIndicadorRetencion(): void
    {
        $path = database_path('data/indicador_tasa_retencion.json');

        if (! File::exists($path)) {
            return;
        }

        $registros = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($registros)) {
            return;
        }

        $indicador = IndicadorMaestro::query()->where('codigo', 'M01.01.02.02-FI-001')->first();

        if ($indicador === null) {
            return;
        }

        foreach ($registros as $registro) {
            $programaCodigo = trim((string) ($registro['programa'] ?? ''));
            $periodo = trim((string) ($registro['periodo'] ?? ''));
            $numerador = (int) ($registro['numerador'] ?? 0);
            $denominador = (int) ($registro['denominador'] ?? 0);

            if ($programaCodigo === '' || $periodo === '' || $denominador === 0) {
                continue;
            }

            $programa = ProgramaEstudio::query()->where('codigo', $programaCodigo)->first();

            if ($programa === null) {
                continue;
            }

            $valor = round(($numerador / $denominador) * 100, 2);

            IndicadorMedicion::query()->updateOrCreate(
                [
                    'indicador_id' => $indicador->id,
                    'programa_estudio_id' => $programa->id,
                    'periodo_academico' => $periodo,
                ],
                [
                    'valor_medido' => $valor,
                    'meta_programada' => $indicador->meta_institucional,
                    'estado_cumplimiento' => $indicador->clasificar($valor),
                    'datos_fuente' => [
                        'origen' => 'json',
                        'numerador' => $numerador,
                        'denominador' => $denominador,
                    ],
                ],
            );
        }
    }

    private function llenarIndicadorRepitencia(): void
    {
        $path = database_path('data/indicador_repitencia.json');

        if (! File::exists($path)) {
            return;
        }

        $registros = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($registros)) {
            return;
        }

        $indicador = IndicadorMaestro::query()->where('codigo', 'M01.01.02.02-FI-002')->first();

        if ($indicador === null) {
            return;
        }

        foreach ($registros as $registro) {
            $programaCodigo = trim((string) ($registro['programa'] ?? ''));
            $periodo = trim((string) ($registro['periodo'] ?? ''));
            $numerador = (int) ($registro['numerador'] ?? 0);
            $denominador = (int) ($registro['denominador'] ?? 0);

            if ($programaCodigo === '' || $periodo === '' || $denominador === 0) {
                continue;
            }

            $programa = ProgramaEstudio::query()->where('codigo', $programaCodigo)->first();

            if ($programa === null) {
                continue;
            }

            $valor = round(($numerador / $denominador) * 100, 2);

            IndicadorMedicion::query()->updateOrCreate(
                [
                    'indicador_id' => $indicador->id,
                    'programa_estudio_id' => $programa->id,
                    'periodo_academico' => $periodo,
                ],
                [
                    'valor_medido' => $valor,
                    'meta_programada' => $indicador->meta_institucional,
                    'estado_cumplimiento' => $indicador->clasificar($valor),
                    'datos_fuente' => [
                        'origen' => 'json',
                        'numerador' => $numerador,
                        'denominador' => $denominador,
                    ],
                ],
            );
        }
    }
}
