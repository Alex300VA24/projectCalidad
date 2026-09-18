@extends('layouts.app')

@section('title', 'Carpetas de Graduación | SIGI Calidad')
@section('page-label', 'Titulación y Graduación')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Titulación y Graduación · TIT/GRAD</span>
            <h1>Carpetas de graduación</h1>
            <p>Validación de condición de egresado, constancias y envío de datos a SUNEDU.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="folder-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar carpeta
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Estudiante</th><th>Código STU</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($folders as $folder)
                        <tr>
                            <td data-label="Estudiante"><strong class="cell-primary">{{ $folder->student?->name ?? '—' }}</strong></td>
                            <td data-label="Código STU">{{ $folder->stu_registration_code ?? '—' }}</td>
                            <td data-label="Estado"><span class="badge">{{ $folder->status }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('graduate-folders.index', ['edit' => $folder->id]) }}#folder-form" aria-label="Editar carpeta">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('graduate-folders.destroy', $folder) }}" onsubmit="return confirm('¿Eliminar esta carpeta?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar carpeta"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state"><h3>No hay carpetas registradas</h3><p>Registra la primera carpeta de graduación.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="folder-form" data-form-drawer hidden aria-labelledby="folder-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="folder-form-title">{{ $editing ? 'Editar carpeta' : 'Registrar carpeta' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('graduate-folders.update', $editing) : route('graduate-folders.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label><span>Estudiante</span><select required name="student_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('student_id', $editing?->student_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Código de registro STU</span><input name="stu_registration_code" value="{{ old('stu_registration_code', $editing?->stu_registration_code) }}"></label>
                    <label class="span-2"><span>Estado</span><select required name="status">@foreach(['en_verificacion' => 'En verificación', 'observado' => 'Observado', 'completado' => 'Completado', 'enviado_sunedu' => 'Enviado a SUNEDU'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $editing?->status ?? 'en_verificacion') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <div class="span-2" style="display:grid;gap:10px">
                        <label style="display:flex;align-items:center;gap:10px;flex-direction:row"><input type="checkbox" style="width:auto;min-height:0" name="egresado_condition_validated" value="1" @checked(old('egresado_condition_validated', $editing?->egresado_condition_validated))><span>Condición de egresado validada</span></label>
                        <label style="display:flex;align-items:center;gap:10px;flex-direction:row"><input type="checkbox" style="width:auto;min-height:0" name="approval_constancy" value="1" @checked(old('approval_constancy', $editing?->approval_constancy))><span>Constancia de aprobación</span></label>
                        <label style="display:flex;align-items:center;gap:10px;flex-direction:row"><input type="checkbox" style="width:auto;min-height:0" name="expedito_constancy" value="1" @checked(old('expedito_constancy', $editing?->expedito_constancy))><span>Constancia de expedito</span></label>
                        <label style="display:flex;align-items:center;gap:10px;flex-direction:row"><input type="checkbox" style="width:auto;min-height:0" name="no_adeudo_constancy" value="1" @checked(old('no_adeudo_constancy', $editing?->no_adeudo_constancy))><span>Constancia de no adeudo (amnistía)</span></label>
                    </div>
                    <label class="span-2"><span>Payload de datos SUNEDU (JSON)</span><textarea rows="3" name="sunedu_data_payload">{{ old('sunedu_data_payload', $editing?->sunedu_data_payload ? json_encode($editing->sunedu_data_payload, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar carpeta' }}</button></div>
            </form>
        </div>
    </section>
@endsection
