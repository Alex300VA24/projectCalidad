<?php

namespace App\Http\Controllers;

use App\Services\MapaProcesosCatalogService;
use Illuminate\View\View;

class MapaProcesosController extends Controller
{
    /**
     * Additional cross-links shown on a process card besides its primary route,
     * for processes that feed more than one form in the system.
     *
     * @var array<string, array<int, array{label: string, route: string}>>
     */
    private const EXTRA_LINKS = [
        'SD-02' => [
            ['label' => 'Sesiones', 'route' => 'tutoring-sessions.index'],
            ['label' => 'Derivaciones', 'route' => 'student-referrals.index'],
        ],
        'SD-03' => [
            ['label' => 'Pruebas', 'route' => 'anonymous-exams.index'],
            ['label' => 'Actas', 'route' => 'syllabus-socializations.index'],
        ],
        'EPC-01' => [
            ['label' => 'Carga docente', 'route' => 'teaching-load-requirements.index'],
            ['label' => 'Calendario', 'route' => 'academic-calendars.index'],
        ],
        'EPC-02' => [
            ['label' => 'Matrices', 'route' => 'research-competency-matrices.index'],
            ['label' => 'Proyectos', 'route' => 'research-projects.index'],
        ],
        'GRAD-01' => [
            ['label' => 'Carpetas', 'route' => 'graduate-folders.index'],
            ['label' => 'Historiales', 'route' => 'physical-academic-histories.index'],
        ],
    ];

    /**
     * Presentation metadata for each of the 4 sequential sections of the process map,
     * keyed by the exact `section` value stored in the catalog.
     *
     * @var array<string, array{label: string, subtitle: string, accent: string}>
     */
    private const SECTIONS = [
        'Sección 1: Gestión Curricular (GC)' => [
            'label' => 'Gestión Curricular (GC)',
            'subtitle' => 'Planificación, revisión y actualización del currículo',
            'accent' => 'blue',
        ],
        'Sección 2: Seguimiento y Evaluación del Estudiante (SD/EV)' => [
            'label' => 'Seguimiento y Evaluación (SD/EV)',
            'subtitle' => 'Acompañamiento, tutoría y evaluación del logro académico',
            'accent' => 'green',
        ],
        'Sección 3: Ejecución del Plan Curricular (EPC)' => [
            'label' => 'Ejecución del Plan Curricular (EPC)',
            'subtitle' => 'Desarrollo académico, prácticas e investigación formativa',
            'accent' => 'indigo',
        ],
        'Sección 4: Graduación, Titulación y Egresados (GRAD/TIT/SE)' => [
            'label' => 'Graduación, Egresados y Matrícula',
            'subtitle' => 'Cierre académico, validación documental, seguimiento y matrícula',
            'accent' => 'amber',
        ],
    ];

    public function __invoke(): View
    {
        $sections = self::SECTIONS;

        foreach (MapaProcesosCatalogService::all() as $proceso) {
            $proceso['extra_links'] = self::EXTRA_LINKS[$proceso['code']] ?? [];
            $sections[$proceso['section']]['procesos'][] = $proceso;
        }

        return view('mapa-procesos.index', [
            'sections' => $sections,
            'entities' => MapaProcesosCatalogService::allEntities(),
            'executionReportsByPeriod' => $this->executionReportsByPeriod(),
            'consolidatedReportsByPeriod' => $this->consolidatedReportsByPeriod(),
            'studentReferences' => $this->studentReferences(),
        ]);
    }

