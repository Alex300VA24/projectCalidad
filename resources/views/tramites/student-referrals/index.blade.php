@extends('layouts.app')

@section('title', 'Derivaciones | SIGI Calidad')
@section('page-label', 'Seguimiento del Estudiante')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Seguimiento del Estudiante · SD-03</span>
            <h1>Derivaciones y contrarreferencias</h1>
            <p>Fichas de derivación de estudiantes a Bienestar, Psicología o Servicio Social.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="referral-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar derivación
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Estudiante</th><th>Derivado por</th><th>Derivado a</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($referrals as $referral)
                        <tr>
                            <td data-label="Estudiante"><strong class="cell-primary">{{ $referral->student?->name ?? '—' }}</strong></td>
                            <td data-label="Derivado por">{{ $referral->referrer?->name ?? '—' }}</td>
                            <td data-label="Derivado a">{{ $referral->referred_to }}</td>
                            <td data-label="Estado"><span class="badge">{{ $referral->status }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('student-referrals.index', ['edit' => $referral->id]) }}#referral-form" aria-label="Editar derivación">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('student-referrals.destroy', $referral) }}" onsubmit="return confirm('¿Eliminar esta derivación?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar derivación"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><h3>No hay derivaciones registradas</h3><p>Registra la primera derivación.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="referral-form" data-form-drawer hidden aria-labelledby="referral-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="referral-form-title">{{ $editing ? 'Editar derivación' : 'Registrar derivación' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('student-referrals.update', $editing) : route('student-referrals.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label><span>Programa de estudios</span><select required name="programa_estudio_id"><option value="">Selecciona</option>@foreach($programas as $programa)<option value="{{ $programa->id }}" @selected(old('programa_estudio_id', $editing?->programa_estudio_id) == $programa->id)>{{ $programa->nombre }}</option>@endforeach</select></label>
                    <label><span>Periodo</span><select required name="periodo_academico_id"><option value="">Selecciona</option>@foreach($periodos as $periodo)<option value="{{ $periodo->id }}" @selected(old('periodo_academico_id', $editing?->periodo_academico_id) == $periodo->id)>{{ $periodo->codigo }}</option>@endforeach</select></label>
                    <label><span>Estudiante</span><select required name="student_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('student_id', $editing?->student_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Derivado por</span><select required name="referrer_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('referrer_id', $editing?->referrer_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Derivado a</span><select required name="referred_to">@foreach(['Bienestar' => 'Bienestar', 'Psicologia' => 'Psicología', 'Social' => 'Servicio Social'] as $value => $label)<option value="{{ $value }}" @selected(old('referred_to', $editing?->referred_to) === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label><span>Estado</span><select required name="status">@foreach(['derivado' => 'Derivado', 'en_atencion' => 'En atención', 'contrarreferido' => 'Contrarreferido', 'cerrado' => 'Cerrado'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $editing?->status ?? 'derivado') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label class="span-2"><span>Hoja de referencia y contrarreferencia (JSON)</span><textarea rows="3" name="referral_sheet" placeholder="Formato F.M01.04-DDA/PG-06">{{ old('referral_sheet', $editing?->referral_sheet ? json_encode($editing->referral_sheet, JSON_PRETTY_PRINT) : '') }}</textarea></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar derivación' }}</button></div>
            </form>
        </div>
    </section>
@endsection
