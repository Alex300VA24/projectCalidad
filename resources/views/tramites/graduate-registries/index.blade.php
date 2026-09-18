@extends('layouts.app')

@section('title', 'Registro de Egresados | SIGI Calidad')
@section('page-label', 'Seguimiento al Egresado')

@section('content')
    <x-formato-guia codigo="SE-01" />

    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Seguimiento al Egresado · SE-01</span>
            <h1>Registro de egresados</h1>
            <p>Base de datos de egresados, lista de aptos e informes estadísticos anuales.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="registry-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar egresado
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Egresado</th><th>N.º lista de aptos</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($registries as $registry)
                        <tr>
                            <td data-label="Egresado"><strong class="cell-primary">{{ $registry->student?->name ?? '—' }}</strong></td>
                            <td data-label="N.º lista">{{ $registry->apt_list_number ?? '—' }}</td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('graduate-registries.index', ['edit' => $registry->id]) }}#registry-form" aria-label="Editar registro">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('graduate-registries.destroy', $registry) }}" onsubmit="return confirm('¿Eliminar este registro?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar registro"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-state"><h3>No hay egresados registrados</h3><p>Registra el primer egresado.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="registry-form" data-form-drawer hidden aria-labelledby="registry-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="registry-form-title">{{ $editing ? 'Editar egresado' : 'Registrar egresado' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('graduate-registries.update', $editing) : route('graduate-registries.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label><span>Programa de estudios</span><select required name="programa_estudio_id"><option value="">Selecciona</option>@foreach($programas as $programa)<option value="{{ $programa->id }}" @selected(old('programa_estudio_id', $editing?->programa_estudio_id) == $programa->id)>{{ $programa->nombre }}</option>@endforeach</select></label>
                    <label><span>Periodo</span><select required name="periodo_academico_id"><option value="">Selecciona</option>@foreach($periodos as $periodo)<option value="{{ $periodo->id }}" @selected(old('periodo_academico_id', $editing?->periodo_academico_id) == $periodo->id)>{{ $periodo->codigo }}</option>@endforeach</select></label>
                    <label><span>Condicion laboral</span><select name="condicion_laboral"><option value="">Sin informacion</option>@foreach(['EMPLEADO' => 'Empleado', 'OTRA_AREA' => 'Otra area', 'BUSCANDO' => 'Buscando empleo', 'DESEMPLEADO' => 'Desempleado'] as $value => $label)<option value="{{ $value }}" @selected(old('condicion_laboral', $editing?->condicion_laboral) === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label><span><input type="checkbox" name="titulado" value="1" @checked(old('titulado', $editing?->titulado))> Titulado</span></label>
                    <label><span><input type="checkbox" name="labora_especialidad" value="1" @checked(old('labora_especialidad', $editing?->labora_especialidad))> Labora en especialidad</span></label>
                    <label><span>Egresado</span><select required name="student_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('student_id', $editing?->student_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>N.º de lista de aptos</span><input name="apt_list_number" value="{{ old('apt_list_number', $editing?->apt_list_number) }}" placeholder="Formato F-M01.05-DCU/PG-01"></label>
                    <label class="span-2"><span>Ficha de actualización (JSON)</span><textarea rows="3" name="update_form_data" placeholder="Formato PG-02">{{ old('update_form_data', $editing?->update_form_data ? json_encode($editing->update_form_data, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Registro en base de datos (JSON)</span><textarea rows="3" name="database_record" placeholder="Formato PG-03">{{ old('database_record', $editing?->database_record ? json_encode($editing->database_record, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Informe estadístico anual (JSON)</span><textarea rows="3" name="annual_stats_report" placeholder="Formato PG-04">{{ old('annual_stats_report', $editing?->annual_stats_report ? json_encode($editing->annual_stats_report, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar egresado' }}</button></div>
            </form>
        </div>
    </section>

    @push('modals')
        <x-pdf-modal />
    @endpush
@endsection
