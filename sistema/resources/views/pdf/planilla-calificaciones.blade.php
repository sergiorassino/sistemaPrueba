{{-- Planilla por curso/materia: misma grilla visual que el boletín (consulta-calificaciones-alumno), filas = estudiantes. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 10mm 7mm 8mm 7mm; }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 6pt;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .layer { position: relative; }
        .titulo {
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            margin: 0 0 3px 0;
            font-size: 10pt;
            letter-spacing: 0.02em;
        }
        .subtitulo { text-align: center; margin: 0 0 4px 0; font-size: 6.5pt; }
        .meta { margin: 0 0 4px 0; font-size: 6.5pt; line-height: 1.25; }
        .meta strong.meta-materia { font-size: 8pt; }
        .meta strong.meta-curso { font-size: 6.5pt; font-weight: 700; }

        table.outer {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
        }
        table.outer tbody tr.fila-alumno td {
            vertical-align: middle;
        }
        table.outer tbody tr.fila-alumno td.bay-ec {
            overflow: hidden;
        }
        th.bay, td.bay {
            border: 0.75pt solid #333333;
            border-radius: 1.5pt;
            padding: 0;
            vertical-align: middle;
            overflow: hidden;
            background-color: #fff;
        }
        th.bay-ec, td.bay-ec,
        th.bay-ord, td.bay-ord {
            border: 0.75pt solid #333333;
            border-radius: 1.5pt;
            background-color: #fff;
        }
        th.bay-ord, td.bay-ord {
            font-weight: 700;
            font-size: 5pt;
            text-align: center;
            padding: 0 1px !important;
            line-height: 1.05;
        }
        td.bay-ord {
            font-weight: 400;
        }
        th.bay-ec {
            font-weight: 700;
            font-size: 5.5pt;
            padding: 2px 3px;
            text-align: center;
            line-height: 1.1;
        }
        td.bay-ec {
            text-align: left !important;
            font-weight: 400;
            text-transform: uppercase;
            padding: 0 !important;
            line-height: 1;
            white-space: nowrap;
            overflow: hidden;
        }
        table.inner tr.data td.celda-nombre {
            text-align: left !important;
            white-space: nowrap;
            overflow: hidden;
        }
        table.inner {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin: 0;
            font-size: 5.5pt;
        }
        table.inner th, table.inner td {
            padding: 0 1px;
            text-align: center;
            vertical-align: middle;
            border: none;
        }
        table.inner tr:first-child th {
            font-weight: 700;
            font-size: 5pt;
            background-color: #fff;
            border-bottom: 0.55pt solid #333;
            padding: 1px;
        }
        table.inner tr:nth-child(2) th {
            font-weight: 700;
            font-size: 4.8pt;
            background-color: #fff;
            border-bottom: 0.45pt solid #666;
        }
        table.inner tr:nth-child(2) th:not(:last-child) {
            border-right: 0.4pt solid #888;
        }
        table.inner tr.data td {
            border-top: 0.3pt solid #bbb;
            border-right: 0.3pt solid #999;
        }
        table.inner tr.data td:last-child { border-right: none; }
        table.outer tbody tr.fila-alumno {
            page-break-inside: auto;
            page-break-after: auto;
        }

        th.bay-col, td.bay-col {
            font-weight: 700;
            font-size: 5pt;
            text-align: center;
            padding: 1px !important;
            line-height: 1.05;
            background-color: #fff;
        }
        td.bay-col {
            font-weight: 400;
        }
        td.bay-prom {
            font-weight: 700;
        }
        td.bay.bay-desaprobado,
        td.bay.bay-desaprobado table.inner tr.data td {
            background-color: #b8b8b8 !important;
        }

        .pie-footer { width: 100%; margin-top: 12mm; }
        .pie-footer-tabla {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .pie-footer-tabla td { vertical-align: bottom; padding: 0; border: 0; }
        .pie-firmas-gutter {
            width: 2cm;
            min-width: 2cm;
            max-width: 2cm;
            padding: 0;
            border: 0;
            font-size: 1pt;
            line-height: 1;
        }
        .pie-firma-spacer {
            width: auto;
            padding: 0;
            border: 0;
            font-size: 1pt;
            line-height: 1;
        }
        .pie-firma-izq,
        .pie-firma-der {
            width: 68mm;
            vertical-align: bottom;
            padding: 0;
            border: 0;
        }
        .pie-firma-izq { text-align: left; }
        .pie-firma-der { text-align: right; }
        .firma-bloque {
            margin: 0;
            padding: 0;
            width: 65mm;
        }
        .pie-firma-der .firma-bloque {
            margin-left: auto;
            margin-right: 0;
        }
        .firma-linea {
            border-bottom: 0.55pt dotted #333;
            height: 6mm;
            width: 65mm;
            min-width: 65mm;
            display: block;
            margin: 0;
        }
        .firma-label {
            font-size: 6pt;
            text-align: center;
            margin: 1px 0 0 0;
            line-height: 1.15;
            font-weight: 400;
            width: 65mm;
        }
    </style>
</head>
<body>
@php
    use App\Support\PromedioAnualCalificacionesSecundario;
    $filas = $filas ?? [];
    $layout = $layoutFilas ?? [
        'fontDataPt' => 6.2,
        'fontEcPt' => 6.1,
        'fontColPt' => 6.0,
        'espacioFilasPx' => 0.94,
        'paddingCeldaVertPx' => 1.4,
        'lineHeightFila' => 1.44,
    ];
    $padV = $layout['paddingCeldaVertPx'] ?? 1.2;
    $lh = $layout['lineHeightFila'] ?? 1.2;
    $styCeldaVert = sprintf('padding-top:%spx;padding-bottom:%spx;line-height:%s;', $padV, $padV, $lh);
    $styCeldaNombre = sprintf('padding:%spx 3px !important;line-height:%s;', $padV, $lh);
    $styCeldaOrd = sprintf('padding:%spx 1px !important;line-height:%s;', $padV, $lh);
    $styBayCol = sprintf('padding:%spx 1px !important;line-height:%s;', $padV, $lh);
    $blank = "\u{00A0}";
    $celda = static function (array $fila, string $col) use ($blank): string {
        $s = trim((string) ($fila[$col] ?? ''));
        return $s === '' ? $blank : $s;
    };
    $promCelda = static function (array $fila) use ($blank): string {
        $s = trim((string) ($fila['prom'] ?? ''));
        if ($s === '') {
            return $blank;
        }
        $n = str_replace(',', '.', $s);
        if (is_numeric($n)) {
            return number_format((float) $n, 2, ',', '');
        }
        return $s;
    };
    /*
     * Anchos % (Dompdf). Eval achicado vs boletín; 10 % menos en cada celda de notas → espacio a Estudiante.
     */
    $factorAnchoNotas = 0.9;
    $wEvPctNarrow = 7.05;
    $wEvPctBase = 8.1 * 0.9;
    $wEvPct = $wEvPctNarrow * $factorAnchoNotas;
    $wEv = number_format($wEvPct, 2, '.', '').'%';
    $freedEval = 8 * 8.1 * 0.1;
    $addColoq = $freedEval / 3;
    $toPromOnly = 8 * ($wEvPctBase - $wEvPctNarrow);
    $wJisPct = 5.8 * $factorAnchoNotas;
    $wJis = number_format($wJisPct, 2, '.', '').'%';
    $wDicPct = (1.8 + $addColoq) * $factorAnchoNotas;
    $wFebPct = $wDicPct;
    $wPromPct = (1.26 + $addColoq + $toPromOnly) * $factorAnchoNotas;
    $wDic = number_format($wDicPct, 2, '.', '').'%';
    $wFeb = number_format($wFebPct, 2, '.', '').'%';
    $wProm = number_format($wPromPct, 2, '.', '').'%';
    $ahorroNotasPct = (8 * $wEvPctNarrow) + (2 * 5.8) + (1.8 + $addColoq) * 2 + (1.26 + $addColoq + $toPromOnly);
    $ahorroNotasPct -= (8 * $wEvPct) + (2 * $wJisPct) + $wDicPct * 2 + $wPromPct;
    $wEstudianteTotal = 19.54;
    $wOrdPct = 2.5;
    $wEcPct = ($wEstudianteTotal - $wOrdPct) + $ahorroNotasPct;
    $wOrd = number_format($wOrdPct, 2, '.', '').'%';
    $wEc = number_format($wEcPct, 2, '.', '').'%';
    $wOrdCell = 'width:'.$wOrd.';min-width:0;overflow:hidden;';
    $wCell = 'width:'.$wEc.';min-width:0;overflow:hidden;';
    $wEvCell = 'width:'.$wEv.';min-width:0;overflow:hidden;';
    $wJisCell = 'width:'.$wJis.';min-width:0;overflow:hidden;';
    $wDicCell = 'width:'.$wDic.';min-width:0;overflow:hidden;';
    $wFebCell = 'width:'.$wFeb.';min-width:0;overflow:hidden;';
    $wPromCell = 'width:'.$wProm.';min-width:0;overflow:hidden;';
    $filasLista = array_values($filas);
    $styFontEc = 'font-size:'.$layout['fontEcPt'].'pt;';
    $styFontData = 'font-size:'.$layout['fontDataPt'].'pt;';
    $styFontCol = 'font-size:'.$layout['fontColPt'].'pt;';
    $spacingFilas = $layout['espacioFilasPx'].'px';
@endphp

<div class="layer">
        @include('pdf.partials.header', ['header' => $pdfHeader ?? null])

        <p class="titulo">Planilla de calificaciones</p>
        <p class="subtitulo">
            @if (! empty($ano))
                Ciclo lectivo {{ $ano }}
            @endif
        </p>

        <p class="meta">
            <strong class="meta-materia">{{ mb_strtoupper((string) ($materiaLabel ?? '')) }}</strong>
            @if (trim((string) ($cursoLabel ?? '')) !== '')
                &nbsp;·&nbsp;<strong class="meta-curso">{{ $cursoLabel }}</strong>
            @endif
            @if (trim((string) ($profesoresLinea ?? '')) !== '' && ($profesoresLinea ?? '') !== '—')
                <br><span>Prof: {{ $profesoresLinea }}</span>
            @endif
        </p>

        <table class="outer" cellspacing="0" width="100%" style="border-spacing:2px {{ $spacingFilas }};">
            <thead>
            <tr>
                <th class="bay-ord" style="{{ $wOrdCell }}">Nº</th>
                <th class="bay-ec" style="{{ $wCell }}">Estudiante</th>
                @for ($e = 1; $e <= 8; $e++)
                    <th class="bay" style="{{ $wEvCell }}">
                        <table class="inner" cellspacing="0" width="100%">
                            <tr><th colspan="3">Eval. {{ $e }}</th></tr>
                            <tr>
                                <th>N</th>
                                <th>R1</th>
                                <th>R2</th>
                            </tr>
                        </table>
                    </th>
                @endfor
                <th class="bay" style="{{ $wJisCell }}">
                    <table class="inner" cellspacing="0" width="100%">
                        <tr><th colspan="2">JIS 1</th></tr>
                        <tr><th>N</th><th>R</th></tr>
                    </table>
                </th>
                <th class="bay" style="{{ $wJisCell }}">
                    <table class="inner" cellspacing="0" width="100%">
                        <tr><th colspan="2">JIS 2</th></tr>
                        <tr><th>N</th><th>R</th></tr>
                    </table>
                </th>
                <th class="bay bay-col" style="{{ $wDicCell }}">Coloq.<br>Dic</th>
                <th class="bay bay-col" style="{{ $wFebCell }}">Coloq.<br>Feb</th>
                <th class="bay bay-col" style="{{ $wPromCell }}">Prom.<br>Final</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($filasLista as $idx => $fila)
                <tr class="fila-alumno">
                    <td class="bay-ord" style="{{ $wOrdCell }}">
                        <table class="inner" cellspacing="0" width="100%">
                            <tr class="data">
                                <td class="celda-ord" style="{{ $styCeldaOrd }}{{ $styFontData }}">{{ $idx + 1 }}</td>
                            </tr>
                        </table>
                    </td>
                    <td class="bay-ec" style="{{ $wCell }}">
                        <table class="inner" cellspacing="0" width="100%">
                            <tr class="data">
                                <td class="celda-nombre" style="{{ $styCeldaNombre }}{{ $styFontEc }}">{{ mb_strtoupper((string) ($fila['alumno'] ?? '')) }}</td>
                            </tr>
                        </table>
                    </td>
                    @for ($e = 1; $e <= 8; $e++)
                        @php
                            $b = ($e - 1) * 3 + 1;
                            $c1 = 'ic'.str_pad((string) $b, 2, '0', STR_PAD_LEFT);
                            $c2 = 'ic'.str_pad((string) ($b + 1), 2, '0', STR_PAD_LEFT);
                            $c3 = 'ic'.str_pad((string) ($b + 2), 2, '0', STR_PAD_LEFT);
                            $camposEval = [$c1, $c2, $c3];
                            $desaprobado = PromedioAnualCalificacionesSecundario::bloqueDesaprobado($camposEval, $fila);
                        @endphp
                        <td @class(['bay', 'bay-desaprobado' => $desaprobado]) style="{{ $wEvCell }}">
                            <table class="inner" cellspacing="0" width="100%">
                                <tr class="data">
                                    <td style="{{ $styCeldaVert }}{{ $styFontData }}">{{ $celda($fila, $c1) }}</td>
                                    <td style="{{ $styCeldaVert }}{{ $styFontData }}">{{ $celda($fila, $c2) }}</td>
                                    <td style="{{ $styCeldaVert }}{{ $styFontData }}">{{ $celda($fila, $c3) }}</td>
                                </tr>
                            </table>
                        </td>
                    @endfor
                    @php
                        $desapJis1 = PromedioAnualCalificacionesSecundario::bloqueDesaprobado(['ic25', 'ic26'], $fila);
                        $desapJis2 = PromedioAnualCalificacionesSecundario::bloqueDesaprobado(['ic27', 'ic28'], $fila);
                    @endphp
                    <td @class(['bay', 'bay-desaprobado' => $desapJis1]) style="{{ $wJisCell }}">
                        <table class="inner" cellspacing="0" width="100%">
                            <tr class="data">
                                <td style="{{ $styCeldaVert }}{{ $styFontData }}">{{ $celda($fila, 'ic25') }}</td>
                                <td style="{{ $styCeldaVert }}{{ $styFontData }}">{{ $celda($fila, 'ic26') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td @class(['bay', 'bay-desaprobado' => $desapJis2]) style="{{ $wJisCell }}">
                        <table class="inner" cellspacing="0" width="100%">
                            <tr class="data">
                                <td style="{{ $styCeldaVert }}{{ $styFontData }}">{{ $celda($fila, 'ic27') }}</td>
                                <td style="{{ $styCeldaVert }}{{ $styFontData }}">{{ $celda($fila, 'ic28') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td class="bay bay-col" style="{{ $wDicCell }}{{ $styBayCol }}{{ $styFontCol }}">{{ $celda($fila, 'dic') }}</td>
                    <td class="bay bay-col" style="{{ $wFebCell }}{{ $styBayCol }}{{ $styFontCol }}">{{ $celda($fila, 'feb') }}</td>
                    <td class="bay bay-col bay-prom" style="{{ $wPromCell }}{{ $styBayCol }}{{ $styFontCol }}">{{ $promCelda($fila) }}</td>
                </tr>
            @empty
                <tr>
                    <td class="bay-ec" colspan="15" style="text-align:center;">Sin estudiantes con calificaciones para esta materia.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="pie-footer pie-footer--solo-firmas">
            <table class="pie-footer-tabla" cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td class="pie-firmas-gutter">&nbsp;</td>
                    <td class="pie-firma-izq" valign="bottom">
                        <div class="firma-bloque">
                            <div class="firma-linea"></div>
                            <p class="firma-label">Firma Preceptor/a</p>
                        </div>
                    </td>
                    <td class="pie-firma-spacer">&nbsp;</td>
                    <td class="pie-firma-der" valign="bottom" align="right">
                        <div class="firma-bloque">
                            <div class="firma-linea"></div>
                            <p class="firma-label">Firma Director/a</p>
                        </div>
                    </td>
                    <td class="pie-firmas-gutter">&nbsp;</td>
                </tr>
            </table>
        </div>
</div>
</body>
</html>
