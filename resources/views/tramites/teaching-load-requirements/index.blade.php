@extends('layouts.app')

@section('title', 'Requerimiento de Carga Lectiva | SIGI Calidad')
@section('page-label', 'Ejecución del Plan Curricular')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Ejecución del Plan Curricular · EPC-01</span>
            <h1>Requerimiento de carga lectiva</h1>
            <p>Demanda de perfiles docentes y propuesta del departamento académico por periodo.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="requirement-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar requerimiento
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Periodo</th><th>Conformidad de Dirección</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($requirements as $requirement)
                        <tr>
                            <td data-label="Periodo"><strong class="cell-primary">{{ $requirement->academic_period }}</strong></td>
                            <td data-label="Conformidad"><span class="badge {{ $requirement->director_conformity ? 'success' : 'warning' }}"><i></i>{{ $requirement->director_conformity ? 'Conforme' : 'Pendiente' }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('teaching-load-requirements.index', ['edit' => $requirement->id]) }}#requirement-form" aria-label="Editar requerimiento">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('teaching-load-requirements.destroy', $requirement) }}" onsubmit="return confirm('¿Eliminar este requerimiento?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar requerimiento"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-state"><h3>No hay requerimientos registrados</h3><p>Registra el primer requerimiento de carga lectiva.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="requirement-form" data-form-drawer hidden aria-labelledby="requirement-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="requirement-form-title">{{ $editing ? 'Editar requerimiento' : 'Registrar requerimiento' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('teaching-load-requirements.update', $editing) : route('teaching-load-requirements.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label><span>Periodo académico</span><input required name="academic_period" value="{{ old('academic_period', $editing?->academic_period) }}" placeholder="2026-II"></label>
                    <label style="display:flex;align-items:center;gap:10px;flex-direction:row"><input type="checkbox" style="width:auto;min-height:0" name="director_conformity" value="1" @checked(old('director_conformity', $editing?->director_conformity))><span>Conformidad de Dirección</span></label>
                    <label class="span-2"><span>Demandas de perfil por curso (JSON)</span><textarea rows="3" name="course_profile_demands">{{ old('course_profile_demands', $editing?->course_profile_demands ? json_encode($editing->course_profile_demands, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Propuesta del departamento (JSON)</span><textarea rows="3" name="department_proposal">{{ old('department_proposal', $editing?->department_proposal ? json_encode($editing->department_proposal, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar requerimiento' }}</button></div>
            </form>
        </div>
    </section>
@endsection
