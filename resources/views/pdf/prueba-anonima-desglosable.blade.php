<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Prueba anónima — desglosable</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; }
        .sheet { border: 1px solid #333; padding: 16px; margin-bottom: 12px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .code { font-family: DejaVu Sans Mono, monospace; }
        .tear-line { border-top: 1px dashed #666; margin: 16px 0; text-align: center; color: #666; }
        .desglosable { border: 1px solid #333; padding: 12px; width: 60%; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px 0; }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <strong>Prueba anónima — {{ $exam->course->name }}</strong>
            <span>Formato: {{ $exam->format_code }}</span>
        </div>
        <table>
            <tr><td>Curso</td><td>{{ $exam->course->code }} — {{ $exam->course->name }}</td></tr>
            <tr><td>Docente</td><td>{{ $exam->teacher->name }}</td></tr>
            <tr><td>Fecha de examen</td><td>{{ $exam->exam_date->format('d/m/Y') }}</td></tr>
            <tr><td>Código de sobre lacrado</td><td class="code">{{ $exam->sealed_envelope_code }}</td></tr>
        </table>
    </div>

    <div class="tear-line">✂ — corte aquí — datos personales van en sobre separado — ✂</div>

    <div class="desglosable">
        <strong>Cupón desglosable — datos personales</strong>
        <table>
            <tr><td>Apellidos y nombres</td><td>____________________________</td></tr>
            <tr><td>Código de estudiante</td><td>____________________________</td></tr>
            <tr><td>Firma</td><td>____________________________</td></tr>
            <tr><td>Código de sobre lacrado</td><td class="code">{{ $exam->sealed_envelope_code }}</td></tr>
        </table>
    </div>
</body>
</html>
