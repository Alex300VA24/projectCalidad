<?php

namespace App\Services\Procedures;

use App\Models\AnonymousExam;
use App\Models\Course;
use App\Models\SufficiencyExam;
use App\Services\Procedures\Concerns\LogsProcedureActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentEvaluationService
{
    use LogsProcedureActivity;

    /**
     * Actividades 2.1 a 2.5: genera el código de sobre lacrado y estructura
     * los registros desglosables (formato F-M01.03.02.02-DRT/PG-001).
     */
    public function prepareAnonymousExam(
        Course $course,
        int $teacherId,
        \DateTimeInterface|string $examDate,
        int $studentCount,
        ?int $procedureId = null,
    ): AnonymousExam {
        $exam = AnonymousExam::create([
            'procedure_id' => $procedureId,
            'course_id' => $course->id,
            'teacher_id' => $teacherId,
            'exam_date' => $examDate,
            'desglosables_count' => $studentCount,
            'status' => AnonymousExam::STATUS_PREPARADO,
        ]);

        $exam->update([
            'sealed_envelope_code' => sprintf('SOBRE-%d-%s', $exam->id, Str::upper(Str::random(8))),
        ]);

        $this->logActivity($procedureId, '2.1', null, AnonymousExam::STATUS_PREPARADO);

        $path = "anonymous-exams/{$exam->id}/desglosable.pdf";
        Storage::put($path, Pdf::loadView('pdf.prueba-anonima-desglosable', ['exam' => $exam])->output());

        $this->logActivity($procedureId, '2.5', AnonymousExam::STATUS_PREPARADO, AnonymousExam::STATUS_PREPARADO, 'Desglosables generados');

        return $exam->fresh();
    }

    /**
     * Actividades 2.8 a 2.11: consolida notas ciegas, empareja con los
     * desglosables del sobre y deja el resultado listo para el SGA.
     *
     * @param  array<int, array{desglosable_code: string, grade: float}>  $blindGrades
     */
    public function gradeAnonymousExam(AnonymousExam $exam, array $blindGrades): AnonymousExam
    {
        $fromStatus = $exam->status;

        $exam->update([
            'grades_data' => $blindGrades,
            'status' => AnonymousExam::STATUS_CALIFICADO,
        ]);

        $this->logActivity($exam->procedure_id, '2.8', $fromStatus, AnonymousExam::STATUS_DESGLOSADO_LACRADO);
        $this->logActivity($exam->procedure_id, '2.9', AnonymousExam::STATUS_DESGLOSADO_LACRADO, AnonymousExam::STATUS_CALIFICADO);

        $exam->update(['status' => AnonymousExam::STATUS_CONSOLIDADO]);
        $this->logActivity($exam->procedure_id, '2.11', AnonymousExam::STATUS_CALIFICADO, AnonymousExam::STATUS_CONSOLIDADO, 'Notas consolidadas para SGA');

        return $exam->fresh();
    }

    /**
     * Actividades 3.1 a 3.7: propuesta de jurado, resolución de Dirección,
     * registro de acta y notificación a Registro Técnico.
     *
     * @param  array<int, string>  $juryMembers
     */
    public function processSufficiencyExam(
        int $studentId,
        Course $course,
        array $juryMembers,
        ?int $procedureId = null,
    ): SufficiencyExam {
        $exam = SufficiencyExam::create([
            'procedure_id' => $procedureId,
            'student_id' => $studentId,
            'course_id' => $course->id,
            'jury_members' => $juryMembers,
            'status' => 'jurado_propuesto',
        ]);

        $this->logActivity($procedureId, '3.1', null, 'jurado_propuesto');

        return $exam;
    }

    public function issueDirectorResolution(SufficiencyExam $exam, string $resolutionNumber): SufficiencyExam
    {
        $exam->update([
            'director_approval' => true,
            'resolution_number' => $resolutionNumber,
            'status' => 'resolucion_emitida',
        ]);

        $this->logActivity($exam->procedure_id, '3.4', 'jurado_propuesto', 'resolucion_emitida');

        return $exam->fresh();
    }

    public function registerSufficiencyExamAct(SufficiencyExam $exam, string $actNumber, float $score): SufficiencyExam
    {
        $exam->update([
            'act_number' => $actNumber,
            'score' => $score,
            'status' => 'acta_registrada',
        ]);

        $path = "sufficiency-exams/{$exam->id}/acta.pdf";
        Storage::put($path, Pdf::loadView('pdf.acta-examen-suficiencia', ['exam' => $exam->fresh()])->output());

        $this->logActivity($exam->procedure_id, '3.6', 'resolucion_emitida', 'acta_registrada');
        $this->logActivity($exam->procedure_id, '3.7', 'acta_registrada', 'acta_registrada', 'Registro Técnico notificado');

        return $exam->fresh();
    }
}
