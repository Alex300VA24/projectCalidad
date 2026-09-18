@extends('layouts.app')

@section('title', 'Exámenes Anónimos | SIGI Calidad')
@section('page-label', 'Evaluación del Estudiante')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Evaluación del Estudiante · EV-02A</span>
            <h1>Exámenes anónimos</h1>
            <p>Preparación, aplicación y calificación de pruebas anónimas con sobre lacrado y desglosables.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="exam-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar examen
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Curso</th><th>Docente</th><th>Sobre lacrado</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($exams as $exam)
                        <tr>
                            <td data-label="Curso"><strong class="cell-primary">{{ $exam->course?->name ?? '—' }}</strong></td>
                            <td data-label="Docente">{{ $exam->teacher?->name ?? '—' }}</td>
                            <td data-label="Sobre lacrado"><span class="code">{{ $exam->sealed_envelope_code ?? '—' }}</span></td>
                            <td data-label="Estado"><span class="badge">{{ $exam->status }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('anonymous-exams.index', ['edit' => $exam->id]) }}#exam-form" aria-label="Editar examen">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('anonymous-exams.destroy', $exam) }}" onsubmit="return confirm('¿Eliminar este examen?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar examen"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><h3>No hay exámenes registrados</h3><p>Registra el primer examen anónimo.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="exam-form" data-form-drawer hidden aria-labelledby="exam-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="exam-form-title">{{ $editing ? 'Editar examen' : 'Registrar examen' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('anonymous-exams.update', $editing) : route('anonymous-exams.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label><span>Curso</span><select required name="course_id"><option value="">Selecciona</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id', $editing?->course_id) == $course->id)>{{ $course->name }}</option>@endforeach</select></label>
                    <label><span>Docente</span><select required name="teacher_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('teacher_id', $editing?->teacher_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Fecha de examen</span><input required type="date" name="exam_date" value="{{ old('exam_date', $editing?->exam_date?->format('Y-m-d')) }}"></label>
                    <label><span>N.º de desglosables</span><input required type="number" min="0" name="desglosables_count" value="{{ old('desglosables_count', $editing?->desglosables_count ?? 0) }}"></label>
                    <label><span>Código de formato</span><input required name="format_code" value="{{ old('format_code', $editing?->format_code ?? 'F-M01.03.02.02-DRT/PG-001') }}"></label>
                    <label><span>Código de sobre lacrado</span><input name="sealed_envelope_code" value="{{ old('sealed_envelope_code', $editing?->sealed_envelope_code) }}" placeholder="SOBRE-1-XXXXXXXX"></label>
                    <label class="span-2"><span>Estado</span><select required name="status">@foreach(['preparado' => 'Preparado', 'aplicado' => 'Aplicado', 'desglosado_lacrado' => 'Desglosado y lacrado', 'calificado' => 'Calificado', 'consolidado' => 'Consolidado'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $editing?->status ?? 'preparado') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label class="span-2"><span>Notas ciegas (JSON)</span><textarea rows="3" name="grades_data">{{ old('grades_data', $editing?->grades_data ? json_encode($editing->grades_data, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar examen' }}</button></div>
            </form>
        </div>
    </section>
@endsection
