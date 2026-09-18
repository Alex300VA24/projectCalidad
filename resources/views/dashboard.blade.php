@extends('layouts.app')

@section('title', 'Resumen | SIGI Calidad')
@section('page-label', 'Resumen ejecutivo')

@section('content')
    <section class="hero-panel reveal">
        <div>
            <span class="eyebrow">Periodo institucional · {{ $periodoActual }}</span>
            <h1>La calidad se gestiona<br><em>con evidencia.</em></h1>
            <p>Una lectura clara del desempeño institucional para anticipar riesgos y orientar decisiones.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="{{ route('quality-indicators.dashboard') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9m5 10V5m5 14v-8m5 8V7"/><path d="m4 8 5-3 5 5 5-4"/></svg>
                    Ver dashboard de calidad
                </a>
                <a class="btn btn-secondary" href="{{ route('documents.index') }}">Explorar evidencias</a>
            </div>
        </div>
        <div class="score-orbit" aria-label="Cumplimiento institucional: {{ $average }} por ciento">
            <div class="score-ring" style="--score: {{ $average }}">
                <div><strong>{{ number_format($average, 1) }}%</strong><span>cumplimiento<br>global</span></div>
            </div>
            <p><span class="status-dot"></span> Calculado con {{ $totalIndicadores }} {{ Str::plural('indicador', $totalIndicadores) }}</p>
        </div>
    </section>

    <section class="metric-grid reveal" aria-label="Métricas principales">
        <article class="metric-card featured">
            <span class="metric-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg></span>
            <div><span>Indicadores conformes</span><strong>{{ $conformes }}</strong><small>de {{ $totalIndicadores }} evaluados</small></div>
        </article>
        <article class="metric-card">
            <span class="metric-icon warning"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v5M12 17h.01"/><path d="m12 3 9 17H3z"/></svg></span>
            <div><span>Observados</span><strong>{{ $observados }}</strong><small>requieren seguimiento</small></div>
        </article>
        <article class="metric-card">
            <span class="metric-icon danger"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v5M12 17h.01"/><circle cx="12" cy="12" r="9"/></svg></span>
            <div><span>Estado crítico</span><strong>{{ $criticos }}</strong><small>acción prioritaria</small></div>
        </article>
        <article class="metric-card">
            <span class="metric-icon indigo"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5"/></svg></span>
            <div><span>Evidencias</span><strong>{{ \App\Models\Document::count() }}</strong><small>documentos vinculados</small></div>
        </article>
    </section>

    <div class="dashboard-grid reveal">
        <section class="panel">
            <div class="panel-heading">
                <div><span class="eyebrow">{{ $programaEstudio->nombre ?? 'Sin programa activo' }} · {{ $periodoActual }}</span><h2>Indicadores prioritarios</h2></div>
                <a class="text-link" href="{{ route('quality-indicators.dashboard') }}">Ver todos <span aria-hidden="true">→</span></a>
            </div>
            <div class="indicator-list">
                @forelse ($indicadoresPrioritarios as $indicator)
                    <article class="indicator-row">
                        <div class="indicator-main">
                            <span class="code">{{ $indicator['codigo'] }}</span>
                            <div><strong>{{ $indicator['nombre'] }}</strong><small>{{ $indicator['proceso'] }}</small></div>
                        </div>
                        <div class="progress-cell">
                            <div class="progress-meta"><span>{{ $indicator['valor'] }} / {{ $indicator['meta'] }} {{ $indicator['unidad'] }}</span><strong>{{ $indicator['progress'] }}%</strong></div>
                            <div class="progress-track"><span class="{{ $indicator['estadoKey'] }}" style="width: {{ $indicator['progress'] }}%"></span></div>
                        </div>
                        <span class="badge {{ $indicator['estadoKey'] }}"><i></i>{{ $indicator['estado'] }}</span>
                    </article>
                @empty
                    <div class="empty-state">
                        <h3>Aún no hay datos de indicadores</h3>
                        <p>
                            @if ($programaEstudio === null)
                                Activa un programa de estudio para comenzar a calcular los indicadores oficiales.
                            @else
                                No hay mediciones calculadas para {{ $periodoActual }} todavía.
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        <aside class="panel evidence-panel">
            <div class="panel-heading">
                <div><span class="eyebrow">Repositorio</span><h2>Evidencia reciente</h2></div>
            </div>
            <div class="evidence-list">
                @forelse ($documents as $document)
                    <button class="evidence-item" type="button" data-open-pdf data-title="{{ $document->title }}" data-preview="{{ $document->preview_url }}" data-external="{{ $document->external_url }}">
                        <span class="file-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 13h6M9 17h4"/></svg></span>
                        <span><strong>{{ $document->title }}</strong><small>{{ $document->section }}</small></span>
                        <svg class="arrow-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                    </button>
                @empty
                    <p class="muted">No hay evidencias registradas.</p>
                @endforelse
            </div>
            <a class="btn btn-soft full" href="{{ route('documents.index') }}">Ir al repositorio</a>
        </aside>
    </div>

    @push('modals')
        <x-pdf-modal />
    @endpush
@endsection
