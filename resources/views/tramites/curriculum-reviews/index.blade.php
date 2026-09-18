@extends('layouts.app')

@section('title', 'Revisiones Curriculares | SIGI Calidad')
@section('page-label', 'Gestión Curricular')

@section('content')
    <x-formato-guia codigo="GC-01" />

    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Gestión Curricular · GC-01</span>
            <h1>Revisiones curriculares</h1>
            <p>Evaluación del COTECCU sobre currículos vigentes: revalidar, ajustar o rediseñar.</p>
        </div>
        <button class="btn btn-primary" type="button" data-open-form="review-form">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Registrar revisión
        </button>
    </header>

    <a class="text-link" href="{{ route('tramites.hub') }}">&larr; Volver a Trámites</a>

    <section class="panel table-panel reveal" style="margin-top:16px">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead><tr><th>Currículo</th><th>COTECCU</th><th>Decisión</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td data-label="Currículo"><strong class="cell-primary">{{ $review->curriculum?->name ?? '—' }}</strong></td>
                            <td data-label="COTECCU">{{ $review->coteccuUser?->name ?? '—' }}</td>
                            <td data-label="Decisión">{{ $review->decision ? ucfirst($review->decision) : '—' }}</td>
                            <td data-label="Estado"><span class="badge">{{ $review->state }}</span></td>
                            <td class="row-actions">
                                <div class="row-actions-wrap">
                                    <a class="icon-btn small" href="{{ route('curriculum-reviews.index', ['edit' => $review->id]) }}#review-form" aria-label="Editar revisión">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('curriculum-reviews.destroy', $review) }}" onsubmit="return confirm('¿Eliminar esta revisión?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn small danger-action" type="submit" aria-label="Eliminar revisión"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><h3>No hay revisiones registradas</h3><p>Registra la primera revisión curricular.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="form-drawer" id="review-form" data-form-drawer hidden aria-labelledby="review-form-title">
        <div class="drawer-backdrop" data-close-form></div>
        <div class="drawer-panel" role="dialog" aria-modal="true">
            <header class="drawer-header">
                <div><span class="eyebrow">{{ $editing ? 'Editar registro' : 'Nuevo registro' }}</span><h2 id="review-form-title">{{ $editing ? 'Editar revisión' : 'Registrar revisión' }}</h2></div>
                <button class="icon-btn" type="button" data-close-form aria-label="Cerrar formulario"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></button>
            </header>
            <form class="drawer-form" method="POST" action="{{ $editing ? route('curriculum-reviews.update', $editing) : route('curriculum-reviews.store') }}">
                @csrf
                @if ($editing) @method('PUT') @endif
                <div class="form-grid">
                    <label class="span-2"><span>Currículo</span><select required name="curriculum_id"><option value="">Selecciona</option>@foreach($curricula as $curriculum)<option value="{{ $curriculum->id }}" @selected(old('curriculum_id', $editing?->curriculum_id) == $curriculum->id)>{{ $curriculum->name }} ({{ $curriculum->version }})</option>@endforeach</select></label>
                    <label class="span-2"><span>Responsable COTECCU</span><select required name="coteccu_user_id"><option value="">Selecciona</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('coteccu_user_id', $editing?->coteccu_user_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></label>
                    <label><span>Decisión</span><select name="decision"><option value="">Pendiente</option>@foreach(['revalidar' => 'Revalidar', 'ajustar' => 'Ajustar', 'redisenar' => 'Rediseñar'] as $value => $label)<option value="{{ $value }}" @selected(old('decision', $editing?->decision) === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label><span>Estado</span><select required name="state">@foreach(['en_revision' => 'En revisión', 'aprobado' => 'Aprobado', 'rechazado' => 'Rechazado', 'completado' => 'Completado'] as $value => $label)<option value="{{ $value }}" @selected(old('state', $editing?->state ?? 'en_revision') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label><span>Ruta del informe técnico</span><input name="technical_report_path" value="{{ old('technical_report_path', $editing?->technical_report_path) }}" placeholder="storage/informes/..."></label>
                    <label class="span-2"><span>Lista de cotejo (JSON)</span><textarea rows="4" name="review_checklist_data" placeholder='{"F-M01.01-DPA-009": {...}}'>{{ old('review_checklist_data', $editing?->review_checklist_data ? json_encode($editing->review_checklist_data, JSON_PRETTY_PRINT) : '') }}</textarea><small>Formato F-M01.01-DPA-009. Debe ser un JSON válido.</small></label>
                </div>
                <div class="drawer-footer"><button class="btn btn-secondary" type="button" data-close-form>Cancelar</button><button class="btn btn-primary" type="submit">{{ $editing ? 'Guardar cambios' : 'Guardar revisión' }}</button></div>
            </form>
        </div>
    </section>

    @push('modals')
        <x-pdf-modal />
    @endpush
@endsection
