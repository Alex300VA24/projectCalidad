<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExportarIndicadoresController;
use App\Http\Controllers\FormatoOficialController;
use App\Http\Controllers\MapaProcesosController;
use App\Http\Controllers\Procedures\AcademicCalendarController;
use App\Http\Controllers\Procedures\AdmissionAnalysisController;
use App\Http\Controllers\Procedures\AnonymousExamController;
use App\Http\Controllers\Procedures\CourseController;
use App\Http\Controllers\Procedures\CourseExecutionReportController;
use App\Http\Controllers\Procedures\CurriculumController;
use App\Http\Controllers\Procedures\CurriculumRedesignController;
use App\Http\Controllers\Procedures\CurriculumReviewController;
use App\Http\Controllers\Procedures\EducationalObjectiveEvaluationController;
use App\Http\Controllers\Procedures\GradeCorrectionController;
use App\Http\Controllers\Procedures\GraduateCompetencyEvaluationController;
use App\Http\Controllers\Procedures\GraduateFolderController;
use App\Http\Controllers\Procedures\GraduateRegistryController;
use App\Http\Controllers\Procedures\InternshipController;
use App\Http\Controllers\Procedures\MatriculaController;
use App\Http\Controllers\Procedures\PhysicalAcademicHistoryController;
use App\Http\Controllers\Procedures\ResearchCompetencyMatrixController;
use App\Http\Controllers\Procedures\ResearchLineController;
use App\Http\Controllers\Procedures\ResearchProjectController;
use App\Http\Controllers\Procedures\StudentMobilityController;
use App\Http\Controllers\Procedures\StudentReferralController;
use App\Http\Controllers\Procedures\SufficiencyExamController;
use App\Http\Controllers\Procedures\SyllabusController;
use App\Http\Controllers\Procedures\SyllabusSocializationController;
use App\Http\Controllers\Procedures\TeacherPerformanceEvaluationController;
use App\Http\Controllers\Procedures\TeachingLoadRequirementController;
use App\Http\Controllers\Procedures\TramitesHubController;
use App\Http\Controllers\Procedures\TutoringSessionController;
use App\Models\IndicadorMaestro;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/mapa-procesos', MapaProcesosController::class)->name('mapa-procesos.index');
Route::get('/formatos/{slug}', FormatoOficialController::class)->name('formatos.show');

Route::view('/indicadores/calidad', 'indicators.dashboard-calidad')->name('quality-indicators.dashboard');
Route::get('/indicadores/calidad/exportar', ExportarIndicadoresController::class)->name('quality-indicators.export');
Route::get('/indicadores/calidad/{indicador:codigo}/historial', function (IndicadorMaestro $indicador) {
    return view('indicators.historial', ['indicador' => $indicador]);
})->name('quality-indicators.historial');

