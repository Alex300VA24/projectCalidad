<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Historial académico físico</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { margin-bottom: 16px; color: #444; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Historial académico físico</h1>
    <div class="meta">
        <span>Estudiante: {{ $history->student->name }}</span> —
        <span>Año de ingreso: {{ $history->entry_year }}</span>
    </div>

    <table>
        <thead>
            <tr><th>Curso</th><th>Periodo</th><th>Nota</th><th>Créditos</th></tr>
        </thead>
        <tbody>
            @foreach ($history->physical_history_data['cursos'] ?? [] as $curso)
                <tr>
                    <td>{{ $curso['nombre'] ?? '' }}</td>
                    <td>{{ $curso['periodo'] ?? '' }}</td>
                    <td>{{ $curso['nota'] ?? '' }}</td>
                    <td>{{ $curso['creditos'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>Emitido para Registros Académicos — {{ now()->format('d/m/Y') }}</p>
</body>
</html>
