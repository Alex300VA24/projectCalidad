<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Indicadores de calidad academica</title>
    <style>
        body { color: #172554; font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p { margin: 0 0 14px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 7px; text-align: left; vertical-align: top; }
        th { background: #e0e7ff; }
        .code { font-family: DejaVu Sans Mono, monospace; }
    </style>
</head>
<body>
    <h1>Indicadores de Gestion y Calidad Academica</h1>
    <p><strong>Programa:</strong> {{ $programa->nombre }} &nbsp; <strong>Periodo:</strong> {{ $periodo }}</p>
    <table>
        <thead><tr><th>Indicador</th><th>Formula</th><th>Valor</th><th>Meta</th><th>Estado</th><th>Analisis</th><th>Accion</th></tr></thead>
        <tbody>
        @forelse ($mediciones as $medicion)
            <tr>
                <td><span class="code">{{ $medicion->indicador->codigo }}</span><br>{{ $medicion->indicador->nombre }}</td>
                <td>{{ $medicion->indicador->formula_texto }}</td>
                <td>{{ number_format((float) $medicion->valor_medido, 2) }}{{ $medicion->indicador->unidad_medida === 'PORCENTAJE' ? '%' : '' }}</td>
                <td>{{ $medicion->meta_programada === null ? 'No configurada' : number_format((float) $medicion->meta_programada, 2) }}</td>
                <td>{{ $medicion->estado_cumplimiento }}</td>
                <td>{{ $medicion->analisis_causas ?: 'Pendiente' }}</td>
                <td>{{ $medicion->acciones_mejora ?: 'Pendiente' }}</td>
            </tr>
        @empty
            <tr><td colspan="7">Sin datos para el periodo seleccionado.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
