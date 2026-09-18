@extends('layouts.app')

@section('title', 'Rediseños Curriculares | SIGI Calidad')
@section('page-label', 'Gestión Curricular')

@section('content')
    <x-formato-guia codigo="GC-02" />

    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Gestión Curricular · GC-02</span>
            <h1>Rediseños curriculares</h1>
            <p>Plan de trabajo, estructura y validación con grupos de interés para currículos en rediseño.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="redesign-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar rediseño
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Currículo</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($redesigns as $redesign)
                        <tr>
                            <td data-label="Currículo"><strong class="cell-primary">{{ $redesign->curriculum?->name ?? '—' }}</strong></td>
                            <td data-label="Estado"><span class="badge">{{ $redesign->state }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('curriculum-redesigns.index', ['edit' => $redesign->id]) }}#redesign-form" aria-label="Editar rediseño">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('curriculum-redesigns.destroy', $redesign) }}" onsubmit="return confirm('¿Eliminar este rediseño?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar rediseño"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-state"><h3>No hay rediseños registrados</h3><p>Registra el primer rediseño curricular.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="redesign-form" data-form-drawer hidden aria-labelledby="redesign-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="redesign-form-title">{{ $editing ? 'Editar rediseño' : 'Registrar rediseño' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('curriculum-redesigns.update', $editing) : route('curriculum-redesigns.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label class="span-2"><span>Currículo</span><select required name="curriculum_id"><option value="">Selecciona</option>@foreach($curricula as $curriculum)<option value="{{ $curriculum->id }}" @selected(old('curriculum_id', $editing?->curriculum_id) == $curriculum->id)>{{ $curriculum->name }} ({{ $curriculum->version }})</option>@endforeach</select></label>
                    <label class="span-2"><span>Estado</span><select required name="state">@foreach(['en_elaboracion' => 'En elaboración', 'en_validacion' => 'En validación', 'aprobado' => 'Aprobado', 'completado' => 'Completado'] as $value => $label)<option value="{{ $value }}" @selected(old('state', $editing?->state ?? 'en_elaboracion') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label class="span-2"><span>Plan de trabajo (JSON)</span><textarea rows="3" name="work_plan_data">{{ old('work_plan_data', $editing?->work_plan_data ? json_encode($editing->work_plan_data, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Formato de estructura (JSON)</span><textarea rows="3" name="structure_format_data" placeholder="Formato F-M01.01-DPA-002">{{ old('structure_format_data', $editing?->structure_format_data ? json_encode($editing->structure_format_data, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Validación con grupos de interés (JSON)</span><textarea rows="3" name="validation_stakeholders_data">{{ old('validation_stakeholders_data', $editing?->validation_stakeholders_data ? json_encode($editing->validation_stakeholders_data, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar rediseño' }}</button></div>
            </form>
        </div>
    </section>

    @push('modals')
        <x-pdf-modal />
    @endpush
@endsection
