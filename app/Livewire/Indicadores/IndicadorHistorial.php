<?php

namespace App\Livewire\Indicadores;

use App\Models\Document;
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

            return $cumple ? '#88E788' : '#c0362c';
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

        $puntosMedidos = [];

        foreach ($labelsVentana as $indice => $periodo) {
            $valor = $valoresVentana[$indice] ?? null;

            if ($valor !== null) {
                $puntosMedidos[] = ['periodo' => $periodo, 'valor' => (float) $valor];
            }
        }

        $ultimoPunto = $puntosMedidos === [] ? null : $puntosMedidos[array_key_last($puntosMedidos)];
        $ultimoValor = $ultimoPunto['valor'] ?? null;
        $ultimoPeriodo = $ultimoPunto['periodo'] ?? null;
        $ultimoCumple = $ultimoValor === null || $meta === null
            ? null
            : ($sentidoMenorIgual ? $ultimoValor <= $meta : $ultimoValor >= $meta);
        $esIndicadorSilabos = $this->indicador->codigo === CalculadorIndicadoresService::CODIGO_SILABOS;
        $esIndicadorRetencion = $this->indicador->codigo === CalculadorIndicadoresService::CODIGO_RETENCION;
        $esIndicadorRepitencia = $this->indicador->codigo === CalculadorIndicadoresService::CODIGO_REPITENCIA;
        $documentosIndicador = Document::query()
            ->where('document_type', Document::TIPO_CALIDAD)
            ->where('section', $this->indicador->macro_proceso)
            ->get();
        $documentoIndicador = $documentosIndicador->first(fn (Document $documento): bool => str_contains($documento->title, $this->indicador->codigo))
            ?? $documentosIndicador->first();

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
            'esIndicadorSilabos' => $esIndicadorSilabos,
            'esIndicadorRetencion' => $esIndicadorRetencion,
            'esIndicadorRepitencia' => $esIndicadorRepitencia,
            'interpretacionesSilabos' => $esIndicadorSilabos ? $this->interpretacionesSilabos($puntosMedidos, $meta) : [],
            'interpretacionesRetencion' => $esIndicadorRetencion ? $this->interpretacionesRetencion($puntosMedidos, $meta) : [],
            'interpretacionesRepitencia' => $esIndicadorRepitencia ? $this->interpretacionesRepitencia($puntosMedidos, $meta) : [],
            'documentoIndicador' => $documentoIndicador,
        ]);
    }

    /**
     * @param  list<array{periodo: string, valor: float}>  $puntosMedidos
     * @return list<array{titulo: string, texto: string, tono: string}>
     */
    private function interpretacionesSilabos(array $puntosMedidos, ?float $meta): array
    {
        if ($puntosMedidos === []) {
            return [[
                'titulo' => 'Lectura pendiente',
                'texto' => 'Aún no hay mediciones para interpretar la evolución de los sílabos visados.',
                'tono' => 'neutral',
            ]];
        }

        $ultimo = $puntosMedidos[array_key_last($puntosMedidos)];
        $interpretaciones = [[
            'titulo' => 'Resultado más reciente',
            'texto' => 'En '.$ultimo['periodo'].', el '.number_format($ultimo['valor'], 1, ',', '.').'% de los sílabos fue visado antes del inicio del semestre.',
            'tono' => 'neutral',
        ]];

        if ($meta === null) {
            $interpretaciones[] = [
                'titulo' => 'Meta institucional',
                'texto' => 'Este indicador todavía no tiene una meta institucional configurada para comparar el resultado.',
                'tono' => 'neutral',
            ];
        } else {
            $brecha = round($ultimo['valor'] - $meta, 1);
            $metaFormateada = number_format($meta, 1, ',', '.').'%';

            if ($brecha > 0) {
                $textoMeta = 'El resultado supera la meta institucional de '.$metaFormateada.' en '.number_format($brecha, 1, ',', '.').' puntos porcentuales.';
            } elseif ($brecha < 0) {
                $textoMeta = 'Faltan '.number_format(abs($brecha), 1, ',', '.').' puntos porcentuales para alcanzar la meta institucional de '.$metaFormateada.'.';
            } else {
                $textoMeta = 'El resultado alcanza exactamente la meta institucional de '.$metaFormateada.'.';
            }

            $interpretaciones[] = [
                'titulo' => 'Cumplimiento de la meta',
                'texto' => $textoMeta,
                'tono' => $brecha >= 0 ? 'positivo' : 'atencion',
            ];
        }

        if (count($puntosMedidos) < 2) {
            $interpretaciones[] = [
                'titulo' => 'Tendencia semestral',
                'texto' => 'Se necesita al menos una segunda medición para identificar una tendencia.',
                'tono' => 'neutral',
            ];

            return $interpretaciones;
        }

        $anterior = $puntosMedidos[count($puntosMedidos) - 2];
        $variacion = round($ultimo['valor'] - $anterior['valor'], 1);

        if ($variacion > 0) {
            $textoTendencia = 'La cobertura aumentó '.number_format($variacion, 1, ',', '.').' puntos porcentuales respecto a '.$anterior['periodo'].'.';
        } elseif ($variacion < 0) {
            $textoTendencia = 'La cobertura disminuyó '.number_format(abs($variacion), 1, ',', '.').' puntos porcentuales respecto a '.$anterior['periodo'].'.';
        } else {
            $textoTendencia = 'La cobertura se mantuvo sin variación respecto a '.$anterior['periodo'].'.';
        }

        $interpretaciones[] = [
            'titulo' => 'Tendencia semestral',
            'texto' => $textoTendencia,
            'tono' => $variacion > 0 ? 'positivo' : ($variacion < 0 ? 'atencion' : 'neutral'),
        ];

        return $interpretaciones;
    }

    /**
     * @param  list<array{periodo: string, valor: float}>  $puntosMedidos
     * @return list<array{titulo: string, texto: string, tono: string}>
     */
    private function interpretacionesRetencion(array $puntosMedidos, ?float $meta): array
    {
        if ($puntosMedidos === []) {
            return [[
                'titulo' => 'Lectura pendiente',
                'texto' => 'Aún no hay mediciones para interpretar la retención ni la deserción estudiantil.',
                'tono' => 'neutral',
            ]];
        }

        $ultimo = $puntosMedidos[array_key_last($puntosMedidos)];
        $desercionUltimo = round(100 - $ultimo['valor'], 1);
        $interpretaciones = [[
            'titulo' => 'Resultado más reciente',
            'texto' => 'En '.$ultimo['periodo'].', la tasa de retención fue de '.number_format($ultimo['valor'], 1, ',', '.').'%, lo que equivale a una tasa de deserción de '.number_format($desercionUltimo, 1, ',', '.').'%.',
            'tono' => 'neutral',
        ]];

        if ($meta === null) {
            $interpretaciones[] = [
                'titulo' => 'Meta institucional',
                'texto' => 'Este indicador todavía no tiene una meta institucional configurada para comparar el resultado.',
                'tono' => 'neutral',
            ];
        } else {
            $brecha = round($ultimo['valor'] - $meta, 1);
            $metaFormateada = number_format($meta, 1, ',', '.').'%';

            if ($brecha > 0) {
                $textoMeta = 'La retención supera la meta institucional de '.$metaFormateada.' en '.number_format($brecha, 1, ',', '.').' puntos porcentuales.';
            } elseif ($brecha < 0) {
                $textoMeta = 'Faltan '.number_format(abs($brecha), 1, ',', '.').' puntos porcentuales de retención para alcanzar la meta institucional de '.$metaFormateada.'.';
            } else {
                $textoMeta = 'La retención alcanza exactamente la meta institucional de '.$metaFormateada.'.';
            }

            $interpretaciones[] = [
                'titulo' => 'Cumplimiento de la meta',
                'texto' => $textoMeta,
                'tono' => $brecha >= 0 ? 'positivo' : 'atencion',
            ];
        }

        if (count($puntosMedidos) < 2) {
            $interpretaciones[] = [
                'titulo' => 'Tendencia semestral',
                'texto' => 'Se necesita al menos una segunda medición para identificar una tendencia de deserción.',
                'tono' => 'neutral',
            ];

            return $interpretaciones;
        }

        $anterior = $puntosMedidos[count($puntosMedidos) - 2];
        $variacion = round($ultimo['valor'] - $anterior['valor'], 1);

        if ($variacion > 0) {
            $textoTendencia = 'La retención aumentó '.number_format($variacion, 1, ',', '.').' puntos porcentuales (la deserción bajó en la misma proporción) respecto a '.$anterior['periodo'].'.';
        } elseif ($variacion < 0) {
            $textoTendencia = 'La retención disminuyó '.number_format(abs($variacion), 1, ',', '.').' puntos porcentuales (la deserción subió en la misma proporción) respecto a '.$anterior['periodo'].'.';
        } else {
            $textoTendencia = 'La retención se mantuvo sin variación respecto a '.$anterior['periodo'].'.';
        }

        $interpretaciones[] = [
            'titulo' => 'Tendencia semestral',
            'texto' => $textoTendencia,
            'tono' => $variacion > 0 ? 'positivo' : ($variacion < 0 ? 'atencion' : 'neutral'),
        ];

        return $interpretaciones;
    }

    /**
     * @param  list<array{periodo: string, valor: float}>  $puntosMedidos
     * @return list<array{titulo: string, texto: string, tono: string}>
     */
    private function interpretacionesRepitencia(array $puntosMedidos, ?float $meta): array
    {
        if ($puntosMedidos === []) {
            return [[
                'titulo' => 'Lectura pendiente',
                'texto' => 'Aún no hay mediciones para interpretar la repitencia estudiantil.',
                'tono' => 'neutral',
            ]];
        }

        $ultimo = $puntosMedidos[array_key_last($puntosMedidos)];
        $interpretaciones = [[
            'titulo' => 'Resultado más reciente',
            'texto' => 'En '.$ultimo['periodo'].', el '.number_format($ultimo['valor'], 1, ',', '.').'% de los estudiantes matriculados repitió una misma experiencia curricular.',
            'tono' => 'neutral',
        ]];

        if ($meta === null) {
            $interpretaciones[] = [
                'titulo' => 'Meta institucional',
                'texto' => 'Este indicador todavía no tiene una meta institucional configurada para comparar el resultado.',
                'tono' => 'neutral',
            ];
        } else {
            $brecha = round($meta - $ultimo['valor'], 1);
            $metaFormateada = number_format($meta, 1, ',', '.').'%';

            if ($brecha > 0) {
                $textoMeta = 'El resultado se mantiene dentro de la meta institucional (máximo '.$metaFormateada.'), con '.number_format($brecha, 1, ',', '.').' puntos porcentuales de margen.';
            } elseif ($brecha < 0) {
                $textoMeta = 'El resultado supera la meta institucional (máximo '.$metaFormateada.') en '.number_format(abs($brecha), 1, ',', '.').' puntos porcentuales, un nivel de repitencia por encima de lo aceptable.';
            } else {
                $textoMeta = 'El resultado alcanza exactamente el límite de la meta institucional de '.$metaFormateada.'.';
            }

            $interpretaciones[] = [
                'titulo' => 'Cumplimiento de la meta',
                'texto' => $textoMeta,
                'tono' => $brecha >= 0 ? 'positivo' : 'atencion',
            ];
        }

        if (count($puntosMedidos) < 2) {
            $interpretaciones[] = [
                'titulo' => 'Tendencia semestral',
                'texto' => 'Se necesita al menos una segunda medición para identificar una tendencia de repitencia.',
                'tono' => 'neutral',
            ];

            return $interpretaciones;
        }

        $anterior = $puntosMedidos[count($puntosMedidos) - 2];
        $variacion = round($ultimo['valor'] - $anterior['valor'], 1);

        if ($variacion > 0) {
            $textoTendencia = 'La repitencia aumentó '.number_format($variacion, 1, ',', '.').' puntos porcentuales respecto a '.$anterior['periodo'].'.';
        } elseif ($variacion < 0) {
            $textoTendencia = 'La repitencia disminuyó '.number_format(abs($variacion), 1, ',', '.').' puntos porcentuales respecto a '.$anterior['periodo'].'.';
        } else {
            $textoTendencia = 'La repitencia se mantuvo sin variación respecto a '.$anterior['periodo'].'.';
        }

        $interpretaciones[] = [
            'titulo' => 'Tendencia semestral',
            'texto' => $textoTendencia,
            'tono' => $variacion < 0 ? 'positivo' : ($variacion > 0 ? 'atencion' : 'neutral'),
        ];

        return $interpretaciones;
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
