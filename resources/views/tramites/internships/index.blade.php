@extends('layouts.app')

@section('title', 'Prácticas Preprofesionales | SIGI Calidad')
@section('page-label', 'Ejecución del Plan Curricular')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Ejecución del Plan Curricular · EPC-05</span>
            <h1>Prácticas preprofesionales</h1>
            <p>Convenios, plan de monitoreo e informe final de prácticas de estudiantes.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="internship-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar práctica
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Estudiante</th><th>Empresa</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($internships as $internship)
                        <tr>
                            <td data-label="Estudiante"><strong class="cell-primary">{{ $internship->student?->name ?? '—' }}</strong></td>
                            <td data-label="Empresa">{{ $internship->company_name }}</td>
                            <td data-label="Estado"><span class="badge">{{ $internship->status }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('internships.index', ['edit' => $internship->id]) }}#internship-form" aria-label="Editar práctica">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('internships.destroy', $internship) }}" onsubmit="return confirm('¿Eliminar esta práctica?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar práctica"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state"><h3>No hay prácticas registradas</h3><p>Registra la primera práctica preprofesional.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="internship-form" data-form-drawer hidden aria-labelledby="internship-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="internship-form-title">{{ $editing ? 'Editar práctica' : 'Registrar práctica' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('internships.update', $editing) : route('internships.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label><span>Estudiante</span><select required name="student_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('student_id', $editing?->student_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Empresa</span><input required name="company_name" value="{{ old('company_name', $editing?->company_name) }}"></label>
                    <label><span>N.º de convenio</span><input name="agreement_number" value="{{ old('agreement_number', $editing?->agreement_number) }}"></label>
                    <label><span>Estado</span><select required name="status">@foreach(['en_curso' => 'En curso', 'concluida' => 'Concluida', 'observada' => 'Observada'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $editing?->status ?? 'en_curso') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label class="span-2"><span>Ruta del informe final</span><input name="final_report_path" value="{{ old('final_report_path', $editing?->final_report_path) }}"></label>
                    <label class="span-2"><span>Plan de monitoreo (JSON)</span><textarea rows="3" name="monitoring_plan">{{ old('monitoring_plan', $editing?->monitoring_plan ? json_encode($editing->monitoring_plan, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar práctica' }}</button></div>
            </form>
        </div>
    </section>
@endsection
