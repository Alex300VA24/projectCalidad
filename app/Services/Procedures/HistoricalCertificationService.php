<?php

namespace App\Services\Procedures;

use App\Models\PhysicalAcademicHistory;
use App\Models\User;
use App\Services\Procedures\Concerns\LogsProcedureActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class HistoricalCertificationService
{
    use LogsProcedureActivity;

    private const LAST_ELIGIBLE_ENTRY_YEAR = 2007;

    /**
     * Actividades 2, 5, 6 y 7: valida año de ingreso <= 2007, consolida las
     * actas físicas impresas, registra el historial y emite el certificado
     * para Registros Académicos.
     *
     * @param  array<string, mixed>  $sourceActsReferences
     * @param  array<string, mixed>  $physicalHistoryData
     */
    public function processPre2007Certificate(
        User $student,
        int $entryYear,
        array $sourceActsReferences,
        array $physicalHistoryData,
        ?int $procedureId = null,
    ): PhysicalAcademicHistory {
        if ($entryYear > self::LAST_ELIGIBLE_ENTRY_YEAR) {
            throw new InvalidArgumentException(
                'CERT-01 solo aplica a ingresantes hasta el año '.self::LAST_ELIGIBLE_ENTRY_YEAR.'.'
            );
        }

        $history = PhysicalAcademicHistory::create([
            'procedure_id' => $procedureId,
            'student_id' => $student->id,
            'entry_year' => $entryYear,
            'source_acts_references' => $sourceActsReferences,
            'physical_history_data' => $physicalHistoryData,
            'status' => 'elaborado',
        ]);

        $this->logActivity($procedureId, '2', null, 'elaborado');

        $history->update(['status' => 'verificado']);
        $this->logActivity($procedureId, '5', 'elaborado', 'verificado');

        $path = "physical-academic-histories/{$history->id}/certificado.pdf";
        Storage::put($path, Pdf::loadView('pdf.historial-academico-fisico', ['history' => $history->fresh()])->output());

        $history->update([
            'certificate_printed_at' => now(),
            'status' => 'enviado_registros',
        ]);

        $this->logActivity($procedureId, '6', 'verificado', 'enviado_registros', 'Certificado impreso');
        $this->logActivity($procedureId, '7', 'enviado_registros', 'enviado_registros', 'Enviado a Registros Académicos');

        return $history->fresh();
    }
}
