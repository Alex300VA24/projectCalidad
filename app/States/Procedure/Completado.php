<?php

namespace App\States\Procedure;

class Completado extends ProcedureState
{
    public static string $name = 'completado';

    public function color(): string
    {
        return 'blue';
    }
}
