@extends('layouts.app')

@section('title', 'Proyectos de Investigación | SIGI Calidad')
@section('page-label', 'Investigación Formativa')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Investigación Formativa · IF-02</span>
            <h1>Proyectos de investigación</h1>
            <p>Proyectos de estudiantes con asesor y línea de investigación asignados.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="project-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar proyecto
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Proyecto</th><th>Estudiante</th><th>Asesor</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($projects as $project)
                        <tr>
                            <td data-label="Proyecto"><strong class="cell-primary">{{ $project->title }}</strong><small>{{ $project->researchLine?->name }}</small></td>
                            <td data-label="Estudiante">{{ $project->student?->name ?? '—' }}</td>
                            <td data-label="Asesor">{{ $project->advisor?->name ?? '—' }}</td>
                            <td data-label="Estado"><span class="badge">{{ $project->project_status }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('research-projects.index', ['edit' => $project->id]) }}#project-form" aria-label="Editar proyecto">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('research-projects.destroy', $project) }}" onsubmit="return confirm('¿Eliminar este proyecto?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar proyecto"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><h3>No hay proyectos registrados</h3><p>Registra el primer proyecto de investigación.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="project-form" data-form-drawer hidden aria-labelledby="project-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="project-form-title">{{ $editing ? 'Editar proyecto' : 'Registrar proyecto' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('research-projects.update', $editing) : route('research-projects.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label class="span-2"><span>Título del proyecto</span><input required name="title" value="{{ old('title', $editing?->title) }}"></label>
                    <label><span>Estudiante</span><select required name="student_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('student_id', $editing?->student_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Asesor</span><select required name="advisor_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('advisor_id', $editing?->advisor_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Línea de investigación</span><select required name="research_line_id"><option value="">Selecciona</option>@foreach($researchLines as $line)<option value="{{ $line->id }}" @selected(old('research_line_id', $editing?->research_line_id) == $line->id)>{{ $line->name }}</option>@endforeach</select></label>
                    <label><span>Estado del proyecto</span><select required name="project_status">@foreach(['en_proceso' => 'En proceso', 'sustentado' => 'Sustentado', 'observado' => 'Observado', 'aprobado' => 'Aprobado'] as $value => $label)<option value="{{ $value }}" @selected(old('project_status', $editing?->project_status ?? 'en_proceso') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label class="span-2"><span>N.º de acta de sustentación</span><input name="sustentation_act_number" value="{{ old('sustentation_act_number', $editing?->sustentation_act_number) }}"></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar proyecto' }}</button></div>
            </form>
        </div>
    </section>
@endsection
