<?php

namespace App\Services\Procedures\Concerns;

use App\Models\ProcedureLog;

trait LogsProcedureActivity
{
    private function logActivity(
        ?int $procedureId,
        string $activityCode,
        ?string $fromState,
        string $toState,
        ?string $comments = null,
    ): void {
        if ($procedureId === null) {
            return;
        }

        ProcedureLog::create([
            'procedure_id' => $procedureId,
            'user_id' => auth()->id(),
            'activity_code' => $activityCode,
            'from_state' => $fromState,
            'to_state' => $toState,
            'comments' => $comments,
        ]);
    }
}
