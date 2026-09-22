@extends('layouts.app')

@section('title', 'Mapa de Procesos | SIGI Calidad')
@section('page-label', 'Mapa de Procesos y Gestión Documental')

@section('content')
    @php
        $processCount = collect($sections)->sum(fn (array $section): int => count($section['procesos'] ?? []));
        $formatCount = collect($sections)->sum(
            fn (array $section): int => collect($section['procesos'] ?? [])->sum(
                fn (array $process): int => count($process['formats'] ?? []),
            ),
        );
    @endphp

    <div data-process-map>
        <header class="mapa-hero reveal">
            <div class="mapa-hero-copy">
                <span class="eyebrow">Gestión integral por procesos</span>
                <h1>Mapa de Procesos y Gestión Documental</h1>
                <p>
                    Sigue la ruta académica completa, identifica responsables y consulta cada formato o evidencia oficial desde un solo lugar.
                </p>
                <div class="mapa-hero-actions">
                    <a class="btn btn-primary" href="#catalogo-procesos">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h10"/><path d="m17 16 3 3-3 3"/></svg>
                        Explorar procesos
                    </a>
                    <a class="btn btn-secondary" href="{{ route('quality-indicators.dashboard') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9m5 10V5m5 14v-8m5 8V7"/><path d="m4 8 5-3 5 5 5-4"/></svg>
                        Dashboard de calidad
                    </a>
                </div>
            </div>

            <dl class="mapa-hero-metrics" aria-label="Resumen del mapa de procesos">
                <div>
                    <dt>Etapas</dt>
                    <dd>{{ count($sections) }}</dd>
                </div>
                <div>
                    <dt>Procesos</dt>
                    <dd>{{ $processCount }}</dd>
                </div>
                <div>
                    <dt>Formatos</dt>
                    <dd>{{ $formatCount }}</dd>
                </div>
            </dl>
        </header>

        <section class="panel mapa-overview reveal" aria-labelledby="ruta-principal-title">
            <div class="mapa-section-heading">
                <div>
                    <span class="eyebrow">Vista ejecutiva</span>
                    <h2 id="ruta-principal-title">Ruta principal de formación</h2>
                    <p>Del diseño curricular a los resultados. Selecciona una etapa para revisar procesos, responsables y documentos.</p>
                </div>
                <span class="mapa-flow-key"><i></i> Flujo académico</span>
            </div>

            <nav aria-label="Etapas del mapa de procesos">
                <ol class="mapa-flow">
                    @foreach ($sections as $meta)
                        <li class="accent-{{ $meta['accent'] }}">
                            <a href="#etapa-{{ $loop->iteration }}">
                                <span class="mapa-flow-number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="mapa-flow-copy">
                                    <small>Sección {{ $loop->iteration }}</small>
                                    <strong>{{ $meta['label'] }}</strong>
                                    <span>{{ $meta['subtitle'] }}</span>
                                </span>
                                <span class="mapa-flow-count">{{ count($meta['procesos'] ?? []) }} procesos</span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </nav>

            <div class="mapa-feedback">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 7h-9a5 5 0 0 0-5 5v5"/><path d="m9 14-3 3-3-3"/></svg>
                <div>
                    <strong>El ciclo no termina con el egreso.</strong>
                    <span>Indicadores, empleabilidad y objetivos educacionales retroalimentan la siguiente revisión curricular.</span>
                </div>
                <a href="{{ route('quality-indicators.dashboard') }}">Ver mejora continua</a>
            </div>
        </section>

        <section id="catalogo-procesos" class="mapa-catalog" aria-labelledby="catalogo-title">
            <div class="mapa-section-heading mapa-catalog-heading">
                <div>
                    <span class="eyebrow">Detalle operativo</span>
                    <h2 id="catalogo-title">Procesos, responsables y formatos</h2>
                    <p>Busca por nombre, código, responsable o documento.</p>
                </div>
            </div>

            <div class="panel mapa-toolbar">
                <label class="mapa-search">
                    <span class="sr-only">Buscar en el mapa de procesos</span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                    <input type="search" placeholder="Ejemplo: sílabo, matrícula, GC-03…" autocomplete="off" data-process-search>
                </label>
                <label class="mapa-entity-filter">
                    <span class="sr-only">Filtrar por responsable</span>
                    <select data-process-entity-filter>
                        <option value="">Todos los responsables</option>
                        @foreach ($entities as $entity)
                            <option value="{{ Str::lower($entity) }}">{{ $entity }}</option>
                        @endforeach
                    </select>
                </label>
                <p class="mapa-result-count" aria-live="polite" data-process-count>
                    {{ $processCount }} procesos disponibles
                </p>
            </div>

            <div class="mapa-catalog-grid">
                @foreach ($sections as $meta)
                    <section id="etapa-{{ $loop->iteration }}" class="panel mapa-lane accent-{{ $meta['accent'] }}" data-process-section>
                        <header class="mapa-lane-head">
                            <span class="mapa-lane-icon" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <span class="eyebrow">Sección {{ $loop->iteration }}</span>
                                <h2>{{ $meta['label'] }}</h2>
                                <p>{{ $meta['subtitle'] }}</p>
                            </div>
                            <span class="mapa-lane-total">{{ count($meta['procesos'] ?? []) }}</span>
                        </header>

                        <div class="mapa-lane-cards">
                            @foreach ($meta['procesos'] ?? [] as $proceso)
                                @php
                                    $searchableText = collect([
                                        $proceso['code'],
                                        $proceso['title'],
                                        $proceso['role'],
                                        $proceso['description'],
                                        collect($proceso['formats'] ?? [])->pluck('code')->implode(' '),
                                        collect($proceso['formats'] ?? [])->pluck('name')->implode(' '),
                                    ])->implode(' ');
                                @endphp
                                <article class="proceso-card" data-process-card data-searchable="{{ Str::lower($searchableText) }}" data-entities="{{ Str::lower(implode('|', $proceso['entities'] ?? [])) }}">
                                    <div class="proceso-card-main">
                                        <div class="proceso-card-meta">
                                            <span class="badge">{{ $proceso['code'] }}</span>
                                            @if (!empty($proceso['indicator_code']))
                                                <span class="badge badge-indicator">Indicador {{ $proceso['indicator_code'] }}</span>
                                            @endif
                                        </div>
                                        <h3>{{ $proceso['title'] }}</h3>
                                        <p class="proceso-owner">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3"/><path d="M5 21a7 7 0 0 1 14 0"/></svg>
                                            <span><small>Responsable</small>{{ $proceso['role'] }}</span>
                                        </p>
                                        <p class="proceso-description">{{ $proceso['description'] }}</p>
                                    </div>

                                    <div class="proceso-card-actions">
                                        @if (!empty($proceso['formats']))
                                            <details class="proceso-formats">
                                                <summary>
                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5"/></svg>
                                                    {{ count($proceso['formats']) }} {{ count($proceso['formats']) === 1 ? 'formato oficial' : 'formatos oficiales' }}
                                                    <svg class="proceso-format-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
                                                </summary>
                                                <div class="proceso-format-list">
                                                    @foreach ($proceso['formats'] as $formato)
                                                        @php
                                                            $formatoUrl = route('formatos.show', Str::slug($proceso['code'].' '.$formato['code']));
                                                            $esPdf = strtolower(pathinfo($formato['path'], PATHINFO_EXTENSION)) === 'pdf';
                                                            $previewUrl = $esPdf ? $formatoUrl : 'https://view.officeapps.live.com/op/embed.aspx?src='.urlencode($formatoUrl);
                                                            $llenadoUrl = \App\Services\MapaProcesosCatalogService::filledLink($proceso['code'], $formato['code']);
                                                        @endphp
                                                        <div class="proceso-format-row">
                                                            <span class="proceso-format-label"><strong>{{ $formato['code'] }}</strong>{{ $formato['name'] }}</span>
                                                            <div class="proceso-format-actions">
                                                                <button type="button"
                                                                        @if ($proceso['code'] === 'EPC-01' && in_array($formato['code'], ['M01.01.03.01-F-005', 'M01.01.03.01-F-013'], true))
                                                                            data-open-execution-reports="{{ $formato['code'] }}"
                                                                        @elseif ($proceso['code'] === 'SD-02' && $formato['code'] === 'F.M01.04-DDA/PG-06')
                                                                            data-open-student-references
                                                                        @else
                                                                            data-open-pdf
                                                                            data-title="{{ $formato['code'] }} {{ $formato['name'] }}"
                                                                            data-preview="{{ $previewUrl }}"
                                                                            data-external="{{ $formatoUrl }}"
                                                                        @endif
                                                                        title="Ver {{ $formato['name'] }}">
                                                                    Ver
                                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                                                </button>
                                                                <a href="{{ $formatoUrl }}" title="Descargar {{ $formato['name'] }}">
                                                                    Descargar
                                                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 21h16"/></svg>
                                                                </a>
                                                                @if ($llenadoUrl)
                                                                    <a class="proceso-format-llenado" href="{{ $llenadoUrl }}" target="_blank" rel="noopener" title="Ver ejemplo llenado en Drive">
                                                                        Ver llenado
                                                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3h7v7"/><path d="M10 14 21 3"/><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"/></svg>
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </details>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="panel mapa-empty" data-process-empty hidden>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4M8 11h6"/></svg>
                <h3>No encontramos procesos</h3>
                <p>Prueba con otro nombre, código, responsable o formato.</p>
            </div>
        </section>

        <section class="mapa-transversal" aria-labelledby="transversal-title">
            <div class="mapa-section-heading">
                <div>
                    <span class="eyebrow">Capas transversales</span>
                    <h2 id="transversal-title">Lo que sostiene y mejora el flujo</h2>
                </div>
            </div>

            <div class="mapa-support-grid">
                <article class="panel support-card accent-green">
                    <div class="support-card-head">
                        <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/><path d="M19 12h2M3 12h2m7-9v2m0 14v2M5.6 5.6 7 7m10 10 1.4 1.4M18.4 5.6 17 7M7 17l-1.4 1.4"/></svg></span>
                        <div><small>Soporte</small><strong>Recursos institucionales</strong></div>
                    </div>
                    <p>Gestión administrativa, tecnológica, documental y de acompañamiento académico.</p>
                    <div class="support-links">
                        <a href="{{ route('documents.index') }}">Gestión documental</a>
                    </div>
                </article>

                <article class="panel support-card accent-blue">
                    <div class="support-card-head">
                        <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9m5 10V5m5 14v-8m5 8V7"/><path d="m4 8 5-3 5 5 5-4"/></svg></span>
                        <div><small>Estrategia</small><strong>Dirección y calidad</strong></div>
                    </div>
                    <p>Planificación, control de indicadores y decisiones para asegurar la mejora continua.</p>
                    <div class="support-links">
                        <a href="{{ route('quality-indicators.dashboard') }}">Aseguramiento de calidad</a>
                    </div>
                </article>

                <article class="panel support-card accent-indigo">
                    <div class="support-card-head">
                        <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg></span>
                        <div><small>Resultados</small><strong>Egreso y retroalimentación</strong></div>
                    </div>
                    <p>Graduación, inserción laboral y evaluación de objetivos educacionales.</p>
                </article>
            </div>
        </section>
    </div>

    @push('modals')
        <x-pdf-modal />
        <x-quality-evidence-modals
            :execution-reports-by-period="$executionReportsByPeriod"
            :consolidated-reports-by-period="$consolidatedReportsByPeriod"
            :student-references="$studentReferences"
        />
    @endpush
@endsection
