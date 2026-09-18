@extends('layouts.app')

@section('title', 'Evaluación de Objetivos Educacionales | SIGI Calidad')
@section('page-label', 'Seguimiento al Egresado')

@section('content')
    <x-formato-guia codigo="SE-02" />

    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Seguimiento al Egresado · SE-02</span>
            <h1>Evaluación de objetivos educacionales</h1>
            <p>Encuestas a grupos de interés y retroalimentación curricular por periodo.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="objective-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar evaluación
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Periodo</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($evaluations as $evaluation)
                        <tr>
                            <td data-label="Periodo"><strong class="cell-primary">{{ $evaluation->academic_period }}</strong></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('educational-objective-evaluations.index', ['edit' => $evaluation->id]) }}#objective-form" aria-label="Editar evaluación">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('educational-objective-evaluations.destroy', $evaluation) }}" onsubmit="return confirm('¿Eliminar esta evaluación?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar evaluación"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2"><div class="empty-state"><h3>No hay evaluaciones registradas</h3><p>Registra la primera evaluación de objetivos educacionales.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="objective-form" data-form-drawer hidden aria-labelledby="objective-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="objective-form-title">{{ $editing ? 'Editar evaluación' : 'Registrar evaluación' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('educational-objective-evaluations.update', $editing) : route('educational-objective-evaluations.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label class="span-2"><span>Periodo académico</span><input required name="academic_period" value="{{ old('academic_period', $editing?->academic_period) }}" placeholder="2026-II"></label>
                    <label class="span-2"><span>Encuesta a grupos de interés (JSON)</span><textarea rows="3" name="stakeholders_survey_data">{{ old('stakeholders_survey_data', $editing?->stakeholders_survey_data ? json_encode($editing->stakeholders_survey_data, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Informe de nivel de competencias (JSON)</span><textarea rows="3" name="competency_level_report" placeholder="Formato F-M01.05-DCU/PG-09">{{ old('competency_level_report', $editing?->competency_level_report ? json_encode($editing->competency_level_report, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Acciones de retroalimentación curricular (JSON)</span><textarea rows="3" name="curriculum_feedback_actions">{{ old('curriculum_feedback_actions', $editing?->curriculum_feedback_actions ? json_encode($editing->curriculum_feedback_actions, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar evaluación' }}</button></div>
            </form>
        </div>
    </section>

    @push('modals')
        <x-pdf-modal />
    @endpush
@endsection
