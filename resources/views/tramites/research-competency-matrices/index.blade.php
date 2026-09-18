@extends('layouts.app')

@section('title', 'Matrices de Competencias de Investigación | SIGI Calidad')
@section('page-label', 'Investigación Formativa')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Investigación Formativa · IF-01</span>
            <h1>Matrices de competencias de investigación</h1>
            <p>Matriz de competencias investigativas por currículo, formato F-M01.03.02.04-DDA/PG-02.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="matrix-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar matriz
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Currículo</th><th>Validada</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($matrices as $matrix)
                        <tr>
                            <td data-label="Currículo"><strong class="cell-primary">{{ $matrix->curriculum?->name ?? '—' }}</strong></td>
                            <td data-label="Validada"><span class="badge {{ $matrix->is_validated ? 'success' : 'warning' }}"><i></i>{{ $matrix->is_validated ? 'Sí' : 'Pendiente' }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('research-competency-matrices.index', ['edit' => $matrix->id]) }}#matrix-form" aria-label="Editar matriz">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('research-competency-matrices.destroy', $matrix) }}" onsubmit="return confirm('¿Eliminar esta matriz?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar matriz"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-state"><h3>No hay matrices registradas</h3><p>Registra la primera matriz de competencias.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="matrix-form" data-form-drawer hidden aria-labelledby="matrix-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="matrix-form-title">{{ $editing ? 'Editar matriz' : 'Registrar matriz' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('research-competency-matrices.update', $editing) : route('research-competency-matrices.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label class="span-2"><span>Currículo</span><select required name="curriculum_id"><option value="">Selecciona</option>@foreach($curricula as $curriculum)<option value="{{ $curriculum->id }}" @selected(old('curriculum_id', $editing?->curriculum_id) == $curriculum->id)>{{ $curriculum->name }} ({{ $curriculum->version }})</option>@endforeach</select></label>
                    <label><span>Fecha de validación</span><input type="date" name="validation_date" value="{{ old('validation_date', $editing?->validation_date?->format('Y-m-d')) }}"></label>
                    <label style="display:flex;align-items:center;gap:10px;flex-direction:row"><input type="checkbox" style="width:auto;min-height:0" name="is_validated" value="1" @checked(old('is_validated', $editing?->is_validated))><span>Matriz validada</span></label>
                    <label class="span-2"><span>Matriz de competencias (JSON)</span><textarea rows="4" name="matrix_data">{{ old('matrix_data', $editing?->matrix_data ? json_encode($editing->matrix_data, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar matriz' }}</button></div>
            </form>
        </div>
    </section>
@endsection