Route::get('/documentos', [DocumentController::class, 'index'])->name('documents.index');
Route::post('/documentos', [DocumentController::class, 'store'])->name('documents.store');
Route::delete('/documentos/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

Route::get('/tramites', TramitesHubController::class)->name('tramites.hub');

Route::get('/tramites/curriculos', [CurriculumController::class, 'index'])->name('curricula.index');
Route::post('/tramites/curriculos', [CurriculumController::class, 'store'])->name('curricula.store');
Route::put('/tramites/curriculos/{curriculum}', [CurriculumController::class, 'update'])->name('curricula.update');
Route::delete('/tramites/curriculos/{curriculum}', [CurriculumController::class, 'destroy'])->name('curricula.destroy');

Route::get('/tramites/cursos', [CourseController::class, 'index'])->name('courses.index');
Route::post('/tramites/cursos', [CourseController::class, 'store'])->name('courses.store');
Route::put('/tramites/cursos/{course}', [CourseController::class, 'update'])->name('courses.update');
Route::delete('/tramites/cursos/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');

Route::get('/tramites/lineas-investigacion', [ResearchLineController::class, 'index'])->name('research-lines.index');
Route::post('/tramites/lineas-investigacion', [ResearchLineController::class, 'store'])->name('research-lines.store');
Route::put('/tramites/lineas-investigacion/{researchLine}', [ResearchLineController::class, 'update'])->name('research-lines.update');
Route::delete('/tramites/lineas-investigacion/{researchLine}', [ResearchLineController::class, 'destroy'])->name('research-lines.destroy');

Route::get('/tramites/revisiones-curriculares', [CurriculumReviewController::class, 'index'])->name('curriculum-reviews.index');
Route::post('/tramites/revisiones-curriculares', [CurriculumReviewController::class, 'store'])->name('curriculum-reviews.store');
Route::put('/tramites/revisiones-curriculares/{curriculumReview}', [CurriculumReviewController::class, 'update'])->name('curriculum-reviews.update');
Route::delete('/tramites/revisiones-curriculares/{curriculumReview}', [CurriculumReviewController::class, 'destroy'])->name('curriculum-reviews.destroy');

Route::get('/tramites/redisenos-curriculares', [CurriculumRedesignController::class, 'index'])->name('curriculum-redesigns.index');
Route::post('/tramites/redisenos-curriculares', [CurriculumRedesignController::class, 'store'])->name('curriculum-redesigns.store');
Route::put('/tramites/redisenos-curriculares/{curriculumRedesign}', [CurriculumRedesignController::class, 'update'])->name('curriculum-redesigns.update');
Route::delete('/tramites/redisenos-curriculares/{curriculumRedesign}', [CurriculumRedesignController::class, 'destroy'])->name('curriculum-redesigns.destroy');

Route::get('/tramites/silabos', [SyllabusController::class, 'index'])->name('syllabi.index');
Route::post('/tramites/silabos', [SyllabusController::class, 'store'])->name('syllabi.store');
Route::put('/tramites/silabos/{syllabus}', [SyllabusController::class, 'update'])->name('syllabi.update');
Route::delete('/tramites/silabos/{syllabus}', [SyllabusController::class, 'destroy'])->name('syllabi.destroy');

Route::get('/tramites/analisis-admision', [AdmissionAnalysisController::class, 'index'])->name('admission-analyses.index');
Route::post('/tramites/analisis-admision', [AdmissionAnalysisController::class, 'store'])->name('admission-analyses.store');
Route::put('/tramites/analisis-admision/{admissionAnalysis}', [AdmissionAnalysisController::class, 'update'])->name('admission-analyses.update');
Route::delete('/tramites/analisis-admision/{admissionAnalysis}', [AdmissionAnalysisController::class, 'destroy'])->name('admission-analyses.destroy');

Route::get('/tramites/tutorias', [TutoringSessionController::class, 'index'])->name('tutoring-sessions.index');
Route::post('/tramites/tutorias', [TutoringSessionController::class, 'store'])->name('tutoring-sessions.store');
Route::put('/tramites/tutorias/{tutoringSession}', [TutoringSessionController::class, 'update'])->name('tutoring-sessions.update');
Route::delete('/tramites/tutorias/{tutoringSession}', [TutoringSessionController::class, 'destroy'])->name('tutoring-sessions.destroy');

Route::get('/tramites/derivaciones', [StudentReferralController::class, 'index'])->name('student-referrals.index');
Route::post('/tramites/derivaciones', [StudentReferralController::class, 'store'])->name('student-referrals.store');
Route::put('/tramites/derivaciones/{studentReferral}', [StudentReferralController::class, 'update'])->name('student-referrals.update');
Route::delete('/tramites/derivaciones/{studentReferral}', [StudentReferralController::class, 'destroy'])->name('student-referrals.destroy');

Route::get('/tramites/socializacion-silabos', [SyllabusSocializationController::class, 'index'])->name('syllabus-socializations.index');
Route::post('/tramites/socializacion-silabos', [SyllabusSocializationController::class, 'store'])->name('syllabus-socializations.store');
Route::put('/tramites/socializacion-silabos/{syllabusSocialization}', [SyllabusSocializationController::class, 'update'])->name('syllabus-socializations.update');
Route::delete('/tramites/socializacion-silabos/{syllabusSocialization}', [SyllabusSocializationController::class, 'destroy'])->name('syllabus-socializations.destroy');

Route::get('/tramites/examenes-anonimos', [AnonymousExamController::class, 'index'])->name('anonymous-exams.index');
Route::post('/tramites/examenes-anonimos', [AnonymousExamController::class, 'store'])->name('anonymous-exams.store');
Route::put('/tramites/examenes-anonimos/{anonymousExam}', [AnonymousExamController::class, 'update'])->name('anonymous-exams.update');
Route::delete('/tramites/examenes-anonimos/{anonymousExam}', [AnonymousExamController::class, 'destroy'])->name('anonymous-exams.destroy');

Route::get('/tramites/examenes-suficiencia', [SufficiencyExamController::class, 'index'])->name('sufficiency-exams.index');
Route::post('/tramites/examenes-suficiencia', [SufficiencyExamController::class, 'store'])->name('sufficiency-exams.store');
Route::put('/tramites/examenes-suficiencia/{sufficiencyExam}', [SufficiencyExamController::class, 'update'])->name('sufficiency-exams.update');
Route::delete('/tramites/examenes-suficiencia/{sufficiencyExam}', [SufficiencyExamController::class, 'destroy'])->name('sufficiency-exams.destroy');

Route::get('/tramites/correccion-notas', [GradeCorrectionController::class, 'index'])->name('grade-corrections.index');
Route::post('/tramites/correccion-notas', [GradeCorrectionController::class, 'store'])->name('grade-corrections.store');
Route::put('/tramites/correccion-notas/{gradeCorrection}', [GradeCorrectionController::class, 'update'])->name('grade-corrections.update');
Route::delete('/tramites/correccion-notas/{gradeCorrection}', [GradeCorrectionController::class, 'destroy'])->name('grade-corrections.destroy');

Route::get('/tramites/evaluacion-competencias-egreso', [GraduateCompetencyEvaluationController::class, 'index'])->name('graduate-competency-evaluations.index');
Route::post('/tramites/evaluacion-competencias-egreso', [GraduateCompetencyEvaluationController::class, 'store'])->name('graduate-competency-evaluations.store');
Route::put('/tramites/evaluacion-competencias-egreso/{graduateCompetencyEvaluation}', [GraduateCompetencyEvaluationController::class, 'update'])->name('graduate-competency-evaluations.update');
Route::delete('/tramites/evaluacion-competencias-egreso/{graduateCompetencyEvaluation}', [GraduateCompetencyEvaluationController::class, 'destroy'])->name('graduate-competency-evaluations.destroy');

Route::get('/tramites/matrices-competencias-investigacion', [ResearchCompetencyMatrixController::class, 'index'])->name('research-competency-matrices.index');
Route::post('/tramites/matrices-competencias-investigacion', [ResearchCompetencyMatrixController::class, 'store'])->name('research-competency-matrices.store');
Route::put('/tramites/matrices-competencias-investigacion/{researchCompetencyMatrix}', [ResearchCompetencyMatrixController::class, 'update'])->name('research-competency-matrices.update');
Route::delete('/tramites/matrices-competencias-investigacion/{researchCompetencyMatrix}', [ResearchCompetencyMatrixController::class, 'destroy'])->name('research-competency-matrices.destroy');

Route::get('/tramites/proyectos-investigacion', [ResearchProjectController::class, 'index'])->name('research-projects.index');
Route::post('/tramites/proyectos-investigacion', [ResearchProjectController::class, 'store'])->name('research-projects.store');
Route::put('/tramites/proyectos-investigacion/{researchProject}', [ResearchProjectController::class, 'update'])->name('research-projects.update');
Route::delete('/tramites/proyectos-investigacion/{researchProject}', [ResearchProjectController::class, 'destroy'])->name('research-projects.destroy');

Route::get('/tramites/requerimiento-carga-lectiva', [TeachingLoadRequirementController::class, 'index'])->name('teaching-load-requirements.index');
Route::post('/tramites/requerimiento-carga-lectiva', [TeachingLoadRequirementController::class, 'store'])->name('teaching-load-requirements.store');
Route::put('/tramites/requerimiento-carga-lectiva/{teachingLoadRequirement}', [TeachingLoadRequirementController::class, 'update'])->name('teaching-load-requirements.update');
Route::delete('/tramites/requerimiento-carga-lectiva/{teachingLoadRequirement}', [TeachingLoadRequirementController::class, 'destroy'])->name('teaching-load-requirements.destroy');

Route::get('/tramites/calendario-academico', [AcademicCalendarController::class, 'index'])->name('academic-calendars.index');
Route::post('/tramites/calendario-academico', [AcademicCalendarController::class, 'store'])->name('academic-calendars.store');
Route::put('/tramites/calendario-academico/{academicCalendar}', [AcademicCalendarController::class, 'update'])->name('academic-calendars.update');
Route::delete('/tramites/calendario-academico/{academicCalendar}', [AcademicCalendarController::class, 'destroy'])->name('academic-calendars.destroy');

Route::get('/tramites/informes-ejecucion-curso', [CourseExecutionReportController::class, 'index'])->name('course-execution-reports.index');
Route::post('/tramites/informes-ejecucion-curso', [CourseExecutionReportController::class, 'store'])->name('course-execution-reports.store');
Route::put('/tramites/informes-ejecucion-curso/{courseExecutionReport}', [CourseExecutionReportController::class, 'update'])->name('course-execution-reports.update');
Route::delete('/tramites/informes-ejecucion-curso/{courseExecutionReport}', [CourseExecutionReportController::class, 'destroy'])->name('course-execution-reports.destroy');

Route::get('/tramites/evaluacion-docente', [TeacherPerformanceEvaluationController::class, 'index'])->name('teacher-performance-evaluations.index');
Route::post('/tramites/evaluacion-docente', [TeacherPerformanceEvaluationController::class, 'store'])->name('teacher-performance-evaluations.store');
Route::put('/tramites/evaluacion-docente/{teacherPerformanceEvaluation}', [TeacherPerformanceEvaluationController::class, 'update'])->name('teacher-performance-evaluations.update');
Route::delete('/tramites/evaluacion-docente/{teacherPerformanceEvaluation}', [TeacherPerformanceEvaluationController::class, 'destroy'])->name('teacher-performance-evaluations.destroy');

Route::get('/tramites/practicas-preprofesionales', [InternshipController::class, 'index'])->name('internships.index');
Route::post('/tramites/practicas-preprofesionales', [InternshipController::class, 'store'])->name('internships.store');
Route::put('/tramites/practicas-preprofesionales/{internship}', [InternshipController::class, 'update'])->name('internships.update');
Route::delete('/tramites/practicas-preprofesionales/{internship}', [InternshipController::class, 'destroy'])->name('internships.destroy');

Route::get('/tramites/historiales-academicos-fisicos', [PhysicalAcademicHistoryController::class, 'index'])->name('physical-academic-histories.index');
Route::post('/tramites/historiales-academicos-fisicos', [PhysicalAcademicHistoryController::class, 'store'])->name('physical-academic-histories.store');
Route::put('/tramites/historiales-academicos-fisicos/{physicalAcademicHistory}', [PhysicalAcademicHistoryController::class, 'update'])->name('physical-academic-histories.update');
Route::delete('/tramites/historiales-academicos-fisicos/{physicalAcademicHistory}', [PhysicalAcademicHistoryController::class, 'destroy'])->name('physical-academic-histories.destroy');

Route::get('/tramites/carpetas-graduacion', [GraduateFolderController::class, 'index'])->name('graduate-folders.index');
Route::post('/tramites/carpetas-graduacion', [GraduateFolderController::class, 'store'])->name('graduate-folders.store');
Route::put('/tramites/carpetas-graduacion/{graduateFolder}', [GraduateFolderController::class, 'update'])->name('graduate-folders.update');
Route::delete('/tramites/carpetas-graduacion/{graduateFolder}', [GraduateFolderController::class, 'destroy'])->name('graduate-folders.destroy');

Route::get('/tramites/registro-egresados', [GraduateRegistryController::class, 'index'])->name('graduate-registries.index');
Route::post('/tramites/registro-egresados', [GraduateRegistryController::class, 'store'])->name('graduate-registries.store');
Route::put('/tramites/registro-egresados/{graduateRegistry}', [GraduateRegistryController::class, 'update'])->name('graduate-registries.update');
Route::delete('/tramites/registro-egresados/{graduateRegistry}', [GraduateRegistryController::class, 'destroy'])->name('graduate-registries.destroy');

Route::get('/tramites/evaluacion-objetivos-educacionales', [EducationalObjectiveEvaluationController::class, 'index'])->name('educational-objective-evaluations.index');
Route::post('/tramites/evaluacion-objetivos-educacionales', [EducationalObjectiveEvaluationController::class, 'store'])->name('educational-objective-evaluations.store');
Route::put('/tramites/evaluacion-objetivos-educacionales/{educationalObjectiveEvaluation}', [EducationalObjectiveEvaluationController::class, 'update'])->name('educational-objective-evaluations.update');
Route::delete('/tramites/evaluacion-objetivos-educacionales/{educationalObjectiveEvaluation}', [EducationalObjectiveEvaluationController::class, 'destroy'])->name('educational-objective-evaluations.destroy');

Route::get('/tramites/movilidad-estudiantil', [StudentMobilityController::class, 'index'])->name('student-mobilities.index');
Route::post('/tramites/movilidad-estudiantil', [StudentMobilityController::class, 'store'])->name('student-mobilities.store');
Route::put('/tramites/movilidad-estudiantil/{studentMobility}', [StudentMobilityController::class, 'update'])->name('student-mobilities.update');
Route::delete('/tramites/movilidad-estudiantil/{studentMobility}', [StudentMobilityController::class, 'destroy'])->name('student-mobilities.destroy');

Route::get('/tramites/matriculas', [MatriculaController::class, 'index'])->name('matriculas.index');
Route::post('/tramites/matriculas', [MatriculaController::class, 'storeMatricula'])->name('matriculas.store');
Route::put('/tramites/matriculas/{matricula}', [MatriculaController::class, 'updateMatricula'])->name('matriculas.update');
Route::delete('/tramites/matriculas/{matricula}', [MatriculaController::class, 'destroyMatricula'])->name('matriculas.destroy');

Route::post('/tramites/incidencias-matricula', [MatriculaController::class, 'storeIncidencia'])->name('incidencias-matricula.store');
Route::put('/tramites/incidencias-matricula/{incidencia}', [MatriculaController::class, 'updateIncidencia'])->name('incidencias-matricula.update');
Route::delete('/tramites/incidencias-matricula/{incidencia}', [MatriculaController::class, 'destroyIncidencia'])->name('incidencias-matricula.destroy');
