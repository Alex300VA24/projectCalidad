@props([
    'executionReportsByPeriod',
    'consolidatedReportsByPeriod',
    'studentReferences',
])

@foreach ([
    ['code' => 'M01.01.03.01-F-005', 'title' => 'Informes de Ejecución de las Asignaturas', 'singular' => 'informe', 'plural' => 'informes', 'reports' => $executionReportsByPeriod],
    ['code' => 'M01.01.03.01-F-013', 'title' => 'Consolidado de la Ejecución de la Asignatura', 'singular' => 'consolidado', 'plural' => 'consolidados', 'reports' => $consolidatedReportsByPeriod],
] as $formatModal)
    <div class="modal-layer" data-execution-reports-modal="{{ $formatModal['code'] }}" hidden>
        <div class="modal-backdrop" data-execution-reports-close></div>
        <section class="execution-reports-modal" role="dialog" aria-modal="true" aria-labelledby="execution-reports-title-{{ $formatModal['code'] }}" aria-describedby="execution-reports-description-{{ $formatModal['code'] }}">
            <header class="modal-header">
                <div>
                    <span class="eyebrow">{{ $formatModal['code'] }}</span>
                    <h2 id="execution-reports-title-{{ $formatModal['code'] }}">{{ $formatModal['title'] }}</h2>
                </div>
                <button class="icon-btn" type="button" data-execution-reports-close aria-label="Cerrar {{ $formatModal['plural'] }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </header>
            <div class="execution-reports-content">
                <p id="execution-reports-description-{{ $formatModal['code'] }}">Selecciona un semestre para consultar los {{ $formatModal['plural'] }} registrados.</p>
                <div class="execution-period-buttons" role="group" aria-label="Semestre académico">
                    @foreach ($formatModal['reports'] as $period => $reports)
                        <button type="button" data-execution-period="{{ $period }}" aria-pressed="{{ $period === '2026-I' ? 'true' : 'false' }}">
                            {{ $period }} <span>{{ count($reports) }}</span>
                        </button>
                    @endforeach
                </div>
                @foreach ($formatModal['reports'] as $period => $reports)
                    <section class="execution-period-panel" data-execution-period-panel="{{ $period }}" aria-label="{{ ucfirst($formatModal['plural']) }} {{ $period }}" @if ($period !== '2026-I') hidden @endif>
                        <div class="execution-period-heading">
                            <h3>Semestre {{ $period }}</h3>
                            <span>{{ count($reports) }} {{ count($reports) === 1 ? $formatModal['singular'] : $formatModal['plural'] }}</span>
                        </div>
                        @forelse ($reports as $report)
                            <article class="execution-report">
                                <div>
                                    <h4>{{ $report['title'] }}</h4>
                                    <p>{{ $report['detail'] }}</p>
                                </div>
                                <button class="btn btn-secondary btn-compact" type="button" data-open-pdf
                                        data-title="{{ $report['title'] }} · {{ $report['detail'] }}"
                                        data-preview="{{ $report['preview_url'] }}"
                                        data-external="{{ $report['link'] }}">
                                    Visualizar documento
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            </article>
                        @empty
                            <div class="execution-reports-empty">
                                <h4>Sin {{ $formatModal['plural'] }} registrados</h4>
                                <p>Aún no hay archivos para el semestre {{ $period }}.</p>
                            </div>
                        @endforelse
                    </section>
                @endforeach
            </div>
        </section>
    </div>
@endforeach

<div class="modal-layer" data-student-references-modal hidden>
    <div class="modal-backdrop" data-student-references-close></div>
    <section class="execution-reports-modal" role="dialog" aria-modal="true" aria-labelledby="student-references-title" aria-describedby="student-references-description">
        <header class="modal-header">
            <div>
                <span class="eyebrow">F.M01.04-DDA/PG-06</span>
                <h2 id="student-references-title">Hoja de Referencia y Contra Referencia</h2>
            </div>
            <button class="icon-btn" type="button" data-student-references-close aria-label="Cerrar referencias">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </header>
        <div class="execution-reports-content">
            <p id="student-references-description">Selecciona un alumno para consultar sus archivos.</p>
            @if ($studentReferences !== [])
                <div class="student-reference-buttons" role="group" aria-label="Alumnos con referencias">
                    @foreach ($studentReferences as $student => $documents)
                        <button type="button" data-student-reference="{{ $loop->iteration }}" aria-expanded="false" aria-controls="student-reference-files-{{ $loop->iteration }}">
                            <span>{{ $student }}</span>
                            <span class="student-reference-count">{{ count($documents) }} {{ count($documents) === 1 ? 'archivo' : 'archivos' }}</span>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="execution-reports-empty">
                    <h3>Sin alumnos registrados</h3>
                    <p>Aún no hay archivos de referencia.</p>
                </div>
            @endif
            @foreach ($studentReferences as $student => $documents)
                <section class="execution-period-panel student-reference-panel" id="student-reference-files-{{ $loop->iteration }}" data-student-reference-panel="{{ $loop->iteration }}" aria-label="Archivos de {{ $student }}" hidden>
                    <div class="execution-period-heading">
                        <h3>{{ $student }}</h3>
                        <span>{{ count($documents) }} {{ count($documents) === 1 ? 'archivo' : 'archivos' }}</span>
                    </div>
                    @foreach ($documents as $document)
                        <article class="execution-report">
                            <div><h4>{{ $document['title'] }}</h4></div>
                            <button class="btn btn-secondary btn-compact" type="button" data-open-pdf
                                    data-title="{{ $document['title'] }} · {{ $student }}"
                                    data-preview="{{ $document['preview_url'] }}"
                                    data-external="{{ $document['link'] }}">
                                Visualizar documento
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </article>
                    @endforeach
                </section>
            @endforeach
        </div>
    </section>
</div>
