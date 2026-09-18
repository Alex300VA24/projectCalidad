@extends('layouts.app')

@section('title', 'Corrección de Notas | SIGI Calidad')
@section('page-label', 'Evaluación del Estudiante')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Evaluación del Estudiante · EV-03</span>
            <h1>Corrección de notas</h1>
            <p>Solicitudes de rectificación de calificaciones por FUT del estudiante.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="correction-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar solicitud
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Estudiante</th><th>Curso</th><th>FUT</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($corrections as $correction)
                        <tr>
                            <td data-label="Estudiante"><strong class="cell-primary">{{ $correction->student?->name ?? '—' }}</strong></td>
                            <td data-label="Curso">{{ $correction->course?->name ?? '—' }}</td>
                            <td data-label="FUT">{{ $correction->fut_number }}</td>
                            <td data-label="Estado"><span class="badge">{{ $correction->status }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('grade-corrections.index', ['edit' => $correction->id]) }}#correction-form" aria-label="Editar solicitud">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('grade-corrections.destroy', $correction) }}" onsubmit="return confirm('¿Eliminar esta solicitud?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar solicitud"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><h3>No hay solicitudes registradas</h3><p>Registra la primera solicitud de corrección.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="correction-form" data-form-drawer hidden aria-labelledby="correction-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="correction-form-title">{{ $editing ? 'Editar solicitud' : 'Registrar solicitud' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('grade-corrections.update', $editing) : route('grade-corrections.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label><span>Estudiante</span><select required name="student_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('student_id', $editing?->student_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Curso</span><select required name="course_id"><option value="">Selecciona</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id', $editing?->course_id) == $course->id)>{{ $course->name }}</option>@endforeach</select></label>
                    <label><span>Docente</span><select required name="teacher_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('teacher_id', $editing?->teacher_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>N.º de FUT</span><input required name="fut_number" value="{{ old('fut_number', $editing?->fut_number) }}"></label>
                    <label class="span-2"><span>Motivo de la solicitud</span><textarea required rows="2" name="request_reason">{{ old('request_reason', $editing?->request_reason) }}</textarea></label>
                    <label class="span-2"><span>Informe académico</span><textarea rows="3" name="academic_report">{{ old('academic_report', $editing?->academic_report) }}</textarea></label>
                    <label><span>Estado</span><select required name="status">@foreach(['solicitado' => 'Solicitado', 'en_evaluacion' => 'En evaluación', 'aprobado' => 'Aprobado', 'rechazado' => 'Rechazado', 'elevado_decanato' => 'Elevado a Decanato'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $editing?->status ?? 'solicitado') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label style="display:flex;align-items:center;gap:10px;flex-direction:row"><input type="checkbox" style="width:auto;min-height:0" name="elevated_to_dean" value="1" @checked(old('elevated_to_dean', $editing?->elevated_to_dean))><span>Elevado a Decanato</span></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar solicitud' }}</button></div>
            </form>
        </div>
    </section>
@endsection
