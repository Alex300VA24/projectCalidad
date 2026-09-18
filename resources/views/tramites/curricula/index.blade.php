@extends('layouts.app')

@section('title', 'Currículos | SIGI Calidad')
@section('page-label', 'Gestión Curricular')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Gestión Curricular · GC</span>
            <h1>Currículos</h1>
            <p>Registra los planes de estudio vigentes que sustentan revisiones, rediseños y sílabos.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="curriculum-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar currículo
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Currículo</th><th>Versión</th><th>Aprobado</th><th>Cursos</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($curricula as $curriculum)
                        <tr>
                            <td data-label="Currículo"><strong class="cell-primary">{{ $curriculum->name }}</strong></td>
                            <td data-label="Versión">{{ $curriculum->version }}</td>
                            <td data-label="Aprobado">{{ $curriculum->approved_at?->translatedFormat('d M Y') ?? '—' }}</td>
                            <td data-label="Cursos">{{ $curriculum->courses_count }}</td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('curricula.index', ['edit' => $curriculum->id]) }}#curriculum-form" aria-label="Editar {{ $curriculum->name }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('curricula.destroy', $curriculum) }}" onsubmit="return confirm('¿Eliminar este currículo?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar {{ $curriculum->name }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><h3>No hay currículos registrados</h3><p>Registra el primer plan de estudios para habilitar cursos y sílabos.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="curriculum-form" data-form-drawer hidden aria-labelledby="curriculum-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="curriculum-form-title">{{ $editing ? 'Editar currículo' : 'Registrar currículo' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('curricula.update', $editing) : route('curricula.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label class="span-2"><span>Nombre del plan de estudios</span><input required name="name" value="{{ old('name', $editing?->name) }}" placeholder="Ej. Plan Curricular Ingeniería de Sistemas"></label>
                    <label><span>Versión</span><input required name="version" value="{{ old('version', $editing?->version) }}" placeholder="2026-I"></label>
                    <label><span>Fecha de aprobación</span><input type="date" name="approved_at" value="{{ old('approved_at', $editing?->approved_at?->format('Y-m-d')) }}"></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar currículo' }}</button></div>
            </form>
        </div>
    </section>
@endsection
