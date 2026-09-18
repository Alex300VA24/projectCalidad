<?php

namespace App\Http\Controllers\Procedures;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use App\Models\AdmissionAnalysis;
use App\Models\AnonymousExam;
use App\Models\Course;
use App\Models\CourseExecutionReport;
use App\Models\Curriculum;
use App\Models\CurriculumRedesign;
use App\Models\CurriculumReview;
use App\Models\EducationalObjectiveEvaluation;
use App\Models\GradeCorrection;
use App\Models\GraduateCompetencyEvaluation;
use App\Models\GraduateFolder;
use App\Models\GraduateRegistry;
use App\Models\Internship;
use App\Models\Matricula;
use App\Models\PhysicalAcademicHistory;
use App\Models\ResearchCompetencyMatrix;
use App\Models\ResearchLine;
use App\Models\ResearchProject;
use App\Models\StudentMobility;
use App\Models\StudentReferral;
use App\Models\SufficiencyExam;
use App\Models\Syllabus;
use App\Models\SyllabusSocialization;
use App\Models\TeacherPerformanceEvaluation;
use App\Models\TeachingLoadRequirement;
use App\Models\TutoringSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TramitesHubController extends Controller
{
    public function __invoke(Request $request): View
    {
        $groups = [
            [
                'title' => 'Catálogos',
                'description' => 'Datos base compartidos por todos los dominios.',
                'items' => [
                    ['label' => 'Currículos', 'route' => 'curricula.index', 'count' => Curriculum::count()],
                    ['label' => 'Cursos', 'route' => 'courses.index', 'count' => Course::count()],
                    ['label' => 'Líneas de investigación', 'route' => 'research-lines.index', 'count' => ResearchLine::count()],
                ],
            ],
            [
                'title' => 'Gestión Curricular · GC',
                'description' => 'Revisión, rediseño y sílabos.',
                'items' => [
                    ['label' => 'Revisiones curriculares', 'route' => 'curriculum-reviews.index', 'count' => CurriculumReview::count()],
                    ['label' => 'Rediseños curriculares', 'route' => 'curriculum-redesigns.index', 'count' => CurriculumRedesign::count()],
                    ['label' => 'Sílabos', 'route' => 'syllabi.index', 'count' => Syllabus::count()],
                ],
            ],
            [
                'title' => 'Seguimiento del Estudiante · SD',
                'description' => 'Admisión, tutoría y derivaciones.',
                'items' => [
                    ['label' => 'Análisis de admisión', 'route' => 'admission-analyses.index', 'count' => AdmissionAnalysis::count()],
                    ['label' => 'Sesiones de tutoría', 'route' => 'tutoring-sessions.index', 'count' => TutoringSession::count()],
                    ['label' => 'Derivaciones', 'route' => 'student-referrals.index', 'count' => StudentReferral::count()],
                ],
            ],
            [
                'title' => 'Evaluación del Estudiante · EV',
                'description' => 'Socialización, exámenes y correcciones.',
                'items' => [
                    ['label' => 'Socialización de sílabos', 'route' => 'syllabus-socializations.index', 'count' => SyllabusSocialization::count()],
                    ['label' => 'Exámenes anónimos', 'route' => 'anonymous-exams.index', 'count' => AnonymousExam::count()],
                    ['label' => 'Exámenes de suficiencia', 'route' => 'sufficiency-exams.index', 'count' => SufficiencyExam::count()],
                    ['label' => 'Corrección de notas', 'route' => 'grade-corrections.index', 'count' => GradeCorrection::count()],
                    ['label' => 'Evaluación de competencias de egreso', 'route' => 'graduate-competency-evaluations.index', 'count' => GraduateCompetencyEvaluation::count()],
                ],
            ],
            [
                'title' => 'Investigación Formativa · IF',
                'description' => 'Matrices y proyectos de investigación.',
                'items' => [
                    ['label' => 'Matrices de competencias', 'route' => 'research-competency-matrices.index', 'count' => ResearchCompetencyMatrix::count()],
                    ['label' => 'Proyectos de investigación', 'route' => 'research-projects.index', 'count' => ResearchProject::count()],
                ],
            ],
            [
                'title' => 'Ejecución del Plan Curricular · EPC',
                'description' => 'Carga lectiva, calendario y prácticas.',
                'items' => [
                    ['label' => 'Requerimiento de carga lectiva', 'route' => 'teaching-load-requirements.index', 'count' => TeachingLoadRequirement::count()],
                    ['label' => 'Calendario académico', 'route' => 'academic-calendars.index', 'count' => AcademicCalendar::count()],
                    ['label' => 'Informes de ejecución de curso', 'route' => 'course-execution-reports.index', 'count' => CourseExecutionReport::count()],
                    ['label' => 'Evaluación docente', 'route' => 'teacher-performance-evaluations.index', 'count' => TeacherPerformanceEvaluation::count()],
                    ['label' => 'Prácticas preprofesionales', 'route' => 'internships.index', 'count' => Internship::count()],
                ],
            ],
            [
                'title' => 'Certificación Histórica · CERT-01',
                'description' => 'Historiales académicos de ingresantes hasta 2007.',
                'items' => [
                    ['label' => 'Historiales académicos físicos', 'route' => 'physical-academic-histories.index', 'count' => PhysicalAcademicHistory::count()],
                ],
            ],
            [
                'title' => 'Titulación y Graduación · TIT/GRAD',
                'description' => 'Carpetas de graduación y envío a SUNEDU.',
                'items' => [
                    ['label' => 'Carpetas de graduación', 'route' => 'graduate-folders.index', 'count' => GraduateFolder::count()],
                ],
            ],
            [
                'title' => 'Seguimiento al Egresado · SE',
                'description' => 'Registro de egresados y objetivos educacionales.',
                'items' => [
                    ['label' => 'Registro de egresados', 'route' => 'graduate-registries.index', 'count' => GraduateRegistry::count()],
                    ['label' => 'Evaluación de objetivos educacionales', 'route' => 'educational-objective-evaluations.index', 'count' => EducationalObjectiveEvaluation::count()],
                ],
            ],
            [
                'title' => 'Matrícula y Movilidad · MAT/MOV',
                'description' => 'Matrículas regulares, incidencias de matrícula y movilidad.',
                'items' => [
                    ['label' => 'Matrícula e incidencias (MAT-01)', 'route' => 'matriculas.index', 'count' => Matricula::count()],
                    ['label' => 'Movilidad estudiantil', 'route' => 'student-mobilities.index', 'count' => StudentMobility::count()],
                ],
            ],
        ];

        $accents = ['indigo', 'blue', 'green', 'amber', 'red'];

        foreach ($groups as $index => &$group) {
            $group['anchor'] = Str::slug($group['title']);
            $group['accent'] = $accents[$index % count($accents)];
        }
        unset($group);

        $query = trim((string) $request->get('buscar'));

        if ($query !== '') {
            $needle = mb_strtolower($query);

            $groups = collect($groups)
                ->map(function (array $group) use ($needle) {
                    $group['items'] = array_values(array_filter(
                        $group['items'],
                        fn (array $item) => str_contains(mb_strtolower($item['label']), $needle)
                    ));

                    return $group;
                })
                ->filter(fn (array $group) => count($group['items']) > 0)
                ->values()
                ->all();
        }

        $totalItems = array_sum(array_map(fn (array $group) => count($group['items']), $groups));

        return view('tramites.hub', [
            'groups' => $groups,
            'query' => $query,
            'totalItems' => $totalItems,
        ]);
    }
}
