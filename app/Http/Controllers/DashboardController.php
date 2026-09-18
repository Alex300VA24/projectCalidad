<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\IndicadorMaestro;
use App\Models\IndicadorMedicion;
use App\Models\PeriodoAcademico;
use App\Models\ProgramaEstudio;
use App\Services\CalculadorIndicadoresService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(CalculadorIndicadoresService $calculador): View
    {
        $programaEstudio = ProgramaEstudio::query()->where('activo', true)->first();
        $periodoActual = PeriodoAcademico::query()->orderByDesc('codigo')->value('codigo') ?? $this->periodoPorDefecto();

        $mediciones = $programaEstudio
            ? $calculador->calcularActuales($programaEstudio, $periodoActual)
            : collect();

        $conformes = $mediciones->where('estado_cumplimiento', 'CONFORME')->count();
        $observados = $mediciones->whereIn('estado_cumplimiento', ['OBSERVADO', 'NO_CONFORME'])->count();
        $criticos = $mediciones->where('estado_cumplimiento', 'CRITICO')->count();

        return view('dashboard', [
            'programaEstudio' => $programaEstudio,
            'periodoActual' => $periodoActual,
            'totalIndicadores' => $mediciones->count(),
            'average' => $mediciones->isEmpty() ? 0 : round(($conformes / $mediciones->count()) * 100, 1),
            'conformes' => $conformes,
            'observados' => $observados,
            'criticos' => $criticos,
            'indicadoresPrioritarios' => $this->priorizar($mediciones),
            'documents' => Document::latest('publication_date')->take(4)->get(),
        ]);
    }

    /** @return Collection<int, array<string, mixed>> */
    private function priorizar(Collection $mediciones): Collection
    {
        $orden = ['CRITICO' => 0, 'OBSERVADO' => 1, 'NO_CONFORME' => 1, 'SIN_CONFIGURACION' => 2, 'CONFORME' => 3];

        return $mediciones
            ->sortBy(fn (IndicadorMedicion $medicion) => $orden[$medicion->estado_cumplimiento] ?? 4)
            ->take(5)
            ->map(fn (IndicadorMedicion $medicion) => [
                'codigo' => $medicion->indicador->codigo,
                'nombre' => $medicion->indicador->nombre,
                'proceso' => $medicion->indicador->proceso,
                'valor' => $medicion->valor_medido,
                'meta' => $medicion->meta_programada,
                'unidad' => $medicion->indicador->unidad_medida,
                'progress' => $this->progreso($medicion),
                'estado' => $this->etiquetaEstado($medicion->estado_cumplimiento),
                'estadoKey' => $this->claveEstado($medicion->estado_cumplimiento),
            ])
            ->values();
    }

    private function progreso(IndicadorMedicion $medicion): float
    {
        $meta = (float) $medicion->meta_programada;
        $valor = (float) $medicion->valor_medido;

        if ($meta <= 0) {
            return 0;
        }

        if ($medicion->indicador->sentido_meta === IndicadorMaestro::SENTIDO_MENOR_IGUAL) {
            return $valor <= $meta ? 100 : max(0, round(100 - (($valor - $meta) / $meta) * 100, 1));
        }

        return min(round(($valor / $meta) * 100, 1), 100);
    }

    private function etiquetaEstado(string $estado): string
    {
        return match ($estado) {
            'CONFORME' => 'Conforme',
            'OBSERVADO' => 'Observado',
            'NO_CONFORME' => 'No conforme',
            'CRITICO' => 'Crítico',
            default => 'Sin datos',
        };
    }

    private function claveEstado(string $estado): string
    {
        return match ($estado) {
            'CONFORME' => 'success',
            'CRITICO' => 'danger',
            default => 'warning',
        };
    }

    private function periodoPorDefecto(): string
    {
        return now()->format('Y').(now()->month <= 6 ? '-I' : '-II');
    }
}
