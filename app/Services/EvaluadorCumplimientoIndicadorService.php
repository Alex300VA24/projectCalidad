<?php

namespace App\Services;

use App\Models\IndicadorMaestro;

class EvaluadorCumplimientoIndicadorService
{
    public function evaluar(IndicadorMaestro $indicador, float $valor): string
    {
        if ($indicador->meta_institucional === null) {
            return 'SIN_CONFIGURACION';
        }

        $meta = (float) $indicador->meta_institucional;
        $nivelCritico = $indicador->nivel_critico === null ? null : (float) $indicador->nivel_critico;

        if ($indicador->sentido_meta === IndicadorMaestro::SENTIDO_MENOR_IGUAL) {
            if ($valor <= $meta) {
                return 'CONFORME';
            }

            if ($nivelCritico !== null && $valor >= $nivelCritico) {
                return 'CRITICO';
            }

            return $nivelCritico === null ? 'NO_CONFORME' : 'OBSERVADO';
        }

        if ($valor >= $meta) {
            return 'CONFORME';
        }

        if ($nivelCritico !== null && $valor < $nivelCritico) {
            return 'CRITICO';
        }

        return $nivelCritico === null ? 'NO_CONFORME' : 'OBSERVADO';
    }
}
