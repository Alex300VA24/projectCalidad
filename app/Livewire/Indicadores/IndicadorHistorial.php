<?php

namespace App\Livewire\Indicadores;

use App\Models\Document;
use App\Models\IndicadorMaestro;
use App\Models\IndicadorMedicion;
use App\Models\ProgramaEstudio;
use App\Services\CalculadorIndicadoresService;
use App\Services\EvaluadorCumplimientoIndicadorService;
use App\Services\IndicadorCompetenciasEsperadasService;
use App\Services\IndicadorDesaprobadosDosOMasVecesService;
use App\Services\IndicadorDesaprobadosPorCohorteService;
use App\Services\IndicadorDesaprobadosService;
use App\Services\IndicadorEgresadosService;
use App\Services\IndicadorTutoriaService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Throwable;

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

    /** @var list<string> */
    public array $periodosDesaprobados = [];

    public string $periodoSeleccionado = '';

    public ?string $errorDatosDesaprobados = null;

    /** @var Collection<int, IndicadorMaestro> */
    public Collection $indicadoresDisponibles;

    public ?string $codigoIndicadorAnterior = null;

    public ?string $codigoIndicadorSiguiente = null;

    public function mount(
        IndicadorMaestro $indicador,
        IndicadorDesaprobadosService $indicadorDesaprobados,
        IndicadorDesaprobadosDosOMasVecesService $indicadorDesaprobadosDosOMasVeces,
        IndicadorDesaprobadosPorCohorteService $indicadorDesaprobadosPorCohorte,
        IndicadorCompetenciasEsperadasService $indicadorCompetenciasEsperadas,
        IndicadorEgresadosService $indicadorEgresados,
        IndicadorTutoriaService $indicadorTutoria,
    ): void {
        Gate::authorize('viewAny', IndicadorMedicion::class);
        abort_if($indicador->estaRetirado(), 404);
        $this->indicador = $indicador;
        $this->programaEstudioId = ProgramaEstudio::query()->where('activo', true)->value('id') ?? 0;

        $this->indicadoresDisponibles = IndicadorMaestro::query()->vigentes()->orderBy('codigo')->get(['id', 'codigo', 'nombre']);
        $indice = $this->indicadoresDisponibles->search(fn (IndicadorMaestro $item): bool => $item->is($indicador));

        if ($indice !== false) {
            $this->codigoIndicadorAnterior = $indice > 0 ? $this->indicadoresDisponibles->get($indice - 1)?->codigo : null;
            $this->codigoIndicadorSiguiente = $this->indicadoresDisponibles->get($indice + 1)?->codigo;
        }

        if ($this->esIndicadorDesaprobados()
            || $this->esIndicadorDesaprobadosDosOMasVeces()
            || $this->esIndicadorDesaprobadosPorCohorte()
            || $this->esIndicadorCompetenciasEsperadas()
            || $this->esIndicadorEgresados()
            || $this->esIndicadorTutoria()) {
            try {
                $servicio = match (true) {
                    $this->esIndicadorDesaprobados() => $indicadorDesaprobados,
                    $this->esIndicadorDesaprobadosDosOMasVeces() => $indicadorDesaprobadosDosOMasVeces,
                    $this->esIndicadorDesaprobadosPorCohorte() => $indicadorDesaprobadosPorCohorte,
                    $this->esIndicadorEgresados() => $indicadorEgresados,
                    $this->esIndicadorTutoria() => $indicadorTutoria,
                    default => $indicadorCompetenciasEsperadas,
                };
                $this->periodosDesaprobados = $this->esIndicadorCompetenciasEsperadas()
                    || $this->esIndicadorEgresados()
                    || $this->esIndicadorTutoria()
                    ? $servicio->periodos($this->indicador->codigo)
                    : $servicio->periodos();
                $this->periodoSeleccionado = $this->periodosDesaprobados[0] ?? '';

                if ($this->esIndicadorDesaprobadosPorCohorte()
                    || $this->esIndicadorCompetenciasEsperadas()
                    || $this->esIndicadorEgresados()
                    || $this->esIndicadorTutoria()) {
                    $this->vista = 'grafico';
                }
            } catch (Throwable $exception) {
                report($exception);
                $this->errorDatosDesaprobados = 'No se pudo cargar la información del indicador. Verifica el archivo JSON e inténtalo nuevamente.';
            }
        }
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

    public function render(
        CalculadorIndicadoresService $calculador,
        IndicadorDesaprobadosService $indicadorDesaprobados,
        IndicadorDesaprobadosDosOMasVecesService $indicadorDesaprobadosDosOMasVeces,
        IndicadorDesaprobadosPorCohorteService $indicadorDesaprobadosPorCohorte,
        IndicadorCompetenciasEsperadasService $indicadorCompetenciasEsperadas,
        IndicadorEgresadosService $indicadorEgresados,
        IndicadorTutoriaService $indicadorTutoria,
    ): View {
        if ($this->esIndicadorTutoria()) {
            return $this->renderizarIndicadorTutoria($indicadorTutoria);
        }

        if ($this->esIndicadorEgresados()) {
            return $this->renderizarIndicadorEgresados($indicadorEgresados);
        }

        if ($this->esIndicadorDesaprobados()) {
            return $this->renderizarIndicadorDesaprobados($indicadorDesaprobados);
        }

        if ($this->esIndicadorDesaprobadosDosOMasVeces()) {
            return $this->renderizarIndicadorDesaprobadosDosOMasVeces($indicadorDesaprobadosDosOMasVeces);
        }

        if ($this->esIndicadorDesaprobadosPorCohorte()) {
            return $this->renderizarIndicadorDesaprobadosPorCohorte($indicadorDesaprobadosPorCohorte);
        }

        if ($this->esIndicadorCompetenciasEsperadas()) {
            return $this->renderizarIndicadorCompetenciasEsperadas($indicadorCompetenciasEsperadas);
        }

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
        $documentoIndicador = $this->documentoIndicador();

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

    private function esIndicadorDesaprobados(): bool
    {
        return $this->indicador->codigo === IndicadorDesaprobadosService::CODIGO;
    }

    private function esIndicadorDesaprobadosDosOMasVeces(): bool
    {
        return $this->indicador->codigo === IndicadorDesaprobadosDosOMasVecesService::CODIGO;
    }

    private function esIndicadorDesaprobadosPorCohorte(): bool
    {
        return $this->indicador->codigo === IndicadorDesaprobadosPorCohorteService::CODIGO;
    }

    private function esIndicadorCompetenciasEsperadas(): bool
    {
        return in_array($this->indicador->codigo, [
            IndicadorCompetenciasEsperadasService::CODIGO_GENERALES,
            IndicadorCompetenciasEsperadasService::CODIGO_ESPECIFICAS,
        ], true);
    }

    private function esIndicadorEgresados(): bool
    {
        return in_array($this->indicador->codigo, IndicadorEgresadosService::codigos(), true);
    }

    private function esIndicadorTutoria(): bool
    {
        return in_array($this->indicador->codigo, IndicadorTutoriaService::codigos(), true);
    }

    private function renderizarIndicadorEgresados(IndicadorEgresadosService $servicio): View
    {
        return $this->renderizarIndicadorDesdeJson(
            $servicio,
            'Resultado por cohorte, promoción o período',
            'Cada opción se calcula por separado con valores absolutos del archivo JSON.',
        );
    }

    private function renderizarIndicadorTutoria(IndicadorTutoriaService $servicio): View
    {
        return $this->renderizarIndicadorDesdeJson(
            $servicio,
            'Resultado por período de medición',
            'El porcentaje se recalcula con los valores absolutos de la fuente JSON.',
        );
    }

    private function renderizarIndicadorDesdeJson(
        IndicadorEgresadosService|IndicadorTutoriaService $servicio,
        string $tituloResultado,
        string $descripcionResultado,
    ): View {
        $resultado = null;

        try {
            $this->periodosDesaprobados = $servicio->periodos($this->indicador->codigo);

            if (! in_array($this->periodoSeleccionado, $this->periodosDesaprobados, true)) {
                $this->periodoSeleccionado = $this->periodosDesaprobados[0] ?? '';
            }

            if ($this->periodoSeleccionado !== '') {
                $resultado = $servicio->resultado($this->indicador->codigo, $this->periodoSeleccionado);
            }

            $this->errorDatosDesaprobados = null;
        } catch (Throwable $exception) {
            report($exception);
            $this->errorDatosDesaprobados = 'No se pudo cargar la información del indicador. Verifica el archivo JSON e inténtalo nuevamente.';
        }

        $esEgresadosPromocion = $servicio instanceof IndicadorEgresadosService
            && $this->indicador->codigo === IndicadorEgresadosService::CODIGO_EGRESADOS_PROMOCION;
        $esTutoria = $servicio instanceof IndicadorTutoriaService;
        $esGraficoComparativoPeriodos = $esEgresadosPromocion || $esTutoria;
        $chartConfig = match (true) {
            $esGraficoComparativoPeriodos && $this->errorDatosDesaprobados === null => $this->configuracionGraficoComparativoPeriodos($servicio),
            $esGraficoComparativoPeriodos => ['type' => 'bar', 'data' => ['labels' => [], 'datasets' => []]],
            default => $this->configuracionGraficoEgresados($resultado),
        };

        return view('livewire.indicadores.indicador-egresados', [
            'resultado' => $resultado,
            'chartConfig' => $chartConfig,
            'esGraficoComparativoPeriodos' => $esGraficoComparativoPeriodos,
            'etiquetaPeriodoGrafico' => $esEgresadosPromocion ? 'año' : 'período',
            'etiquetaPeriodosGrafico' => $esEgresadosPromocion ? 'años' : 'períodos',
            'breakdownChartConfig' => $this->configuracionGraficoDesgloseEgresados($resultado),
            'documentoIndicador' => $this->documentoIndicador(),
            'tituloResultado' => $tituloResultado,
            'descripcionResultado' => $descripcionResultado,
            'interpretacionesEgresados' => $this->errorDatosDesaprobados === null
                ? $this->interpretacionesEgresados($resultado, $servicio, $esGraficoComparativoPeriodos)
                : [],
        ]);
    }

    /** @return array<string, mixed> */
    private function configuracionGraficoComparativoPeriodos(
        IndicadorEgresadosService|IndicadorTutoriaService $servicio,
    ): array {
        $periodos = array_reverse($servicio->periodos($this->indicador->codigo));

        $valores = array_map(
            fn (string $periodo): ?float => $servicio->resultado($this->indicador->codigo, $periodo)['porcentaje'] ?? null,
            $periodos,
        );

        return [
            'type' => 'bar',
            'data' => [
                'labels' => $periodos,
                'datasets' => [[
                    'label' => $this->indicador->nombre,
                    'data' => $valores,
                    'backgroundColor' => '#4f46e5',
                    'borderRadius' => 6,
                    'borderSkipped' => false,
                    'maxBarThickness' => 56,
                    'showValueLabels' => true,
                    'valueLabelSuffix' => '%',
                ]],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $resultado
     * @return array<string, mixed>
     */
    private function configuracionGraficoEgresados(?array $resultado): array
    {
        if ($resultado === null || $resultado['porcentaje'] === null) {
            return ['type' => 'doughnut', 'data' => ['labels' => [], 'datasets' => []]];
        }

        $colorPrincipal = match ($resultado['estado'] ?? null) {
            'amarillo' => '#d97706',
            'rojo' => '#c0362c',
            default => '#047857',
        };

        return [
            'type' => 'doughnut',
            'data' => [
                'labels' => ['Resultado del indicador', 'Resto hasta el total'],
                'datasets' => [[
                    'label' => $this->indicador->nombre,
                    'data' => [$resultado['numerador'], max(0, $resultado['denominador'] - $resultado['numerador'])],
                    'backgroundColor' => [$colorPrincipal, '#e2e8f0'],
                    'borderColor' => [$colorPrincipal, '#cbd5e1'],
                    'borderWidth' => 1,
                    'metricDetails' => [
                        'total' => $resultado['denominador'],
                        'periodo' => $resultado['periodo'],
                    ],
                ]],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $resultado
     * @return array<string, mixed>|null
     */
    private function configuracionGraficoDesgloseEgresados(?array $resultado): ?array
    {
        if ($resultado === null || $resultado['desglose'] === []) {
            return null;
        }

        return [
            'type' => 'bar',
            'data' => [
                'labels' => array_column($resultado['desglose'], 'dimension'),
                'datasets' => [[
                    'label' => 'Nivel de satisfacción',
                    'data' => array_column($resultado['desglose'], 'porcentaje'),
                    'backgroundColor' => '#4f46e5',
                    'borderRadius' => 5,
                    'borderSkipped' => false,
                    'maxBarThickness' => 24,
                    'showValueLabels' => true,
                    'valueLabelSuffix' => '%',
                    'tooltipAsPercent' => true,
                ]],
            ],
        ];
    }

    private function renderizarIndicadorDesaprobados(IndicadorDesaprobadosService $indicadorDesaprobados): View
    {
        $experiencias = [];

        try {
            $this->periodosDesaprobados = $indicadorDesaprobados->periodos();

            if (! in_array($this->periodoSeleccionado, $this->periodosDesaprobados, true)) {
                $this->periodoSeleccionado = $this->periodosDesaprobados[0] ?? '';
            }

            if ($this->periodoSeleccionado !== '') {
                $experiencias = $indicadorDesaprobados->experienciasDelPeriodo($this->periodoSeleccionado);
            }

            $this->errorDatosDesaprobados = null;
        } catch (Throwable $exception) {
            report($exception);
            $this->errorDatosDesaprobados = 'No se pudo cargar la información del indicador. Verifica el archivo JSON e inténtalo nuevamente.';
        }

        $chartConfig = $this->configuracionGraficoDesaprobados($experiencias);

        return view('livewire.indicadores.indicador-desaprobados', [
            'experiencias' => $experiencias,
            'chartConfig' => $chartConfig,
            'chartHeight' => max(420, count($experiencias) * 34),
            'interpretacionesDesaprobados' => $this->interpretacionesDesaprobados($experiencias),
            'documentoIndicador' => $this->documentoIndicador(),
        ]);
    }

    private function renderizarIndicadorDesaprobadosPorCohorte(
        IndicadorDesaprobadosPorCohorteService $indicadorDesaprobados,
    ): View {
        $experiencias = [];

        try {
            $this->periodosDesaprobados = $indicadorDesaprobados->periodos();

            if ($this->periodoSeleccionado === '' && $this->periodosDesaprobados !== []) {
                $this->periodoSeleccionado = $this->periodosDesaprobados[0];
            }

            if ($this->periodoSeleccionado !== '') {
                $experiencias = $indicadorDesaprobados->experienciasDelPeriodo($this->periodoSeleccionado);
            }

            $this->errorDatosDesaprobados = null;
        } catch (Throwable $exception) {
            report($exception);
            $this->errorDatosDesaprobados = 'No se pudo cargar la información del indicador. Verifica el archivo JSON e inténtalo nuevamente.';
        }

        return view('livewire.indicadores.indicador-desaprobados', [
            'experiencias' => $experiencias,
            'resultadoAgregado' => $indicadorDesaprobados->calcularResultadoAgregado($experiencias),
            'chartConfig' => $this->configuracionGraficoDesaprobados($experiencias),
            'chartHeight' => max(420, count($experiencias) * 34),
            'interpretacionesDesaprobados' => $this->interpretacionesDesaprobados($experiencias),
            'documentoIndicador' => $this->documentoIndicador(),
            'esIndicadorCohorte' => true,
            'fuenteDatos' => 'indicador_M01_04_PG_I5_datos_actualizados.json',
        ]);
    }

    private function renderizarIndicadorCompetenciasEsperadas(
        IndicadorCompetenciasEsperadasService $indicadorCompetencias,
    ): View {
        $experiencias = [];

        try {
            $this->periodosDesaprobados = $indicadorCompetencias->periodos($this->indicador->codigo);

            if (! in_array($this->periodoSeleccionado, $this->periodosDesaprobados, true)) {
                $this->periodoSeleccionado = $this->periodosDesaprobados[0] ?? '';
            }

            if ($this->periodoSeleccionado !== '') {
                $experiencias = $indicadorCompetencias->experienciasDelPeriodo(
                    $this->indicador->codigo,
                    $this->periodoSeleccionado,
                );
            }

            $this->errorDatosDesaprobados = null;
        } catch (Throwable $exception) {
            report($exception);
            $this->errorDatosDesaprobados = 'No se pudo cargar la información del indicador. Verifica el archivo JSON e inténtalo nuevamente.';
        }

        $esCompetenciaGeneral = $this->indicador->codigo === IndicadorCompetenciasEsperadasService::CODIGO_GENERALES;

        return view('livewire.indicadores.indicador-competencias-esperadas', [
            'experiencias' => $experiencias,
            'resultadoAgregado' => $indicadorCompetencias->calcularResultadoAgregado($experiencias),
            'chartConfig' => $this->configuracionGraficoCompetencias($experiencias),
            'chartHeight' => max(320, count($experiencias) * 56),
            'documentoIndicador' => $this->documentoIndicador(),
            'esCompetenciaGeneral' => $esCompetenciaGeneral,
            'fuenteDatos' => $esCompetenciaGeneral
                ? 'competencias_generales_esperadas_PG_I2.json'
                : 'competencias_especificas_esperadas_PG_I3.json',
            'interpretacionesCompetencias' => $this->interpretacionesCompetencias($experiencias),
        ]);
    }

    private function renderizarIndicadorDesaprobadosDosOMasVeces(
        IndicadorDesaprobadosDosOMasVecesService $indicadorDesaprobados,
    ): View {
        $datosPeriodo = null;

        try {
            $this->periodosDesaprobados = $indicadorDesaprobados->periodos();

            if ($this->periodoSeleccionado === '' && $this->periodosDesaprobados !== []) {
                $this->periodoSeleccionado = $this->periodosDesaprobados[0];
            }

            if ($this->periodoSeleccionado !== '') {
                $datosPeriodo = $indicadorDesaprobados->datosDelPeriodo($this->periodoSeleccionado);
            }

            $this->errorDatosDesaprobados = null;
        } catch (Throwable $exception) {
            report($exception);
            $this->errorDatosDesaprobados = 'No se pudo cargar la información del indicador. Verifica el archivo JSON e inténtalo nuevamente.';
        }

        $chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => ['Segunda vez', 'Tercera vez', 'Cuarta vez'],
                'datasets' => [[
                    'label' => 'Cantidad de estudiantes',
                    'data' => $datosPeriodo === null ? [] : [
                        $datosPeriodo['segunda_vez'],
                        $datosPeriodo['tercera_vez'],
                        $datosPeriodo['cuarta_vez'],
                    ],
                    'backgroundColor' => ['#4f46e5', '#059669', '#d97706'],
                    'borderRadius' => 6,
                    'borderSkipped' => false,
                    'maxBarThickness' => 72,
                    'showValueLabels' => true,
                    'valueLabelDecimals' => 0,
                ]],
            ],
        ];

        return view('livewire.indicadores.indicador-desaprobados-dos-o-mas-veces', [
            'datosPeriodo' => $datosPeriodo,
            'chartConfig' => $chartConfig,
            'porcentajeIndicador' => $datosPeriodo === null
                ? null
                : rtrim(rtrim(number_format($datosPeriodo['porcentaje_indicador'], 2, '.', ''), '0'), '.'),
            'documentoIndicador' => $this->documentoIndicador(),
            'interpretacionesReincidencia' => $this->interpretacionesReincidencia($datosPeriodo),
        ]);
    }

    private function documentoIndicador(): ?Document
    {
        $documentosIndicador = Document::query()
            ->where('document_type', Document::TIPO_CALIDAD)
            ->where('section', $this->indicador->macro_proceso)
            ->get();

        $tituloDocumento = Arr::get($this->indicador->configuracion ?? [], 'documento_titulo');

        if (is_string($tituloDocumento) && $tituloDocumento !== '') {
            return $documentosIndicador->first(
                fn (Document $documento): bool => $documento->title === $tituloDocumento,
            );
        }

        return $documentosIndicador->first(fn (Document $documento): bool => str_contains($documento->title, $this->indicador->codigo))
            ?? $documentosIndicador->first();
    }

    /**
     * @param  list<array{
     *     nombre_curso: string,
     *     codigo_curso: string,
     *     periodo?: string,
     *     numero_desaprobados: int,
     *     total_matriculados: int,
     *     porcentaje_desaprobados: float|null
     * }>  $experiencias
     * @return array<string, mixed>
     */
    private function configuracionGraficoDesaprobados(array $experiencias): array
    {
        return [
            'type' => 'bar',
            'data' => [
                'labels' => array_column($experiencias, 'nombre_curso'),
                'datasets' => [[
                    'label' => 'Estudiantes desaprobados',
                    'data' => array_column($experiencias, 'porcentaje_desaprobados'),
                    'backgroundColor' => '#c0362c',
                    'borderRadius' => 5,
                    'borderSkipped' => false,
                    'maxBarThickness' => 22,
                    'showValueLabels' => true,
                    'valueLabelSuffix' => '%',
                    'courseDetails' => $experiencias,
                ]],
            ],
        ];
    }

    /**
     * @param  list<array{
     *     nombre_curso: string,
     *     codigo_curso_reporte: string|null,
     *     codigo_curso_plan_estudios: string|null,
     *     ciclo_plan: string|null,
     *     tipo: string,
     *     numero_estudiantes_que_logran_nivel_esperado: int,
     *     total_estudiantes_matriculados: int,
     *     porcentaje_logro: float|null
     * }>  $experiencias
     * @return array<string, mixed>
     */
    private function configuracionGraficoCompetencias(array $experiencias): array
    {
        return [
            'type' => 'bar',
            'data' => [
                'labels' => array_column($experiencias, 'nombre_curso'),
                'datasets' => [[
                    'label' => 'Logro del nivel esperado',
                    'data' => array_column($experiencias, 'porcentaje_logro'),
                    'backgroundColor' => '#1f7a4d',
                    'borderRadius' => 5,
                    'borderSkipped' => false,
                    'maxBarThickness' => 22,
                    'showValueLabels' => true,
                    'valueLabelSuffix' => '%',
                    'courseDetails' => $experiencias,
                ]],
            ],
        ];
    }

    /**
     * @param  list<array{
     *     nombre_curso: string,
     *     codigo_curso: string,
     *     numero_desaprobados: int,
     *     total_matriculados: int,
     *     porcentaje_desaprobados: float|null
     * }>  $experiencias
     * @return list<array{titulo: string, texto: string, tono: string}>
     */
    private function interpretacionesDesaprobados(array $experiencias): array
    {
        $experienciasConDatos = array_values(array_filter(
            $experiencias,
            fn (array $experiencia): bool => $experiencia['porcentaje_desaprobados'] !== null,
        ));

        if ($experienciasConDatos === []) {
            return [];
        }

        $experienciaMayorPorcentaje = $experienciasConDatos[0];

        foreach ($experienciasConDatos as $experiencia) {
            if ($experiencia['porcentaje_desaprobados'] > $experienciaMayorPorcentaje['porcentaje_desaprobados']) {
                $experienciaMayorPorcentaje = $experiencia;
            }
        }

        $cantidadExperiencias = count($experiencias);
        $cantidadSinDesaprobados = count(array_filter(
            $experienciasConDatos,
            fn (array $experiencia): bool => $experiencia['numero_desaprobados'] === 0,
        ));
        $cantidadConDesaprobados = count($experienciasConDatos) - $cantidadSinDesaprobados;

        return [
            [
                'titulo' => 'Mayor porcentaje observado',
                'texto' => 'En '.$this->periodoSeleccionado.', '.$experienciaMayorPorcentaje['nombre_curso'].' presenta el porcentaje más alto: '.$this->formatearPorcentaje($experienciaMayorPorcentaje['porcentaje_desaprobados']).' ('.$experienciaMayorPorcentaje['numero_desaprobados'].' de '.$experienciaMayorPorcentaje['total_matriculados'].' matriculados).',
                'tono' => 'atencion',
            ],
            [
                'titulo' => 'Cursos sin desaprobados',
                'texto' => $cantidadSinDesaprobados.' de '.$cantidadExperiencias.' experiencias curriculares no registran estudiantes desaprobados en el período seleccionado.',
                'tono' => 'positivo',
            ],
            [
                'titulo' => 'Cobertura del registro',
                'texto' => 'Se analizaron '.$cantidadExperiencias.' experiencias curriculares; '.$cantidadConDesaprobados.' registran al menos un estudiante desaprobado. Los porcentajes se interpretan por curso, no como promedio del período.',
                'tono' => 'neutral',
            ],
        ];
    }

    private function formatearPorcentaje(float $porcentaje): string
    {
        return rtrim(rtrim(number_format($porcentaje, 2, ',', '.'), '0'), ',').'%';
    }

    /**
     * @param  list<array{
     *     nombre_curso: string,
     *     numero_estudiantes_que_logran_nivel_esperado: int,
     *     total_estudiantes_matriculados: int,
     *     porcentaje_logro: float|null
     * }>  $experiencias
     * @return list<array{titulo: string, texto: string, tono: string}>
     */
    private function interpretacionesCompetencias(array $experiencias): array
    {
        $experienciasConDatos = array_values(array_filter(
            $experiencias,
            fn (array $experiencia): bool => $experiencia['porcentaje_logro'] !== null,
        ));

        if ($experienciasConDatos === []) {
            return [];
        }

        $experienciaMayorLogro = $experienciasConDatos[0];
        $experienciaMenorLogro = $experienciasConDatos[0];

        foreach ($experienciasConDatos as $experiencia) {
            if ($experiencia['porcentaje_logro'] > $experienciaMayorLogro['porcentaje_logro']) {
                $experienciaMayorLogro = $experiencia;
            }

            if ($experiencia['porcentaje_logro'] < $experienciaMenorLogro['porcentaje_logro']) {
                $experienciaMenorLogro = $experiencia;
            }
        }

        $interpretaciones = [
            [
                'titulo' => 'Mayor logro observado',
                'texto' => 'En '.$this->periodoSeleccionado.', '.$experienciaMayorLogro['nombre_curso'].' presenta el porcentaje de logro más alto: '.$this->formatearPorcentaje($experienciaMayorLogro['porcentaje_logro']).' ('.$experienciaMayorLogro['numero_estudiantes_que_logran_nivel_esperado'].' de '.$experienciaMayorLogro['total_estudiantes_matriculados'].' matriculados).',
                'tono' => 'positivo',
            ],
            [
                'titulo' => 'Menor logro observado',
                'texto' => 'En '.$this->periodoSeleccionado.', '.$experienciaMenorLogro['nombre_curso'].' presenta el porcentaje de logro más bajo: '.$this->formatearPorcentaje($experienciaMenorLogro['porcentaje_logro']).' ('.$experienciaMenorLogro['numero_estudiantes_que_logran_nivel_esperado'].' de '.$experienciaMenorLogro['total_estudiantes_matriculados'].' matriculados).',
                'tono' => 'atencion',
            ],
        ];

        $meta = $this->indicador->meta_institucional === null ? null : (float) $this->indicador->meta_institucional;

        if ($meta === null) {
            $interpretaciones[] = [
                'titulo' => 'Cobertura del registro',
                'texto' => 'Se analizaron '.count($experiencias).' experiencias curriculares en el período seleccionado. Este indicador todavía no tiene una meta institucional configurada para comparar los resultados.',
                'tono' => 'neutral',
            ];

            return $interpretaciones;
        }

        $cantidadQueAlcanzaMeta = count(array_filter(
            $experienciasConDatos,
            fn (array $experiencia): bool => $experiencia['porcentaje_logro'] >= $meta,
        ));

        $interpretaciones[] = [
            'titulo' => 'Cumplimiento de la meta',
            'texto' => $cantidadQueAlcanzaMeta.' de '.count($experienciasConDatos).' experiencias curriculares alcanzan o superan la meta institucional de '.number_format($meta, 0).'%.',
            'tono' => $cantidadQueAlcanzaMeta > 0 ? 'positivo' : 'atencion',
        ];

        return $interpretaciones;
    }

    /**
     * @param  array{
     *     segunda_vez: int,
     *     tercera_vez: int,
     *     cuarta_vez: int,
     *     total_estudiantes_dos_o_mas_veces: int,
     *     total_estudiantes_matriculados_semestre: int
     * }|null  $datosPeriodo
     * @return list<array{titulo: string, texto: string, tono: string}>
     */
    private function interpretacionesReincidencia(?array $datosPeriodo): array
    {
        if ($datosPeriodo === null) {
            return [];
        }

        $conteos = [
            'segunda vez' => $datosPeriodo['segunda_vez'],
            'tercera vez' => $datosPeriodo['tercera_vez'],
            'cuarta vez' => $datosPeriodo['cuarta_vez'],
        ];

        $categoriaMasFrecuente = array_key_first($conteos);

        foreach ($conteos as $categoria => $cantidad) {
            if ($cantidad > $conteos[$categoriaMasFrecuente]) {
                $categoriaMasFrecuente = $categoria;
            }
        }

        $totalMatriculados = $datosPeriodo['total_estudiantes_matriculados_semestre'];
        $totalReincidentes = $datosPeriodo['total_estudiantes_dos_o_mas_veces'];
        $porcentaje = $totalMatriculados > 0 ? round(($totalReincidentes / $totalMatriculados) * 100, 2) : null;

        return [
            [
                'titulo' => 'Categoría más frecuente',
                'texto' => 'En '.$this->periodoSeleccionado.', la categoría "'.$categoriaMasFrecuente.'" concentra la mayor cantidad de estudiantes reincidentes: '.$conteos[$categoriaMasFrecuente].' estudiantes.',
                'tono' => 'atencion',
            ],
            [
                'titulo' => 'Peso sobre la matrícula',
                'texto' => $porcentaje === null
                    ? 'No se registraron estudiantes matriculados en el período para calcular la proporción.'
                    : $totalReincidentes.' de '.$totalMatriculados.' estudiantes matriculados ('.$this->formatearPorcentaje($porcentaje).') llevaron una misma experiencia curricular dos o más veces.',
                'tono' => $porcentaje !== null && $porcentaje > 0 ? 'atencion' : 'positivo',
            ],
            [
                'titulo' => 'Distribución por número de veces',
                'texto' => 'Segunda vez: '.$conteos['segunda vez'].'; tercera vez: '.$conteos['tercera vez'].'; cuarta vez: '.$conteos['cuarta vez'].' estudiantes.',
                'tono' => 'neutral',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $resultado
     * @return list<array{titulo: string, texto: string, tono: string}>
     */
    private function interpretacionesEgresados(
        ?array $resultado,
        IndicadorEgresadosService|IndicadorTutoriaService $servicio,
        bool $esGraficoComparativoPeriodos,
    ): array {
        if ($esGraficoComparativoPeriodos) {
            return $this->interpretacionesComparativoPeriodos($servicio);
        }

        if ($resultado === null || $resultado['porcentaje'] === null) {
            return [];
        }

        $interpretaciones = [[
            'titulo' => 'Resultado del período',
            'texto' => 'En '.mb_strtolower($resultado['etiqueta_periodo']).' '.$resultado['periodo'].', el resultado fue '.number_format($resultado['porcentaje'], 2, ',', '.').'% ('.$resultado['numerador'].' de '.$resultado['denominador'].').',
            'tono' => 'neutral',
        ]];

        $estado = $resultado['estado'] ?? null;

        if ($estado !== null) {
            $interpretaciones[] = [
                'titulo' => 'Estado del indicador',
                'texto' => match ($estado) {
                    'rojo' => 'El resultado se encuentra en estado crítico según los umbrales institucionales.',
                    'amarillo' => 'El resultado se encuentra en un estado de observación según los umbrales institucionales.',
                    default => 'El resultado se encuentra dentro del rango esperado según los umbrales institucionales.',
                },
                'tono' => $estado === 'rojo' || $estado === 'amarillo' ? 'atencion' : 'positivo',
            ];
        }

        if (! empty($resultado['desglose'])) {
            $mejorDimension = $resultado['desglose'][0];
            $peorDimension = $resultado['desglose'][0];

            foreach ($resultado['desglose'] as $dimension) {
                if ($dimension['porcentaje'] > $mejorDimension['porcentaje']) {
                    $mejorDimension = $dimension;
                }

                if ($dimension['porcentaje'] < $peorDimension['porcentaje']) {
                    $peorDimension = $dimension;
                }
            }

            $interpretaciones[] = [
                'titulo' => 'Dimensión con mejor resultado',
                'texto' => $mejorDimension['dimension'].' registra el mayor nivel de satisfacción: '.number_format($mejorDimension['porcentaje'], 2, ',', '.').'%.',
                'tono' => 'positivo',
            ];
            $interpretaciones[] = [
                'titulo' => 'Dimensión con menor resultado',
                'texto' => $peorDimension['dimension'].' registra el menor nivel de satisfacción: '.number_format($peorDimension['porcentaje'], 2, ',', '.').'%.',
                'tono' => 'atencion',
            ];
        }

        return $interpretaciones;
    }

    /** @return list<array{titulo: string, texto: string, tono: string}> */
    private function interpretacionesComparativoPeriodos(
        IndicadorEgresadosService|IndicadorTutoriaService $servicio,
    ): array {
        $periodos = array_reverse($servicio->periodos($this->indicador->codigo));
        $puntos = [];

        foreach ($periodos as $periodo) {
            $valor = $servicio->resultado($this->indicador->codigo, $periodo)['porcentaje'] ?? null;

            if ($valor !== null) {
                $puntos[] = ['periodo' => $periodo, 'valor' => (float) $valor];
            }
        }

        if ($puntos === []) {
            return [[
                'titulo' => 'Lectura pendiente',
                'texto' => 'Aún no hay resultados calculables para comparar entre períodos.',
                'tono' => 'neutral',
            ]];
        }

        $ultimo = $puntos[array_key_last($puntos)];
        $interpretaciones = [[
            'titulo' => 'Resultado más reciente',
            'texto' => 'En '.$ultimo['periodo'].', el resultado fue '.number_format($ultimo['valor'], 2, ',', '.').'%.',
            'tono' => 'neutral',
        ]];

        $mejorPunto = $puntos[0];
        $peorPunto = $puntos[0];

        foreach ($puntos as $punto) {
            if ($punto['valor'] > $mejorPunto['valor']) {
                $mejorPunto = $punto;
            }

            if ($punto['valor'] < $peorPunto['valor']) {
                $peorPunto = $punto;
            }
        }

        $interpretaciones[] = [
            'titulo' => 'Comparación entre períodos',
            'texto' => 'El valor más alto se registró en '.$mejorPunto['periodo'].' ('.number_format($mejorPunto['valor'], 2, ',', '.').'%) y el más bajo en '.$peorPunto['periodo'].' ('.number_format($peorPunto['valor'], 2, ',', '.').'%).',
            'tono' => 'neutral',
        ];

        if (count($puntos) < 2) {
            $interpretaciones[] = [
                'titulo' => 'Tendencia',
                'texto' => 'Se necesita al menos un segundo período con datos para identificar una tendencia.',
                'tono' => 'neutral',
            ];

            return $interpretaciones;
        }

        $anterior = $puntos[count($puntos) - 2];
        $variacion = round($ultimo['valor'] - $anterior['valor'], 2);

        if ($variacion > 0) {
            $textoTendencia = 'El resultado aumentó '.number_format($variacion, 2, ',', '.').' puntos porcentuales respecto a '.$anterior['periodo'].'.';
        } elseif ($variacion < 0) {
            $textoTendencia = 'El resultado disminuyó '.number_format(abs($variacion), 2, ',', '.').' puntos porcentuales respecto a '.$anterior['periodo'].'.';
        } else {
            $textoTendencia = 'El resultado se mantuvo sin variación respecto a '.$anterior['periodo'].'.';
        }

        $interpretaciones[] = [
            'titulo' => 'Tendencia',
            'texto' => $textoTendencia,
            'tono' => $variacion > 0 ? 'positivo' : ($variacion < 0 ? 'atencion' : 'neutral'),
        ];

        return $interpretaciones;
    }
}
