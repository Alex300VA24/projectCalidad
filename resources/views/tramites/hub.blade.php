@extends('layouts.app')

@section('title', 'Trámites BPMN | SIGI Calidad')
@section('page-label', 'Trámites de la Escuela Profesional')

@section('content')
    <header class="page-heading reveal">
        <div>
            <span class="eyebrow">Sistema de procedimientos · BPMN</span>
            <h1>Trámites de la Escuela Profesional</h1>
            <p>Gestión curricular, evaluación, investigación, titulación y seguimiento al egresado, organizados por los 11 dominios de procedimientos.</p>
        </div>
        <a class="btn btn-secondary" href="{{ route('mapa-procesos.index') }}">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
            Ver mapa de procesos
        </a>
    </header>

    <section class="panel toolbar-panel reveal tramites-toolbar">
        <form class="search-form" method="GET" action="{{ route('tramites.hub') }}">
            <label class="search-box">
                <span class="sr-only">Buscar trámite</span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input type="search" name="buscar" value="{{ $query }}" placeholder="Buscar trámite por nombre, ej. matrícula, sílabo, tutoría">
            </label>
            <button class="btn btn-secondary btn-compact" type="submit">Buscar</button>
            @if ($query !== '')
                <a class="text-link" href="{{ route('tramites.hub') }}">Limpiar</a>
            @endif
        </form>
        <span class="result-count">{{ $totalItems }} {{ Str::plural('trámite', $totalItems) }}</span>
    </section>

    @if ($query === '')
        <nav class="tramites-jumpnav reveal" aria-label="Ir a un dominio de trámites">
            @foreach ($groups as $group)
                <a href="#{{ $group['anchor'] }}">{{ $group['title'] }}</a>
            @endforeach
        </nav>
    @endif

    <div class="mapa-catalog-grid reveal">
        @foreach ($groups as $group)
            <section class="panel mapa-lane accent-{{ $group['accent'] }}" id="{{ $group['anchor'] }}">
                <header class="mapa-lane-head">
                    <span class="mapa-lane-icon" aria-hidden="true">{{ str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT) }}</span>
                    <div>
                        <span class="eyebrow">{{ $group['description'] }}</span>
                        <h2>{{ $group['title'] }}</h2>
                    </div>
                    <span class="mapa-lane-total">{{ count($group['items']) }}</span>
                </header>
                <div class="mapa-lane-cards">
                    @foreach ($group['items'] as $item)
                        <a class="tramite-item" href="{{ route($item['route']) }}">
                            <strong>{{ $item['label'] }}</strong>
                            <span class="badge"><i></i>{{ $item['count'] }} {{ Str::plural('registro', $item['count']) }}</span>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5"/></svg>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach

        @if ($query !== '' && $totalItems === 0)
            <div class="empty-state panel span-all">
                <span class="empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg></span>
                <h3>No encontramos trámites para “{{ $query }}”</h3>
                <p>Prueba con otro término o <a class="text-link" href="{{ route('tramites.hub') }}">vuelve a la lista completa</a>.</p>
            </div>
        @endif
    </div>
@endsection
