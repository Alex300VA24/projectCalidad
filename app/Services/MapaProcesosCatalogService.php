<?php

namespace App\Services;

use Illuminate\Support\Str;

class MapaProcesosCatalogService
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'GC-01' => [
                'code' => 'GC-01',
                'section' => 'Sección 1: Gestión Curricular (GC)',
                'section_short' => 'Gestión Curricular',
                'title' => 'Revisión y Ajuste Curricular',
                'role' => 'COTECCU y Dirección',
                'route' => 'curriculum-reviews.index',
                'formats' => [
                    [
                        'code' => 'F-M01.01-DPA-001',
                        'name' => 'Revisión del Modelo Educativo',
                        'path' => 'M01.01.01 Gestión Curricular/F-M01.01-DPA-001 Revision del Modelo Educativo  Rev2.docx',
                    ],
                    [
                        'code' => 'F-M01.01-DPA-009',
                        'name' => 'Lista de Cotejo para Evaluar Currículo',
                        'path' => 'M01.01.01 Gestión Curricular/F-M01.01-DPA-009 Lista de cotejo para evaluar curriculo Rev1.docx',
                    ],
                ],
                'description' => 'Evaluación del currículo mediante lista de cotejo, revisión del modelo educativo y ejecución de ajustes curriculares.',
            ],
            'GC-02' => [
                'code' => 'GC-02',
                'section' => 'Sección 1: Gestión Curricular (GC)',
                'section_short' => 'Gestión Curricular',
                'title' => 'Rediseño y Estructura Curricular',
                'role' => 'COTECCU y Dirección',
                'route' => 'curriculum-redesigns.index',
                'formats' => [
                    [
                        'code' => 'F-M01.01-DPA-002',
                        'name' => 'Estructura del Currículo',
                        'path' => 'M01.01.01 Gestión Curricular/F-M01.01-DPA-002 Estructura del curriculo Rev2.docx',
                    ],
                    [
                        'code' => 'F-M01.01-DPA-008',
                        'name' => 'Estructura del Modelo Educativo',
                        'path' => 'M01.01.01 Gestión Curricular/F-M01.01-DPA-008 Estructura del Modelo Educativo Rev1.docx',
                    ],
                ],
                'description' => 'Validación de la propuesta o rediseño curricular conforme a la estructura oficial y lineamientos del modelo educativo.',
            ],
            'GC-03' => [
                'code' => 'GC-03',
                'section' => 'Sección 1: Gestión Curricular (GC)',
                'section_short' => 'Gestión Curricular',
                'title' => 'Visado de Sílabos',
                'role' => 'Director de Escuela',
                'route' => 'syllabi.index',
                'indicator_code' => 'I-M01.01-DPA-004',
                'formats' => [
                    [
                        'code' => 'F-M01.01-DPA-003',
                        'name' => 'Sílabo por Objetivos',
                        'path' => 'M01.01.01 Gestión Curricular/F-M01.01-DPA-003 Silabo por Objetivos Rev2.docx',
                    ],
                    [
                        'code' => 'F-M01.01-DPA-004',
                        'name' => 'Sílabo por Competencias',
                        'path' => 'M01.01.01 Gestión Curricular/F-M01.01-DPA-004 Silabo por Competencias Rev2 .docx',
                    ],
                    [
                        'code' => 'F-M01.01-DPA-006',
                        'name' => 'Guía de Aprendizaje',
                        'path' => 'M01.01.01 Gestión Curricular/F-M01.01-DPA-006 Guía de Aprendizaje Rev1.docx',
                    ],
                    [
                        'code' => 'F-M01.01-DPA-007',
                        'name' => 'Módulos de Aprendizaje No Presencial',
                        'path' => 'M01.01.01 Gestión Curricular/F-M01.01-DPA-007 Módulos de Aprendizaje No Presencial Rev1.docx',
                    ],
                ],
                'description' => 'Revisión obligatoria y visado digital del sílabo antes del inicio del semestre académico.',
            ],
            'SD-01' => [
                'code' => 'SD-01',
                'section' => 'Sección 2: Seguimiento y Evaluación del Estudiante (SD/EV)',
                'section_short' => 'Seguimiento y Evaluación',
                'title' => 'Nivelación de Ingresantes',
                'role' => 'Comisión de Nivelación y Dirección',
                'route' => 'admission-analyses.index',
                'formats' => [
                    [
                        'code' => 'F1',
                        'name' => 'Resultados de la Evaluación del Perfil del Ingresante',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.05 Seguimiento al Desempeño de los Estudiantes/F1 Resultados de la Evaluacion del Perfil del Ingresante.docx',
                    ],
                    [
                        'code' => 'F2',
                        'name' => 'Programa de Nivelación de Competencias de los Ingresantes',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.05 Seguimiento al Desempeño de los Estudiantes/F2 Programa de Nivelacion de Competencias de los Ingresantes.docx',
                    ],
                    [
                        'code' => 'F3',
                        'name' => 'Informe Final del Programa de Nivelación de Competencias de Ingresantes',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.05 Seguimiento al Desempeño de los Estudiantes/F3 Informe Final del Programa de Nivelación de Competencias de Ingresantes.docx',
                    ],
                ],
                'description' => 'Evaluación diagnóstica de ingresantes, diseño del plan de nivelación e informe de resultados de competencias.',
            ],
            'SD-02' => [
                'code' => 'SD-02',
                'section' => 'Sección 2: Seguimiento y Evaluación del Estudiante (SD/EV)',
                'section_short' => 'Seguimiento y Evaluación',
                'title' => 'Tutoría y Consejería',
                'role' => 'Comité de Tutoría y Docente Tutor',
                'route' => 'tutoring-sessions.index',
                'indicator_code' => 'M01.04-DDA-FI-001',
                'formats' => [
                    [
                        'code' => 'F4',
                        'name' => 'Plan de Consejería Académica y Tutoría',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.05 Seguimiento al Desempeño de los Estudiantes/F4 Plan de Consejería Academica y Tutoria.docx',
                    ],
                    [
                        'code' => 'F5',
                        'name' => 'Registro de Consejería Académica y Tutoría',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.05 Seguimiento al Desempeño de los Estudiantes/F5 Registro de Consejería Academica y Tutoria.docx',
                    ],
                    [
                        'code' => 'F6',
                        'name' => 'Hoja de Referencia y Contra Referencia',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.05 Seguimiento al Desempeño de los Estudiantes/F6 Hoja de Referencia y Contra Referencia.docx',
                    ],
                    [
                        'code' => 'F7',
                        'name' => 'Informe de Actividades de Consejería Académica y Tutoría',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.05 Seguimiento al Desempeño de los Estudiantes/F7 Informe de Actividades de Consejería Academica y Tutoria.docx',
                    ],
                ],
                'description' => 'Acompañamiento psicopedagógico y académico al estudiante, derivaciones y control de eficacia.',
            ],
            'SD-03' => [
                'code' => 'SD-03',
                'section' => 'Sección 2: Seguimiento y Evaluación del Estudiante (SD/EV)',
                'section_short' => 'Seguimiento y Evaluación',
                'title' => 'Evaluación y Pruebas Anónimas',
                'role' => 'Docentes, Estudiantes y Dirección',
                'route' => 'anonymous-exams.index',
                'formats' => [
                    [
                        'code' => 'F1',
                        'name' => 'Prueba Anónima',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.02 Evaluación del Estudiante/F1 Prueba Anonima.docx',
                    ],
                ],
                'description' => 'Garantía de transparencia evaluativa mediante pruebas anónimas codificadas por asignatura.',
            ],
            'SD-04' => [
                'code' => 'SD-04',
                'section' => 'Sección 2: Seguimiento y Evaluación del Estudiante (SD/EV)',
                'section_short' => 'Seguimiento y Evaluación',
                'title' => 'Logro de Competencias de Egreso',
                'role' => 'Comisión de Evaluación',
                'route' => 'graduate-competency-evaluations.index',
                'formats' => [
                    [
                        'code' => 'F2',
                        'name' => 'Informe de Logro de Competencias',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.02 Evaluación del Estudiante/F2 Informe de logro de competencias.docx',
                    ],
                ],
                'description' => 'Medición sistemática del nivel alcanzado en las competencias genéricas y específicas del perfil de egreso.',
            ],
            'EPC-01' => [
                'code' => 'EPC-01',
                'section' => 'Sección 3: Ejecución del Plan Curricular (EPC)',
                'section_short' => 'Ejecución Curricular',
                'title' => 'Consolidación de la Ejecución',
                'role' => 'Dirección de Escuela',
                'route' => 'course-execution-reports.index',
                'indicator_code' => 'M01.01.03.01-F-013',
                'formats' => [
                    [
                        'code' => 'F1',
                        'name' => 'Acta de Distribución de Carga Lectiva',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.01 Ejecución del Plan Curricular/F1 Acta de Distribución de Carga Lectiva Ver1.docx',
                    ],
                    [
                        'code' => 'F2',
                        'name' => 'Socialización de Sílabo',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.01 Ejecución del Plan Curricular/F2 Socialización de sílabo.xlsx',
                    ],
                    [
                        'code' => 'F5',
                        'name' => 'Informe de Ejecución de las Asignaturas',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.01 Ejecución del Plan Curricular/F5 Informe de  Ejecución de la Asignaturas Ver2.docx',
                    ],
                    [
                        'code' => 'F13',
                        'name' => 'Consolidado de la Ejecución de la Asignatura',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.01 Ejecución del Plan Curricular/F13 Consolidado de la Ejecución de la Asignatura Ver1.xlsx',
                    ],
                ],
                'description' => 'Monitoreo del avance silábico de asignaturas, distribución de carga académica y consolidado semestral de ejecución.',
            ],
            'EPC-02' => [
                'code' => 'EPC-02',
                'section' => 'Sección 3: Ejecución del Plan Curricular (EPC)',
                'section_short' => 'Ejecución Curricular',
                'title' => 'Investigación Formativa',
                'role' => 'COTECCU y Dirección',
                'route' => 'research-competency-matrices.index',
                'formats' => [
                    [
                        'code' => 'F1',
                        'name' => 'Modelo de Investigación Formativa',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.04 Investigación Formativa/F1 Modelo de Investigación Form.docx',
                    ],
                    [
                        'code' => 'F2',
                        'name' => 'Matriz de Competencia y Experiencias Curriculares de Investigación',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.04 Investigación Formativa/F2 Matriz de Competencia, U. de.docx',
                    ],
                ],
                'description' => 'Articulación de asignaturas de investigación formativa y sus matrices de competencia por ciclo.',
            ],
            'EPC-03' => [
                'code' => 'EPC-03',
                'section' => 'Sección 3: Ejecución del Plan Curricular (EPC)',
                'section_short' => 'Ejecución Curricular',
                'title' => 'Prácticas Preprofesionales',
                'role' => 'Dirección de Escuela',
                'route' => 'internships.index',
                'formats' => [
                    [
                        'code' => 'F10',
                        'name' => 'Perfil de las Sedes de PPP',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.01 Ejecución del Plan Curricular/F10 Perfil de las sedes de PPP Ver2.docx',
                    ],
                    [
                        'code' => 'F11',
                        'name' => 'Conformidad de las PPP',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.01 Ejecución del Plan Curricular/F11 Conformidad de las PPP Ver3.docx',
                    ],
                    [
                        'code' => 'F12',
                        'name' => 'Monitoreo de PPP',
                        'path' => 'M01.01.03 Enseñanza – Aprendizaje/M01.01.03.01 Ejecución del Plan Curricular/F12 Monitoreo de PPP Ver3.docx',
                    ],
                ],
                'description' => 'Gestión de sedes, conformidad institucional y seguimiento del desempeño preprofesional.',
            ],
            'GRAD-01' => [
                'code' => 'GRAD-01',
                'section' => 'Sección 4: Graduación, Titulación y Egresados (GRAD/TIT/SE)',
                'section_short' => 'Graduación y Titulación',
                'title' => 'Certificación y Graduación',
                'role' => 'Secretaría y Dirección de Escuela',
                'route' => 'graduate-folders.index',
                'formats' => [
                    [
                        'code' => 'M01.01.04.01-F-001',
                        'name' => 'Certificado de Estudios Físico 2007',
                        'path' => 'M01.01.04 Resultados de la Formación/M01.01.04.01 Certificación/Formatos/M01.01.04.01-F-001 Certificado de estudios físico 2007.docx',
                    ],
                    [
                        'code' => 'M01.01.04.01-F-002',
                        'name' => 'Certificado de Estudios Físico 2008',
                        'path' => 'M01.01.04 Resultados de la Formación/M01.01.04.01 Certificación/Formatos/M01.01.04.01-F-002 Certificado de estudios físico 2008.pdf',
                    ],
                    [
                        'code' => 'M01.01.04.02-F-005',
                        'name' => 'Constancia de Expedido',
                        'path' => 'M01.01.04 Resultados de la Formación/M01.01.04.02 Graduación/Formatos/M01.01.04.02-F-005 Constancia de Expedido.docx',
                    ],
                    [
                        'code' => 'M01.01.04.02-F-006',
                        'name' => 'Constancia de No Adeudo',
                        'path' => 'M01.01.04 Resultados de la Formación/M01.01.04.02 Graduación/Formatos/M01.01.04.02-F-006 Constancia de No Adeudo.docx',
                    ],
                    [
                        'code' => 'M01.01.04.02-F-007',
                        'name' => 'Constancia de Custodia y Cumplimiento de Requisitos para Optar el Grado de Bachiller',
                        'path' => 'M01.01.04 Resultados de la Formación/M01.01.04.02 Graduación/Formatos/M01.01.04.02-F-007 Constancia de custodia y cumplimiento de requisitos para optar el grado de bachiller.docx',
                    ],
                ],
                'description' => 'Validación documental de egresados, certificados de estudios y constancias para graduación.',
            ],
            'SE-01' => [
                'code' => 'SE-01',
                'section' => 'Sección 4: Graduación, Titulación y Egresados (GRAD/TIT/SE)',
                'section_short' => 'Seguimiento al Egresado',
                'title' => 'Registro de Egresados',
                'role' => 'Responsable de Seguimiento',
                'route' => 'graduate-registries.index',
                'indicator_code' => 'M01.05-DCU-FI-001',
                'formats' => [
                    [
                        'code' => 'F1',
                        'name' => 'Listado de Egresados Aptos para Graduarse',
                        'path' => 'M01.01.04 Resultados de la Formación/M01.01.04.04 Seguimiento al Egresados/F1 Listado de Egresados Aptos para Graduarse.docx',
                    ],
                    [
                        'code' => 'F2',
                        'name' => 'Registro de Egresados',
                        'path' => 'M01.01.04 Resultados de la Formación/M01.01.04.04 Seguimiento al Egresados/F2 Registro de Egresados.docx',
                    ],
                    [
                        'code' => 'F3',
                        'name' => 'Base de Datos de Egresados',
                        'path' => 'M01.01.04 Resultados de la Formación/M01.01.04.04 Seguimiento al Egresados/F3 Base de Datos de Egresados.docx',
                    ],
                    [
                        'code' => 'F4',
                        'name' => 'Informe Estadístico Anual del Estado de Egresados',
                        'path' => 'M01.01.04 Resultados de la Formación/M01.01.04.04 Seguimiento al Egresados/F4 Informe Estadistico Anual Estado de Egresados.docx',
                    ],
                ],
                'description' => 'Actualización continua del padrón de egresados y estadística periódica de graduados.',
            ],
            'SE-02' => [
                'code' => 'SE-02',
                'section' => 'Sección 4: Graduación, Titulación y Egresados (GRAD/TIT/SE)',
                'section_short' => 'Objetivos Educacionales',
                'title' => 'Objetivos Educacionales y Retroalimentación',
                'role' => 'Dirección de Escuela',
                'route' => 'educational-objective-evaluations.index',
                'indicator_code' => 'M01.05-DCU-FI-002',
                'formats' => [
                    [
                        'code' => 'F6',
                        'name' => 'Informe Estadístico Semestral-Anual de Inserción Laboral',
                        'path' => 'M01.01.04 Resultados de la Formación/M01.01.04.04 Seguimiento al Egresados/F6 Informe Estadistico Semestral-Anual de Insercion Laboral.docx',
                    ],
                    [
                        'code' => 'F9',
                        'name' => 'Informe del Nivel de Logro de las Competencias',
                        'path' => 'M01.01.04 Resultados de la Formación/M01.01.04.04 Seguimiento al Egresados/F9 Informe del Nivel de Logro de las Competencias.docx',
                    ],
                    [
                        'code' => 'F10',
                        'name' => 'Informe sobre Cambios (Mejoras) del Perfil de Egreso',
                        'path' => 'M01.01.04 Resultados de la Formación/M01.01.04.04 Seguimiento al Egresados/F10 Informe sobre cambios (mejoras) del perfil de egreso.docx',
                    ],
                    [
                        'code' => 'F11',
                        'name' => 'Informe sobre Cambios (Mejoras) de los Objetivos Educacionales',
                        'path' => 'M01.01.04 Resultados de la Formación/M01.01.04.04 Seguimiento al Egresados/F11 Informe sobre cambios (mejoras) de los objetivos educacionales.docx',
                    ],
                ],
                'description' => 'Medición de empleabilidad, inserción en la especialidad y retroalimentación para la mejora curricular continua.',
            ],
            'MAT-01' => [
                'code' => 'MAT-01',
                'section' => 'Sección 4: Graduación, Titulación y Egresados (GRAD/TIT/SE)',
                'section_short' => 'Matrícula',
                'title' => 'Matrícula e Incidencias',
                'role' => 'Dirección y Secretaría de Escuela',
                'route' => 'matriculas.index',
                'indicator_code' => 'M01.01.02.02-FI-001',
                'formats' => [
                    [
                        'code' => 'M01.01.02.02-PR–001',
                        'name' => 'Procedimiento de Matrícula en Pregrado',
                        'path' => 'M01.01.02 Gestión del Ingreso/M01.01.02.02 Matrícula/M01.01.02.02-PR–001  Procedimiento de Matrícula en Pregrado.pdf',
                    ],
                    [
                        'code' => 'M01.01.02.02-F-003',
                        'name' => 'Ficha de Matrícula',
                        'path' => 'M01.01.02 Gestión del Ingreso/M01.01.02.02 Matrícula/Formatos/M01.01.02.02-F-003 Ficha de Matrícula.docx',
                    ],
                    [
                        'code' => 'M01.01.02.02-FI-001',
                        'name' => 'Tasa de Retención por Programa de Estudios',
                        'path' => 'M01.01.02 Gestión del Ingreso/M01.01.02.02 Matrícula/Fichas de indicadores/M01.01.02.02-FI-001 Tasa de Retención por Programa de Estudios.xlsx',
                    ],
                    [
                        'code' => 'M01.01.02.02-FI-003',
                        'name' => 'Índice de Resolución de Problemas en el Proceso de Matrícula',
                        'path' => 'M01.01.02 Gestión del Ingreso/M01.01.02.02 Matrícula/Fichas de indicadores/M01.01.02.02-FI-003 Índice de resolución de problemas en el proceso de matrícula.xlsx',
                    ],
                ],
                'description' => 'Habilitación de cursos, confirmación de matrículas de estudiantes y resolución de incidencias en el proceso.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getByCode(string $code): ?array
    {
        return self::all()[$code] ?? null;
    }

    /**
     * @return array{code: string, name: string, path: string, proceso_code: string}|null
     */
    public static function findFormatBySlug(string $slug): ?array
    {
        foreach (self::all() as $proceso) {
            foreach ($proceso['formats'] ?? [] as $formato) {
                if (Str::slug($proceso['code'].' '.$formato['code']) === $slug) {
                    return [
                        'code' => $formato['code'],
                        'name' => $formato['name'],
                        'path' => $formato['path'],
                        'proceso_code' => $proceso['code'],
                    ];
                }
            }
        }

        return null;
    }
}
