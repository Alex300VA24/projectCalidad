@extends('layouts.app')

@section('title', 'Exámenes de Suficiencia | SIGI Calidad')
@section('page-label', 'Evaluación del Estudiante')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Evaluación del Estudiante · EV-02B</span>
            <h1>Exámenes de suficiencia</h1>
            <p>Aprobación de Dirección, jurado, resolución y acta de exámenes de suficiencia.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="sufficiency-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar examen
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Estudiante</th><th>Curso</th><th>Nota</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($exams as $exam)
                        <tr>
                            <td data-label="Estudiante"><strong class="cell-primary">{{ $exam->student?->name ?? '—' }}</strong></td>
                            <td data-label="Curso">{{ $exam->course?->name ?? '—' }}</td>
                            <td data-label="Nota">{{ $exam->score ?? '—' }}</td>
                            <td data-label="Estado"><span class="badge">{{ $exam->status }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('sufficiency-exams.index', ['edit' => $exam->id]) }}#sufficiency-form" aria-label="Editar examen">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('sufficiency-exams.destroy', $exam) }}" onsubmit="return confirm('¿Eliminar este examen?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar examen"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><h3>No hay exámenes registrados</h3><p>Registra el primer examen de suficiencia.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="sufficiency-form" data-form-drawer hidden aria-labelledby="sufficiency-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="sufficiency-form-title">{{ $editing ? 'Editar examen' : 'Registrar examen' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('sufficiency-exams.update', $editing) : route('sufficiency-exams.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label><span>Estudiante</span><select required name="student_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('student_id', $editing?->student_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Curso</span><select required name="course_id"><option value="">Selecciona</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id', $editing?->course_id) == $course->id)>{{ $course->name }}</option>@endforeach</select></label>
                    <label><span>Estado</span><select required name="status">@foreach(['solicitado' => 'Solicitado', 'aprobado_director' => 'Aprobado por Dirección', 'jurado_designado' => 'Jurado designado', 'rendido' => 'Rendido', 'resuelto' => 'Resuelto'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $editing?->status ?? 'solicitado') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label><span>Nota (0-20)</span><input type="number" step="0.01" min="0" max="20" name="score" value="{{ old('score', $editing?->score) }}"></label>
                    <label><span>N.º de resolución</span><input name="resolution_number" value="{{ old('resolution_number', $editing?->resolution_number) }}"></label>
                    <label><span>N.º de acta</span><input name="act_number" value="{{ old('act_number', $editing?->act_number) }}"></label>
                    <label class="span-2"><label style="display:flex;align-items:center;gap:10px;flex-direction:row"><input type="checkbox" style="width:auto;min-height:0" name="director_approval" value="1" @checked(old('director_approval', $editing?->director_approval))><span>Aprobado por Dirección</span></label></label>
                    <label class="span-2"><span>Miembros del jurado (JSON)</span><textarea rows="3" name="jury_members">{{ old('jury_members', $editing?->jury_members ? json_encode($editing->jury_members, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar examen' }}</button></div>
            </form>
        </div>
    </section>
@endsection
