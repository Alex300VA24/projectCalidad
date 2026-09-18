<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Acta de examen de suficiencia</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f2f2f2; width: 30%; }
        .signatures { display: flex; justify-content: space-between; margin-top: 48px; }
        .signature { width: 30%; text-align: center; border-top: 1px solid #333; padding-top: 4px; }
    </style>
</head>
<body>
    <h1>Acta de examen de suficiencia</h1>

    <table>
        <tr><th>Estudiante</th><td>{{ $exam->student->name }}</td></tr>
        <tr><th>Curso</th><td>{{ $exam->course->code }} — {{ $exam->course->name }}</td></tr>
        <tr><th>Resolución</th><td>{{ $exam->resolution_number }}</td></tr>
        <tr><th>Número de acta</th><td>{{ $exam->act_number }}</td></tr>
        <tr><th>Nota</th><td>{{ $exam->score }}</td></tr>
    </table>

    <div class="signatures">
        @foreach ($exam->jury_members ?? [] as $member)
            <div class="signature">{{ is_array($member) ? ($member['name'] ?? '') : $member }}</div>
        @endforeach
    </div>
</body>
</html>
