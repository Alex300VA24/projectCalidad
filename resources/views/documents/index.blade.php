@extends('layouts.app')

@section('title', 'Documentos | SIGI Calidad')
@section('page-label', 'Repositorio de evidencias')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Memoria institucional</span>
            <h1>Documentos</h1>
            <p>Consulta documentos institucionales, sílabos visados por semestre y formatos del sistema de calidad.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="document-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Vincular documento
        </button>
    </header>

    <nav class="document-type-nav reveal" aria-label="Tipo de documento">
        <a @class(['active' => $activeType === 'institucional']) href="{{ route('documents.index', ['tipo' => 'institucional']) }}" @if ($activeType === 'institucional') aria-current="page" @endif>
            <span class="document-type-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7h7l2 2h9v10H3z"/><path d="M3 7V5h7l2 2"/></svg></span>
            <span><strong>Documentos institucionales</strong><small>Normativa y gestión institucional</small></span>
        </a>
        <a @class(['active' => $activeType === 'silabo']) href="{{ route('documents.index', ['tipo' => 'silabo']) }}" @if ($activeType === 'silabo') aria-current="page" @endif>
            <span class="document-type-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5a3 3 0 0 1 3-3h13v17H7a3 3 0 0 0-3 3z"/><path d="M4 5v17M8 6h8M8 10h8"/></svg></span>
            <span><strong>Sílabos visados</strong><small>Organizados por semestre y ciclo</small></span>
        </a>
        <a @class(['active' => $activeType === 'calidad']) href="{{ route('documents.index', ['tipo' => 'calidad']) }}" @if ($activeType === 'calidad') aria-current="page" @endif>
            <span class="document-type-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l7 3v5c0 4.7-2.8 8-7 10-4.2-2-7-5.3-7-10V6z"/><path d="m9 12 2 2 4-5"/></svg></span>
            <span><strong>Documentos de calidad</strong><small>Evidencias del sistema de calidad</small></span>
        </a>
    </nav>

    @if ($activeType === 'silabo')
        <section class="semester-picker panel reveal" aria-labelledby="semester-picker-title">
            <div>
                <span class="eyebrow">Sílabos</span>
                <h2 id="semester-picker-title">Semestre académico</h2>
                <p>Mostrando {{ $activePeriodo }}. Selecciona otro semestre para cambiar la consulta.</p>
            </div>
            <nav class="semester-grid" aria-label="Semestres disponibles">
            @foreach ($periodos as $periodo)
                    <a @class(['active' => $activePeriodo === $periodo]) href="{{ route('documents.index', ['tipo' => 'silabo', 'periodo' => $periodo]) }}" @if ($activePeriodo === $periodo) aria-current="page" @endif>
                        <span>{{ $periodo }}</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                    </a>
            @endforeach
            </nav>
        </section>

        <section class="cycle-picker panel reveal" aria-labelledby="cycle-picker-title">
            <div>
                <span class="eyebrow">{{ $activePeriodo }}</span>
                <h2 id="cycle-picker-title">Ciclo académico</h2>
                <p>{{ str_ends_with($activePeriodo, '-I') ? 'Este semestre muestra solo ciclos impares.' : 'Este semestre muestra solo ciclos pares.' }}</p>
            </div>
            <nav class="cycle-grid" aria-label="Ciclos disponibles para {{ $activePeriodo }}">
                @foreach ($cycles as $cycle)
                    @php($cycleLabel = match ($cycle) { 1 => '1.er ciclo', 3 => '3.er ciclo', default => $cycle.'.º ciclo' })
                    <a @class(['active' => $activeCycle === $cycle]) href="{{ route('documents.index', ['tipo' => 'silabo', 'periodo' => $activePeriodo, 'ciclo' => $cycle]) }}" @if ($activeCycle === $cycle) aria-current="page" @endif>
                        <span>{{ $cycleLabel }}</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                    </a>
                @endforeach
            </nav>
        </section>
    @endif

    @if ($showDocuments)
        <section class="document-grid reveal" aria-label="Documentos disponibles">
            @foreach ($documents as $document)
                <article class="document-card">
                    <div class="document-card-top">
                        <span class="pdf-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 13h6M9 17h4"/></svg></span>
                        <span class="file-type">PDF</span>
                        <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('¿Retirar este documento?')">
                            @csrf @method('DELETE')
                            <button class="icon-btn small danger-action" type="submit" aria-label="Retirar {{ $document->title }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                        </form>
                    </div>
                    <div class="document-meta">
                        <span>{{ $document->document_type === 'silabo' ? ($document->ciclo_academico.'.º ciclo · '.($document->periodoAcademico->codigo ?? 'Sin semestre')) : $document->type_label }}</span>
                        <time datetime="{{ $document->publication_date->format('Y-m-d') }}">{{ $document->publication_date->translatedFormat('d M Y') }}</time>
                    </div>
                    <h2>{{ $document->title }}</h2>
                    <p>{{ $document->description ?: $document->type_label.' vinculado al repositorio.' }}</p>
                    @if ($document->is_drive_folder)
                        <a class="document-open" href="{{ $document->external_url }}" target="_blank" rel="noopener">
                            Abrir carpeta en Drive
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 5h5v5M11 13l8-8M19 14v5H5V5h5"/></svg>
                        </a>
                    @else
                        <button class="document-open" type="button" data-open-pdf data-title="{{ $document->title }}" data-preview="{{ $document->preview_url }}" data-external="{{ $document->external_url }}">
                            Visualizar documento
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                        </button>
                    @endif
                </article>
            @endforeach

            @if ($activeType === 'calidad')
                @foreach ($qualityDocuments as $qualityDocument)
                    <article class="document-card">
                        <div class="document-card-top">
                            <span class="pdf-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 13h6M9 17h4"/></svg></span>
                            <span class="file-type">{{ $qualityDocument['extension'] }}</span>
                        </div>
                        <div class="document-meta">
                            <span>{{ $qualityDocument['process_code'] }} · {{ $qualityDocument['code'] }}</span>
                            <span>Formato oficial</span>
                        </div>
                        <h2>{{ $qualityDocument['name'] }}</h2>
                        <p>{{ $qualityDocument['process_title'] }}</p>
                        <div class="document-card-actions">
                            @if ($qualityDocument['viewer'] === 'execution-reports')
                                <button class="document-open" type="button" data-open-execution-reports="{{ $qualityDocument['code'] }}">
                                    Visualizar documentos
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            @elseif ($qualityDocument['viewer'] === 'student-references')
                                <button class="document-open" type="button" data-open-student-references>
                                    Visualizar documentos
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                            @endif
                            <a class="document-open document-open-secondary" href="{{ $qualityDocument['url'] }}" target="_blank" rel="noopener">
                                {{ $qualityDocument['viewer'] ? 'Abrir formato' : 'Abrir documento' }}
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 5h5v5M11 13l8-8M19 14v5H5V5h5"/></svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            @endif

            @if ($documents->isEmpty() && $qualityDocuments->isEmpty())
                <div class="empty-state panel span-all">
                    <span class="empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5"/></svg></span>
                    <h3>No hay documentos en esta sección</h3>
                    <p>{{ $activeType === 'silabo' ? 'Vincula el sílabo visado de este semestre para comenzar.' : 'Vincula un documento de Google Drive para comenzar.' }}</p>
                </div>
            @endif
        </section>

        @if ($activeType === 'calidad' && $qualityDocuments->hasPages())
            <nav class="documents-pagination panel reveal" aria-label="Paginación de documentos de calidad">
                <p>Mostrando {{ $qualityDocuments->firstItem() }}–{{ $qualityDocuments->lastItem() }} de {{ $qualityDocuments->total() }} formatos</p>
                <div class="documents-pagination-links">
                    @if ($qualityDocuments->onFirstPage())
                        <span class="pagination-direction" aria-disabled="true">Anterior</span>
                    @else
                        <a class="pagination-direction" href="{{ $qualityDocuments->previousPageUrl() }}" rel="prev">Anterior</a>
                    @endif

                    @foreach ($qualityDocuments->getUrlRange(1, $qualityDocuments->lastPage()) as $page => $url)
                        <a @class(['pagination-page', 'active' => $page === $qualityDocuments->currentPage()]) href="{{ $url }}" @if ($page === $qualityDocuments->currentPage()) aria-current="page" @endif>
                            <span class="sr-only">Página </span>{{ $page }}
                        </a>
                    @endforeach

                    @if ($qualityDocuments->hasMorePages())
                        <a class="pagination-direction" href="{{ $qualityDocuments->nextPageUrl() }}" rel="next">Siguiente</a>
                    @else
                        <span class="pagination-direction" aria-disabled="true">Siguiente</span>
                    @endif
                </div>
            </nav>
        @endif
    @endif

    <section class="form-drawer" id="document-form" data-form-drawer hidden aria-labelledby="document-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header"><div><span class="eyebrow">Nueva evidencia</span><h2 id="document-form-title">Vincular documento</h2></div><button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button></header>
            <form class="drawer-form" method="POST" action="{{ route('documents.store') }}" data-document-form>
                @csrf
                <div class="form-grid">
                    <fieldset class="form-fieldset span-2">
                        <legend>Tipo de documento</legend>
                        <div class="form-grid">
                            <label><input type="radio" name="document_type" value="institucional" @checked(old('document_type', $activeType) === 'institucional')> Documento institucional</label>
                            <label><input type="radio" name="document_type" value="silabo" @checked(old('document_type', $activeType) === 'silabo')> Sílabo visado</label>
                            <label><input type="radio" name="document_type" value="calidad" @checked(old('document_type', $activeType) === 'calidad')> Documento de calidad</label>
                        </div>
                    </fieldset>
                    <label class="span-2"><span>Título</span><input required name="title" value="{{ old('title') }}" placeholder="Nombre del documento"></label>
                    <div class="span-2 form-grid" data-type-panel="silabo">
                        <label><span>Semestre</span><select name="periodo_academico_id" data-syllabus-period>
                            <option value="">Selecciona un semestre</option>
                            @foreach ($periodoOptions as $periodo)
                                <option value="{{ $periodo->id }}" data-period="{{ $periodo->codigo }}" @selected(old('periodo_academico_id') !== null ? (string) old('periodo_academico_id') === (string) $periodo->id : $activePeriodo === $periodo->codigo)>{{ $periodo->codigo }}</option>
                            @endforeach
                        </select></label>
                        <label><span>Ciclo</span><select name="ciclo_academico" data-syllabus-cycle>
                            <option value="">Selecciona un ciclo</option>
                            @foreach (range(1, 10) as $cycle)
                                @php($cycleLabel = match ($cycle) { 1 => '1.er ciclo', 3 => '3.er ciclo', default => $cycle.'.º ciclo' })
                                <option value="{{ $cycle }}" data-parity="{{ $cycle % 2 === 0 ? 'even' : 'odd' }}" @selected(old('ciclo_academico') !== null ? (int) old('ciclo_academico') === $cycle : $activeCycle === $cycle)>{{ $cycleLabel }}</option>
                            @endforeach
                        </select></label>
                    </div>

                    <label><span>Fecha de publicación</span><input required type="date" name="publication_date" value="{{ old('publication_date', now()->format('Y-m-d')) }}"></label>
                    <label class="span-2"><span>Enlace de Google Drive</span><input required type="url" name="drive_url" value="{{ old('drive_url') }}" placeholder="https://drive.google.com/file/d/.../view"></label>
                    <label class="span-2"><span>Descripción</span><textarea name="description" rows="4" placeholder="Breve descripción del contenido">{{ old('description') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">Guardar vínculo</button></div>
            </form>
        </div>
    </section>

    @push('modals')
        <x-pdf-modal />
        @if ($activeType === 'calidad')
            <x-quality-evidence-modals
                :execution-reports-by-period="$executionReportsByPeriod"
                :consolidated-reports-by-period="$consolidatedReportsByPeriod"
                :student-references="$studentReferences"
            />
        @endif
    @endpush
@endsection
