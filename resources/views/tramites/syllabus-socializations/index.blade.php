@extends('layouts.app')

@section('title', 'Socialización de Sílabos | SIGI Calidad')
@section('page-label', 'Evaluación del Estudiante')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Evaluación del Estudiante · EV-01</span>
            <h1>Socialización de sílabos</h1>
            <p>Actas de socialización de sílabos con estudiantes al inicio del periodo.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="socialization-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar socialización
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Curso</th><th>Docente</th><th>Fecha</th><th>Firmas</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($socializations as $socialization)
                        <tr>
                            <td data-label="Curso"><strong class="cell-primary">{{ $socialization->course?->name ?? '—' }}</strong></td>
                            <td data-label="Docente">{{ $socialization->teacher?->name ?? '—' }}</td>
                            <td data-label="Fecha">{{ $socialization->socialization_date?->translatedFormat('d M Y') }}</td>
                            <td data-label="Firmas">{{ $socialization->student_signatures_count }}</td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('syllabus-socializations.index', ['edit' => $socialization->id]) }}#socialization-form" aria-label="Editar socialización">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('syllabus-socializations.destroy', $socialization) }}" onsubmit="return confirm('¿Eliminar este registro?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar socialización"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><h3>No hay socializaciones registradas</h3><p>Registra la primera acta de socialización.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="socialization-form" data-form-drawer hidden aria-labelledby="socialization-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="socialization-form-title">{{ $editing ? 'Editar socialización' : 'Registrar socialización' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('syllabus-socializations.update', $editing) : route('syllabus-socializations.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label class="span-2"><span>Sílabo</span><select required name="syllabus_id"><option value="">Selecciona</option>@foreach($syllabi as $syllabus)<option value="{{ $syllabus->id }}" @selected(old('syllabus_id', $editing?->syllabus_id) == $syllabus->id)>{{ $syllabus->course?->name }} · {{ $syllabus->academic_period }}</option>@endforeach</select></label>
                    <label><span>Curso</span><select required name="course_id"><option value="">Selecciona</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id', $editing?->course_id) == $course->id)>{{ $course->name }}</option>@endforeach</select></label>
                    <label><span>Docente</span><select required name="teacher_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('teacher_id', $editing?->teacher_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Fecha de socialización</span><input required type="date" name="socialization_date" value="{{ old('socialization_date', $editing?->socialization_date?->format('Y-m-d')) }}"></label>
                    <label><span>N.º de firmas de estudiantes</span><input required type="number" min="0" name="student_signatures_count" value="{{ old('student_signatures_count', $editing?->student_signatures_count ?? 0) }}"></label>
                    <label class="span-2"><span>Ruta del acta</span><input name="act_path" value="{{ old('act_path', $editing?->act_path) }}" placeholder="storage/actas/..."></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar socialización' }}</button></div>
            </form>
        </div>
    </section>
@endsection
