@props(['indicador', 'indicadores', 'codigoAnterior', 'codigoSiguiente'])

<nav class="flex items-center gap-1.5" aria-label="Navegar entre indicadores">
    <a href="{{ $codigoAnterior ? route('quality-indicators.historial', $codigoAnterior) : '#' }}"
        @class([
            'inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-[var(--border)] bg-[var(--surface)] text-[var(--ink)] transition hover:border-indigo-400 hover:text-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600',
            'pointer-events-none opacity-40' => ! $codigoAnterior,
        ])
        @unless ($codigoAnterior) aria-disabled="true" tabindex="-1" @endunless
        title="Indicador anterior">
        <svg class="size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        <span class="sr-only">Indicador anterior</span>
    </a>

    <label class="sr-only" for="indicador-selector">Ir a otro indicador</label>
    <select id="indicador-selector" onchange="if (this.value) { window.location.href = this.value; }"
        class="min-h-11 max-w-[200px] cursor-pointer rounded-lg border border-[var(--border)] bg-[var(--surface)] px-3 text-sm font-semibold text-[var(--ink)] outline-none transition hover:border-indigo-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 sm:max-w-xs">
        @foreach ($indicadores as $item)
            <option value="{{ route('quality-indicators.historial', $item->codigo) }}" @selected($item->is($indicador))>{{ $item->codigo }} · {{ \Illuminate\Support\Str::limit($item->nombre, 42) }}</option>
        @endforeach
    </select>

    <a href="{{ $codigoSiguiente ? route('quality-indicators.historial', $codigoSiguiente) : '#' }}"
        @class([
            'inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-[var(--border)] bg-[var(--surface)] text-[var(--ink)] transition hover:border-indigo-400 hover:text-indigo-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600',
            'pointer-events-none opacity-40' => ! $codigoSiguiente,
        ])
        @unless ($codigoSiguiente) aria-disabled="true" tabindex="-1" @endunless
        title="Siguiente indicador">
        <span class="sr-only">Siguiente indicador</span>
        <svg class="size-4" viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
    </a>
</nav>
