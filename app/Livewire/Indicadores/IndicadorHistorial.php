<?php

namespace App\Livewire\Indicadores;

use App\Models\IndicadorMaestro;
use App\Models\IndicadorMedicion;
use App\Models\ProgramaEstudio;
use App\Services\CalculadorIndicadoresService;
use App\Services\EvaluadorCumplimientoIndicadorService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class IndicadorHistorial extends Component
{
    private const TAMANO_VENTANA = 5;

    public IndicadorMaestro $indicador;

    public int $programaEstudioId = 0;

    public string $vista = 'tabla';

    public int $ventanaOffset = 0;

    public ?string $periodoEditando = null;

    public string $numerador = '';

    public string $denominador = '';

    public string $valorManual = '';

    public function mount(IndicadorMaestro $indicador): void
    {
        Gate::authorize('viewAny', IndicadorMedicion::class);
        $this->indicador = $indicador;
        $this->programaEstudioId = ProgramaEstudio::query()->where('activo', true)->value('id') ?? 0;
    }

    public function cambiarVista(string $vista): void
    {
        $this->vista = in_array($vista, ['tabla', 'grafico'], true) ? $vista : 'tabla';
    }

    public function moverVentana(int $delta, CalculadorIndicadoresService $calculador): void
    {
        $total = count($this->historico($calculador)['labels']);
        $maximo = max(0, $total - self::TAMANO_VENTANA);
        $this->ventanaOffset = max(0, min($maximo, $this->ventanaOffset + $delta));
    }

    public function editar(string $periodo): void
    {
        $medicion = $this->medicion($periodo);

        if ($medicion !== null) {
            abort_if($medicion->consolidada_en !== null, 404);
            $this->numerador = (string) Arr::get($medicion->datos_fuente ?? [], 'numerador', '');
            $this->denominador = (string) Arr::get($medicion->datos_fuente ?? [], 'denominador', '');
            $this->valorManual = (string) $medicion->valor_medido;
        } else {
            $this->reset(['numerador', 'denominador', 'valorManual']);
            if ($this->usaFormulaManual()) {
                $this->denominador = (string) (str_ends_with($periodo, '-I') ? 31 : 29);
            }
        }

        $this->periodoEditando = $periodo;
        $this->resetValidation();
    }

    public function guardar(EvaluadorCumplimientoIndicadorService $evaluador): void
    {
        abort_if($this->periodoEditando === null, 404);
        $medicion = $this->medicion($this->periodoEditando);

        if ($medicion !== null) {
            abort_if($medicion->consolidada_en !== null, 404);
        }

        $usaFormula = $this->usaFormulaManual();

        if ($usaFormula) {
            $validated = $this->validate([
                'numerador' => ['required', 'integer', 'min:0'],
                'denominador' => ['required', 'integer', 'min:1'],
            ]);
            $valor = round(((int) $validated['numerador'] / (int) $validated['denominador']) * 100, 2);
            $datosFuente = ['origen' => 'manual', 'numerador' => (int) $validated['numerador'], 'denominador' => (int) $validated['denominador']];
        } else {
            $validated = $this->validate([
                'valorManual' => ['required', 'numeric', 'min:0', 'max:100'],
            ]);
            $valor = round((float) $validated['valorManual'], 2);
            $datosFuente = ['origen' => 'manual'];
        }

        IndicadorMedicion::query()->updateOrCreate(
            [
                'indicador_id' => $this->indicador->id,
                'programa_estudio_id' => $this->programaEstudioId,
                'periodo_academico' => $this->periodoEditando,
            ],
            [
                'valor_medido' => $valor,
                'meta_programada' => $this->indicador->meta_institucional,
                'estado_cumplimiento' => $evaluador->evaluar($this->indicador, $valor),
                'registrado_por' => auth()->id(),
                'datos_fuente' => $datosFuente,
            ],
        );

        $this->cerrarForm();
        session()->flash('quality-success', 'Valor del indicador guardado en el histórico.');
    }

    public function cerrarForm(): void
    {
        $this->reset(['periodoEditando', 'numerador', 'denominador', 'valorManual']);
        $this->resetValidation();
    }

    public function render(CalculadorIndicadoresService $calculador): View
    {
        $historico = $this->historico($calculador);
        $mediciones = IndicadorMedicion::query()
            ->where('indicador_id', $this->indicador->id)
            ->where('programa_estudio_id', $this->programaEstudioId)
            ->get()->keyBy('periodo_academico');

        $total = count($historico['labels']);
        $fin = $total - $this->ventanaOffset;
        $inicio = max(0, $fin - self::TAMANO_VENTANA);
        $unidad = $this->indicador->unidad_medida === 'PORCENTAJE' ? '%' : '';
        $meta = $this->indicador->meta_institucional === null ? null : (float) $this->indicador->meta_institucional;

        $labelsVentana = array_slice($historico['labels'], $inicio, $fin - $inicio);
        $valoresVentana = array_slice($historico['valores'], $inicio, $fin - $inicio);
        $sentidoMenorIgual = $this->indicador->sentido_meta === IndicadorMaestro::SENTIDO_MENOR_IGUAL;
        $coloresBarras = array_map(function (?float $valor) use ($meta, $sentidoMenorIgual): string {
            if ($valor === null || $meta === null) {
                return '#94a3b8';
            }

            $cumple = $sentidoMenorIgual ? $valor <= $meta : $valor >= $meta;

            return $cumple ? '#087f5b' : '#c0362c';
        }, $valoresVentana);

        $config = [
            'type' => 'bar',
            'data' => [
                'labels' => $labelsVentana,
                'datasets' => array_values(array_filter([
                    $meta === null ? null : [
                        'type' => 'line',
                        'label' => 'Meta institucional ('.number_format($meta, 0).$unidad.')',
                        'data' => array_fill(0, count($labelsVentana), $meta),
                        'borderColor' => '#c0362c',
                        'borderDash' => [6, 4],
                        'borderWidth' => 2,
                        'pointRadius' => 0,
                        'fill' => false,
                        'order' => 0,
                    ],
                    [
                        'type' => 'bar',
                        'label' => $this->indicador->nombre,
                        'data' => $valoresVentana,
                        'backgroundColor' => $coloresBarras,
                        'borderRadius' => 6,
                        'borderSkipped' => false,
                        'maxBarThickness' => 56,
                        'showValueLabels' => true,
                        'valueLabelSuffix' => $unidad,
                        'order' => 1,
                    ],
                ])),
            ],
        ];

        $ultimoValor = collect($valoresVentana)->last();
        $ultimoPeriodo = collect($labelsVentana)->last();
        $ultimoCumple = $ultimoValor === null || $meta === null
            ? null
            : ($sentidoMenorIgual ? $ultimoValor <= $meta : $ultimoValor >= $meta);

        return view('livewire.indicadores.indicador-historial', [
            'periodos' => array_reverse($historico['labels']),
            'mediciones' => $mediciones,
            'usaFormulaManual' => $this->usaFormulaManual(),
            'etiquetasFormula' => Arr::get($this->indicador->configuracion ?? [], 'formula_manual', []),
            'chartConfig' => $config,
            'puedeAnterior' => $inicio > 0,
            'puedeSiguiente' => $this->ventanaOffset > 0,
            'unidad' => $unidad,
            'ultimoValor' => $ultimoValor,
            'ultimoPeriodo' => $ultimoPeriodo,
            'ultimoCumple' => $ultimoCumple,
            'meta' => $meta,
        ]);
    }

    /** @return array{labels: list<string>, valores: list<float|null>} */
    private function historico(CalculadorIndicadoresService $calculador): array
    {
        $programa = ProgramaEstudio::query()->find($this->programaEstudioId);

        if ($programa === null) {
            return ['labels' => [], 'valores' => []];
        }

        return $calculador->datosHistoricoIndicador($programa, $this->indicador);
    }

    private function medicion(string $periodo): ?IndicadorMedicion
    {
        return IndicadorMedicion::query()->where([
            'indicador_id' => $this->indicador->id,
            'programa_estudio_id' => $this->programaEstudioId,
            'periodo_academico' => $periodo,
        ])->first();
    }

    private function usaFormulaManual(): bool
    {
        return Arr::get($this->indicador->configuracion ?? [], 'formula_manual') !== null;
    }
}
