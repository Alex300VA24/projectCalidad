@extends('layouts.app')

@section('title', 'Historiales Académicos Físicos | SIGI Calidad')
@section('page-label', 'Certificación Histórica')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Certificación Histórica · CERT-01</span>
            <h1>Historiales académicos físicos</h1>
            <p>Elaboración del historial de estudiantes ingresantes hasta el año 2007, a partir de actas impresas.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="history-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar historial
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Estudiante</th><th>Año de ingreso</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($histories as $history)
                        <tr>
                            <td data-label="Estudiante"><strong class="cell-primary">{{ $history->student?->name ?? '—' }}</strong></td>
                            <td data-label="Año de ingreso">{{ $history->entry_year }}</td>
                            <td data-label="Estado"><span class="badge">{{ $history->status }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('physical-academic-histories.index', ['edit' => $history->id]) }}#history-form" aria-label="Editar historial">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('physical-academic-histories.destroy', $history) }}" onsubmit="return confirm('¿Eliminar este historial?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar historial"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state"><h3>No hay historiales registrados</h3><p>Registra el primer historial académico físico.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="history-form" data-form-drawer hidden aria-labelledby="history-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="history-form-title">{{ $editing ? 'Editar historial' : 'Registrar historial' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('physical-academic-histories.update', $editing) : route('physical-academic-histories.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label><span>Estudiante</span><select required name="student_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('student_id', $editing?->student_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Año de ingreso (hasta 2007)</span><input required type="number" max="2007" name="entry_year" value="{{ old('entry_year', $editing?->entry_year) }}"></label>
                    <label class="span-2"><span>Estado</span><select required name="status">@foreach(['elaborado' => 'Elaborado', 'verificado' => 'Verificado', 'enviado_registros' => 'Enviado a Registros'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $editing?->status ?? 'elaborado') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label class="span-2"><span>Referencias de actas fuente (JSON)</span><textarea rows="3" name="source_acts_references">{{ old('source_acts_references', $editing?->source_acts_references ? json_encode($editing->source_acts_references, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Datos del historial físico (JSON)</span><textarea rows="3" name="physical_history_data">{{ old('physical_history_data', $editing?->physical_history_data ? json_encode($editing->physical_history_data, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar historial' }}</button></div>
            </form>
        </div>
    </section>
@endsection
