@extends('layouts.app')

@section('title', 'Matrícula e Incidencias (MAT-01) | SIGI Calidad')
@section('page-label', 'Gestión de Matrícula')

@section('content')
    <x-formato-guia codigo="MAT-01" />

    <header class="page-heading reveal" style="margin-bottom: 16px;">
        <div>
            <span class="eyebrow">Procedimiento MAT-01 · Calidad Académica</span>
            <h1 style="font-size: clamp(24px, 3.5vw, 32px); margin-bottom: 6px;">Gestión de Matrícula e Incidencias</h1>
            <p style="margin:0; color:var(--ink-soft);">
                Registro operativo de matrículas (alimenta retención y repitencia) y control de resolución de incidencias.
            </p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            @if($tab === 'matriculas')
                <button class="btn btn-primary" type="button" data-open-form="matricula-form">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Registrar matrícula
                </button>
            @else
                <button class="btn btn-primary" type="button" data-open-form="incidencia-form">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Reportar incidencia
                </button>
            @endif
        </div>
    </header>

    {{-- Selector de Periodo y Pestañas --}}
    <div class="panel toolbar-panel reveal" style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 20px;">
        <nav class="section-tabs" style="margin: 0; padding: 0; border: none;" aria-label="Cambiar entre matrículas e incidencias">
            <a @class(['active' => $tab === 'matriculas']) href="{{ route('matriculas.index', ['tab' => 'matriculas', 'periodo' => $periodoSeleccionado]) }}">
                Matrículas de estudiantes <span>{{ $conteoMatriculas }}</span>
            </a>
            <a @class(['active' => $tab === 'incidencias']) href="{{ route('matriculas.index', ['tab' => 'incidencias', 'periodo' => $periodoSeleccionado]) }}">
                Incidencias de matrícula (F-003) <span>{{ $conteoIncidencias }}</span>
            </a>
        </nav>

        <form method="GET" action="{{ route('matriculas.index') }}" style="display: flex; align-items: center; gap: 8px;">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <label for="periodo_filtro" style="font-size: 13px; font-weight: 600; color: var(--ink-soft);">Periodo:</label>
            <select id="periodo_filtro" name="periodo" onchange="this.form.submit()" style="padding: 6px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px; background: var(--surface);">
                <option value="">Todos los periodos</option>
                @foreach($periodos as $p)
                    <option value="{{ $p->codigo }}" @selected($periodoSeleccionado === $p->codigo)>{{ $p->codigo }}</option>
                @endforeach
            </select>
            @if($periodoSeleccionado)
                <a class="text-link" href="{{ route('matriculas.index', ['tab' => $tab]) }}" style="font-size: 12px;">Ver todos</a>
            @endif
        </form>
    </div>

    @if($tab === 'matriculas')
        {{-- PESTAÑA: MATRÍCULAS --}}
        <section class="panel table-panel reveal">
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Curso / Asignatura</th>
                            <th>Ciclo</th>
                            <th>Periodo</th>
                            <th>N° Matrícula</th>
                            <th>Resultado</th>
                            <th><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matriculas as $mat)
                            <tr>
                                <td data-label="Estudiante">
                                    <strong class="cell-primary">{{ $mat->estudiante?->name ?? 'Estudiante #'.$mat->estudiante_id }}</strong>
                                    <small>{{ $mat->estudiante?->email }}</small>
                                </td>
                                <td data-label="Curso">
                                    <strong>{{ $mat->curso?->name ?? 'Curso #'.$mat->curso_id }}</strong>
                                    <small class="muted">{{ $mat->curso?->code }}</small>
                                </td>
                                <td data-label="Ciclo"><span class="badge">Ciclo {{ $mat->ciclo_academico }}</span></td>
                                <td data-label="Periodo"><strong>{{ $mat->periodo_academico }}</strong></td>
                                <td data-label="N° Matrícula">
                                    @if($mat->numero_matricula >= 2)
                                        <span class="badge" style="background: var(--amber-soft); color: var(--amber); font-weight: 700;">{{ $mat->numero_matricula }}da Matrícula (Repite)</span>
                                    @else
                                        <span class="badge" style="background: var(--green-soft); color: var(--green);">1ra Matrícula</span>
                                    @endif
                                </td>
                                <td data-label="Resultado">
                                    @php
                                        $colorRes = match($mat->estado_resultado) {
                                            'APROBADO' => 'badge approved',
                                            'DESAPROBADO' => 'badge critical',
                                            'INHABILITADO' => 'badge warning',
                                            default => 'badge',
                                        };
                                    @endphp
                                    <span class="{{ $colorRes }}">{{ $mat->estado_resultado }}</span>
                                </td>
                                <td class="row-actions">
                                    <div class="row-actions-wrap">
                                        <a class="icon-btn small" href="{{ route('matriculas.index', ['tab' => 'matriculas', 'periodo' => $periodoSeleccionado, 'edit_matricula' => $mat->id]) }}#matricula-form" aria-label="Editar matrícula">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('matriculas.destroy', $mat) }}" onsubmit="return confirm('¿Eliminar esta matrícula?')">
                                            @csrf @method('DELETE')
                                            <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <h3>No hay matrículas registradas</h3>
                                        <p>Registra las matrículas para calcular automáticamente la retención y la repitencia.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($matriculas->hasPages())
                <div style="padding: 14px 18px; border-top: 1px solid var(--border);">
                    {{ $matriculas->links() }}
                </div>
            @endif
        </section>

    @else
        {{-- PESTAÑA: INCIDENCIAS DE MATRÍCULA --}}
        <section class="panel table-panel reveal">
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Descripción del problema / Caso</th>
                            <th>Periodo</th>
                            <th>Estado</th>
                            <th>Fecha de Resolución</th>
                            <th><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidencias as $inc)
                            <tr>
                                <td data-label="Descripción">
                                    <strong class="cell-primary" style="white-space: normal;">{{ $inc->descripcion }}</strong>
                                    <small>{{ $inc->programaEstudio?->nombre }}</small>
                                </td>
                                <td data-label="Periodo"><strong>{{ $inc->periodo_academico }}</strong></td>
                                <td data-label="Estado">
                                    @php
                                        $badgeClass = match($inc->estado) {
                                            'RESUELTA', 'CERRADA' => 'badge approved',
                                            'EN_ATENCION' => 'badge warning',
                                            default => 'badge critical',
                                        };
                                    @endphp
                                    <span class="{{ $badgeClass }}">{{ $inc->estado }}</span>
                                </td>
                                <td data-label="Fecha de Resolución">
                                    {{ $inc->resuelta_en ? $inc->resuelta_en->format('d/m/Y H:i') : '— Pendiente' }}
                                </td>
                                <td class="row-actions">
                                    <div class="row-actions-wrap">
                                        <a class="icon-btn small" href="{{ route('matriculas.index', ['tab' => 'incidencias', 'periodo' => $periodoSeleccionado, 'edit_incidencia' => $inc->id]) }}#incidencia-form" aria-label="Editar incidencia">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('incidencias-matricula.destroy', $inc) }}" onsubmit="return confirm('¿Eliminar esta incidencia?')">
                                            @csrf @method('DELETE')
                                            <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <h3>No hay incidencias reportadas</h3>
                                        <p>Registra incidencias para medir el índice de resolución (FI-003) en el Dashboard.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($incidencias->hasPages())
                <div style="padding: 14px 18px; border-top: 1px solid var(--border);">
                    {{ $incidencias->links() }}
                </div>
            @endif
        </section>
    @endif

    {{-- DRAWER FORMULARIO MATRÍCULA --}}
    <section class="form-drawer" id="matricula-form" data-form-drawer hidden aria-labelledby="matricula-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div>
                    <span class="eyebrow">{{ $editingMatricula ? 'Editar registro' : 'Nuevo registro' }}</span>
                    <h2 id="matricula-form-title">{{ $editingMatricula ? 'Editar matrícula' : 'Registrar matrícula de estudiante' }}</h2>
                </div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>

            <form class="drawer-form" method="POST" action="{{ $editingMatricula ? route('matriculas.update', $editingMatricula) : route('matriculas.store') }}">
                @csrf
                @if($editingMatricula) @method('PUT') @endif

                <div class="form-grid">
                    <label class="span-2">
                        <span>Programa de Estudios</span>
                        <select name="programa_estudio_id" required>
                            @foreach($programas as $p)
                                <option value="{{ $p->id }}" @selected(old('programa_estudio_id', $editingMatricula?->programa_estudio_id) == $p->id)>{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="span-2">
                        <span>Estudiante</span>
                        <select name="estudiante_id" required>
                            <option value="">Selecciona estudiante...</option>
                            @foreach($estudiantes as $est)
                                <option value="{{ $est->id }}" @selected(old('estudiante_id', $editingMatricula?->estudiante_id) == $est->id)>
                                    {{ $est->name }} ({{ $est->email }})
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="span-2">
                        <span>Curso / Asignatura</span>
                        <select name="curso_id" required>
                            <option value="">Selecciona curso...</option>
                            @foreach($cursos as $c)
                                <option value="{{ $c->id }}" @selected(old('curso_id', $editingMatricula?->curso_id) == $c->id)>
                                    {{ $c->name }} ({{ $c->code }})
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>Periodo Académico</span>
                        <input name="periodo_academico" required value="{{ old('periodo_academico', $editingMatricula?->periodo_academico ?? $periodoSeleccionado ?? '2026-I') }}" placeholder="2026-I">
                    </label>

                    <label>
                        <span>Ciclo Académico</span>
                        <select name="ciclo_academico" required>
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}" @selected(old('ciclo_academico', $editingMatricula?->ciclo_academico) == $i)>Ciclo {{ $i }}</option>
                            @endfor
                        </select>
                    </label>

                    <label>
                        <span>N° de Matrícula</span>
                        <input type="number" name="numero_matricula" min="1" max="5" required value="{{ old('numero_matricula', $editingMatricula?->numero_matricula ?? 1) }}" placeholder="1">
                        <small style="color: var(--ink-soft); font-size: 11px;">1 = 1ra matrícula, 2 o más = repite</small>
                    </label>

                    <label>
                        <span>Estado Matrícula</span>
                        <select name="estado_matricula" required>
                            @foreach(['CONFIRMADA', 'PENDIENTE', 'ANULADA'] as $em)
                                <option value="{{ $em }}" @selected(old('estado_matricula', $editingMatricula?->estado_matricula ?? 'CONFIRMADA') === $em)>{{ $em }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="span-2">
                        <span>Estado de Resultado</span>
                        <select name="estado_resultado" required>
                            @foreach(['CURSANDO', 'APROBADO', 'DESAPROBADO', 'INHABILITADO'] as $er)
                                <option value="{{ $er }}" @selected(old('estado_resultado', $editingMatricula?->estado_resultado ?? 'CURSANDO') === $er)>{{ $er }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="drawer-footer">
                    <button class="btn btn-secondary" type="button" data-close-form>Cancelar</button>
                    <button class="btn btn-primary" type="submit">{{ $editingMatricula ? 'Actualizar matrícula' : 'Guardar matrícula' }}</button>
                </div>
            </form>
        </div>
    </section>

    {{-- DRAWER FORMULARIO INCIDENCIAS --}}
    <section class="form-drawer" id="incidencia-form" data-form-drawer hidden aria-labelledby="incidencia-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div>
                    <span class="eyebrow">{{ $editingIncidencia ? 'Editar registro' : 'Nuevo registro' }}</span>
                    <h2 id="incidencia-form-title">{{ $editingIncidencia ? 'Editar incidencia' : 'Reportar problema en matrícula (FI-003)' }}</h2>
                </div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>

            <form class="drawer-form" method="POST" action="{{ $editingIncidencia ? route('incidencias-matricula.update', $editingIncidencia) : route('incidencias-matricula.store') }}">
                @csrf
                @if($editingIncidencia) @method('PUT') @endif

                <div class="form-grid">
                    <label class="span-2">
                        <span>Programa de Estudios</span>
                        <select name="programa_estudio_id" required>
                            @foreach($programas as $p)
                                <option value="{{ $p->id }}" @selected(old('programa_estudio_id', $editingIncidencia?->programa_estudio_id) == $p->id)>{{ $p->nombre }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="span-2">
                        <span>Periodo Académico</span>
                        <input name="periodo_academico" required value="{{ old('periodo_academico', $editingIncidencia?->periodo_academico ?? $periodoSeleccionado ?? '2026-I') }}" placeholder="2026-I">
                    </label>

                    <label class="span-2">
                        <span>Descripción de la Incidencia / Problema</span>
                        <textarea name="descripcion" rows="4" required placeholder="Detalla el problema con cupos, convalidación, pago o sistema...">{{ old('descripcion', $editingIncidencia?->descripcion) }}</textarea>
                    </label>

                    <label class="span-2">
                        <span>Estado de Atención</span>
                        <select name="estado" required>
                            @foreach(['REPORTADA' => 'REPORTADA (Pendiente)', 'EN_ATENCION' => 'EN_ATENCION (En proceso)', 'RESUELTA' => 'RESUELTA (Atendida satisfactoriamente)', 'CERRADA' => 'CERRADA (Finalizada)'] as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('estado', $editingIncidencia?->estado ?? 'REPORTADA') === $val)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        <small style="color: var(--ink-soft); font-size: 11px;">Al marcar como RESUELTA o CERRADA suma favorablemente al indicador FI-003.</small>
                    </label>
                </div>

                <div class="drawer-footer">
                    <button class="btn btn-secondary" type="button" data-close-form>Cancelar</button>
                    <button class="btn btn-primary" type="submit">{{ $editingIncidencia ? 'Actualizar incidencia' : 'Guardar incidencia' }}</button>
                </div>
            </form>
        </div>
    </section>

    @push('modals')
        <x-pdf-modal />
    @endpush
@endsection
