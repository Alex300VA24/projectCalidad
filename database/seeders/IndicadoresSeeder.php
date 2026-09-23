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
                'codigo' => 'M01.04/PG-I2',
                'nombre' => 'Estudiantes que logran las COMPETENCIAS GENERALES esperadas (%)',
                'proceso' => 'Seguimiento al Desempeño de los Estudiantes',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Evaluar la eficacia de la formación en la etapa de Estudios Generales.',
                'formula_texto' => '(N° de estudiantes que logran el nivel esperado de desarrollo de COMPETENCIAS GENERALES / N° total de estudiantes por promoción o cohorte) x 100',
                'meta_institucional' => 20,
                'nivel_critico' => 15,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'ANUAL',
                'responsable' => 'Director(a) de Escuela Profesional',
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'N° de estudiantes que logran el nivel esperado de desarrollo de COMPETENCIAS GENERALES',
                        'denominador_label' => 'N° total de estudiantes por promoción o cohorte',
                    ],
                    'documento_titulo' => 'Estudiantes que logran las COMPETENCIAS GENERALES esperadas',
                    'fuente_verificacion' => 'Resultados de las evaluaciones en las experiencias curriculares (Estudios Generales y de especialidad).',
                ],
            ],
            [
                'codigo' => 'M01.04/PG-I3',
                'nombre' => 'Estudiantes que logran las COMPETENCIAS ESPECÍFICAS esperadas (%)',
                'proceso' => 'Seguimiento al Desempeño de los Estudiantes',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Evaluar la eficacia de la formación.',
                'formula_texto' => '(N° de estudiantes que logran el nivel esperado de desarrollo de COMPETENCIAS ESPECÍFICAS / N° total de estudiantes por promoción o cohorte) x 100',
                'meta_institucional' => 20,
                'nivel_critico' => 15,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'ANUAL',
                'responsable' => 'Director(a) de Escuela Profesional',
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'N° de estudiantes que logran el nivel esperado de desarrollo de COMPETENCIAS ESPECÍFICAS',
                        'denominador_label' => 'N° total de estudiantes por promoción o cohorte',
                    ],
                    'documento_titulo' => 'Estudiantes que logran las COMPETENCIAS ESPECIFICAS  esperadas (%)',
                    'fuente_verificacion' => 'Resultados de las evaluaciones en las experiencias curriculares (Estudios Generales y de especialidad).',
                ],
            ],
            [
                'codigo' => 'M01.04/PG-I5',
                'nombre' => 'Estudiantes desaprobados (%) - Por promoción o cohorte y por experiencia curricular',
                'proceso' => 'Seguimiento al Desempeño de los Estudiantes',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Identificar estudiantes con problemas de rendimiento académico y que no tienen el nivel de avance esperado.',
                'formula_texto' => '(Número de estudiantes desaprobados / Número total de estudiantes matriculados en la experiencia curricular) × 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MENOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
                'responsable' => 'Director(a) de Escuela Profesional',
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'Número de estudiantes desaprobados',
                        'denominador_label' => 'Número total de estudiantes matriculados en la experiencia curricular',
                    ],
                    'documento_titulo' => 'Estudiantes desaprobados (%) - Por promoción o cohorte  y por expiencia curricular',
                    'fuente_verificacion' => 'Reportes de estudiantes matriculados.',
                ],
            ],
            [
                'codigo' => 'M01.04/PG-I7',
                'nombre' => 'Logro de objetivos del programa de tutoría académica y apoyo pedagógico (%)',
                'proceso' => 'Seguimiento al Desempeño de los Estudiantes',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Evaluar el cumplimiento del programa de tutoría académica y apoyo pedagógico.',
                'formula_texto' => '(N° de actividades ejecutadas / N° de actividades planificadas) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
                'responsable' => 'Director(a) de Escuela Profesional',
                'configuracion' => [
                    'fuente_json' => 'logro_objetivos_programa_tutoria.json',
                    'formula_manual' => [
                        'numerador_label' => 'N° de actividades ejecutadas',
                        'denominador_label' => 'N° de actividades planificadas',
                    ],
                    'documento_titulo' => 'Logro de objetivos del programa de tutoría académica y apoyo pedagógico',
                    'fuente_verificacion' => 'Informe de actividades de consejería académica y tutoría.',
                ],
            ],
            [
                'codigo' => 'M01.04/PG-I8',
                'nombre' => 'Satisfacción del estudiante con los programas de consejería académica y tutoría (%)',
                'proceso' => 'Seguimiento al Desempeño de los Estudiantes',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Evaluar el nivel de satisfacción de los estudiantes con el programa de consejería académica y tutoría.',
                'formula_texto' => '((N° de estudiantes satisfechos + N° de estudiantes muy satisfechos) / N° total de estudiantes encuestados que participan en el programa de consejería académica y tutoría) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
                'responsable' => 'Director(a) de Escuela Profesional',
                'configuracion' => [
                    'fuente_json' => 'satisfaccion_estudiante_consejeria_tutoria.json',
                    'formula_manual' => [
                        'numerador_label' => 'N° de estudiantes satisfechos + N° de estudiantes muy satisfechos',
                        'denominador_label' => 'N° total de estudiantes encuestados que participan en el programa de consejería académica y tutoría',
                    ],
                    'documento_titulo' => 'Satisfacción del estudiante con  los programas de consejería académica y tutoría (%)',
                    'fuente_verificacion' => 'Resultados de la encuesta de satisfacción aplicada a estudiantes participantes.',
                ],
            ],
            [
                'codigo' => 'M01.03.04/PG-I1',
                'nombre' => 'EGRESADOS TITULADOS por promoción o cohorte (%)',
                'proceso' => 'TITULACIÓN - M01.03.04 - PREGRADO Y PROGRAMAS DE SEGUNDA ESPECIALIDAD',
                'macro_proceso' => 'Resultados de la Formación',
                'finalidad' => 'Medir la proporción de egresados titulados por promoción o cohorte.',
                'formula_texto' => '(N° total de egresados titulados / N° total de egresados) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'ANUAL',
                'configuracion' => [
                    'fuente_json' => 'egresados_titulados_por_cohorte.json',
                    'formula_manual' => [
                        'numerador_label' => 'N° total de egresados titulados',
                        'denominador_label' => 'N° total de egresados',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.03.04/PG-I2',
                'nombre' => 'Egresados titulados dentro de los DOCE MESES después de ser declarado EGRESADO/GRADUADO (%)',
                'proceso' => 'TITULACIÓN - M01.03.04 - PREGRADO Y PROGRAMAS DE SEGUNDA ESPECIALIDAD',
                'macro_proceso' => 'Resultados de la Formación',
                'finalidad' => 'Medir la titulación oportuna de los egresados y graduados.',
                'formula_texto' => '(N° total de titulados en ≤ 12 meses / N° total de graduados) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'ANUAL',
                'configuracion' => [
                    'fuente_json' => 'egresados_titulados_12_meses.json',
                    'formula_manual' => [
                        'numerador_label' => 'N° total de titulados en doce meses',
                        'denominador_label' => 'N° total de graduados',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.05/PG-I1',
                'nombre' => 'Egresados de la carrera por promoción (%)',
                'proceso' => 'Seguimiento a los Egresados - M01.05 - PREGRADO Y PROGRAMAS DE SEGUNDA ESPECIALIDAD',
                'macro_proceso' => 'Resultados de la Formación',
                'finalidad' => 'Medir la proporción de ingresantes que egresan de la carrera por promoción.',
                'formula_texto' => '(N° de egresados / N° total de ingresantes) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'ANUAL',
                'configuracion' => [
                    'fuente_json' => 'egresados_por_promocion.json',
                    'formula_manual' => [
                        'numerador_label' => 'N° de egresados',
                        'denominador_label' => 'N° total de ingresantes',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.05/PG-I2',
                'nombre' => 'Empleabilidad de los egresados (% de egresados que trabajan en su carrera)',
                'proceso' => 'Seguimiento a los Egresados - M01.05 - PREGRADO Y PROGRAMAS DE SEGUNDA',
                'macro_proceso' => 'Resultados de la Formación',
                'finalidad' => 'Medir la inserción laboral pertinente de los egresados.',
                'formula_texto' => '(N° de egresados trabajando en su área / N° total de encuestados) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'ANUAL',
                'configuracion' => [
                    'fuente_json' => 'empleabilidad_egresados.json',
                    'formula_manual' => [
                        'numerador_label' => 'N° de egresados trabajando en su área',
                        'denominador_label' => 'N° total de encuestados',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.05/PG-I3',
                'nombre' => 'Cumplimiento de los objetivos educacionales (%)',
                'proceso' => 'Seguimiento a los Egresados - M01.05 - PREGRADO Y PROGRAMAS DE SEGUNDA',
                'macro_proceso' => 'Resultados de la Formación',
                'finalidad' => 'Evaluar el cumplimiento de los objetivos educacionales del programa.',
                'formula_texto' => '(N° de objetivos con nivel bueno y muy bueno / N° total de objetivos) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'ANUAL',
                'configuracion' => [
                    'fuente_json' => 'cumplimiento_objetivos_educacionales.json',
                    'formula_manual' => [
                        'numerador_label' => 'N° de objetivos con nivel bueno y muy bueno',
                        'denominador_label' => 'N° total de objetivos',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.05/PG-I4',
                'nombre' => 'Satisfacción de los egresados (%)',
                'proceso' => 'Seguimiento a los Egresados - M01.05 - PREGRADO Y PROGRAMAS DE SEGUNDA',
                'macro_proceso' => 'Resultados de la Formación',
                'finalidad' => 'Medir la satisfacción de los egresados con la formación recibida.',
                'formula_texto' => '((N° satisfechos + muy satisfechos) / N° total de encuestados) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'ANUAL',
                'configuracion' => [
                    'fuente_json' => 'satisfaccion_egresados.json',
                    'formula_manual' => [
                        'numerador_label' => 'N° de egresados satisfechos y muy satisfechos',
                        'denominador_label' => 'N° total de encuestados',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.05/PG-I5',
                'nombre' => 'Satisfacción de los empleadores (%)',
                'proceso' => 'Seguimiento a los Egresados - M01.05 - PREGRADO Y PROGRAMAS DE SEGUNDA',
                'macro_proceso' => 'Resultados de la Formación',
                'finalidad' => 'Medir la satisfacción de los empleadores con el desempeño de los egresados.',
                'formula_texto' => '((N° de empleadores satisfechos + muy satisfechos) / N° total de empleadores encuestados) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MAYOR_IGUAL,
                'frecuencia' => 'ANUAL',
                'configuracion' => [
                    'fuente_json' => 'satisfaccion_empleadores.json',
                    'formula_manual' => [
                        'numerador_label' => 'N° de empleadores satisfechos y muy satisfechos',
                        'denominador_label' => 'N° total de empleadores encuestados',
                    ],
                ],
            ],
            [
                'codigo' => 'M01.03.02.02/PG-I1',
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
                'codigo' => 'M01.03.02.02/PG-I2',
                'nombre' => 'Estudiantes desaprobados dos o más veces en una experiencia curricular',
                'proceso' => 'Evaluación del Estudiante',
                'macro_proceso' => 'Enseñanza y Aprendizaje',
                'finalidad' => 'Identificar a los estudiantes con problemas de rendimiento académico para planificar actividades de apoyo o toma de decisiones en el marco del Estatuto de la UNT.',
                'formula_texto' => '(N° de estudiantes que llevaron un curso dos, tres o cuatro veces / N° total de estudiantes matriculados en el semestre) x 100',
                'meta_institucional' => null,
                'nivel_critico' => null,
                'sentido_meta' => IndicadorMaestro::SENTIDO_MENOR_IGUAL,
                'frecuencia' => 'SEMESTRAL',
                'configuracion' => [
                    'formula_manual' => [
                        'numerador_label' => 'N° de estudiantes que llevaron un curso dos, tres o cuatro veces',
                        'denominador_label' => 'N° total de estudiantes matriculados en el semestre',
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
