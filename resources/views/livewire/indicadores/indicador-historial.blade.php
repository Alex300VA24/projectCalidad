<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('quality-indicators.dashboard', ['proceso' => $indicador->macro_proceso]) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-[var(--indigo)] transition hover:underline">
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

    @if (session('quality-success'))
        <div class="flex items-center gap-3 rounded-lg border border-emerald-600/25 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            <svg class="size-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
            {{ session('quality-success') }}
        </div>
    @endif

    <div class="quality-view-switch" role="tablist" aria-label="Vista del indicador">
        <button id="history-table-tab" type="button" role="tab" aria-controls="history-table-panel" aria-selected="{{ $vista === 'tabla' ? 'true' : 'false' }}" wire:click="cambiarVista('tabla')"
            class="quality-view-tab">
            <svg class="size-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="1.5"/><path d="M3 10h18M9 4v16"/></svg>
            Histórico y registro
        </button>
        <button id="history-chart-tab" type="button" role="tab" aria-controls="history-chart-panel" aria-selected="{{ $vista === 'grafico' ? 'true' : 'false' }}" wire:click="cambiarVista('grafico')"
            class="quality-view-tab">
            <svg class="size-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V10m6 10V4m6 16v-7"/></svg>
            Ver gráfico
        </button>
    </div>

    @if ($vista === 'tabla')
        <section id="history-table-panel" class="overflow-hidden rounded-lg border border-[var(--border)] bg-[var(--surface)] shadow-sm" role="tabpanel" aria-labelledby="history-table-tab historial-heading">
            <div class="border-b border-[var(--border)] p-5">
                <span class="eyebrow">Registro semestral</span>
                <h2 id="historial-heading" class="mt-1">Historial de mediciones</h2>
                <p class="mb-0 mt-1 text-sm text-[var(--ink-soft)]">Completa los semestres sin datos para construir el histórico del indicador.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] border-collapse text-left">
                    <thead class="bg-[var(--surface-alt)] font-mono text-[11px] uppercase tracking-[0.08em] text-[var(--ink-soft)]">
                        <tr>
                            <th class="px-5 py-3">Periodo</th>
                            @if ($usaFormulaManual)
                                <th class="px-5 py-3">Detalle</th>
                            @endif
                            <th class="px-5 py-3">Valor</th>
                            @if ($esIndicadorRetencion)
                                <th class="px-5 py-3">Tasa deserción</th>
                            @endif
                            <th class="px-5 py-3">Meta</th>
                            <th class="px-5 py-3">Estado</th>
                            <th class="px-5 py-3"><span class="sr-only">Acción</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border)] [&>tr:nth-child(even)]:bg-[var(--surface-alt)]/40">
                        @forelse ($periodos as $periodo)
                            @php
                                $medicion = $mediciones->get($periodo);
                                $bloqueado = $medicion?->consolidada_en !== null;
                                $estilos = match ($medicion?->estado_cumplimiento) {
                                    'CONFORME' => ['label' => 'Conforme', 'dot' => 'bg-[#88E788]'],
                                    'OBSERVADO' => ['label' => 'Observado', 'dot' => 'bg-[#facc15]'],
                                    'CRITICO' => ['label' => 'Crítico', 'dot' => 'bg-[var(--red)]'],
                                    'NO_CONFORME' => ['label' => 'No conforme', 'dot' => 'bg-[var(--red)]'],
                                    default => ['label' => 'Sin datos', 'dot' => 'bg-slate-400'],
                                };
                            @endphp
                            <tr class="transition-colors hover:bg-indigo-50/60">
                                <td class="px-5 py-4 font-mono text-sm font-semibold">{{ $periodo }}</td>
                                @if ($usaFormulaManual)
                                    <td class="px-5 py-4 font-mono text-sm text-[var(--ink-soft)]">
                                        @if ($medicion && ($n = data_get($medicion->datos_fuente, 'numerador')) !== null)
                                            {{ $n }} / {{ data_get($medicion->datos_fuente, 'denominador') }}
                                        @else
                                            &mdash;
                                        @endif
                                    </td>
                                @endif
                                <td class="px-5 py-4 font-mono text-base font-semibold">{{ $medicion ? number_format((float) $medicion->valor_medido, 1).$unidad : '—' }}</td>
                                @if ($esIndicadorRetencion)
                                    <td class="px-5 py-4 font-mono text-base font-semibold">{{ $medicion ? number_format(100 - (float) $medicion->valor_medido, 1).$unidad : '—' }}</td>
                                @endif
                                <td class="px-5 py-4 font-mono text-sm text-[var(--ink-soft)]">{{ $indicador->meta_institucional === null ? '—' : number_format((float) $indicador->meta_institucional, 0).$unidad }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex size-4 items-center justify-center rounded-full ring-4 ring-black/5" title="{{ $estilos['label'] }}">
                                        <span class="size-3.5 shrink-0 rounded-full {{ $estilos['dot'] }}" aria-hidden="true"></span>
                                        <span class="sr-only">{{ $estilos['label'] }}</span>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if ($bloqueado)
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--ink-soft)]">
                                            <svg class="size-3.5" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="1.5"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                                            Consolidado
                                        </span>
                                    @else
                                        <button type="button" wire:click="editar('{{ $periodo }}')" class="min-h-11 cursor-pointer rounded-lg border border-[var(--border)] px-3 text-sm font-semibold transition hover:border-indigo-500 hover:text-indigo-700 focus-visible:ring-2 focus-visible:ring-indigo-500">{{ $medicion ? 'Editar' : 'Registrar' }}</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ ($usaFormulaManual ? 6 : 5) + ($esIndicadorRetencion ? 1 : 0) }}" class="px-5 py-10 text-center text-sm text-[var(--ink-soft)]">Aún no hay periodos académicos disponibles.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="border-t border-[var(--border)] px-5 py-3 text-xs leading-5 text-[var(--ink-soft)]">
                <strong class="font-semibold text-[var(--ink)]">Nota.</strong>
                El color indica el estado de cumplimiento frente a la meta institucional:
                <span class="inline-flex items-center gap-1"><span class="size-2 rounded-full bg-[#88E788]" aria-hidden="true"></span>verde, conforme (cumple la meta)</span>;
                <span class="inline-flex items-center gap-1"><span class="size-2 rounded-full bg-[#facc15]" aria-hidden="true"></span>amarillo, observado (próximo a la meta)</span>;
                <span class="inline-flex items-center gap-1"><span class="size-2 rounded-full bg-[var(--red)]" aria-hidden="true"></span>rojo, crítico (no cumple la meta)</span>;
                <span class="inline-flex items-center gap-1"><span class="size-2 rounded-full bg-slate-400" aria-hidden="true"></span>gris, sin datos (medición aún no registrada)</span>.
            </p>
        </section>
    @else
        <section id="history-chart-panel" class="rounded-lg border border-[var(--border)] bg-[var(--surface)] p-5 shadow-sm" role="tabpanel" aria-labelledby="history-chart-tab chart-heading">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <span class="eyebrow">Tendencia semestral</span>
                    <h2 id="chart-heading" class="mt-1">Evolución frente a la meta</h2>
                    <p class="mb-0 mt-1 text-sm text-[var(--ink-soft)]">Eje vertical fijo de 0% a 100%. Cada barra es un semestre; la línea punteada marca la meta institucional.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="moverVentana(1)" @disabled(! $puedeAnterior) aria-label="Ver periodos anteriores" class="grid size-11 shrink-0 cursor-pointer place-items-center rounded-lg border border-[var(--border)] transition hover:border-indigo-500 hover:text-indigo-700 disabled:cursor-not-allowed disabled:opacity-40">
                        <svg class="size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <button type="button" wire:click="moverVentana(-1)" @disabled(! $puedeSiguiente) aria-label="Ver periodos recientes" class="grid size-11 shrink-0 cursor-pointer place-items-center rounded-lg border border-[var(--border)] transition hover:border-indigo-500 hover:text-indigo-700 disabled:cursor-not-allowed disabled:opacity-40">
                        <svg class="size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </div>

            @if ($ultimoValor !== null)
                <div class="mt-4 inline-flex flex-wrap items-center gap-2 rounded-lg border border-[var(--border)] bg-[var(--surface-alt)] px-3.5 py-2 text-sm">
                    <span class="text-[var(--ink-soft)]">Último periodo registrado ({{ $ultimoPeriodo }}):</span>
                    <strong class="font-mono">{{ number_format((float) $ultimoValor, 1) }}{{ $unidad }}</strong>
                    @if ($ultimoCumple !== null)
                        <span @class(['inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold', 'bg-[#88E788] text-[#1f7a1f]' => $ultimoCumple, 'bg-[var(--red-soft)] text-[var(--red)]' => ! $ultimoCumple])>
                            {{ $ultimoCumple ? 'Cumple la meta' : 'Por debajo de la meta' }}
                        </span>
                    @endif
                </div>
            @endif

            <div class="mx-auto mt-6 h-80 w-full max-w-3xl" wire:key="indicador-chart-{{ $indicador->id }}-{{ $ventanaOffset }}" x-init="$nextTick(() => window.renderQualityCharts?.($el))">
                <canvas data-quality-chart data-chart-percent="1" data-chart-config="{{ json_encode($chartConfig) }}" aria-label="Gráfico de barras de {{ $indicador->nombre }}" @if ($esIndicadorSilabos || $esIndicadorRetencion || $esIndicadorRepitencia) aria-describedby="syllabus-chart-interpretation" @endif role="img"></canvas>
            </div>

            <div class="mx-auto mt-4 flex max-w-3xl flex-wrap items-center justify-center gap-x-5 gap-y-2 border-t border-[var(--border)] pt-4 text-xs font-medium text-[var(--ink-soft)]">
                @if ($meta !== null)
                    <span class="inline-flex items-center gap-1.5">
                        <span class="inline-block h-0 w-5 border-t-2 border-dashed" style="border-color:#c0362c" aria-hidden="true"></span>
                        Meta institucional ({{ number_format($meta, 0) }}{{ $unidad }})
                    </span>
                @endif
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-2.5 shrink-0 rounded-sm" style="background-color:#88E788" aria-hidden="true"></span>
                    Cumple la meta
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="size-2.5 shrink-0 rounded-sm" style="background-color:#c0362c" aria-hidden="true"></span>
                    No cumple la meta
                </span>
            </div>

            <p class="mx-auto mt-2 max-w-3xl text-center text-xs leading-5 text-[var(--ink-soft)]">
                <strong class="font-semibold text-[var(--ink)]">Nota.</strong> Elaboración propia a partir del registro semestral del indicador.
            </p>

            @if ($esIndicadorSilabos || $esIndicadorRetencion || $esIndicadorRepitencia)
                @php
                    $interpretaciones = match (true) {
                        $esIndicadorSilabos => $interpretacionesSilabos,
                        $esIndicadorRetencion => $interpretacionesRetencion,
                        default => $interpretacionesRepitencia,
                    };
                @endphp
                <aside id="syllabus-chart-interpretation" class="mt-6 border-t border-[var(--border)] pt-5" aria-labelledby="syllabus-interpretation-heading">
                    <div class="max-w-2xl">
                        <span class="eyebrow">Lectura del resultado</span>
                        <h3 id="syllabus-interpretation-heading" class="mt-1 text-lg">Interpretación del gráfico</h3>
                        <p class="mb-0 mt-1 text-sm leading-6 text-[var(--ink-soft)]">La lectura relaciona el último semestre medido con la meta institucional y con el periodo anterior visible.</p>
                    </div>
                    <div class="mt-4 grid gap-3 lg:grid-cols-3">
                        @foreach ($interpretaciones as $interpretacion)
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

    @if ($periodoEditando)
        <div class="fixed inset-0 z-[110] grid place-items-end sm:place-items-center" role="presentation" x-on:keydown.escape.window="$wire.cerrarForm()">
            <button type="button" class="absolute inset-0 cursor-pointer bg-slate-950/70 backdrop-blur-sm" wire:click="cerrarForm" aria-label="Cerrar formulario"></button>
            <section class="relative w-full rounded-t-2xl bg-[var(--surface)] p-5 shadow-2xl sm:w-[min(92vw,480px)] sm:rounded-xl sm:p-7" role="dialog" aria-modal="true" aria-labelledby="medicion-title" x-init="$nextTick(() => $el.querySelector('input')?.focus())">
                <div class="flex items-start justify-between gap-4">
                    <div><span class="eyebrow">{{ $periodoEditando }}</span><h2 id="medicion-title" class="mt-1">Registrar valor</h2></div>
                    <button type="button" wire:click="cerrarForm" class="grid size-11 cursor-pointer place-items-center rounded-lg border border-[var(--border)] transition hover:border-indigo-500 hover:text-indigo-700" aria-label="Cerrar"><svg class="size-5" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                </div>
                <form wire:submit="guardar" class="mt-6 grid gap-5">
                    @if ($usaFormulaManual)
                        <label class="grid gap-2 text-sm font-semibold">
                            <span>{{ $etiquetasFormula['numerador_label'] ?? 'Numerador' }}</span>
                            <input type="number" min="0" step="1" wire:model="numerador" class="min-h-11 rounded-lg border border-[var(--border)] bg-[var(--surface)] px-3 font-mono text-base font-normal outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            @error('numerador') <span class="text-sm font-normal text-red-700">{{ $message }}</span> @enderror
                        </label>
                        <label class="grid gap-2 text-sm font-semibold">
                            <span>{{ $etiquetasFormula['denominador_label'] ?? 'Denominador' }}</span>
                            <input type="number" min="1" step="1" wire:model="denominador" class="min-h-11 rounded-lg border border-[var(--border)] bg-[var(--surface)] px-3 font-mono text-base font-normal outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            @error('denominador') <span class="text-sm font-normal text-red-700">{{ $message }}</span> @enderror
                        </label>
                        <p class="mb-0 text-xs text-[var(--ink-soft)]">El porcentaje se calcula automáticamente al guardar.</p>
                    @else
                        <label class="grid gap-2 text-sm font-semibold">
                            <span>Valor medido (%)</span>
                            <input type="number" min="0" max="100" step="0.01" wire:model="valorManual" class="min-h-11 rounded-lg border border-[var(--border)] bg-[var(--surface)] px-3 font-mono text-base font-normal outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            @error('valorManual') <span class="text-sm font-normal text-red-700">{{ $message }}</span> @enderror
                        </label>
                    @endif
                    <div class="flex flex-col-reverse gap-2 border-t border-[var(--border)] pt-5 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="cerrarForm" class="min-h-11 cursor-pointer rounded-lg border border-[var(--border)] px-5 font-semibold transition hover:border-indigo-500">Cancelar</button>
                        <button type="submit" class="min-h-11 cursor-pointer rounded-lg bg-emerald-700 px-5 font-semibold text-white transition hover:bg-emerald-800 disabled:cursor-wait disabled:opacity-70" wire:loading.attr="disabled">Guardar valor</button>
                    </div>
                </form>
            </section>
        </div>
    @endif
</div>
