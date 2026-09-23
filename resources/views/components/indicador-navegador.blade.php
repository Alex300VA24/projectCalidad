@props(['indicador', 'indicadores', 'codigoAnterior', 'codigoSiguiente'])

@php
    $rutaHistorial = fn (string $codigo) => route(
        str_contains($codigo, '/') ? 'quality-indicators.historial-with-slash' : 'quality-indicators.historial',
        $codigo
    );
@endphp

<nav class="flex items-center gap-2" aria-label="Navegar entre indicadores">
    <a href="{{ $codigoAnterior ? $rutaHistorial($codigoAnterior) : '#' }}"
        @class([
            'inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-[var(--border)] bg-[var(--surface)] text-[var(--ink)] shadow-sm transition hover:border-indigo-400 hover:text-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600',
            'pointer-events-none opacity-40' => ! $codigoAnterior,
        ])
        @unless ($codigoAnterior) aria-disabled="true" tabindex="-1" @endunless
        title="Indicador anterior">
        <svg class="size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        <span class="sr-only">Indicador anterior</span>
    </a>

    <label class="sr-only" for="indicador-selector">Ir a otro indicador</label>
    <select id="indicador-selector" onchange="if (this.value) { window.location.href = this.value; }"
        class="min-h-11 w-[220px] cursor-pointer rounded-lg border border-[var(--border)] bg-[var(--surface)] px-3 text-sm font-semibold text-[var(--ink)] shadow-sm outline-none transition hover:border-indigo-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 sm:w-72">
        @foreach ($indicadores as $item)
            <option value="{{ $rutaHistorial($item->codigo) }}" @selected($item->is($indicador))>{{ $item->codigo }} · {{ \Illuminate\Support\Str::limit($item->nombre, 48) }}</option>
        @endforeach
    </select>

    <a href="{{ $codigoSiguiente ? $rutaHistorial($codigoSiguiente) : '#' }}"
        @class([
            'inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-[var(--border)] bg-[var(--surface)] text-[var(--ink)] shadow-sm transition hover:border-indigo-400 hover:text-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600',
            'pointer-events-none opacity-40' => ! $codigoSiguiente,
        ])
        @unless ($codigoSiguiente) aria-disabled="true" tabindex="-1" @endunless
        title="Siguiente indicador">
        <span class="sr-only">Siguiente indicador</span>
        <svg class="size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
    </a>
</nav>
