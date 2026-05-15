<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 14mm 12mm 16mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9.5pt; color: #111; line-height: 1.25; }
        .cabecera-informe {
            width: calc(100% - 16px - 1.5pt);
            border: 0.75pt solid #111;
            border-radius: 8px;
            padding: 6px 8px 8px 8px;
            margin-bottom: 8px;
        }
        .cabecera-informe .pdf-header {
            border: 0;
            margin-bottom: 4px;
            width: 100%;
            padding: 0;
        }
        .cabecera-informe .titulo-informe {
            font-weight: 700;
            font-size: 10pt;
            text-align: center;
            text-transform: uppercase;
            margin: 6px 0 2px 0;
        }
        .cabecera-informe .alumno {
            font-weight: 700;
            font-size: 9.5pt;
            text-align: center;
            text-transform: uppercase;
            margin: 2px 0;
        }
        .cabecera-informe .curso {
            font-weight: 700;
            font-size: 9.5pt;
            text-align: center;
            text-transform: uppercase;
            margin: 0;
        }
        table.detalle {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.detalle th,
        table.detalle td {
            border: 0.75pt solid #333;
            padding: 1.5px 4px;
            font-size: 8.5pt;
            line-height: 1.1;
        }
        table.detalle th {
            font-weight: 700;
            text-align: center;
            background: #f5f5f5;
        }
        table.detalle td { vertical-align: middle; }
        table.detalle .col-fecha { width: 18%; text-align: center; }
        table.detalle .col-cant { width: 12%; text-align: center; }
        table.detalle .col-tipo { width: 28%; }
        table.detalle .col-just { width: 10%; text-align: center; }
        table.detalle .col-obs { width: 32%; }
        .totales { margin: 4px 0 24px 0; font-size: 9.5pt; }
        .totales p { margin: 3px 0; }
        .totales .label { font-weight: 700; }
        .firmas {
            width: 100%;
            margin-top: 36px;
        }
        .firmas table { width: 100%; border-collapse: collapse; }
        .firmas td { width: 50%; vertical-align: top; text-align: center; padding: 0 12px; }
        .firmas .linea {
            border-top: 0.75pt dotted #333;
            margin: 0 auto 4px auto;
            width: 85%;
            height: 28px;
        }
        .firmas .etiqueta { font-size: 9pt; }
    </style>
</head>
<body>
    <div class="cabecera-informe">
        @include('pdf.partials.header', ['header' => $pdfHeader ?? null])
        <p class="titulo-informe">Informe de inasistencias — {{ $ano }}</p>
        <p class="alumno">
            {{ $alumnoLinea }}@if($dni !== '') — {{ $dni }}@endif
        </p>
        @if ($cursoLabel !== '')
            <p class="curso">{{ $cursoLabel }}</p>
        @endif
        @if ($filtroFechasActivo ?? false)
            <p class="curso" style="font-weight:400;font-size:8.5pt;margin-top:4px;">
                Período: {{ $fechaDesde }} — {{ $fechaHasta }}
            </p>
        @endif
    </div>

    <table class="detalle" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th class="col-fecha">Fecha</th>
                <th class="col-cant">Cantidad</th>
                <th class="col-tipo">Tipo</th>
                <th class="col-just">Just. / Injus.</th>
                <th class="col-obs">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inasistencias as $i)
                @php
                    $just = strtoupper(trim((string) ($i->just ?? '')));
                    $codigoJust = $just === 'J' ? 'J' : 'I';
                @endphp
                <tr>
                    <td class="col-fecha">{{ $i->fecha?->format('d/m/Y') ?? '—' }}</td>
                    <td class="col-cant">
                        @if ($i->cantidad !== null)
                            {{ number_format((float) $i->cantidad, 2, ',', '') }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="col-tipo">{{ $i->etiquetaTipo() }}</td>
                    <td class="col-just">{{ $codigoJust }}</td>
                    <td class="col-obs">{{ trim((string) ($i->obs ?? '')) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">Sin inasistencias registradas en el período.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="totales">
        <p><span class="label">Inasistencias justificadas:</span> {{ $resumen->formatear($resumen->justificadas) }}</p>
        <p><span class="label">Inasistencias injustificadas:</span> {{ $resumen->formatear($resumen->injustificadas) }}</p>
        <p><span class="label">Total de inasistencias:</span> {{ $resumen->formatear($resumen->totalClase()) }}</p>
        <p><span class="label">Inasistencias a educación física:</span> {{ $resumen->educacionFisicaRegistros }}</p>
    </div>

    <div class="firmas">
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <div class="linea"></div>
                    <p class="etiqueta">Firma del Preceptor/a</p>
                </td>
                <td>
                    <div class="linea"></div>
                    <p class="etiqueta">Firma Responsable</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
