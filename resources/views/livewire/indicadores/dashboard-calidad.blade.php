<div class="space-y-6">
    <header class="relative overflow-hidden rounded-xl bg-[#172554] px-5 py-7 text-white shadow-[0_18px_50px_rgba(23,37,84,.18)] sm:px-7 lg:flex lg:items-end lg:justify-between lg:gap-8 lg:px-9 lg:py-9">
        <div class="pointer-events-none absolute inset-0 opacity-15" aria-hidden="true" style="background-image:radial-gradient(circle,#fff 1px,transparent 1px);background-size:22px 22px;mask-image:linear-gradient(to left,#000,transparent 80%)"></div>
        <div class="relative max-w-3xl">
            <span class="font-mono text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-200">Indicadores de calidad académica</span>
            <h1 class="mt-3 max-w-2xl text-balance font-mono text-3xl font-semibold leading-tight tracking-[-0.04em] sm:text-4xl">Indicadores</h1>
            <p class="mt-3 max-w-2xl text-base leading-7 text-white/75">Monitorea el cumplimiento institucional, detecta brechas y prioriza acciones de mejora desde una sola vista.</p>
        </div>
        <div class="relative mt-6 flex flex-wrap gap-2 lg:mt-0 lg:max-w-md lg:justify-end">
            <span class="inline-flex min-h-9 items-center gap-2 rounded-full border border-white/15 bg-white/[0.08] px-3 text-sm text-white/85">
                <svg class="size-4 text-emerald-300" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4m8-4v4M3 10h18"/><rect x="3" y="4" width="18" height="17" rx="2"/></svg>
                Periodo {{ $periodoAcademico }}
            </span>
            <span class="inline-flex min-h-9 items-center gap-2 rounded-full border border-white/15 bg-white/[0.08] px-3 text-sm text-white/85">
                <svg class="size-4 text-emerald-300" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9m5 10V5m5 14v-8m5 8V7"/></svg>
                {{ $indicadores->count() }} indicadores
            </span>
        </div>
    </header>

    @if (session('quality-success'))
        <div class="flex items-center gap-3 rounded-lg border border-emerald-600/25 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            <svg class="size-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
            {{ session('quality-success') }}
        </div>
    @endif

    @php
        $procesosUnt = ['Gestión Curricular', 'Gestión del Ingreso', 'Enseñanza y Aprendizaje', 'Resultados de la Formación'];
        $indicadoresPorProceso = $indicadores->groupBy('macro_proceso');
    @endphp
    <section aria-labelledby="kpi-heading" x-data="{ proceso: new URLSearchParams(window.location.search).get('proceso') || '{{ $procesosUnt[0] }}' }">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div><span class="eyebrow">Consulta institucional</span><h2 id="kpi-heading" class="mt-1">Indicadores por proceso</h2><p class="mb-0 mt-1 text-sm text-[var(--ink-soft)]">Selecciona un indicador para consultar su detalle e histórico.</p></div>
            @if ($puedeConsolidar)
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="consolidar" wire:confirm="El periodo quedara como historico inmutable. ¿Continuar?" class="inline-flex min-h-11 cursor-pointer items-center rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white transition hover:bg-emerald-800 focus-visible:ring-2 focus-visible:ring-emerald-500" wire:loading.attr="disabled">Consolidar periodo</button>
                </div>
            @endif
        </div>

        @php
            $iconosProceso = [
                'Gestión Curricular' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
                'Gestión del Ingreso' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 8v6M19 11h6"/>',
                'Enseñanza y Aprendizaje' => '<path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5"/>',
                'Resultados de la Formación' => '<path d="M3 3v18h18"/><path d="M7 15l4-6 3 3 5-7"/>',
            ];
        @endphp
        <div class="mb-4 flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Procesos institucionales">
            @foreach ($procesosUnt as $proceso)
                <button id="process-tab-{{ Str::slug($proceso) }}" type="button" role="tab" aria-controls="process-panel-{{ Str::slug($proceso) }}" x-on:click="proceso = '{{ $proceso }}'" :aria-selected="proceso === '{{ $proceso }}'"
                    class="quality-process-tab">
                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true">{!! $iconosProceso[$proceso] ?? '' !!}</svg>
                    {{ $proceso }}
                    <span class="ml-1 font-mono text-xs opacity-70">({{ $indicadoresPorProceso->get($proceso, collect())->count() }})</span>
                </button>
            @endforeach
        </div>

        @foreach ($procesosUnt as $proceso)
            <div id="process-panel-{{ Str::slug($proceso) }}" x-show="proceso === '{{ $proceso }}'" x-cloak class="grid gap-3 md:grid-cols-2 xl:grid-cols-4" role="tabpanel" aria-labelledby="process-tab-{{ Str::slug($proceso) }}">
                @forelse ($indicadoresPorProceso->get($proceso, collect()) as $indicador)
                    <article class="group flex min-h-56 flex-col rounded-xl border border-[var(--border)] bg-[var(--surface)] p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md focus-within:border-indigo-400 motion-reduce:transform-none motion-reduce:transition-none">
                        <span class="w-fit rounded bg-[var(--indigo-soft)] px-2 py-1 font-mono text-[10px] font-semibold text-[var(--indigo)]">{{ $indicador->codigo }}</span>
                        <h3 class="mt-4 text-base font-semibold leading-6">{{ $indicador->nombre }}</h3>
                        <p class="mb-5 mt-1 text-sm text-[var(--ink-soft)]">{{ $indicador->proceso }}</p>
                        <div class="mt-auto flex flex-wrap gap-2">
                        <a href="{{ route(str_contains($indicador->codigo, '/') ? 'quality-indicators.historial-with-slash' : 'quality-indicators.historial', $indicador->codigo) }}" class="quality-card-link primary" aria-label="Ver {{ $indicador->nombre }}">
                            <svg class="size-3.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9m5 10V5m5 14v-8m5 8V7"/></svg>
                            Ver
                        </a>
                            @if ($indicador->documento && ($indicador->documento->preview_url || $indicador->documento->external_url))
                                <button class="quality-card-link" type="button" data-open-pdf data-title="{{ $indicador->documento->title ?? 'Documento del indicador' }}" data-preview="{{ $indicador->documento->preview_url ?? $indicador->documento->external_url }}" data-external="{{ $indicador->documento->external_url ?? $indicador->documento->preview_url }}">
                                    Ver documento
                                    <svg class="size-3.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-lg border border-dashed border-[var(--border)] bg-[var(--surface)] p-10 text-center"><h3>Sin indicadores en este proceso</h3><p class="mb-0 text-[var(--ink-soft)]">Aún no se ha configurado ningún indicador para {{ $proceso }}.</p></div>
                @endforelse
            </div>
        @endforeach
    </section>

</div>
