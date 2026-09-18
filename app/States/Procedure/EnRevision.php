<?php

namespace App\States\Procedure;

class EnRevision extends ProcedureState
{
    public static string $name = 'en_revision';

    public function color(): string
    {
        return 'amber';
    }
}
