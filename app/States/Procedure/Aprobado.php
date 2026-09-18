<?php

namespace App\States\Procedure;

class Aprobado extends ProcedureState
{
    public static string $name = 'aprobado';

    public function color(): string
    {
        return 'green';
    }
}
