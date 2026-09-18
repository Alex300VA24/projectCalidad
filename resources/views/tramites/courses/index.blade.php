@extends('layouts.app')

@section('title', 'Cursos | SIGI Calidad')
@section('page-label', 'Gestión Curricular')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Gestión Curricular · GC</span>
            <h1>Cursos</h1>
            <p>Catálogo de asignaturas asociadas a cada currículo, base para sílabos y exámenes.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="course-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar curso
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Curso</th><th>Currículo</th><th>Créditos</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($courses as $course)
                        <tr>
                            <td data-label="Curso"><div class="table-title"><span class="code">{{ $course->code }}</span><div><strong>{{ $course->name }}</strong></div></div></td>
                            <td data-label="Currículo">{{ $course->curriculum?->name ?? '—' }}</td>
                            <td data-label="Créditos">{{ $course->credits ?? '—' }}</td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('courses.index', ['edit' => $course->id]) }}#course-form" aria-label="Editar {{ $course->name }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('¿Eliminar este curso?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar {{ $course->name }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state"><h3>No hay cursos registrados</h3><p>Registra el primer curso del catálogo.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="course-form" data-form-drawer hidden aria-labelledby="course-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="course-form-title">{{ $editing ? 'Editar curso' : 'Registrar curso' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('courses.update', $editing) : route('courses.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label><span>Código</span><input required name="code" value="{{ old('code', $editing?->code) }}" placeholder="ISW-201"></label>
                    <label><span>Créditos</span><input type="number" min="0" max="255" name="credits" value="{{ old('credits', $editing?->credits) }}"></label>
                    <label class="span-2"><span>Nombre del curso</span><input required name="name" value="{{ old('name', $editing?->name) }}" placeholder="Ej. Técnicas Digitales para Computación"></label>
                    <label class="span-2"><span>Currículo</span><select name="curriculum_id"><option value="">Sin asignar</option>@foreach($curricula as $curriculum)<option value="{{ $curriculum->id }}" @selected(old('curriculum_id', $editing?->curriculum_id) == $curriculum->id)>{{ $curriculum->name }} ({{ $curriculum->version }})</option>@endforeach</select></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar curso' }}</button></div>
            </form>
        </div>
    </section>
@endsection
