<?php

namespace App\States\Procedure;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class ProcedureState extends State
{
    abstract public function color(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Draft::class)
            ->allowTransition(Draft::class, EnRevision::class)
            ->allowTransition(EnRevision::class, Aprobado::class)
            ->allowTransition(EnRevision::class, Rechazado::class)
            ->allowTransition(Rechazado::class, EnRevision::class)
            ->allowTransition(Aprobado::class, Completado::class);
    }
}
