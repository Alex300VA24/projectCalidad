@extends('layouts.app')

@section('title', 'Calendario Académico | SIGI Calidad')
@section('page-label', 'Ejecución del Plan Curricular')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Ejecución del Plan Curricular · EPC-02</span>
            <h1>Calendario académico</h1>
            <p>Capacitaciones, talleres y congresos programados por periodo académico.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="calendar-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar calendario
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Periodo</th><th>Actividades</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($calendars as $calendar)
                        <tr>
                            <td data-label="Periodo"><strong class="cell-primary">{{ $calendar->academic_period }}</strong></td>
                            <td data-label="Actividades">{{ count($calendar->activities_schedule ?? []) }}</td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('academic-calendars.index', ['edit' => $calendar->id]) }}#calendar-form" aria-label="Editar calendario">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('academic-calendars.destroy', $calendar) }}" onsubmit="return confirm('¿Eliminar este calendario?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar calendario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty-state"><h3>No hay calendarios registrados</h3><p>Registra el primer calendario académico.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="calendar-form" data-form-drawer hidden aria-labelledby="calendar-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="calendar-form-title">{{ $editing ? 'Editar calendario' : 'Registrar calendario' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('academic-calendars.update', $editing) : route('academic-calendars.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label class="span-2"><span>Periodo académico</span><input required name="academic_period" value="{{ old('academic_period', $editing?->academic_period) }}" placeholder="2026-II"></label>
                    <label class="span-2"><span>Cronograma de actividades (JSON)</span><textarea rows="4" name="activities_schedule" placeholder='[{"actividad":"Taller de investigación","fecha":"2026-10-01"}]'>{{ old('activities_schedule', $editing?->activities_schedule ? json_encode($editing->activities_schedule, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar calendario' }}</button></div>
            </form>
        </div>
    </section>
@endsection
