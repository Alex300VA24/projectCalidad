<?php

namespace App\Services\Procedures;

use App\Models\Curriculum;
use App\Models\CurriculumReview;
use App\Models\Syllabus;
use App\Services\Procedures\Concerns\LogsProcedureActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class CurriculumManagementService
{
    use LogsProcedureActivity;

    private const DECISIONS = ['revalidar', 'ajustar', 'redisenar'];

    /**
     * Actividades 2.1.1 y 2.1.2: registra la evaluación de currículo del COTECCU
     * (lista de cotejo formato F-M01.01-DPA-009) y su decisión.
     *
     * @param  array<string, mixed>  $checklistData
     */
    public function reviewCurriculum(
        Curriculum $curriculum,
        int $coteccuUserId,
        array $checklistData,
        string $decision,
        ?int $procedureId = null,
    ): CurriculumReview {
        if (! in_array($decision, self::DECISIONS, true)) {
            throw new InvalidArgumentException("Decisión inválida: {$decision}");
        }

        $review = CurriculumReview::create([
            'procedure_id' => $procedureId,
            'curriculum_id' => $curriculum->id,
            'coteccu_user_id' => $coteccuUserId,
            'review_checklist_data' => $checklistData,
            'decision' => $decision,
            'state' => 'evaluado',
        ]);

        $this->logActivity($procedureId, '2.1.1', 'en_revision', 'evaluado', "Decisión COTECCU: {$decision}");
        $this->logActivity($procedureId, '2.1.2', 'evaluado', 'decidido');

        return $review;
    }

    /**
     * Actividades 3.4 y 3.5: visa el sílabo (lista de cotejo F-M01.01-DPA-005)
     * y genera la solicitud en PDF para el jefe de biblioteca (actividad 3.8).
     *
     * @param  array<string, mixed>  $checklist005
     */
    public function viseSyllabus(Syllabus $syllabus, array $checklist005): Syllabus
    {
        $fromState = $syllabus->status;

        $syllabus->update([
            'review_checklist' => $checklist005,
            'status' => 'visado',
        ]);

        $this->logActivity($syllabus->procedure_id, '3.4', $fromState, 'en_revision');
        $this->logActivity($syllabus->procedure_id, '3.5', 'en_revision', 'visado');

        $path = "syllabus/{$syllabus->id}/requerimiento-biblioteca.pdf";
        Storage::put($path, Pdf::loadView('pdf.syllabus-visado', ['syllabus' => $syllabus])->output());

        $this->logActivity($syllabus->procedure_id, '3.8', 'visado', 'visado', 'Solicitud enviada a biblioteca');

        return $syllabus->fresh();
    }
}
