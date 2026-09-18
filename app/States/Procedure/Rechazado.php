<?php

namespace App\States\Procedure;

class Rechazado extends ProcedureState
{
    public static string $name = 'rechazado';

    public function color(): string
    {
        return 'red';
    }
}
