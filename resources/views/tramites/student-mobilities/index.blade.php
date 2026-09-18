@extends('layouts.app')

@section('title', 'Movilidad Estudiantil | SIGI Calidad')
@section('page-label', 'Matrícula y Movilidad')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Matrícula y Movilidad · MAT/MOV</span>
            <h1>Movilidad estudiantil</h1>
            <p>Postulaciones a movilidad y becas, vista interáreas con ORNI, Facultad y Registros Académicos.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="mobility-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar movilidad
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Estudiante</th><th>Destino</th><th>Convocatoria</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($mobilities as $mobility)
                        <tr>
                            <td data-label="Estudiante"><strong class="cell-primary">{{ $mobility->student?->name ?? '—' }}</strong></td>
                            <td data-label="Destino">{{ $mobility->destination_university }}</td>
                            <td data-label="Convocatoria">{{ $mobility->call_type }}</td>
                            <td data-label="Estado"><span class="badge">{{ $mobility->status }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('student-mobilities.index', ['edit' => $mobility->id]) }}#mobility-form" aria-label="Editar movilidad">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('student-mobilities.destroy', $mobility) }}" onsubmit="return confirm('¿Eliminar esta movilidad?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar movilidad"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><h3>No hay postulaciones registradas</h3><p>Registra la primera movilidad estudiantil.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="mobility-form" data-form-drawer hidden aria-labelledby="mobility-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="mobility-form-title">{{ $editing ? 'Editar movilidad' : 'Registrar movilidad' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('student-mobilities.update', $editing) : route('student-mobilities.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label><span>Estudiante</span><select required name="student_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('student_id', $editing?->student_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Universidad de destino</span><input required name="destination_university" value="{{ old('destination_university', $editing?->destination_university) }}"></label>
                    <label><span>Tipo de convocatoria</span><input required name="call_type" value="{{ old('call_type', $editing?->call_type) }}"></label>
                    <label><span>Estado</span><select required name="status">@foreach(['postulado' => 'Postulado', 'seleccionado' => 'Seleccionado', 'en_movilidad' => 'En movilidad', 'convalidado' => 'Convalidado', 'cerrado' => 'Cerrado'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $editing?->status ?? 'postulado') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label><span>Código de registro ORNI</span><input name="orni_registration_code" value="{{ old('orni_registration_code', $editing?->orni_registration_code) }}"></label>
                    <label><span>Estado de subvención</span><input name="subvention_status" value="{{ old('subvention_status', $editing?->subvention_status) }}"></label>
                    <label><span>N.º de resolución de convalidación</span><input name="convalidation_resolution_number" value="{{ old('convalidation_resolution_number', $editing?->convalidation_resolution_number) }}"></label>
                    <div style="display:flex;align-items:center;gap:18px;flex-wrap:wrap">
                        <label style="display:flex;align-items:center;gap:10px;flex-direction:row"><input type="checkbox" style="width:auto;min-height:0" name="is_interareas_view" value="1" @checked(old('is_interareas_view', $editing?->is_interareas_view ?? true))><span>Vista interáreas</span></label>
                        <label style="display:flex;align-items:center;gap:10px;flex-direction:row"><input type="checkbox" style="width:auto;min-height:0" name="fee_exemption" value="1" @checked(old('fee_exemption', $editing?->fee_exemption))><span>Exoneración de pago</span></label>
                    </div>
                    <label class="span-2"><span>Notas equivalentes / convalidadas (JSON)</span><textarea rows="3" name="equivalent_grades_data">{{ old('equivalent_grades_data', $editing?->equivalent_grades_data ? json_encode($editing->equivalent_grades_data, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar movilidad' }}</button></div>
            </form>
        </div>
    </section>
@endsection
