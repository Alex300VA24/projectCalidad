<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Sílabo visado</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        .meta { margin-bottom: 16px; color: #444; }
        .meta span { margin-right: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f2f2f2; width: 30%; }
        .seal { margin-top: 40px; padding-top: 16px; border-top: 1px solid #999; }
        .seal strong { display: block; }
    </style>
</head>
<body>
    <h1>Sílabo visado — {{ $syllabus->course->name }}</h1>
    <div class="meta">
        <span>Periodo: {{ $syllabus->academic_period }}</span>
        <span>Docente: {{ $syllabus->teacher->name }}</span>
        <span>Formato: F-M01.01-DPA-005</span>
    </div>

    <table>
        <tr><th>Curso</th><td>{{ $syllabus->course->code }} — {{ $syllabus->course->name }}</td></tr>
        <tr><th>Estado</th><td>{{ $syllabus->status }}</td></tr>
        @foreach ($syllabus->review_checklist ?? [] as $item => $value)
            <tr><th>{{ $item }}</th><td>{{ is_scalar($value) ? $value : json_encode($value) }}</td></tr>
        @endforeach
    </table>

    <h2>Requerimiento bibliográfico y hemerográfico</h2>
    <table>
        @foreach ($syllabus->library_requirement_data ?? [] as $item => $value)
            <tr><th>{{ $item }}</th><td>{{ is_scalar($value) ? $value : json_encode($value) }}</td></tr>
        @endforeach
    </table>

    <div class="seal">
        <strong>Visado por Dirección de Escuela</strong>
        <span>Fecha: {{ now()->format('d/m/Y') }}</span>
    </div>
</body>
</html>
