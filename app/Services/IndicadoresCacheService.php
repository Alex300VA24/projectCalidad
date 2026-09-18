<?php

namespace App\Services;

use App\Models\PeriodoAcademico;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class IndicadoresCacheService
{
    public function key(int $programaEstudioId, string $periodoAcademico): string
    {
        return "indicadores:actuales:{$programaEstudioId}:{$periodoAcademico}";
    }

    public function clear(int $programaEstudioId, string $periodoAcademico): void
    {
        Cache::forget($this->key($programaEstudioId, $periodoAcademico));
    }

    public function olvidarModelo(Model $modelo): void
    {
        $programaIds = collect([
            $modelo->getAttribute('programa_estudio_id'),
            $modelo->getOriginal('programa_estudio_id'),
        ])->filter()->unique();

        $periodos = collect([
            $modelo->getAttribute('periodo_academico'),
            $modelo->getAttribute('academic_period'),
            $modelo->getOriginal('periodo_academico'),
            $modelo->getOriginal('academic_period'),
        ]);

        foreach (['periodo_academico_id'] as $campo) {
            foreach ([$modelo->getAttribute($campo), $modelo->getOriginal($campo)] as $periodoId) {
                if ($periodoId !== null) {
                    $periodos->push(PeriodoAcademico::query()->whereKey($periodoId)->value('codigo'));
                }
            }
        }

        foreach ($programaIds as $programaId) {
            foreach ($periodos->filter()->unique() as $periodo) {
                Cache::forget($this->key((int) $programaId, (string) $periodo));
            }
        }
    }
}
