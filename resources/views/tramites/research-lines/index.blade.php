@extends('layouts.app')

@section('title', 'Líneas de Investigación | SIGI Calidad')
@section('page-label', 'Investigación Formativa')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Investigación Formativa · IF</span>
            <h1>Líneas de investigación</h1>
            <p>Catálogo institucional de líneas que orientan los proyectos de investigación formativa.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="research-line-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar línea
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Línea de investigación</th><th>Descripción</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($researchLines as $line)
                        <tr>
                            <td data-label="Línea"><strong class="cell-primary">{{ $line->name }}</strong></td>
                            <td data-label="Descripción">{{ Str::limit($line->description, 80) ?: '—' }}</td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('research-lines.index', ['edit' => $line->id]) }}#research-line-form" aria-label="Editar {{ $line->name }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('research-lines.destroy', $line) }}" onsubmit="return confirm('¿Eliminar esta línea?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar {{ $line->name }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-state"><h3>No hay líneas registradas</h3><p>Registra la primera línea de investigación.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="research-line-form" data-form-drawer hidden aria-labelledby="research-line-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="research-line-form-title">{{ $editing ? 'Editar línea' : 'Registrar línea' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('research-lines.update', $editing) : route('research-lines.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label class="span-2"><span>Nombre</span><input required name="name" value="{{ old('name', $editing?->name) }}" placeholder="Ej. Ingeniería de Software y Calidad"></label>
                    <label class="span-2"><span>Descripción</span><textarea rows="3" name="description" placeholder="Alcance de la línea">{{ old('description', $editing?->description) }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar línea' }}</button></div>
            </form>
        </div>
    </section>
@endsection
