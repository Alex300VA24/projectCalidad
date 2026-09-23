@php
    $esIndicadorCohorte = $esIndicadorCohorte ?? false;
    $fuenteDatos = $fuenteDatos ?? 'estudiantes_desaprobados_por_experiencia_curricular.json';
    $resultadoAgregado = $resultadoAgregado ?? ['total_desaprobaciones' => 0, 'total_matriculas' => 0, 'porcentaje' => null];
    $porcentajeAgregado = $resultadoAgregado['porcentaje'] === null
        ? null
        : rtrim(rtrim(number_format($resultadoAgregado['porcentaje'], 2, '.', ''), '0'), '.');
@endphp

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
                @if ($esIndicadorCohorte)
                    <p class="mb-0 mt-3 text-sm font-semibold text-emerald-100">Proceso: {{ $indicador->proceso }}</p>
                @endif
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

    <section class="rounded-lg border border-[var(--border)] bg-[var(--surface)] p-5 shadow-sm" aria-labelledby="desaprobados-filter-heading">
        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_240px] lg:items-end">
            <div>
                <span class="eyebrow">{{ $esIndicadorCohorte ? $indicador->proceso : 'Evaluación del Estudiante' }}</span>
                <h2 id="desaprobados-filter-heading" class="mt-1">{{ $esIndicadorCohorte ? 'Porcentaje de desaprobados por período académico y experiencia curricular' : 'Porcentaje de desaprobados por experiencia curricular' }}</h2>
                <p class="mb-0 mt-1 text-sm leading-6 text-[var(--ink-soft)]">Consulta el registro y el gráfico de cada período sin mezclar experiencias curriculares de otros semestres.</p>
            </div>
            <label class="grid gap-1.5 text-sm font-semibold">
                <span>{{ $esIndicadorCohorte ? 'Período académico' : 'Período' }}</span>
                <select wire:model.live="periodoSeleccionado" wire:loading.attr="disabled" wire:target="periodoSeleccionado" class="min-h-11 w-full cursor-pointer rounded-lg border border-[var(--border)] bg-[var(--surface-alt)] px-3 font-mono text-base text-[var(--ink)] outline-none transition hover:border-indigo-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-wait disabled:opacity-70">
                    @foreach ($periodosDesaprobados as $periodo)
                        <option value="{{ $periodo }}">{{ $periodo }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        @if (! $errorDatosDesaprobados)
            <div class="mt-4 inline-flex items-center gap-2 rounded-lg border border-[var(--border)] bg-[var(--surface-alt)] px-3.5 py-2 text-sm">
                @if ($esIndicadorCohorte)
                    <strong class="font-mono">{{ count($experiencias) }}</strong>
                    <span class="text-[var(--ink-soft)]">experiencias curriculares analizadas</span>
                @else
                    <span class="text-[var(--ink-soft)]">Experiencias curriculares analizadas:</span>
                    <strong class="font-mono">{{ count($experiencias) }}</strong>
                @endif
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
            <h2 class="text-lg font-semibold">Sin períodos académicos</h2>
            <p class="mb-0 mt-1 text-sm text-[var(--ink-soft)]">No existen períodos académicos registrados para este indicador.</p>
        </section>
    @elseif ($experiencias === [])
        <section class="rounded-lg border border-dashed border-[var(--border)] bg-[var(--surface)] p-10 text-center">
            <h2 class="text-lg font-semibold">Sin experiencias curriculares</h2>
            <p class="mb-0 mt-1 text-sm text-[var(--ink-soft)]">No existen experiencias curriculares registradas para este período.</p>
        </section>
    @else
        @if ($esIndicadorCohorte)
            <section class="grid gap-4 rounded-lg border border-red-200 bg-red-50 p-5 text-red-950 shadow-sm sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center" aria-labelledby="desaprobaciones-agregadas-heading">
                <div>
                    <span class="font-mono text-[11px] font-semibold uppercase tracking-[0.08em] text-red-800">Resumen general del semestre</span>
                    <h2 id="desaprobaciones-agregadas-heading" class="mt-1 text-lg">Desaprobaciones del período</h2>
                    <p class="mb-0 mt-1 text-sm leading-6 text-red-900/80">{{ $resultadoAgregado['total_desaprobaciones'] }} desaprobaciones registradas de {{ $resultadoAgregado['total_matriculas'] }} matrículas en cursos.</p>
                </div>
                <strong class="font-mono text-4xl tracking-[-0.04em]">{{ $porcentajeAgregado === null ? 'Sin datos' : $porcentajeAgregado.' %' }}</strong>
            </section>
        @endif

        <div class="quality-view-switch" role="tablist" aria-label="Vista del indicador">
            <button id="desaprobados-table-tab" type="button" role="tab" aria-controls="desaprobados-table-panel" aria-selected="{{ $vista === 'tabla' ? 'true' : 'false' }}" wire:click="cambiarVista('tabla')" class="quality-view-tab">
                <svg class="size-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="1.5"/><path d="M3 10h18M9 4v16"/></svg>
                {{ $esIndicadorCohorte ? 'Detalle de cursos' : 'Histórico y registro' }}
            </button>
            <button id="desaprobados-chart-tab" type="button" role="tab" aria-controls="desaprobados-chart-panel" aria-selected="{{ $vista === 'grafico' ? 'true' : 'false' }}" wire:click="cambiarVista('grafico')" class="quality-view-tab">
                <svg class="size-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V10m6 10V4m6 16v-7"/></svg>
                Ver gráfico
            </button>
        </div>

        @if ($vista === 'tabla')
            <section id="desaprobados-table-panel" class="overflow-hidden rounded-lg border border-[var(--border)] bg-[var(--surface)] shadow-sm" role="tabpanel" aria-labelledby="desaprobados-table-tab desaprobados-table-heading">
                <div class="border-b border-[var(--border)] p-5">
                    <span class="eyebrow">Registro semestral · {{ $periodoSeleccionado }}</span>
                    <h2 id="desaprobados-table-heading" class="mt-1">{{ $esIndicadorCohorte ? 'Detalle por experiencia curricular' : 'Histórico por experiencia curricular' }}</h2>
                    <p class="mb-0 mt-1 text-sm text-[var(--ink-soft)]">Detalle calculado desde el archivo JSON. El orden de los cursos coincide con la fuente institucional.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] border-collapse text-left text-sm">
                        <thead class="bg-[var(--surface-alt)] font-mono text-[11px] uppercase tracking-[0.08em] text-[var(--ink-soft)]">
                            <tr><th class="px-5 py-3">Experiencia curricular</th><th class="px-5 py-3">Código</th>@if ($esIndicadorCohorte)<th class="px-5 py-3 text-right">Aprobados</th>@endif<th class="px-5 py-3 text-right">Desaprobados</th><th class="px-5 py-3 text-right">Matriculados</th><th class="px-5 py-3 text-right">Porcentaje</th></tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--border)] [&>tr:nth-child(even)]:bg-[var(--surface-alt)]/40">
                            @foreach ($experiencias as $experiencia)
                                @php($porcentaje = $experiencia['porcentaje_desaprobados'] === null ? null : rtrim(rtrim(number_format($experiencia['porcentaje_desaprobados'], 2, '.', ''), '0'), '.'))
                                <tr class="transition-colors hover:bg-indigo-50/60">
                                    <td class="px-5 py-4 font-semibold">{{ $experiencia['nombre_curso'] }}</td>
                                    <td class="px-5 py-4 font-mono text-[var(--ink-soft)]">{{ $experiencia['codigo_curso'] }}</td>
                                    @if ($esIndicadorCohorte)<td class="px-5 py-4 text-right font-mono">{{ $experiencia['numero_aprobados'] }}</td>@endif
                                    <td class="px-5 py-4 text-right font-mono">{{ $experiencia['numero_desaprobados'] }}</td>
                                    <td class="px-5 py-4 text-right font-mono">{{ $experiencia['total_matriculados'] }}</td>
                                    <td class="px-5 py-4 text-right font-mono font-semibold">{{ $porcentaje === null ? 'Sin datos' : $porcentaje.'%' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="border-t border-[var(--border)] px-5 py-3 text-xs leading-5 text-[var(--ink-soft)]">
                    <strong class="font-semibold text-[var(--ink)]">Fuente.</strong> Archivo institucional <span class="font-mono">{{ $fuenteDatos }}</span>.
                </p>
            </section>
        @else
            <section id="desaprobados-chart-panel" class="relative rounded-lg border border-[var(--border)] bg-[var(--surface)] p-4 shadow-sm sm:p-5" role="tabpanel" aria-labelledby="desaprobados-chart-tab desaprobados-chart-heading">
                <div>
                    <span class="eyebrow">Período {{ $periodoSeleccionado }}</span>
                    <h2 id="desaprobados-chart-heading" class="mt-1">Estudiantes desaprobados por curso</h2>
                    <p class="mb-0 mt-1 text-sm text-[var(--ink-soft)]">Eje horizontal fijo de 0% a 100%. Pasa el cursor o toca una barra para consultar el detalle.</p>
                </div>

                <figure class="mx-auto mt-6 max-w-5xl border border-slate-300 bg-white p-4 text-[#111827] sm:p-6" aria-labelledby="desaprobados-figure-title" aria-describedby="desaprobados-chart-note">
                    <figcaption id="desaprobados-figure-title" class="mb-4 block text-sm italic">Porcentaje de estudiantes desaprobados por experiencia curricular en el período {{ $periodoSeleccionado }}</figcaption>

                    <div class="relative max-h-[70vh] overflow-auto border-y border-slate-300 bg-white">
                        <div class="min-w-[720px] px-3 py-4" style="height: {{ $chartHeight }}px" wire:key="desaprobados-chart-{{ $periodoSeleccionado }}" x-init="$nextTick(() => window.renderQualityCharts?.($el))">
                            <canvas data-quality-chart data-chart-horizontal-percent="1" data-chart-apa="1" data-chart-config="{{ json_encode($chartConfig) }}" aria-label="Gráfico de barras del porcentaje de estudiantes desaprobados por experiencia curricular en {{ $periodoSeleccionado }}. Usa la vista Detalle de cursos para consultar los mismos datos en una tabla." role="img"></canvas>
                        </div>
                        <div class="absolute inset-0 hidden items-center justify-center bg-white/90" wire:loading.flex wire:target="periodoSeleccionado" role="status">
                            <span class="inline-flex items-center gap-2 border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 shadow-sm">
                                <svg class="size-4 animate-spin motion-reduce:animate-none" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-6.22-8.56"/></svg>
                                Cargando período…
                            </span>
                        </div>
                    </div>

                    <p id="desaprobados-chart-note" class="mt-3 text-xs leading-5 text-slate-700">
                        <em class="font-semibold text-slate-900">Nota.</em> Elaboración propia. Cada valor se calculó mediante: (número de desaprobados / total de matriculados) × 100. No representa un promedio general del período.
                        @if ($esIndicadorCohorte)
                            La información disponible corresponde a períodos académicos y experiencias curriculares, sin desglose por promoción o cohorte.
                        @endif
                    </p>
                </figure>

                <aside class="mt-6 border-t border-[var(--border)] pt-5" aria-labelledby="desaprobados-interpretation-heading">
                    <div class="max-w-2xl">
                        <span class="eyebrow">Lectura del resultado</span>
                        <h3 id="desaprobados-interpretation-heading" class="mt-1 text-lg">Interpretación del gráfico</h3>
                        <p class="mb-0 mt-1 text-sm leading-6 text-[var(--ink-soft)]">La interpretación describe los registros del período sin reemplazar los porcentajes individuales de cada curso.</p>
                    </div>
                    <div class="mt-4 grid gap-3 lg:grid-cols-3">
                        @foreach ($interpretacionesDesaprobados as $interpretacion)
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
            </section>
        @endif
    @endif
</div>
