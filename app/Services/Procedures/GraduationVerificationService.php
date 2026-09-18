<?php

namespace App\Services\Procedures;

use App\Models\GraduateFolder;
use App\Models\User;
use App\Services\Procedures\Concerns\LogsProcedureActivity;
use InvalidArgumentException;

class GraduationVerificationService
{
    use LogsProcedureActivity;

    /**
     * Actividades 5, 6 y 7 de GRAD-01: verifica el récord de créditos y
     * registra la condición de egresado.
     */
    public function validateEgresadoCondition(
        User $student,
        int $creditsCompleted,
        int $creditsRequired,
        ?int $procedureId = null,
    ): GraduateFolder {
        if ($creditsCompleted < $creditsRequired) {
            throw new InvalidArgumentException('El estudiante no cumple el récord de créditos requerido para egresar.');
        }

        $folder = GraduateFolder::firstOrCreate(
            ['student_id' => $student->id],
            ['procedure_id' => $procedureId, 'status' => 'en_verificacion'],
        );

        $folder->update([
            'egresado_condition_validated' => true,
            'status' => 'egresado',
        ]);

        $this->logActivity($procedureId, '5', 'en_verificacion', 'egresado', 'Récord de créditos verificado');
        $this->logActivity($procedureId, '7', 'egresado', 'egresado', 'Condición de egresado registrada');

        return $folder->fresh();
    }

    /**
     * Actividades 11, 13 y 16 de GRAD-02: adjunta constancias, emite el
     * payload normado para SUNEDU y lo deja listo para registro en el STU.
     */
    public function completeStuFolder(
        GraduateFolder $folder,
        bool $approvalConstancy,
        bool $expeditoConstancy,
        bool $noAdeudoConstancy,
    ): GraduateFolder {
        $folder->update([
            'approval_constancy' => $approvalConstancy,
            'expedito_constancy' => $expeditoConstancy,
            'no_adeudo_constancy' => $noAdeudoConstancy,
        ]);

        $this->logActivity($folder->procedure_id, '11', $folder->status, $folder->status, 'Constancias adjuntadas');

        $folder->update(['sunedu_data_payload' => $this->buildSuneduPayload($folder)]);
        $this->logActivity($folder->procedure_id, '13', $folder->status, $folder->status, 'Payload SUNEDU generado');

        $folder->update(['status' => 'listo_stu']);
        $this->logActivity($folder->procedure_id, '16', 'egresado', 'listo_stu', 'Registrado en STU');

        return $folder->fresh();
    }

    /**
     * Estructura placeholder a la espera del esquema oficial SUNEDU/STU.
     *
     * @return array<string, mixed>
     */
    private function buildSuneduPayload(GraduateFolder $folder): array
    {
        return [
            'estudiante' => [
                'nombre' => $folder->student->name,
                'codigo_stu' => $folder->stu_registration_code,
            ],
            'constancias' => [
                'aprobacion_tesis' => $folder->approval_constancy,
                'expedito' => $folder->expedito_constancy,
                'no_adeudo' => $folder->no_adeudo_constancy,
            ],
            'generado_en' => now()->toIso8601String(),
        ];
    }
}
