@extends('layouts.app')

@section('title', 'Tutorías | SIGI Calidad')
@section('page-label', 'Seguimiento del Estudiante')

@section('content')
    <x-formato-guia codigo="SD-02" />

    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Seguimiento del Estudiante · SD-02</span>
            <h1>Sesiones de tutoría</h1>
            <p>Registro de atenciones de tutoría entre docentes tutores y estudiantes.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="session-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar sesión
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Estudiante</th><th>Tutor</th><th>Fecha</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td data-label="Estudiante"><strong class="cell-primary">{{ $session->student?->name ?? '—' }}</strong></td>
                            <td data-label="Tutor">{{ $session->tutor?->name ?? '—' }}</td>
                            <td data-label="Fecha">{{ $session->session_date?->translatedFormat('d M Y') }}</td>
                            <td data-label="Estado"><span class="badge">{{ $session->status }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('tutoring-sessions.index', ['edit' => $session->id]) }}#session-form" aria-label="Editar sesión">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('tutoring-sessions.destroy', $session) }}" onsubmit="return confirm('¿Eliminar esta sesión?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar sesión"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><h3>No hay sesiones registradas</h3><p>Registra la primera sesión de tutoría.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="session-form" data-form-drawer hidden aria-labelledby="session-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="session-form-title">{{ $editing ? 'Editar sesión' : 'Registrar sesión' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('tutoring-sessions.update', $editing) : route('tutoring-sessions.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label><span>Programa de estudios</span><select required name="programa_estudio_id"><option value="">Selecciona</option>@foreach($programas as $programa)<option value="{{ $programa->id }}" @selected(old('programa_estudio_id', $editing?->programa_estudio_id) == $programa->id)>{{ $programa->nombre }}</option>@endforeach</select></label>
                    <label><span>Periodo</span><select required name="periodo_academico_id"><option value="">Selecciona</option>@foreach($periodos as $periodo)<option value="{{ $periodo->id }}" @selected(old('periodo_academico_id', $editing?->periodo_academico_id) == $periodo->id)>{{ $periodo->codigo }}</option>@endforeach</select></label>
                    <label><span>Estudiante</span><select required name="student_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('student_id', $editing?->student_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Tutor</span><select required name="tutor_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('tutor_id', $editing?->tutor_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Fecha de sesión</span><input required type="date" name="session_date" value="{{ old('session_date', $editing?->session_date?->format('Y-m-d')) }}"></label>
                    <label><span>Estado</span><select required name="status">@foreach(['programada' => 'Programada', 'realizada' => 'Realizada', 'cancelada' => 'Cancelada'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $editing?->status ?? 'programada') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label class="span-2"><span>Plan de tutoría</span><input name="plan_id" value="{{ old('plan_id', $editing?->plan_id) }}" placeholder="Formato F.M01.04-DDA/PG-04"></label>
                    <label class="span-2"><span>Registro de atención (JSON)</span><textarea rows="3" name="attention_registry" placeholder="Formato PG-05">{{ old('attention_registry', $editing?->attention_registry ? json_encode($editing->attention_registry, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar sesión' }}</button></div>
            </form>
        </div>
    </section>

    @push('modals')
        <x-pdf-modal />
    @endpush
@endsection
