@extends('layouts.app')

@section('title', 'Evaluación Docente | SIGI Calidad')
@section('page-label', 'Ejecución del Plan Curricular')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Ejecución del Plan Curricular · EPC-04</span>
            <h1>Evaluación de desempeño docente</h1>
            <p>Resultados de encuestas estudiantiles y plan de mejora por docente.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="performance-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar evaluación
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Docente</th><th>Periodo</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($evaluations as $evaluation)
                        <tr>
                            <td data-label="Docente"><strong class="cell-primary">{{ $evaluation->teacher?->name ?? '—' }}</strong></td>
                            <td data-label="Periodo">{{ $evaluation->academic_period }}</td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('teacher-performance-evaluations.index', ['edit' => $evaluation->id]) }}#performance-form" aria-label="Editar evaluación">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('teacher-performance-evaluations.destroy', $evaluation) }}" onsubmit="return confirm('¿Eliminar esta evaluación?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar evaluación"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-state"><h3>No hay evaluaciones registradas</h3><p>Registra la primera evaluación docente.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="performance-form" data-form-drawer hidden aria-labelledby="performance-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="performance-form-title">{{ $editing ? 'Editar evaluación' : 'Registrar evaluación' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('teacher-performance-evaluations.update', $editing) : route('teacher-performance-evaluations.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label><span>Docente</span><select required name="teacher_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('teacher_id', $editing?->teacher_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Periodo académico</span><input required name="academic_period" value="{{ old('academic_period', $editing?->academic_period) }}" placeholder="2026-II"></label>
                    <label class="span-2"><span>Resultados de encuesta estudiantil (JSON)</span><textarea rows="3" name="student_survey_results">{{ old('student_survey_results', $editing?->student_survey_results ? json_encode($editing->student_survey_results, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Consolidado de matriz (JSON)</span><textarea rows="3" name="matrix_consolidation">{{ old('matrix_consolidation', $editing?->matrix_consolidation ? json_encode($editing->matrix_consolidation, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Plan de mejora</span><textarea rows="3" name="improvement_plan">{{ old('improvement_plan', $editing?->improvement_plan) }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar evaluación' }}</button></div>
            </form>
        </div>
    </section>
@endsection
