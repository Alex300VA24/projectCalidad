@extends('layouts.app')

@section('title', 'Evaluación de Competencias de Egreso | SIGI Calidad')
@section('page-label', 'Evaluación del Estudiante')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Evaluación del Estudiante · EV-04</span>
            <h1>Evaluación de competencias del perfil de egreso</h1>
            <p>Comité, metodología y resultados de la evaluación de competencias por periodo.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="evaluation-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar evaluación
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Periodo</th><th>Miembros del comité</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($evaluations as $evaluation)
                        <tr>
                            <td data-label="Periodo"><strong class="cell-primary">{{ $evaluation->academic_period }}</strong></td>
                            <td data-label="Miembros">{{ count($evaluation->committee_members ?? []) }}</td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('graduate-competency-evaluations.index', ['edit' => $evaluation->id]) }}#evaluation-form" aria-label="Editar evaluación">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('graduate-competency-evaluations.destroy', $evaluation) }}" onsubmit="return confirm('¿Eliminar esta evaluación?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar evaluación"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-state"><h3>No hay evaluaciones registradas</h3><p>Registra la primera evaluación de competencias.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="evaluation-form" data-form-drawer hidden aria-labelledby="evaluation-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="evaluation-form-title">{{ $editing ? 'Editar evaluación' : 'Registrar evaluación' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('graduate-competency-evaluations.update', $editing) : route('graduate-competency-evaluations.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label class="span-2"><span>Periodo académico</span><input required name="academic_period" value="{{ old('academic_period', $editing?->academic_period) }}" placeholder="2026-II"></label>
                    <label class="span-2"><span>Miembros del comité (JSON)</span><textarea required rows="3" name="committee_members" placeholder='["Docente A","Docente B"]'>{{ old('committee_members', $editing?->committee_members ? json_encode($editing->committee_members, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Plan metodológico (JSON)</span><textarea rows="3" name="methodology_plan">{{ old('methodology_plan', $editing?->methodology_plan ? json_encode($editing->methodology_plan, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Informe de evaluación (JSON)</span><textarea rows="3" name="evaluation_report" placeholder="Formato F-M01.03.02.02-DRT/PG-02">{{ old('evaluation_report', $editing?->evaluation_report ? json_encode($editing->evaluation_report, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Acciones de mejora (JSON)</span><textarea rows="3" name="improvement_actions">{{ old('improvement_actions', $editing?->improvement_actions ? json_encode($editing->improvement_actions, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar evaluación' }}</button></div>
            </form>
        </div>
    </section>
@endsection