    /**
     * @return array<string, array<int, array{curso: string, grupo: string, profesor: string, title: string, detail: string, link: string, preview_url: string}>>
     */
    private function executionReportsByPeriod(): array
    {
        $reportsByPeriod = ['2025-II' => [], '2026-I' => []];
        $path = database_path('data/ejecucion-asignaturas.json');

        if (! is_file($path)) {
            return $reportsByPeriod;
        }

        $periods = json_decode(file_get_contents($path) ?: '[]', true);

        if (! is_array($periods)) {
            return $reportsByPeriod;
        }

        foreach ($periods as $period) {
            if (! is_array($period) || ! isset($reportsByPeriod[$period['periodo'] ?? '']) || ! is_array($period['informes'] ?? null)) {
                continue;
            }

            foreach ($period['informes'] as $report) {
                if (! is_array($report) || ! is_string($report['link'] ?? null) || ! filter_var($report['link'], FILTER_VALIDATE_URL) || parse_url($report['link'], PHP_URL_SCHEME) !== 'https') {
                    continue;
                }

                $course = (string) ($report['curso'] ?? '');
                $group = (string) ($report['grupo'] ?? '');
                $professor = (string) ($report['profesor'] ?? '');

                $reportsByPeriod[$period['periodo']][] = [
                    'curso' => $course,
                    'grupo' => $group,
                    'profesor' => $professor,
                    'title' => $course,
                    'detail' => "Grupo {$group} · {$professor}",
                    'link' => $report['link'],
                    'preview_url' => $this->drivePreviewUrl($report['link']),
                ];
            }
        }

        return $reportsByPeriod;
    }

    /**
     * @return array<string, array<int, array{title: string, detail: string, link: string, preview_url: string}>>
     */
    private function consolidatedReportsByPeriod(): array
    {
        $reportsByPeriod = ['2025-II' => [], '2026-I' => []];
        $path = database_path('data/consolidado-ejecucion.json');

        if (! is_file($path)) {
            return $reportsByPeriod;
        }

        $reports = json_decode(file_get_contents($path) ?: '[]', true);

        if (! is_array($reports)) {
            return $reportsByPeriod;
        }

        foreach ($reports as $report) {
            if (! is_array($report) || ! isset($reportsByPeriod[$report['periodo'] ?? '']) || ! is_string($report['link'] ?? null) || ! filter_var($report['link'], FILTER_VALIDATE_URL) || parse_url($report['link'], PHP_URL_SCHEME) !== 'https') {
                continue;
            }

            $reportsByPeriod[$report['periodo']][] = [
                'title' => 'Consolidado de la Ejecución de la Asignatura',
                'detail' => 'Semestre '.$report['periodo'],
                'link' => $report['link'],
                'preview_url' => $this->drivePreviewUrl($report['link']),
            ];
        }

        return $reportsByPeriod;
    }

    /**
     * @return array<string, array<int, array{title: string, link: string, preview_url: string}>>
     */
    private function studentReferences(): array
    {
        $path = database_path('data/referencia_contrareferencia.json');

        if (! is_file($path)) {
            return [];
        }

        $documents = json_decode(file_get_contents($path) ?: '[]', true);

        if (! is_array($documents)) {
            return [];
        }

        $referencesByStudent = [];

        foreach ($documents as $document) {
            if (! is_array($document) || ! is_string($document['alumno'] ?? null) || ! is_string($document['concepto'] ?? null) || ! is_string($document['link'] ?? null) || ! filter_var($document['link'], FILTER_VALIDATE_URL) || parse_url($document['link'], PHP_URL_SCHEME) !== 'https') {
                continue;
            }

            $student = trim($document['alumno']);
            $title = trim($document['concepto']);

            if ($student === '' || $title === '') {
                continue;
            }

            $referencesByStudent[$student][] = [
                'title' => $title,
                'link' => $document['link'],
                'preview_url' => $this->drivePreviewUrl($document['link']),
            ];
        }

        return $referencesByStudent;
    }

    private function drivePreviewUrl(string $link): string
    {
        preg_match('~/d/([a-zA-Z0-9_-]+)~', $link, $driveFileId);

        return isset($driveFileId[1])
            ? "https://drive.google.com/file/d/{$driveFileId[1]}/preview"
            : $link;
    }
}
