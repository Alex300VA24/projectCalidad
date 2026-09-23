@php
    $porcentaje = $resultado && $resultado['porcentaje'] !== null
        ? number_format($resultado['porcentaje'], 2, '.', '')
        : null;
    $estado = $resultado['estado'] ?? null;
    $muestraSelectorPeriodo = ! $esGraficoComparativoPeriodos || $vista === 'tabla';
@endphp

<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('quality-indicators.dashboard', ['proceso' => $indicador->macro_proceso]) }}" class="inline-flex min-h-11 items-center gap-1.5 text-sm font-semibold text-[var(--indigo)] transition hover:underline focus-visible:ring-2 focus-visible:ring-indigo-500">
            <svg class="size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            Volver a Indicadores
        </a>

        <x-indicador-navegador :indicador="$indicador" :indicadores="$indicadoresDisponibles" :codigo-anterior="$codigoIndicadorAnterior" :codigo-siguiente="$codigoIndicadorSiguiente" />
    </div>

    <header class="relative overflow-hidden rounded-xl bg-[#172554] px-5 py-6 text-white shadow-[0_18px_50px_rgba(23,37,84,.18)] sm:px-7">
        <div class="pointer-events-none absolute inset-0 opacity-15" aria-hidden="true" style="background-image:radial-gradient(circle,#fff 1px,transparent 1px);background-size:22px 22px;mask-image:linear-gradient(to left,#000,transparent 80%)"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0 flex-1">
                <span class="rounded bg-white/10 px-2 py-1 font-mono text-[11px] font-semibold text-emerald-200">{{ $indicador->codigo }}</span>
                <h1 class="mt-3 text-balance text-2xl font-semibold leading-tight tracking-[-0.02em] sm:text-3xl">{{ $indicador->nombre }}</h1>
                <p class="mb-0 mt-3 text-sm font-semibold text-emerald-100">Proceso: {{ $indicador->proceso }}</p>
            </div>
            <div class="rounded-lg border border-white/15 bg-white/5 p-4 lg:w-96 lg:shrink-0">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-white/50">Finalidad</span>
                <p class="mb-0 mt-1 text-sm leading-6 text-white/80">{{ $indicador->finalidad }}</p>
                <span class="mt-4 block text-[11px] font-semibold uppercase tracking-wide text-white/50">Fórmula</span>
                <p class="mb-0 mt-1 font-mono text-xs leading-5 text-white/70">{{ $indicador->formula_texto }}</p>
                @if ($documentoIndicador && ($documentoIndicador->preview_url || $documentoIndicador->external_url))
                    <button class="mt-4 inline-flex min-h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-lg bg-white px-4 text-sm font-semibold text-[#172554] transition hover:bg-emerald-100" type="button" data-open-pdf data-title="{{ $documentoIndicador->title ?? 'Documento del indicador' }}" data-preview="{{ $documentoIndicador->preview_url ?? $documentoIndicador->external_url }}" data-external="{{ $documentoIndicador->external_url ?? $documentoIndicador->preview_url }}">
                        Ver documento
                        <svg class="size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                @endif
            </div>
        </div>
    </header>

    <section class="relative rounded-lg border border-[var(--border)] bg-[var(--surface)] p-5 shadow-sm" aria-labelledby="graduate-filter-heading">
        <div @class([
            'grid gap-5 lg:items-end',
            'lg:grid-cols-[minmax(0,1fr)_240px]' => $muestraSelectorPeriodo,
        ])>
            <div>
                <span class="eyebrow">Medición {{ mb_strtolower($indicador->frecuencia) }}</span>
                <h2 id="graduate-filter-heading" class="mt-1">{{ $tituloResultado }}</h2>
                <p class="mb-0 mt-1 text-sm leading-6 text-[var(--ink-soft)]">{{ $descripcionResultado }}</p>
            </div>
            @if ($muestraSelectorPeriodo)
                <label class="grid gap-1.5 text-sm font-semibold">
                    <span>{{ $resultado['etiqueta_periodo'] ?? 'Período' }}</span>
                    <select wire:model.live="periodoSeleccionado" wire:loading.attr="disabled" wire:target="periodoSeleccionado" class="min-h-11 w-full cursor-pointer rounded-lg border border-[var(--border)] bg-[var(--surface-alt)] px-3 font-mono text-base text-[var(--ink)] outline-none transition hover:border-indigo-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-wait disabled:opacity-70">
                        @foreach ($periodosDesaprobados as $periodo)
                            <option value="{{ $periodo }}">{{ $periodo }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
        </div>
        @if ($muestraSelectorPeriodo)
            <div class="absolute inset-0 hidden items-center justify-center rounded-lg bg-[var(--surface)]/90" wire:loading.flex wire:target="periodoSeleccionado" role="status">
                <span class="inline-flex items-center gap-2 rounded-lg border border-[var(--border)] bg-[var(--surface)] px-4 py-3 text-sm font-semibold shadow-sm"><svg class="size-4 animate-spin motion-reduce:animate-none" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-6.22-8.56"/></svg>Cargando período…</span>
            </div>
        @endif
    </section>

    @if ($errorDatosDesaprobados)
        <section class="rounded-lg border border-red-300 bg-red-50 p-5 text-red-800" role="alert">
            <h2 class="text-base font-semibold">No se pudieron cargar los datos</h2>
            <p class="mb-0 mt-1 text-sm leading-6">{{ $errorDatosDesaprobados }}</p>
        </section>
    @elseif ($periodosDesaprobados === [])
        <section class="rounded-lg border border-dashed border-[var(--border)] bg-[var(--surface)] p-10 text-center">
            <h2 class="text-lg font-semibold">Sin períodos registrados</h2>
            <p class="mb-0 mt-1 text-sm text-[var(--ink-soft)]">El archivo JSON no contiene cohortes, promociones o períodos de encuesta.</p>
        </section>
    @elseif ($resultado === null)
        <section class="rounded-lg border border-dashed border-[var(--border)] bg-[var(--surface)] p-10 text-center">
            <h2 class="text-lg font-semibold">Sin datos para el período</h2>
            <p class="mb-0 mt-1 text-sm text-[var(--ink-soft)]">Selecciona otro período o verifica la fuente institucional.</p>
        </section>
    @else
        <section @class([
            'grid gap-4 rounded-lg border p-5 shadow-sm sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center',
            'border-amber-300 bg-amber-50 text-amber-950' => $estado === 'amarillo',
            'border-red-300 bg-red-50 text-red-950' => $estado === 'rojo',
            'border-emerald-300 bg-emerald-50 text-emerald-950' => ! in_array($estado, ['amarillo', 'rojo'], true),
        ]) aria-labelledby="graduate-result-heading">
            <div>
                <span class="font-mono text-[11px] font-semibold uppercase tracking-[0.08em] opacity-80">Resultado de {{ mb_strtolower($resultado['etiqueta_periodo']) }} {{ $resultado['periodo'] }}</span>
                <h2 id="graduate-result-heading" class="mt-1 text-lg">{{ $resultado['etiqueta_numerador'] }}</h2>
                <p class="mb-0 mt-1 text-sm leading-6 opacity-80">{{ $resultado['numerador'] }} de {{ $resultado['denominador'] }} registros considerados.</p>
                @if ($estado)
                    <span class="mt-3 inline-flex rounded-full border border-current/20 px-2.5 py-1 font-mono text-[11px] font-semibold uppercase tracking-[0.08em]">Estado: {{ $estado }}</span>
                @endif
            </div>
            <strong class="font-mono text-4xl tracking-[-0.04em]">{{ $porcentaje === null ? 'Sin datos' : $porcentaje.' %' }}</strong>
        </section>

        <div class="quality-view-switch" role="tablist" aria-label="Vista del indicador">
            <button id="graduate-table-tab" type="button" role="tab" aria-controls="graduate-table-panel" aria-selected="{{ $vista === 'tabla' ? 'true' : 'false' }}" wire:click="cambiarVista('tabla')" class="quality-view-tab">
                <svg class="size-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="1.5"/><path d="M3 10h18M9 4v16"/></svg>
                Ver datos
            </button>
            <button id="graduate-chart-tab" type="button" role="tab" aria-controls="graduate-chart-panel" aria-selected="{{ $vista === 'grafico' ? 'true' : 'false' }}" wire:click="cambiarVista('grafico')" class="quality-view-tab">
                <svg class="size-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V10m6 10V4m6 16v-7"/></svg>
                Ver gráfico
            </button>
        </div>

        @if ($vista === 'tabla')
            <section id="graduate-table-panel" class="overflow-hidden rounded-lg border border-[var(--border)] bg-[var(--surface)] shadow-sm" role="tabpanel" aria-labelledby="graduate-table-tab graduate-table-heading">
                <div class="border-b border-[var(--border)] p-5">
                    <span class="eyebrow">{{ $resultado['etiqueta_periodo'] }} {{ $resultado['periodo'] }}</span>
                    <h2 id="graduate-table-heading" class="mt-1">Valores usados en la fórmula</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px] border-collapse text-left text-sm">
                        <thead class="bg-[var(--surface-alt)] font-mono text-[11px] uppercase tracking-[0.08em] text-[var(--ink-soft)]">
                            <tr><th class="px-5 py-3">Período</th><th class="px-5 py-3">Numerador</th><th class="px-5 py-3">Denominador</th><th class="px-5 py-3 text-right">Resultado</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-5 py-4 font-mono font-semibold">{{ $resultado['periodo'] }}</td>
                                <td class="px-5 py-4"><span class="block font-mono font-semibold">{{ $resultado['numerador'] }}</span><span class="mt-1 block text-xs text-[var(--ink-soft)]">{{ $resultado['etiqueta_numerador'] }}</span></td>
                                <td class="px-5 py-4"><span class="block font-mono font-semibold">{{ $resultado['denominador'] }}</span><span class="mt-1 block text-xs text-[var(--ink-soft)]">{{ $resultado['etiqueta_denominador'] }}</span></td>
                                <td class="px-5 py-4 text-right font-mono font-semibold">{{ $porcentaje === null ? 'Sin datos' : $porcentaje.' %' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if ($resultado['desglose'] !== [])
                    <div class="border-t border-[var(--border)] p-5">
                        <h3 class="text-base font-semibold">Satisfacción por competencia específica</h3>
                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full min-w-[560px] border-collapse text-left text-sm">
                                <thead class="bg-[var(--surface-alt)] font-mono text-[11px] uppercase tracking-[0.08em] text-[var(--ink-soft)]"><tr><th class="px-4 py-3">Dimensión</th><th class="px-4 py-3 text-right">Satisfacción</th></tr></thead>
                                <tbody class="divide-y divide-[var(--border)]">
                                    @foreach ($resultado['desglose'] as $dimension)
                                        <tr><td class="px-4 py-3 font-medium">{{ $dimension['dimension'] }}</td><td class="px-4 py-3 text-right font-mono font-semibold">{{ number_format($dimension['porcentaje'], 2, '.', '') }} %</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <p class="border-t border-[var(--border)] px-5 py-3 text-xs leading-5 text-[var(--ink-soft)]"><strong class="font-semibold text-[var(--ink)]">Fuente.</strong> Archivo institucional <span class="font-mono">{{ $resultado['fuente'] }}</span>.</p>
            </section>
        @else
            <section id="graduate-chart-panel" class="rounded-lg border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm sm:p-5" role="tabpanel" aria-labelledby="graduate-chart-tab graduate-chart-heading">
                <div>
                    <span class="eyebrow">Escala de 0 % a 100 %</span>
                    <h2 id="graduate-chart-heading" class="mt-1">{{ $esGraficoComparativoPeriodos ? 'Comparativo por '.$etiquetaPeriodoGrafico : 'Proporción del indicador' }}</h2>
                    <p class="mb-0 mt-1 text-sm text-[var(--ink-soft)]">{{ $esGraficoComparativoPeriodos ? 'Pasa el cursor o toca una barra para consultar el valor de cada '.$etiquetaPeriodoGrafico.'.' : 'Pasa el cursor o toca el anillo para consultar valores absolutos y porcentuales.' }}</p>
                </div>
                <figure class="mx-auto mt-6 max-w-3xl" aria-labelledby="graduate-figure-title" aria-describedby="graduate-chart-note">
                    <figcaption id="graduate-figure-title" class="text-center text-sm font-semibold">{{ $indicador->nombre }} · {{ $esGraficoComparativoPeriodos ? 'Todos los '.$etiquetaPeriodosGrafico.' registrados' : $resultado['periodo'] }}</figcaption>
                    <div @class(['mx-auto mt-4 h-80 w-full', 'max-w-3xl' => $esGraficoComparativoPeriodos, 'max-w-xl' => ! $esGraficoComparativoPeriodos]) wire:key="graduate-chart-{{ $indicador->codigo }}-{{ $periodoSeleccionado }}" x-init="$nextTick(() => window.renderQualityCharts?.($el))">
                        <canvas data-quality-chart @if ($esGraficoComparativoPeriodos) data-chart-percent="1" @endif data-chart-config="{{ json_encode($chartConfig) }}" aria-label="{{ $esGraficoComparativoPeriodos ? 'Gráfico de barras comparativo por '.$etiquetaPeriodoGrafico.'. La vista Ver datos contiene los valores equivalentes.' : 'Gráfico de anillo. Resultado '.($porcentaje ?? 'sin datos').' por ciento para '.$resultado['periodo'].'. La vista Ver datos contiene los valores equivalentes.' }}" role="img"></canvas>
                    </div>
                    <p id="graduate-chart-note" class="mt-3 text-center text-xs leading-5 text-[var(--ink-soft)]"><strong class="font-semibold text-[var(--ink)]">Nota.</strong> Porcentaje calculado en vivo: numerador dividido entre denominador por 100.</p>
                </figure>

                @if ($breakdownChartConfig)
                    <figure class="mx-auto mt-8 max-w-5xl border-t border-[var(--border)] pt-6" aria-labelledby="employer-breakdown-title">
                        <figcaption id="employer-breakdown-title" class="text-sm font-semibold">Satisfacción por competencia específica</figcaption>
                        <div class="mt-4 h-[360px] min-w-0" wire:key="employer-breakdown-{{ $periodoSeleccionado }}" x-init="$nextTick(() => window.renderQualityCharts?.($el))">
                            <canvas data-quality-chart data-chart-horizontal-percent="1" data-chart-config="{{ json_encode($breakdownChartConfig) }}" aria-label="Gráfico de barras del nivel de satisfacción por competencia específica. La vista Ver datos contiene la tabla equivalente." role="img"></canvas>
                        </div>
                    </figure>
                @endif

                @if ($interpretacionesEgresados !== [])
                    <aside class="mx-auto mt-8 max-w-5xl border-t border-[var(--border)] pt-6" aria-labelledby="graduate-interpretation-heading">
                        <div class="max-w-2xl">
                            <span class="eyebrow">Lectura del resultado</span>
                            <h3 id="graduate-interpretation-heading" class="mt-1 text-lg">Interpretación del gráfico</h3>
                            <p class="mb-0 mt-1 text-sm leading-6 text-[var(--ink-soft)]">{{ $esGraficoComparativoPeriodos ? 'La lectura compara el resultado más reciente con los demás '.$etiquetaPeriodosGrafico.' registrados.' : 'La lectura describe el resultado del período sin reemplazar los valores absolutos.' }}</p>
                        </div>
                        <div class="mt-4 grid gap-3 lg:grid-cols-3">
                            @foreach ($interpretacionesEgresados as $interpretacion)
                                <article @class([
                                    'rounded-lg border p-4',
                                    'border-[#88E788] bg-[#eafbea]' => $interpretacion['tono'] === 'positivo',
                                    'border-[#facc15] bg-[#fef9c3]' => $interpretacion['tono'] === 'atencion',
                                    'border-[var(--border)] bg-[var(--surface-alt)]' => $interpretacion['tono'] === 'neutral',
                                ])>
                                    <div class="flex items-start gap-3">
                                        <span @class([
                                            'grid size-9 shrink-0 place-items-center rounded-full',
                                            'bg-[var(--surface)] text-[#1f7a1f]' => $interpretacion['tono'] === 'positivo',
                                            'bg-[var(--surface)] text-[#92710b]' => $interpretacion['tono'] === 'atencion',
                                            'bg-[var(--indigo-soft)] text-[var(--indigo)]' => $interpretacion['tono'] === 'neutral',
                                        ]) aria-hidden="true">
                                            <svg class="size-4" viewBox="0 0 24 24"><path d="M4 19V9m5 10V5m5 14v-8m5 8V7"/></svg>
                                        </span>
                                        <div>
                                            <h4 class="text-sm font-semibold">{{ $interpretacion['titulo'] }}</h4>
                                            <p class="mb-0 mt-1 text-sm leading-6 text-[var(--ink-soft)]">{{ $interpretacion['texto'] }}</p>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </aside>
                @endif
            </section>
        @endif
    @endif
</div>
