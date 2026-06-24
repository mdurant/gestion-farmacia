<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $systemName }} {{ $systemVersion }} — {{ $title }}</title>
    <style>
        @page {
            margin: 28px 28px 52px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding-bottom: 36px;
        }

        .pdf-header {
            margin-bottom: 14px;
            border-bottom: 2px solid #7367f0;
            padding-bottom: 10px;
        }

        .pdf-system-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #5e5873;
            letter-spacing: 0.01em;
        }

        .pdf-report-title {
            margin: 4px 0 0;
            font-size: 20px;
            font-weight: 700;
            color: #7367f0;
            line-height: 1.25;
        }

        .meta {
            color: #666;
            margin-bottom: 16px;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #f3f3f3;
            font-weight: 700;
        }

        tr:nth-child(even) td {
            background: #fafafa;
        }

        .pdf-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            border-top: 1px solid #d8d6de;
            padding: 8px 0 0;
            font-size: 9px;
            color: #6e6b7b;
            text-align: center;
        }

        .pdf-footer strong {
            color: #5e5873;
        }
    </style>
</head>
<body>
    <header class="pdf-header">
        <p class="pdf-system-title">{{ $systemName }} · v{{ $systemVersion }}</p>
        <h1 class="pdf-report-title">{{ $title }}</h1>
    </header>

    <p class="meta">
        Generado: {{ $generatedAt->format('d/m/Y H:i') }} (America/Santiago)
        @if ($filters?->from && $filters?->to)
            · Período: {{ $filters->from }} al {{ $filters->to }}
        @endif
    </p>

    <table>
        <thead>
            <tr>
                @foreach ($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ is_numeric($cell) ? number_format((float) $cell, 0, ',', '.') : $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($headers) }}">Sin datos para los filtros seleccionados</td></tr>
            @endforelse
        </tbody>
    </table>

    <footer class="pdf-footer">
        <strong>{{ $systemName }}</strong> · Versión {{ $systemVersion }}
        · Documento generado automáticamente · Residencias de larga estadía · Chile
    </footer>
</body>
</html>
