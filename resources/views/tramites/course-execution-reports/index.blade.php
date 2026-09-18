@extends('layouts.app')

@section('title', 'Informes de Ejecución de Curso | SIGI Calidad')
@section('page-label', 'Ejecución del Plan Curricular')

@section('content')
    <x-formato-guia codigo="EPC-01" />

    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Ejecución del Plan Curricular · EPC-01 / F-013</span>
            <h1>Consolidado de Ejecución de Asignatura</h1>
            <p>Control del avance silábico por asignatura y portafolio digital docente.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="report-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar informe
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th>Docente</th>
                        <th>Periodo</th>
                        <th>Avance Silábico</th>
                        <th>Estado</th>
                        <th>Portafolio</th>
                        <th><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        @php
                            $avance = $report->execution_summary_data['porcentaje_avance'] ?? null;
                        @endphp
                        <tr>
                            <td data-label="Curso"><strong class="cell-primary">{{ $report->course?->name ?? '—' }}</strong></td>
                            <td data-label="Docente">{{ $report->teacher?->name ?? '—' }}</td>
                            <td data-label="Periodo">{{ $report->academic_period }}</td>
                            <td data-label="Avance">
                                @if($avance !== null)
                                    <strong style="color: var(--green); font-family: 'Fira Code', monospace;">{{ $avance }}%</strong>
                                @else
                                    <span class="muted">No definido</span>
                                @endif
                            </td>
                            <td data-label="Estado">
                                <span class="badge {{ in_array($report->status, ['CONSOLIDADO', 'APROBADO']) ? 'approved' : '' }}">
                                    {{ $report->status }}
                                </span>
                            </td>
                            <td data-label="Portafolio">
                                @if($report->portafolio_digital_url)
                                    <a class="text-link" href="{{ $report->portafolio_digital_url }}" target="_blank" rel="noopener">Ver enlace</a>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('course-execution-reports.index', ['edit' => $report->id]) }}#report-form" aria-label="Editar informe">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('course-execution-reports.destroy', $report) }}" onsubmit="return confirm('¿Eliminar este informe?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar informe"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state"><h3>No hay informes registrados</h3><p>Registra el primer informe de ejecución con el formato M01.01.03.01-F-013.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="report-form" data-form-drawer hidden aria-labelledby="report-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="report-form-title">{{ $editing ? 'Editar informe' : 'Registrar informe F-013' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('course-execution-reports.update', $editing) : route('course-execution-reports.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label class="span-2"><span>Programa de estudios</span><select required name="programa_estudio_id"><option value="">Selecciona</option>@foreach($programas as $programa)<option value="{{ $programa->id }}" @selected(old('programa_estudio_id', $editing?->programa_estudio_id) == $programa->id)>{{ $programa->nombre }}</option>@endforeach</select></label>
                    <label><span>Periodo académico</span><select required name="periodo_academico_id"><option value="">Selecciona</option>@foreach($periodos as $periodo)<option value="{{ $periodo->id }}" @selected(old('periodo_academico_id', $editing?->periodo_academico_id) == $periodo->id)>{{ $periodo->codigo }}</option>@endforeach</select></label>
                    <label><span>Estado del informe</span><select required name="status">@foreach(['BORRADOR' => 'Borrador', 'CONSOLIDADO' => 'Consolidado (Alimenta indicador)', 'APROBADO' => 'Aprobado (Alimenta indicador)', 'ANULADO' => 'Anulado'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $editing?->status ?? 'CONSOLIDADO') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label class="span-2"><span>Curso / Asignatura</span><select required name="course_id"><option value="">Selecciona</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id', $editing?->course_id) == $course->id)>{{ $course->name }}</option>@endforeach</select></label>
                    <label class="span-2"><span>Docente responsable</span><select required name="teacher_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('teacher_id', $editing?->teacher_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    
                    <label class="span-2" style="background: var(--green-soft); padding: 10px; border-radius: 6px; border: 1px solid var(--green);">
                        <span style="color: var(--green); font-weight: 700;">% de Avance Silábico Alcanzado (F-013)</span>
                        <input type="number" step="0.01" min="0" max="100" name="porcentaje_avance" value="{{ old('porcentaje_avance', $editing?->execution_summary_data['porcentaje_avance'] ?? '') }}" placeholder="Ej. 95.00" required>
                        <small style="color: var(--ink);">Este valor alimenta directamente el promedio del indicador M01.01.03.01-F-013 en el Dashboard de Calidad.</small>
                    </label>

                    <label class="span-2"><span>Formato oficial de referencia</span><input required name="socialization_format" value="{{ old('socialization_format', $editing?->socialization_format ?? 'M01.01.03.01-F-013') }}"></label>
                    <label class="span-2"><span>URL del portafolio digital / evidencias</span><input type="url" name="portafolio_digital_url" value="{{ old('portafolio_digital_url', $editing?->portafolio_digital_url) }}" placeholder="https://drive.google.com/..."></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar informe F-013' }}</button></div>
            </form>
        </div>
    </section>

    @push('modals')
        <x-pdf-modal />
    @endpush
@endsection
