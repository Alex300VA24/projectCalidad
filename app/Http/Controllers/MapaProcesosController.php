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
        ]);
    }
}
