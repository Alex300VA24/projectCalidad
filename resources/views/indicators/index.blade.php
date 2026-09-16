@extends('layouts.app')

@section('title', 'Indicadores | SIGI Calidad')
@section('page-label', 'Gestión de indicadores')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Seguimiento institucional</span>
            <h1>Indicadores</h1>
            <p>Registra metas, actualiza avances y detecta desviaciones a tiempo.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="indicator-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Crear indicador
        </button>
    </header>

    <section class="panel toolbar-panel reveal">
        <form class="search-form" method="GET" action="{{ route('indicators.index') }}">
            <label class="search-box">
                <span class="sr-only">Buscar indicadores</span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, código o área">
            </label>
            <button class="btn btn-secondary btn-compact" type="submit">Buscar</button>
            @if(request('buscar'))
                <a class="text-link" href="{{ route('indicators.index') }}">Limpiar</a>
            @endif
        </form>
        <span class="result-count">{{ $indicators->count() }} {{ Str::plural('resultado', $indicators->count()) }}</span>
    </section>

    <section class="panel table-panel reveal">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Indicador</th><th>Responsable</th><th>Avance</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($indicators as $indicator)
                        <tr>
                            <td data-label="Indicador">
                                <div class="table-title"><span class="code">{{ $indicator->code }}</span><div><strong>{{ $indicator->name }}</strong><small>{{ $indicator->area }} · {{ $indicator->frequency }}</small></div></div>
                            </td>
                            <td data-label="Responsable"><strong class="cell-primary">{{ $indicator->responsible }}</strong><small>{{ $indicator->period }}</small></td>
                            <td data-label="Avance">
                                <div class="table-progress"><div><span>{{ $indicator->current_value }} / {{ $indicator->target_value }} {{ $indicator->unit }}</span><strong>{{ $indicator->progress }}%</strong></div><div class="progress-track"><span class="{{ $indicator->status_key }}" style="width: {{ $indicator->progress }}%"></span></div></div>
                            </td>
                            <td data-label="Estado"><span class="badge {{ $indicator->status_key }}"><i></i>{{ $indicator->status }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <button class="icon-btn small" type="button" data-edit-indicator data-id="{{ $indicator->id }}" data-name="{{ $indicator->name }}" data-current="{{ $indicator->current_value }}" aria-label="Actualizar {{ $indicator->name }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </button>
                                    <form method="POST" action="{{ route('indicators.destroy', $indicator) }}" onsubmit="return confirm('¿Eliminar este indicador?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar {{ $indicator->name }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><span class="empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V9m6 10V5m6 14v-7m4 7H2"/></svg></span><h3>No encontramos indicadores</h3><p>Prueba otra búsqueda o registra un indicador nuevo.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="indicator-form" data-form-drawer hidden aria-labelledby="indicator-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header"><div><span class="eyebrow">Nuevo registro</span><h2 id="indicator-form-title">Crear indicador</h2></div><button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button></header>
            <form class="drawer-form" method="POST" action="{{ route('indicators.store') }}">
                @csrf
                <div class="form-grid">
                    <label><span>Código</span><input required name="code" value="{{ old('code') }}" placeholder="CAL-01"></label>
                    <label><span>Periodo</span><input required name="period" value="{{ old('period') }}" placeholder="2026 - III"></label>
                    <label class="span-2"><span>Nombre del indicador</span><input required name="name" value="{{ old('name') }}" placeholder="Ej. Satisfacción de usuarios"></label>
                    <label class="span-2"><span>Objetivo</span><textarea required name="objective" rows="3" placeholder="¿Qué permite evaluar este indicador?">{{ old('objective') }}</textarea></label>
                    <label><span>Área</span><input required name="area" value="{{ old('area') }}" placeholder="Oficina de Calidad"></label>
                    <label><span>Responsable</span><input required name="responsible" value="{{ old('responsible') }}" placeholder="Nombre o unidad"></label>
                    <label><span>Meta</span><input required type="number" step="0.01" min="0.01" name="target_value" value="{{ old('target_value') }}" placeholder="100"></label>
                    <label><span>Valor actual</span><input required type="number" step="0.01" min="0" name="current_value" value="{{ old('current_value', 0) }}"></label>
                    <label><span>Unidad</span><input required name="unit" value="{{ old('unit') }}" placeholder="%, días, proyectos"></label>
                    <label><span>Frecuencia</span><select required name="frequency"><option value="">Selecciona</option>@foreach(['Mensual','Trimestral','Semestral','Anual'] as $frequency)<option @selected(old('frequency') === $frequency)>{{ $frequency }}</option>@endforeach</select></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">Guardar indicador</button></div>
            </form>
        </div>
    </section>

    <section class="simple-modal" data-update-modal hidden>
        <div class="modal-backdrop" data-close-update></div>
        <form class="compact-modal" method="POST" data-update-form>
            @csrf @method('PATCH')
            <header><div><span class="eyebrow">Registrar avance</span><h2 data-update-title>Actualizar indicador</h2></div><button class="icon-btn" type="button" data-close-update aria-label="Cerrar"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button></header>
            <label><span>Nuevo valor actual</span><input type="number" step="0.01" min="0" name="current_value" data-update-value required></label>
            <footer><button class="btn btn-secondary" type="button" data-close-update>Cancelar</button><button class="btn btn-primary" type="submit">Actualizar avance</button></footer>
        </form>
    </section>
@endsection
