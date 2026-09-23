<div class="flex flex-col gap-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('quality-indicators.dashboard', ['proceso' => $indicador->macro_proceso]) }}" class="inline-flex items-center gap-1.5 self-start text-sm font-semibold text-[var(--indigo)] transition hover:underline focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <svg class="size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            Volver a Indicadores
        </a>

        <x-indicador-navegador :indicador="$indicador" :indicadores="$indicadoresDisponibles" :codigo-anterior="$codigoIndicadorAnterior" :codigo-siguiente="$codigoIndicadorSiguiente" />
    </div>

    <header class="relative overflow-hidden rounded-xl bg-[#172554] px-5 py-6 text-white shadow-[0_18px_50px_rgba(23,37,84,.18)] sm:px-7">
        <div class="pointer-events-none absolute inset-0 opacity-15" aria-hidden="true" style="background-image:radial-gradient(circle,#fff 1px,transparent 1px);background-size:22px 22px;mask-image:linear-gradient(to left,#000,transparent 80%)"></div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded bg-white/10 px-2 py-1 font-mono text-[11px] font-semibold text-emerald-200">{{ $indicador->codigo }}</span>
                    <span class="rounded bg-white/10 px-2 py-1 text-xs font-semibold text-white/80">Porcentaje · Semestral</span>
                </div>
                <h1 class="mt-3 text-balance text-2xl font-semibold leading-tight tracking-[-0.02em] sm:text-3xl">{{ $indicador->nombre }}</h1>
            </div>
            <div class="rounded-lg border border-white/15 bg-white/5 p-4 lg:w-96 lg:shrink-0">
                <span class="text-[11px] font-semibold uppercase tracking-wide text-white/50">Finalidad</span>
                <p class="mb-0 mt-1 text-sm leading-6 text-white/80">{{ $indicador->finalidad }}</p>
                <span class="mt-4 block text-[11px] font-semibold uppercase tracking-wide text-white/50">Fórmula</span>
                <p class="mb-0 mt-1 font-mono text-xs leading-5 text-white/70">{{ $indicador->formula_texto }}</p>
                @if ($documentoIndicador && ($documentoIndicador->preview_url || $documentoIndicador->external_url))
                    <button class="mt-4 inline-flex min-h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-lg bg-white px-4 text-sm font-semibold text-[#172554] transition hover:bg-emerald-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" type="button" data-open-pdf data-title="{{ $documentoIndicador->title ?? 'Documento del indicador' }}" data-preview="{{ $documentoIndicador->preview_url ?? $documentoIndicador->external_url }}" data-external="{{ $documentoIndicador->external_url ?? $documentoIndicador->preview_url }}">
                        Ver documento
                        <svg class="size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                @endif
            </div>
        </div>
    </header>

    <section class="rounded-lg border border-[var(--border)] bg-[var(--surface)] p-5 shadow-sm" aria-labelledby="reincidencia-filter-heading">
        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_240px] lg:items-end">
            <div>
                <span class="eyebrow">Evaluación del Estudiante</span>
                <h2 id="reincidencia-filter-heading" class="mt-1">Resultado general por semestre</h2>
                <p class="mb-0 mt-1 text-sm leading-6 text-[var(--ink-soft)]">Los datos son agregados. No identifican estudiantes ni experiencias curriculares individuales.</p>
            </div>
            <label class="grid gap-1.5 text-sm font-semibold">
                <span>Período</span>
                <select wire:model.live="periodoSeleccionado" wire:loading.attr="disabled" wire:target="periodoSeleccionado" @disabled($periodosDesaprobados === []) class="min-h-11 w-full cursor-pointer rounded-lg border border-[var(--border)] bg-[var(--surface-alt)] px-3 font-mono text-base text-[var(--ink)] outline-none transition hover:border-indigo-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-70">
                    @forelse ($periodosDesaprobados as $periodo)
                        <option value="{{ $periodo }}">{{ $periodo }}</option>
                    @empty
                        <option value="">Sin períodos</option>
                    @endforelse
                </select>
            </label>
        </div>
    </section>

    @if ($errorDatosDesaprobados)
        <section class="rounded-lg border border-red-300 bg-red-50 p-5 text-red-800" role="alert">
            <h2 class="text-base font-semibold">No se pudieron cargar los datos</h2>
            <p class="mb-0 mt-1 text-sm leading-6">{{ $errorDatosDesaprobados }}</p>
        </section>
    @elseif ($periodosDesaprobados === [])
        <section class="rounded-lg border border-dashed border-[var(--border)] bg-[var(--surface)] p-10 text-center">
            <h2 class="text-lg font-semibold">Sin períodos registrados</h2>
            <p class="mb-0 mt-1 text-sm text-[var(--ink-soft)]">No existen períodos registrados.</p>
        </section>
    @elseif ($datosPeriodo === null)
        <section class="rounded-lg border border-dashed border-[var(--border)] bg-[var(--surface)] p-10 text-center">
            <h2 class="text-lg font-semibold">Sin información</h2>
            <p class="mb-0 mt-1 text-sm text-[var(--ink-soft)]">No existen datos para este período.</p>
        </section>
    @else
        <section class="relative grid gap-4 lg:grid-cols-[minmax(260px,.72fr)_minmax(0,1.28fr)]" aria-live="polite" aria-busy="false">
            <div class="flex flex-col gap-4">
                <article class="rounded-xl bg-[#172554] p-5 text-white shadow-[0_12px_32px_rgba(23,37,84,.16)] sm:p-6">
                    <span class="text-sm font-semibold text-emerald-200">Resultado del indicador</span>
                    <p class="mb-0 mt-3 font-mono text-4xl font-bold tracking-[-0.04em] sm:text-5xl">{{ $porcentajeIndicador }} %</p>
                    <p class="mb-0 mt-3 text-sm leading-6 text-white/75">{{ $datosPeriodo['total_estudiantes_dos_o_mas_veces'] }} estudiantes de un total de {{ $datosPeriodo['total_estudiantes_matriculados_semestre'] }} matriculados.</p>
                </article>

                <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                    <div class="rounded-lg border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm">
                        <dt class="text-sm leading-5 text-[var(--ink-soft)]">Total de estudiantes que llevaron un curso dos o más veces</dt>
                        <dd class="mb-0 mt-2 font-mono text-2xl font-semibold text-[var(--ink)]">{{ $datosPeriodo['total_estudiantes_dos_o_mas_veces'] }} <span class="text-sm font-medium text-[var(--ink-soft)]">estudiantes</span></dd>
                    </div>
                    <div class="rounded-lg border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm">
                        <dt class="text-sm leading-5 text-[var(--ink-soft)]">Total de estudiantes matriculados en el semestre</dt>
                        <dd class="mb-0 mt-2 font-mono text-2xl font-semibold text-[var(--ink)]">{{ $datosPeriodo['total_estudiantes_matriculados_semestre'] }} <span class="text-sm font-medium text-[var(--ink-soft)]">estudiantes</span></dd>
                    </div>
                </dl>
            </div>

            <article class="min-w-0 rounded-lg border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm sm:p-5">
                <div>
                    <span class="eyebrow">Período {{ $periodoSeleccionado }}</span>
                    <h2 class="mt-1">Cantidad de estudiantes por número de matrícula</h2>
                    <p class="mb-0 mt-1 text-sm leading-6 text-[var(--ink-soft)]">Las barras muestran cantidades absolutas, no porcentajes.</p>
                </div>

                <figure class="mt-5" aria-labelledby="reincidencia-chart-title">
                    <figcaption id="reincidencia-chart-title" class="sr-only">Estudiantes que llevaron un curso por segunda, tercera o cuarta vez en {{ $periodoSeleccionado }}</figcaption>
                    <div class="relative h-[320px] min-w-0 sm:h-[380px]" wire:key="reincidencia-chart-{{ $periodoSeleccionado }}" x-init="$nextTick(() => window.renderQualityCharts?.($el))">
                        <canvas data-quality-chart data-chart-student-count="1" data-chart-period="{{ $periodoSeleccionado }}" data-chart-config="{{ json_encode($chartConfig) }}" aria-label="Segunda vez: {{ $datosPeriodo['segunda_vez'] }} estudiantes; tercera vez: {{ $datosPeriodo['tercera_vez'] }} estudiantes; cuarta vez: {{ $datosPeriodo['cuarta_vez'] }} estudiantes, período {{ $periodoSeleccionado }}" role="img"></canvas>
                    </div>
                    <dl class="mt-4 grid grid-cols-1 gap-2 text-sm sm:grid-cols-3">
                        <div class="rounded-lg bg-[var(--surface-alt)] px-3 py-2"><dt class="text-[var(--ink-soft)]">Segunda vez</dt><dd class="mb-0 mt-1 font-mono font-semibold">{{ $datosPeriodo['segunda_vez'] }}</dd></div>
                        <div class="rounded-lg bg-[var(--surface-alt)] px-3 py-2"><dt class="text-[var(--ink-soft)]">Tercera vez</dt><dd class="mb-0 mt-1 font-mono font-semibold">{{ $datosPeriodo['tercera_vez'] }}</dd></div>
                        <div class="rounded-lg bg-[var(--surface-alt)] px-3 py-2"><dt class="text-[var(--ink-soft)]">Cuarta vez</dt><dd class="mb-0 mt-1 font-mono font-semibold">{{ $datosPeriodo['cuarta_vez'] }}</dd></div>
                    </dl>
                </figure>
            </article>

            @if ($interpretacionesReincidencia !== [])
                <aside class="lg:col-span-2 mt-2 border-t border-[var(--border)] pt-5" aria-labelledby="reincidencia-interpretation-heading">
                    <div class="max-w-2xl">
                        <span class="eyebrow">Lectura del resultado</span>
                        <h3 id="reincidencia-interpretation-heading" class="mt-1 text-lg">Interpretación del gráfico</h3>
                        <p class="mb-0 mt-1 text-sm leading-6 text-[var(--ink-soft)]">La lectura compara las categorías de reincidencia y su peso sobre la matrícula del período.</p>
                    </div>
                    <div class="mt-4 grid gap-3 lg:grid-cols-3">
                        @foreach ($interpretacionesReincidencia as $interpretacion)
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

            <div class="absolute inset-0 hidden items-center justify-center rounded-lg bg-[var(--surface)]/90" wire:loading.flex wire:target="periodoSeleccionado" role="status" aria-live="polite">
                <span class="inline-flex items-center gap-2 rounded-lg border border-[var(--border)] bg-[var(--surface)] px-4 py-3 text-sm font-semibold shadow-sm">
                    <svg class="size-4 animate-spin motion-reduce:animate-none" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-6.22-8.56"/></svg>
                    Cargando período…
                </span>
            </div>
        </section>
    @endif
</div>
