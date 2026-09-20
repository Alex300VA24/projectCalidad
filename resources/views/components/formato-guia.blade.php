@props(['codigo'])

@php
    $proceso = \App\Services\MapaProcesosCatalogService::getByCode($codigo);
    $formatos = $proceso['formats'] ?? [];
    $contextoId = 'proceso-'.\Illuminate\Support\Str::slug($codigo);
@endphp

@if ($proceso)
    <aside class="panel formato-guia reveal" aria-labelledby="{{ $contextoId }}">
        <div class="formato-guia-main">
            <div class="formato-guia-copy">
                <div class="formato-guia-meta">
                    <span class="formato-guia-code">{{ $proceso['code'] }}</span>
                    <span class="eyebrow">{{ $proceso['section_short'] }}</span>
                </div>

                <div class="formato-guia-title-row">
                    <p class="formato-guia-title" id="{{ $contextoId }}">{{ $proceso['title'] }}</p>
                    <span class="formato-guia-role">{{ $proceso['role'] }}</span>
                </div>

                <p class="formato-guia-description">{{ $proceso['description'] }}</p>
            </div>

            <div class="formato-guia-actions">
                @if (! empty($proceso['indicator_code']))
                    <a class="formato-guia-indicator" href="{{ route('quality-indicators.dashboard') }}" title="Consultar este indicador en el dashboard">
                        <span aria-hidden="true"></span>
                        Indicador {{ $proceso['indicator_code'] }}
                    </a>
                @endif

                <a class="btn btn-secondary btn-compact" href="{{ route('mapa-procesos.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 3h6l1 4H8zM5 7h14l-1 14H6z"/><path d="M9 12h6M9 16h4"/></svg>
                    Ver en el mapa
                </a>
            </div>
        </div>

        @if ($formatos)
            <details class="formato-guia-formatos">
                <summary>
                    <span class="formato-guia-format-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 13h6M9 17h4"/></svg>
                    </span>
                    <span class="formato-guia-format-summary">
                        <strong>Formatos e instrumentos oficiales</strong>
                        <small>{{ count($formatos) }} {{ count($formatos) === 1 ? 'archivo disponible' : 'archivos disponibles' }}</small>
                    </span>
                    <svg class="formato-guia-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
                </summary>

                <div class="formato-guia-format-list">
                    @foreach ($formatos as $formato)
                        @php
                            $formatoUrl = route('formatos.show', \Illuminate\Support\Str::slug($proceso['code'].' '.$formato['code']));
                            $esPdf = strtolower(pathinfo($formato['path'], PATHINFO_EXTENSION)) === 'pdf';
                            $llenadoUrl = \App\Services\MapaProcesosCatalogService::filledLink($proceso['code'], $formato['code']);
                        @endphp

                        <div class="formato-guia-format">
                            <span class="formato-guia-format-name">
                                <strong>{{ $formato['code'] }}</strong>
                                <span>{{ $formato['name'] }}</span>
                            </span>

                            <div class="formato-guia-format-actions">
                                @if ($esPdf)
                                    <button class="formato-guia-format-action" type="button"
                                            data-open-pdf
                                            data-title="{{ $formato['code'] }} - {{ $formato['name'] }}"
                                            data-preview="{{ $formatoUrl }}"
                                            data-external="{{ $formatoUrl }}"
                                            aria-label="Ver {{ $formato['name'] }} en PDF">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/></svg>
                                        Ver PDF
                                    </button>
                                @else
                                    <a class="formato-guia-format-action" href="{{ $formatoUrl }}" aria-label="Descargar {{ $formato['name'] }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 21h16"/></svg>
                                        Descargar
                                    </a>
                                @endif

                                @if ($llenadoUrl)
                                    <a class="formato-guia-format-action formato-guia-format-action-ghost" href="{{ $llenadoUrl }}" target="_blank" rel="noopener" aria-label="Ver ejemplo llenado de {{ $formato['name'] }} en Drive">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3h7v7"/><path d="M10 14 21 3"/><path d="M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"/></svg>
                                        Ver llenado
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </details>
        @endif
    </aside>
@endif
