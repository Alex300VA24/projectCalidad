@extends('layouts.app')

@section('title', 'Documentos | SIGI Calidad')
@section('page-label', 'Repositorio de evidencias')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Memoria institucional</span>
            <h1>Documentos</h1>
            <p>Consulta políticas, instrumentos e informes alojados de forma segura en Google Drive.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="document-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Vincular documento
        </button>
    </header>

    <nav class="section-tabs reveal" aria-label="Filtrar por sección">
        <a @class(['active' => !$activeSection]) href="{{ route('documents.index') }}">Todos <span>{{ \App\Models\Document::count() }}</span></a>
        @foreach ($sections as $section)
            <a @class(['active' => $activeSection === $section]) href="{{ route('documents.index', ['seccion' => $section]) }}">{{ $section }}</a>
        @endforeach
    </nav>

    <section class="document-grid reveal">
        @forelse ($documents as $document)
            <article class="document-card">
                <div class="document-card-top">
                    <span class="pdf-mark"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 13h6M9 17h4"/></svg></span>
                    <span class="file-type">PDF</span>
                    <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('¿Retirar este documento?')">
                        @csrf @method('DELETE')
                        <button class="icon-btn small danger-action" type="submit" aria-label="Retirar {{ $document->title }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                    </form>
                </div>
                <div class="document-meta"><span>{{ $document->section }}</span><time datetime="{{ $document->publication_date->format('Y-m-d') }}">{{ $document->publication_date->translatedFormat('d M Y') }}</time></div>
                <h2>{{ $document->title }}</h2>
                <p>{{ $document->description ?: 'Documento de soporte para la gestión institucional.' }}</p>
                <button class="document-open" type="button" data-open-pdf data-title="{{ $document->title }}" data-preview="{{ $document->preview_url }}" data-external="{{ $document->external_url }}">
                    Visualizar documento
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
                </button>
            </article>
        @empty
            <div class="empty-state panel span-all"><span class="empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5"/></svg></span><h3>No hay documentos en esta sección</h3><p>Vincula un PDF de Google Drive para comenzar.</p></div>
        @endforelse
    </section>

    <section class="form-drawer" id="document-form" data-form-drawer hidden aria-labelledby="document-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header"><div><span class="eyebrow">Nueva evidencia</span><h2 id="document-form-title">Vincular documento</h2></div><button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button></header>
            <form class="drawer-form" method="POST" action="{{ route('documents.store') }}">
                @csrf
                <div class="form-grid">
                    <label class="span-2"><span>Título</span><input required name="title" value="{{ old('title') }}" placeholder="Ej. Informe de seguimiento trimestral"></label>
                    <label><span>Sección</span><input required name="section" list="section-options" value="{{ old('section') }}" placeholder="Selecciona o escribe"><datalist id="section-options">@foreach($sections as $section)<option value="{{ $section }}">@endforeach<option value="Políticas y lineamientos"><option value="Instrumentos de gestión"><option value="Informes de seguimiento"><option value="Planes de mejora"></datalist></label>
                    <label><span>Fecha de publicación</span><input required type="date" name="publication_date" value="{{ old('publication_date', now()->format('Y-m-d')) }}"></label>
                    <label class="span-2"><span>Enlace público de Google Drive</span><input required type="url" name="drive_url" value="{{ old('drive_url') }}" placeholder="https://drive.google.com/file/d/.../view"><small>Configura el archivo como “Cualquier persona con el enlace”.</small></label>
                    <label class="span-2"><span>Descripción</span><textarea name="description" rows="4" placeholder="Breve descripción del contenido">{{ old('description') }}</textarea></label>
                </div>
                <div class="drive-tip"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7h.01"/></svg><p><strong>Antes de guardar</strong>Abre el PDF en Drive, pulsa Compartir y habilita el acceso para cualquier persona con el enlace.</p></div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">Guardar vínculo</button></div>
            </form>
        </div>
    </section>

    @push('modals')
        <x-pdf-modal />
    @endpush
@endsection
