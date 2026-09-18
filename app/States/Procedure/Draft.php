<?php

namespace App\States\Procedure;

class Draft extends ProcedureState
{
    public static string $name = 'draft';

    public function color(): string
    {
        return 'gray';
    }
}
