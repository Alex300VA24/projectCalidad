@extends('layouts.app')

@section('title', 'Sílabos | SIGI Calidad')
@section('page-label', 'Gestión Curricular')

@section('content')
    <nav class="page-breadcrumb reveal" aria-label="Migas de pan">
        <a href="{{ route('tramites.hub') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
            Trámites
        </a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">Sílabos</span>
    </nav>

    <header class="page-heading procedure-page-heading reveal">
        <div>
            <span class="eyebrow">Gestión Curricular · GC-03</span>
            <h1>Sílabos</h1>
            <p>Elaboración, revisión y visado de sílabos por periodo académico.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="syllabus-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar sílabo
        </button>
    </header>

    <x-formato-guia codigo="GC-03" />

    <section class="panel table-panel records-panel reveal" aria-labelledby="syllabi-list-title">
        <header class="records-panel-heading">
            <div>
                <span class="eyebrow">Registro académico</span>
                <h2 id="syllabi-list-title">Sílabos registrados</h2>
            </div>
            <span class="records-panel-count">{{ $syllabi->count() }} {{ $syllabi->count() === 1 ? 'registro' : 'registros' }}</span>
        </header>
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Curso</th><th>Docente</th><th>Periodo</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($syllabi as $syllabus)
                        <tr>
                            <td data-label="Curso"><strong class="cell-primary">{{ $syllabus->course?->name ?? '—' }}</strong></td>
                            <td data-label="Docente">{{ $syllabus->teacher?->name ?? '—' }}</td>
                            <td data-label="Periodo">{{ $syllabus->academic_period }}</td>
                            <td data-label="Estado"><span class="badge">{{ $syllabus->status }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('syllabi.index', ['edit' => $syllabus->id]) }}#syllabus-form" aria-label="Editar sílabo">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('syllabi.destroy', $syllabus) }}" onsubmit="return confirm('¿Eliminar este sílabo?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar sílabo"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><h3>No hay sílabos registrados</h3><p>Registra el primer sílabo del periodo.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="syllabus-form" data-form-drawer hidden aria-labelledby="syllabus-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="syllabus-form-title">{{ $editing ? 'Editar sílabo' : 'Registrar sílabo' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('syllabi.update', $editing) : route('syllabi.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif

                <h3 class="form-section-title">1. Datos de identificación</h3>
                <div class="form-grid">
                    <label><span>Programa de estudios</span><select required name="programa_estudio_id"><option value="">Selecciona</option>@foreach($programas as $programa)<option value="{{ $programa->id }}" @selected(old('programa_estudio_id', $editing?->programa_estudio_id) == $programa->id)>{{ $programa->nombre }}</option>@endforeach</select></label>
                    <label><span>Periodo normalizado</span><select required name="periodo_academico_id"><option value="">Selecciona</option>@foreach($periodos as $periodo)<option value="{{ $periodo->id }}" @selected(old('periodo_academico_id', $editing?->periodo_academico_id) == $periodo->id)>{{ $periodo->codigo }}</option>@endforeach</select></label>
                    <label><span>Curso / Experiencia curricular</span><select required name="course_id"><option value="">Selecciona</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id', $editing?->course_id) == $course->id)>{{ $course->name }}</option>@endforeach</select></label>
                    <label><span>Sección</span><input type="text" name="seccion" maxlength="20" value="{{ old('seccion', $editing?->seccion) }}" placeholder="Ej. A"></label>
                    <label><span>Docente</span><select required name="teacher_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('teacher_id', $editing?->teacher_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Tipo de sílabo</span><select name="tipo_silabo"><option value="">Selecciona</option><option value="objetivos" @selected(old('tipo_silabo', $editing?->tipo_silabo) === 'objetivos')>Por Objetivos (F-M01.01-DPA-003)</option><option value="competencias" @selected(old('tipo_silabo', $editing?->tipo_silabo) === 'competencias')>Por Competencias (F-M01.01-DPA-004)</option></select></label>
                    <label><span>Modalidad</span><select name="modalidad"><option value="">Selecciona</option><option value="presencial" @selected(old('modalidad', $editing?->modalidad) === 'presencial')>Presencial</option><option value="semipresencial" @selected(old('modalidad', $editing?->modalidad) === 'semipresencial')>Semipresencial</option><option value="no_presencial" @selected(old('modalidad', $editing?->modalidad) === 'no_presencial')>No presencial</option></select></label>
                    <label><span>Estado</span><select required name="status">@foreach(['draft' => 'Borrador', 'submitted' => 'Presentado', 'reviewed' => 'Revisado', 'visado' => 'Visado'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $editing?->status ?? 'draft') === $value)>{{ $label }}</option>@endforeach</select></label>
                </div>

                <h3 class="form-section-title">2. Fundamentación y aprendizajes esperados</h3>
                <div class="form-grid">
                    <label class="span-2"><span>Fundamentación y descripción (sumilla)</span><textarea rows="2" name="fundamentacion" placeholder="Naturaleza, propósito y contenidos organizados en unidades temáticas">{{ old('fundamentacion', $editing?->fundamentacion) }}</textarea></label>
                    <label class="span-2"><span>Aprendizajes esperados / Competencias</span><textarea rows="2" name="aprendizajes_esperados" placeholder="Transcritas del perfil de egreso">{{ old('aprendizajes_esperados', $editing?->aprendizajes_esperados) }}</textarea></label>
                </div>

                <h3 class="form-section-title">3. Programación académica por unidades</h3>
                @php $unidadesData = old('unidades', $editing?->unidades ?? []); @endphp
                @for ($u = 0; $u < 3; $u++)
                    <fieldset class="form-fieldset">
                        <legend>Unidad {{ $u + 1 }}</legend>
                        <div class="form-grid">
                            <label class="span-2"><span>Denominación</span><input type="text" name="unidades[{{ $u }}][denominacion]" value="{{ $unidadesData[$u]['denominacion'] ?? '' }}"></label>
                            <label class="span-2"><span>Objetivos de aprendizaje / Capacidades</span><textarea rows="2" name="unidades[{{ $u }}][objetivos]">{{ $unidadesData[$u]['objetivos'] ?? '' }}</textarea></label>
                            <label class="span-2"><span>Contenidos y estrategias didácticas</span><textarea rows="2" name="unidades[{{ $u }}][contenidos]">{{ $unidadesData[$u]['contenidos'] ?? '' }}</textarea></label>
                            <label class="span-2"><span>Evaluación (técnica / instrumento)</span><textarea rows="2" name="unidades[{{ $u }}][evaluacion]">{{ $unidadesData[$u]['evaluacion'] ?? '' }}</textarea></label>
                        </div>
                    </fieldset>
                @endfor

                <h3 class="form-section-title">4. Módulo de aprendizaje no presencial <small>(F-M01.01-DPA-007, solo si la modalidad lo requiere)</small></h3>
                <div class="form-grid">
                    <label class="span-2"><span>Guías de aprendizaje (2.2.1)</span><textarea rows="2" name="guias_aprendizaje" placeholder="Adjuntar/describir las guías de aprendizaje del aula virtual">{{ old('guias_aprendizaje', $editing?->guias_aprendizaje) }}</textarea></label>
                    <label class="span-2"><span>Material de trabajo a distancia (2.2.2)</span><textarea rows="2" name="material_trabajo_distancia" placeholder="Lecturas selectas, diapositivas, PDF, otros">{{ old('material_trabajo_distancia', $editing?->material_trabajo_distancia) }}</textarea></label>
                </div>
                <div class="data-table-wrap">
                    <table class="data-table compact-table">
                        <thead><tr><th>N° Semana</th><th>N° Sesión</th><th>Nombre de la sesión</th><th>Objetivo / Capacidad</th></tr></thead>
                        <tbody>
                            @php $sesionesData = old('sesiones_no_presenciales', $editing?->sesiones_no_presenciales ?? []); @endphp
                            @for ($s = 0; $s < 16; $s++)
                                <tr>
                                    <td data-label="N° Semana"><input type="text" name="sesiones_no_presenciales[{{ $s }}][semana]" value="{{ $sesionesData[$s]['semana'] ?? '' }}"></td>
                                    <td data-label="N° Sesión"><input type="text" name="sesiones_no_presenciales[{{ $s }}][sesion]" value="{{ $sesionesData[$s]['sesion'] ?? '' }}"></td>
                                    <td data-label="Nombre de la sesión"><input type="text" name="sesiones_no_presenciales[{{ $s }}][nombre]" value="{{ $sesionesData[$s]['nombre'] ?? '' }}"></td>
                                    <td data-label="Objetivo / Capacidad"><input type="text" name="sesiones_no_presenciales[{{ $s }}][objetivo]" value="{{ $sesionesData[$s]['objetivo'] ?? '' }}"></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <h3 class="form-section-title">5. Tutoría / orientación y bibliografía</h3>
                <div class="form-grid">
                    <label><span>Día de tutoría</span><input type="text" name="tutoria_dia" maxlength="60" value="{{ old('tutoria_dia', $editing?->tutoria_dia) }}"></label>
                    <label><span>Medio (correo/chat/foro/WhatsApp)</span><input type="text" name="tutoria_medio" maxlength="120" value="{{ old('tutoria_medio', $editing?->tutoria_medio) }}"></label>
                    <label><span>Horario</span><input type="text" name="tutoria_horario" maxlength="60" value="{{ old('tutoria_horario', $editing?->tutoria_horario) }}"></label>
                    <label class="span-2"><span>Bibliografía (referencias virtuales con enlaces)</span><textarea rows="2" name="bibliografia">{{ old('bibliografia', $editing?->bibliografia) }}</textarea></label>
                </div>

                <h3 class="form-section-title">6. Lista de cotejo y biblioteca</h3>
                <div class="form-grid">
                    <label class="span-2"><span>Lista de cotejo para revisión del sílabo (JSON) — F-M01.01-DPA-005, nativa del sistema</span><textarea rows="3" name="review_checklist">{{ old('review_checklist', $editing?->review_checklist ? json_encode($editing->review_checklist, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                    <label class="span-2"><span>Requerimiento bibliográfico/hemerográfico (JSON)</span><textarea rows="3" name="library_requirement_data">{{ old('library_requirement_data', $editing?->library_requirement_data ? json_encode($editing->library_requirement_data, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>

                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar sílabo' }}</button></div>
            </form>
        </div>
    </section>

    @push('modals')
        <x-pdf-modal />
    @endpush
@endsection
