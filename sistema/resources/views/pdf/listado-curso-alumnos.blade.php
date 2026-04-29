<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #333; }
        h1 { font-size: 14pt; margin: 0 0 2px 0; color: #111; }
        .modo-estudiantes { font-size: 11pt; font-weight: bold; color: #333; margin: 0 0 10px 0; letter-spacing: 0.02em; }
        h2 { font-size: 12pt; margin: 18px 0 6px 0; color: #333; border-bottom: 1px solid #C1D7DA; padding-bottom: 2px; }
        h2:first-of-type { margin-top: 8px; }
        .meta { font-size: 10pt; margin-bottom: 12px; color: #555; }
        /* auto: la 1.ª columna puede achicarse al contenido; fixed en Dompdf suele repartir ancho por igual e ignorar <col> */
        table.tabla-alumnos { width: 100%; border-collapse: collapse; margin-top: 6px; margin-bottom: 8px; table-layout: auto; }
        th, td { border: 1px solid #ccc; padding: 4px 5px; text-align: left; vertical-align: top; word-wrap: break-word; }
        th { background: #C1D7DA; font-weight: bold; font-size: 8pt; line-height: 1.15; }
        td { font-size: 8pt; }
        /* Encabezado Nº: mismo padding/tipo que el resto de th para alinear la 1.ª línea */
        th.num {
            width: 24pt;
            max-width: 24pt;
            min-width: 24pt;
            padding: 4px 5px;
            text-align: center;
            white-space: nowrap;
            font-size: 8pt;
            line-height: 1.15;
            vertical-align: top;
        }
        td.num {
            width: 24pt;
            max-width: 24pt;
            min-width: 24pt;
            padding: 2px 1px;
            text-align: center;
            white-space: nowrap;
            font-size: 7pt;
            vertical-align: top;
        }
        /* Condición de matrícula: 40 % más pequeña que 8pt base (= 4.8pt), alineado a la derecha */
        th.col-cond, td.col-cond {
            font-size: 4.8pt;
            line-height: 1.12;
            text-align: right;
        }
        .salto { page-break-after: always; }
    </style>
</head>
<body>
    @include('pdf.partials.header', ['header' => $pdfHeader ?? null])

    <h1>Listado de Estudiantes</h1>
    <p class="modo-estudiantes">{{ $modoEstudiantesPdf }}</p>
    <div class="meta">
        <strong>Nivel:</strong> {{ $nivelNombre }}<br>
        <strong>Año lectivo:</strong> {{ $ano ?? '—' }}
    </div>

    @foreach ($bloques as $idx => $bloque)
        @if ($idx > 0)
            <div class="salto"></div>
        @endif
        <h2>{{ $bloque['cursoLabel'] }}</h2>
        <table class="tabla-alumnos">
            <thead>
                <tr>
                    {{-- Ancho inline: Dompdf aplica mejor esto que <col> con table-layout:fixed --}}
                    <th class="num" style="width:24pt;max-width:24pt;min-width:24pt;">Nº</th>
                    @foreach ($columnasMeta as $col)
                        @php $esColCond = ($col['key'] === 'condiciones.condicion'); @endphp
                        <th class="{{ $esColCond ? 'col-cond' : '' }}">{{ $esColCond ? 'Cond.' : $col['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($bloque['alumnos'] as $i => $a)
                    <tr>
                        <td class="num" style="width:24pt;max-width:24pt;min-width:24pt;">{{ $i + 1 }}</td>
                        @foreach ($columnasMeta as $col)
                            @php
                                $esColCond = ($col['key'] === 'condiciones.condicion');
                                $alias = $col['alias'];
                                $v = $a->{$alias} ?? null;
                                if ($v === null || $v === '') {
                                    $out = '—';
                                } elseif (is_numeric($v) && (str_contains($col['key'], 'bloq') || str_ends_with($col['key'], 'inscripto') || str_contains($col['key'], 'acept'))) {
                                    $out = $v ? 'Sí' : 'No';
                                } else {
                                    $out = $v;
                                }
                            @endphp
                            <td class="{{ $esColCond ? 'col-cond' : '' }}">{{ $out }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columnasMeta) + 1 }}" style="text-align:center;color:#666;">No hay alumnos matriculados en este curso.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach
</body>
</html>
