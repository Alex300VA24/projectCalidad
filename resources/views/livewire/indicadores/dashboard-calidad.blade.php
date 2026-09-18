<div class="space-y-5">
    <header class="relative overflow-hidden rounded-xl bg-[#172554] px-5 py-6 text-white shadow-[0_18px_50px_rgba(23,37,84,.18)] sm:px-7 lg:flex lg:items-end lg:justify-between lg:gap-8 lg:px-9 lg:py-8">
        <div class="pointer-events-none absolute inset-0 opacity-15" aria-hidden="true" style="background-image:radial-gradient(circle,#fff 1px,transparent 1px);background-size:22px 22px;mask-image:linear-gradient(to left,#000,transparent 80%)"></div>
        <div class="relative max-w-3xl">
            <span class="font-mono text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-200">Dirección de Escuela · Control institucional</span>
            <h1 class="mt-3 max-w-2xl text-balance font-mono text-3xl font-semibold leading-tight tracking-[-0.04em] sm:text-4xl">Indicadores de calidad académica</h1>
            <p class="mt-3 max-w-2xl text-base leading-7 text-white/70">Lectura automática de sílabos, matrícula, tutoría, avance académico y seguimiento al egresado.</p>
        </div>

        <div class="relative mt-6 grid gap-3 sm:grid-cols-2 lg:mt-0 lg:min-w-[420px]">
            <label class="grid gap-1.5 text-sm font-semibold">
                <span class="text-white/70">Programa de estudios</span>
                <select wire:model.live="programaEstudioId" class="min-h-11 cursor-pointer rounded-lg border border-white/20 bg-white/10 px-3 text-white outline-none transition focus:border-emerald-300 focus:ring-2 focus:ring-emerald-300/30">
                    @forelse ($programas as $programa)
                        <option class="text-slate-900" value="{{ $programa->id }}">{{ $programa->nombre }}</option>
                    @empty
                        <option class="text-slate-900" value="0">Sin programas configurados</option>
                    @endforelse
                </select>
            </label>
            <label class="grid gap-1.5 text-sm font-semibold">
                <span class="text-white/70">Semestre académico</span>
                <select wire:model.live="periodoAcademico" class="min-h-11 cursor-pointer rounded-lg border border-white/20 bg-white/10 px-3 font-mono text-white outline-none transition focus:border-emerald-300 focus:ring-2 focus:ring-emerald-300/30">
                    @foreach ($periodos as $periodo)
                        <option class="text-slate-900" value="{{ $periodo }}">{{ $periodo }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-1.5 text-sm font-semibold">
                <span class="text-white/70">Ciclo académico</span>
                <select wire:model.live="cicloAcademico" class="min-h-11 cursor-pointer rounded-lg border border-white/20 bg-white/10 px-3 text-white outline-none transition focus:border-emerald-300 focus:ring-2 focus:ring-emerald-300/30">
                    <option class="text-slate-900" value="">Todos los ciclos</option>
                    @foreach ($ciclos as $ciclo)
                        <option class="text-slate-900" value="{{ $ciclo }}">Ciclo {{ $ciclo }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-1.5 text-sm font-semibold">
                <span class="text-white/70">Curso</span>
                <select wire:model.live="cursoId" class="min-h-11 cursor-pointer rounded-lg border border-white/20 bg-white/10 px-3 text-white outline-none transition focus:border-emerald-300 focus:ring-2 focus:ring-emerald-300/30">
                    <option class="text-slate-900" value="0">Todos los cursos</option>
                    @foreach ($cursos as $curso)
                        <option class="text-slate-900" value="{{ $curso->id }}">{{ $curso->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </header>

    @if (session('quality-success'))
        <div class="flex items-center gap-3 rounded-lg border border-emerald-600/25 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" role="status">
            <svg class="size-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
            {{ session('quality-success') }}
        </div>
    @endif

    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="Resumen de estados">
        @foreach ([
            ['key' => 'CONFORME', 'label' => 'Conformes', 'caption' => 'Cumplen la meta', 'color' => 'text-[var(--green)]', 'surface' => 'bg-[var(--green-soft)]', 'border' => 'border-t-[var(--green)]'],
            ['key' => 'OBSERVADO', 'label' => 'Observados', 'caption' => 'Requieren seguimiento', 'color' => 'text-[var(--amber)]', 'surface' => 'bg-[var(--amber-soft)]', 'border' => 'border-t-[var(--amber)]'],
            ['key' => 'CRITICO', 'label' => 'Críticos', 'caption' => 'Acción prioritaria', 'color' => 'text-[var(--red)]', 'surface' => 'bg-[var(--red-soft)]', 'border' => 'border-t-[var(--red)]'],
            ['key' => 'SIN_DATOS', 'label' => 'Sin medición', 'caption' => 'Fuente aún vacía', 'color' => 'text-slate-600', 'surface' => 'bg-slate-100', 'border' => 'border-t-slate-400'],
        ] as $estado)
            <article class="rounded-lg border border-[var(--border)] border-t-4 {{ $estado['border'] }} bg-[var(--surface)] p-4 shadow-sm sm:p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="mb-1 text-xs font-semibold uppercase tracking-[0.08em] text-[var(--ink-soft)]">{{ $estado['label'] }}</p>
                        <strong class="font-mono text-3xl leading-none {{ $estado['color'] }}">{{ $resumenEstados[$estado['key']] }}</strong>
                    </div>
                    <span class="grid size-9 place-items-center rounded-full {{ $estado['surface'] }} {{ $estado['color'] }}" aria-hidden="true">
                        <svg class="size-4" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/><path d="M12 8v4m0 4h.01"/></svg>
                    </span>
                </div>
                <p class="mb-0 mt-2 text-sm text-[var(--ink-soft)]">{{ $estado['caption'] }}</p>
            </article>
        @endforeach
    </section>

    <section aria-labelledby="kpi-heading">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
            <div><span class="eyebrow">Semáforo institucional</span><h2 id="kpi-heading" class="mt-1">Desempeño frente a metas UNT</h2></div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('quality-indicators.export', ['programa_estudio_id' => $programaEstudioId, 'periodo_academico' => $periodoAcademico]) }}" class="inline-flex min-h-11 cursor-pointer items-center rounded-lg border border-[var(--border)] bg-[var(--surface)] px-4 text-sm font-semibold transition hover:border-indigo-500 hover:text-indigo-700 focus-visible:ring-2 focus-visible:ring-indigo-500">Exportar PDF</a>
                @if ($puedeConsolidar)
                    <button type="button" wire:click="consolidar" wire:confirm="El periodo quedara como historico inmutable. ¿Continuar?" class="inline-flex min-h-11 cursor-pointer items-center rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white transition hover:bg-emerald-800 focus-visible:ring-2 focus-visible:ring-emerald-500" wire:loading.attr="disabled">Consolidar periodo</button>
                @endif
            <button type="button" wire:click="$refresh" class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border border-[var(--border)] bg-[var(--surface)] px-4 text-sm font-semibold transition hover:border-indigo-500 hover:text-indigo-700 focus-visible:ring-2 focus-visible:ring-indigo-500" wire:loading.attr="disabled">
                <svg class="size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M7 8a7 7 0 0 1 11-2l2 1M17 16a7 7 0 0 1-11 2l-2-1"/></svg>
                <span wire:loading.remove>Recalcular</span><span wire:loading>Calculando…</span>
            </button>
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            @forelse ($indicadores as $indicador)
                @php
                    $medicion = $indicador->mediciones->first();
                    $estado = $medicion?->estado_cumplimiento ?? 'SIN_DATOS';
                    $estilos = match ($estado) {
                        'CONFORME' => ['label' => 'Conforme', 'text' => 'text-[var(--green)]', 'bg' => 'bg-[var(--green-soft)]', 'bar' => 'bg-[var(--green)]', 'border' => 'border-l-[var(--green)]'],
                        'OBSERVADO' => ['label' => 'Observado', 'text' => 'text-[var(--amber)]', 'bg' => 'bg-[var(--amber-soft)]', 'bar' => 'bg-[var(--amber)]', 'border' => 'border-l-[var(--amber)]'],
                        'NO_CONFORME' => ['label' => 'No conforme', 'text' => 'text-[var(--amber)]', 'bg' => 'bg-[var(--amber-soft)]', 'bar' => 'bg-[var(--amber)]', 'border' => 'border-l-[var(--amber)]'],
                        'SIN_CONFIGURACION' => ['label' => 'Sin meta oficial', 'text' => 'text-slate-600', 'bg' => 'bg-slate-100', 'bar' => 'bg-slate-400', 'border' => 'border-l-slate-400'],
                        'CRITICO' => ['label' => 'Crítico', 'text' => 'text-[var(--red)]', 'bg' => 'bg-[var(--red-soft)]', 'bar' => 'bg-[var(--red)]', 'border' => 'border-l-[var(--red)]'],
                        default => ['label' => 'Sin datos', 'text' => 'text-slate-600', 'bg' => 'bg-slate-100', 'bar' => 'bg-slate-400', 'border' => 'border-l-slate-400'],
                    };
                    $valor = $medicion ? (float) $medicion->valor_medido : null;
                    $anchoBarra = $valor === null ? 0 : min(max($valor, 0), 100);
                    $unidad = $indicador->unidad_medida === 'PORCENTAJE' ? '%' : '';
                    $operadorMeta = $indicador->sentido_meta === 'MENOR_IGUAL' ? '≤' : '≥';
                @endphp
                <article class="flex min-h-64 flex-col rounded-lg border border-[var(--border)] border-l-4 {{ $estilos['border'] }} bg-[var(--surface)] p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <span class="rounded bg-indigo-50 px-2 py-1 font-mono text-[10px] font-semibold text-indigo-700">{{ $indicador->codigo }}</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $estilos['bg'] }} {{ $estilos['text'] }}"><i class="size-1.5 rounded-full bg-current" aria-hidden="true"></i>{{ $estilos['label'] }}</span>
                    </div>
                    <h3 class="mt-4 text-base font-semibold leading-6">{{ $indicador->nombre }}</h3>
                    <p class="mb-4 mt-1 text-sm text-[var(--ink-soft)]">{{ $indicador->proceso }}</p>
                    <div class="mt-auto">
                        <div class="flex items-end justify-between gap-3">
                            <div><span class="block text-xs text-[var(--ink-soft)]">Valor medido</span><strong class="font-mono text-3xl tracking-[-0.04em]">{{ $valor === null ? '—' : number_format($valor, 1) }}<small class="ml-0.5 text-base">{{ $unidad }}</small></strong></div>
                            <span class="text-right text-xs text-[var(--ink-soft)]">Meta<br><strong class="font-mono text-[var(--ink)]">{{ $indicador->meta_institucional === null ? 'No configurada' : $operadorMeta.' '.number_format((float) $indicador->meta_institucional, 1).$unidad }}</strong></span>
                        </div>
                        <div class="relative mt-3 h-2 overflow-hidden rounded-full bg-[var(--surface-alt)]" aria-hidden="true"><span class="block h-full rounded-full {{ $estilos['bar'] }} transition-[width] duration-300 motion-reduce:transition-none" style="width:{{ $anchoBarra }}%"></span></div>

                        @php
                            $rutaLlenado = match($indicador->codigo) {
                                'I-M01.01-DPA-004' => route('syllabi.index'),
                                'M01.01.02.02-FI-001', 'M01.01.02.02-FI-002' => route('matriculas.index', ['tab' => 'matriculas', 'periodo' => $periodoAcademico]),
                                'M01.01.02.02-FI-003' => route('matriculas.index', ['tab' => 'incidencias', 'periodo' => $periodoAcademico]),
                                'M01.01.03.01-F-013' => route('course-execution-reports.index'),
                                'M01.04-DDA-FI-001' => route('tutoring-sessions.index'),
                                'M01.05-DCU-FI-001', 'M01.05-DCU-FI-002' => route('graduate-registries.index'),
                                default => route('tramites.hub'),
                            };
                        @endphp
                        <a href="{{ $rutaLlenado }}" class="mt-3.5 inline-flex w-full items-center justify-center gap-1.5 rounded-md border border-[var(--border)] bg-[var(--surface-alt)] px-3 py-1.5 text-xs font-semibold text-[var(--indigo)] transition hover:border-[var(--indigo)] hover:bg-[var(--surface)]">
                            <svg class="size-3.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                            Llenar datos en formato &rarr;
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-lg border border-dashed border-[var(--border)] bg-[var(--surface)] p-10 text-center"><h3>No hay catálogo de indicadores</h3><p class="mb-0 text-[var(--ink-soft)]">Ejecuta el seeder de indicadores para iniciar el cálculo.</p></div>
            @endforelse
        </div>
    </section>

    @php
        $configAprobacion = ['type' => 'bar', 'data' => ['labels' => $graficos['aprobacion']['labels'], 'datasets' => [
            ['label' => 'Aprobados', 'data' => $graficos['aprobacion']['aprobados'], 'backgroundColor' => '#087f5b', 'borderRadius' => 4],
            ['label' => 'Desaprobados', 'data' => $graficos['aprobacion']['desaprobados'], 'backgroundColor' => '#c0362c', 'borderRadius' => 4],
            ['label' => 'Inhabilitados', 'data' => $graficos['aprobacion']['inhabilitados'], 'backgroundColor' => '#64748b', 'borderRadius' => 4],
        ]]];
        $configHistorico = ['type' => 'line', 'data' => ['labels' => $graficos['historico']['labels'], 'datasets' => [
            ['label' => 'Retención', 'data' => $graficos['historico']['retencion'], 'borderColor' => '#4f46e5', 'backgroundColor' => '#4f46e520', 'tension' => 0.28, 'spanGaps' => true],
            ['label' => 'Repitencia', 'data' => $graficos['historico']['repitencia'], 'borderColor' => '#a65d00', 'backgroundColor' => '#a65d0020', 'tension' => 0.28, 'spanGaps' => true],
        ]]];
        $configEmpleabilidad = ['type' => 'doughnut', 'data' => ['labels' => $graficos['empleabilidad']['labels'], 'datasets' => [[
            'data' => $graficos['empleabilidad']['valores'], 'backgroundColor' => ['#087f5b', '#4f46e5', '#a65d00', '#94a3b8'], 'borderWidth' => 0,
        ]]]];
    @endphp

    <section class="grid gap-4 xl:grid-cols-12" aria-labelledby="charts-heading" wire:key="quality-charts-{{ $programaEstudioId }}-{{ $periodoAcademico }}-{{ $cicloAcademico ?? 'todos' }}-{{ $cursoId }}" x-init="$nextTick(() => window.renderQualityCharts?.($el))">
        <h2 id="charts-heading" class="sr-only">Gráficos estadísticos</h2>
        <article class="rounded-lg border border-[var(--border)] bg-[var(--surface)] p-5 shadow-sm xl:col-span-7">
            <span class="eyebrow">Resultados F-013</span><h3 class="mt-1 text-lg">Aprobación por ciclo académico</h3><p class="mt-1 text-sm text-[var(--ink-soft)]">Comparación de aprobados, desaprobados e inhabilitados.</p>
            <div class="mt-5 h-72"><canvas data-quality-chart data-chart-config="{{ json_encode($configAprobacion) }}" aria-label="Gráfico de barras de resultados por ciclo" role="img"></canvas></div>
        </article>
        <article class="rounded-lg border border-[var(--border)] bg-[var(--surface)] p-5 shadow-sm xl:col-span-5">
            <span class="eyebrow">Tendencia histórica</span><h3 class="mt-1 text-lg">Retención y repitencia</h3><p class="mt-1 text-sm text-[var(--ink-soft)]">Evolución semestral de FI-001 y FI-002.</p>
            <div class="mt-5 h-72"><canvas data-quality-chart data-chart-config="{{ json_encode($configHistorico) }}" aria-label="Gráfico de líneas de retención y repitencia" role="img"></canvas></div>
        </article>
        <article class="rounded-lg border border-[var(--border)] bg-[var(--surface)] p-5 shadow-sm xl:col-span-5">
            <span class="eyebrow">Seguimiento al egresado</span><h3 class="mt-1 text-lg">Condición laboral</h3><p class="mt-1 text-sm text-[var(--ink-soft)]">Distribución registrada en el formato PG-06.</p>
            <div class="mx-auto mt-5 h-72 max-w-sm"><canvas data-quality-chart data-chart-config="{{ json_encode($configEmpleabilidad) }}" aria-label="Gráfico de dona de condición laboral de egresados" role="img"></canvas></div>
        </article>
        <article class="rounded-lg border border-[var(--border)] bg-[#172554] p-5 text-white shadow-sm xl:col-span-7">
            <span class="font-mono text-[11px] font-semibold uppercase tracking-[0.14em] text-emerald-200">Tutoría y derivaciones · F-07</span><h3 class="mt-2 text-xl">Cobertura de acompañamiento estudiantil</h3>
            <div class="mt-6 grid grid-cols-3 gap-2 sm:gap-4">
                @foreach ([['label' => 'Programadas', 'value' => $resumenTutoria['programadas']], ['label' => 'Realizadas', 'value' => $resumenTutoria['realizadas']], ['label' => 'Derivaciones', 'value' => $resumenTutoria['derivaciones']]] as $dato)
                    <div class="rounded-lg border border-white/15 bg-white/[0.07] p-3 sm:p-4"><strong class="block font-mono text-2xl sm:text-3xl">{{ $dato['value'] }}</strong><span class="mt-1 block text-xs text-white/65 sm:text-sm">{{ $dato['label'] }}</span></div>
                @endforeach
            </div>
            <p class="mb-0 mt-5 text-sm leading-6 text-white/65">Las derivaciones médicas, psicológicas y sociales se cuentan desde los registros emitidos durante el semestre.</p>
        </article>
    </section>

    <section class="rounded-lg border border-[var(--border)] bg-[var(--surface)] p-5 shadow-sm" aria-labelledby="graduate-metrics-heading">
        <span class="eyebrow">SE-01 y SE-02</span>
        <h2 id="graduate-metrics-heading" class="mt-1">Resultados de egresados</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-3">
            @foreach ([['label' => 'Titulados', 'value' => $resumenEgresados['titulados']], ['label' => 'Laborando', 'value' => $resumenEgresados['laborando']], ['label' => 'En su especialidad', 'value' => $resumenEgresados['especialidad']]] as $metrica)
                <article class="rounded-lg bg-[var(--surface-alt)] p-4"><span class="text-sm text-[var(--ink-soft)]">{{ $metrica['label'] }}</span><strong class="mt-1 block font-mono text-2xl">{{ $metrica['value'] === null ? 'Sin datos' : number_format($metrica['value'], 1).'%' }}</strong></article>
            @endforeach
        </div>
        <p class="mb-0 mt-3 text-sm text-[var(--ink-soft)]">Metricas independientes. Derivaciones y empleo en especialidad son descriptivos sin meta oficial configurada.</p>
    </section>

    <section class="overflow-hidden rounded-lg border border-[var(--border)] bg-[var(--surface)] shadow-sm" aria-labelledby="plans-heading">
        <div class="flex flex-wrap items-end justify-between gap-3 border-b border-[var(--border)] p-5">
            <div><span class="eyebrow">Gestión correctiva</span><h2 id="plans-heading" class="mt-1">Planes de mejora</h2><p class="mb-0 mt-1 text-sm text-[var(--ink-soft)]">Registra causas y acciones para indicadores observados o críticos.</p></div>
            <span class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800">{{ $planesPendientes->count() }} requieren atención</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] border-collapse text-left">
                <thead class="bg-[var(--surface-alt)] font-mono text-[11px] uppercase tracking-[0.08em] text-[var(--ink-soft)]"><tr><th class="px-5 py-3">Indicador</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3">Análisis</th><th class="px-5 py-3">Acción</th><th class="px-5 py-3"><span class="sr-only">Editar</span></th></tr></thead>
                <tbody class="divide-y divide-[var(--border)]">
                    @forelse ($planesPendientes as $medicion)
                        <tr>
                            <td class="px-5 py-4"><strong class="block text-sm">{{ $medicion->indicador->nombre }}</strong><small class="mt-1 block font-mono text-[11px] text-indigo-700">{{ $medicion->indicador->codigo }}</small></td>
                            <td class="px-5 py-4"><span @class(['inline-flex rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-red-50 text-red-700' => $medicion->estado_cumplimiento === 'CRITICO', 'bg-amber-50 text-amber-800' => $medicion->estado_cumplimiento === 'OBSERVADO'])>{{ ucfirst(mb_strtolower($medicion->estado_cumplimiento)) }}</span></td>
                            <td class="max-w-xs px-5 py-4 text-sm text-[var(--ink-soft)]">{{ $medicion->analisis_causas ?: 'Pendiente de registrar' }}</td>
                            <td class="max-w-xs px-5 py-4 text-sm text-[var(--ink-soft)]">{{ $medicion->acciones_mejora ?: 'Pendiente de registrar' }}</td>
                            <td class="px-5 py-4 text-right"><button type="button" wire:click="editarPlan({{ $medicion->indicador_id }})" class="min-h-11 cursor-pointer rounded-lg border border-[var(--border)] px-3 text-sm font-semibold transition hover:border-indigo-500 hover:text-indigo-700 focus-visible:ring-2 focus-visible:ring-indigo-500">{{ $medicion->acciones_mejora ? 'Editar' : 'Registrar' }}</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-[var(--ink-soft)]">No hay indicadores observados o críticos en este periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($medicionEditandoId)
        <div class="fixed inset-0 z-[110] grid place-items-end sm:place-items-center" role="presentation" x-on:keydown.escape.window="$wire.cerrarPlan()">
            <button type="button" class="absolute inset-0 cursor-pointer bg-slate-950/70 backdrop-blur-sm" wire:click="cerrarPlan" aria-label="Cerrar formulario"></button>
            <section class="relative max-h-[92vh] w-full overflow-y-auto rounded-t-2xl bg-[var(--surface)] p-5 shadow-2xl sm:w-[min(92vw,620px)] sm:rounded-xl sm:p-7" role="dialog" aria-modal="true" aria-labelledby="quality-plan-title" x-init="$nextTick(() => $el.querySelector('textarea')?.focus())">
                <div class="flex items-start justify-between gap-4">
                    <div><span class="eyebrow">Acción correctiva</span><h2 id="quality-plan-title" class="mt-1">Plan de mejora</h2></div>
                    <button type="button" wire:click="cerrarPlan" class="grid size-11 cursor-pointer place-items-center rounded-lg border border-[var(--border)] transition hover:border-indigo-500 hover:text-indigo-700" aria-label="Cerrar"><svg class="size-5" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
                </div>
                <form wire:submit="guardarPlan" class="mt-6 grid gap-5">
                    <label class="grid gap-2 text-sm font-semibold"><span>Análisis de causas</span><textarea wire:model="analisisCausas" rows="5" maxlength="3000" class="min-h-32 rounded-lg border border-[var(--border)] bg-[var(--surface)] p-3 text-base font-normal outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20" placeholder="Describe las causas verificables de la desviación"></textarea>@error('analisisCausas') <span class="text-sm font-normal text-red-700">{{ $message }}</span> @enderror</label>
                    <label class="grid gap-2 text-sm font-semibold"><span>Acciones de mejora</span><textarea wire:model="accionesMejora" rows="5" maxlength="3000" class="min-h-32 rounded-lg border border-[var(--border)] bg-[var(--surface)] p-3 text-base font-normal outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20" placeholder="Define acciones, responsables y plazos"></textarea>@error('accionesMejora') <span class="text-sm font-normal text-red-700">{{ $message }}</span> @enderror</label>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-2 text-sm font-semibold"><span>Responsable</span><select wire:model="responsableId" class="min-h-11 rounded-lg border border-[var(--border)] bg-[var(--surface)] px-3 font-normal focus:ring-2 focus:ring-indigo-500"><option value="">Selecciona</option>@foreach($usuariosResponsables as $usuario)<option value="{{ $usuario->id }}">{{ $usuario->name }}</option>@endforeach</select>@error('responsableId') <span class="text-sm font-normal text-red-700">{{ $message }}</span> @enderror</label>
                        <label class="grid gap-2 text-sm font-semibold"><span>Fecha limite</span><input type="date" wire:model="fechaLimite" class="min-h-11 rounded-lg border border-[var(--border)] bg-[var(--surface)] px-3 font-normal focus:ring-2 focus:ring-indigo-500">@error('fechaLimite') <span class="text-sm font-normal text-red-700">{{ $message }}</span> @enderror</label>
                    </div>
                    <label class="grid gap-2 text-sm font-semibold"><span>Evidencia URL (opcional)</span><input type="url" wire:model="evidenciaUrl" class="min-h-11 rounded-lg border border-[var(--border)] bg-[var(--surface)] px-3 font-normal focus:ring-2 focus:ring-indigo-500">@error('evidenciaUrl') <span class="text-sm font-normal text-red-700">{{ $message }}</span> @enderror</label>
                    <div class="flex flex-col-reverse gap-2 border-t border-[var(--border)] pt-5 sm:flex-row sm:justify-end"><button type="button" wire:click="cerrarPlan" class="min-h-11 cursor-pointer rounded-lg border border-[var(--border)] px-5 font-semibold transition hover:border-indigo-500">Cancelar</button><button type="submit" class="min-h-11 cursor-pointer rounded-lg bg-emerald-700 px-5 font-semibold text-white transition hover:bg-emerald-800 disabled:cursor-wait disabled:opacity-70" wire:loading.attr="disabled">Guardar plan</button></div>
                </form>
            </section>
        </div>
    @endif
</div>
