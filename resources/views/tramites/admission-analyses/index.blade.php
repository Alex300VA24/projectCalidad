@extends('layouts.app')

@section('title', 'Análisis de Admisión | SIGI Calidad')
@section('page-label', 'Seguimiento del Estudiante')

@section('content')
    <x-formato-guia codigo="SD-01" />

    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Seguimiento del Estudiante · SD-01</span>
            <h1>Análisis de admisión</h1>
            <p>Resultados de admisión, perfil de ingreso y necesidades de nivelación por periodo.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="analysis-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar análisis
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Periodo</th><th>Informe final</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($analyses as $analysis)
                        <tr>
                            <td data-label="Periodo"><strong class="cell-primary">{{ $analysis->academic_period }}</strong></td>
                            <td data-label="Informe final">{{ $analysis->final_report_path ?? '—' }}</td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('admission-analyses.index', ['edit' => $analysis->id]) }}#analysis-form" aria-label="Editar análisis">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admission-analyses.destroy', $analysis) }}" onsubmit="return confirm('¿Eliminar este análisis?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar análisis"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-state"><h3>No hay análisis registrados</h3><p>Registra el primer análisis de admisión.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="analysis-form" data-form-drawer hidden aria-labelledby="analysis-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="analysis-form-title">{{ $editing ? 'Editar análisis' : 'Registrar análisis' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('admission-analyses.update', $editing) : route('admission-analyses.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label class="span-2"><span>Periodo académico</span><input required name="academic_period" value="{{ old('academic_period', $editing?->academic_period) }}" placeholder="2026-I"></label>
                    <label class="span-2"><span>Ruta del informe final</span><input name="final_report_path" value="{{ old('final_report_path', $editing?->final_report_path) }}" placeholder="Formato PG-03"></label>
                    <label class="span-2"><span>Resultados de admisión (JSON)</span><textarea rows="3" name="admission_results_data">{{ old('admission_results_data', $editing?->admission_results_data ? json_encode($editing->admission_results_data, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Evaluación de perfil de ingreso (JSON)</span><textarea rows="3" name="entry_profile_eval" placeholder="Formato F.M01.04-DDA/PG-01">{{ old('entry_profile_eval', $editing?->entry_profile_eval ? json_encode($editing->entry_profile_eval, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Necesidades de nivelación (JSON)</span><textarea rows="3" name="leveling_needs">{{ old('leveling_needs', $editing?->leveling_needs ? json_encode($editing->leveling_needs, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar análisis' }}</button></div>
            </form>
        </div>
    </section>

    @push('modals')
        <x-pdf-modal />
    @endpush
@endsection
