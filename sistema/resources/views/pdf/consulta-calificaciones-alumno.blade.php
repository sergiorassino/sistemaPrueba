{{-- Boletín de calificaciones (secundario): misma plantilla para autogestión del estudiante y para docentes/secretaría. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        /* A4 apaisado: margen superior amplio para que el header no quede en zona no imprimible */
        @page { margin: 15mm 7mm 6mm 7mm; }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 6pt;
            color: #000;
            margin: 0;
            padding: 0;
            position: relative;
        }
        /* Marca de agua: debe ir DESPUÉS de la tabla en el HTML y en posición absoluta
           sobre el bloque (Dompdf trata mal z-index/fixed respecto al contenido). */
        .sheet-wrap {
            position: relative;
            width: 100%;
            display: block;
            overflow: visible;
        }
        .wm-overlay {
            position: absolute;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
            pointer-events: none;
            overflow: visible;
        }
        /*
         * Centro X/Y respecto solo a la grilla (overlay = alto/ancho de la tabla).
         * top ~54% baja un poco el centro respecto al 50% geométrico para acercarlo al bloque de materias (tbody).
         */
        .wm {
            position: absolute;
            left: 50%;
            top: 54%;
            width: auto;
            margin: 0;
            padding: 0;
            text-align: center;
            font-size: 22pt;
            font-weight: 700;
            color: #c0c0c0;
            opacity: 0.4;
            letter-spacing: 0.5px;
            transform: translate(-50%, -50%) rotate(-29deg);
            transform-origin: center center;
            white-space: nowrap;
            line-height: 1;
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
        .subtitulo { text-align: center; margin: 0 0 5px 0; font-size: 6.5pt; }
        .meta { margin: 0 0 5px 0; font-size: 6.5pt; line-height: 1.25; }

        /* Tabla exterior: separación horizontal entre bloques redondeados */
        table.outer {
            position: relative;
            z-index: 0;
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            /* Separación moderada: mucho border-spacing hace que Dompdf reparta mal los % */
            border-spacing: 3px 2px;
        }

        /* Contenedor de cada bloque (Eval / JIS / coloquio / prom.) */
        th.bay, td.bay {
            border: 0.75pt solid #333333;
            border-radius: 1.5pt;
            padding: 0;
            vertical-align: middle;
            overflow: hidden;
            background-color: #fff;
        }
        th.bay-ec, td.bay-ec {
            border: 0.75pt solid #333333;
            border-radius: 1.5pt;
            background-color: #fff;
        }
        th.bay-ec {
            font-weight: 700;
            font-size: 5.5pt;
            padding: 3px 4px;
            text-align: center;
            line-height: 1.15;
        }
        td.bay-ec {
            text-align: left !important;
            font-weight: 400;
            text-transform: uppercase;
            font-size: 5.8pt;
            padding: 2px 4px !important;
            background-color: #fff;
        }

        table.inner {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin: 0;
            font-size: 5.8pt;
        }
        table.inner th, table.inner td {
            padding: 1px 2px;
            text-align: center;
            vertical-align: middle;
            border: none;
        }
        /* Título del bloque (Eval. n / JIS n) */
        table.inner tr:first-child th {
            font-weight: 700;
            font-size: 5.2pt;
            background-color: #fff;
            border-bottom: 0.55pt solid #333;
            padding: 2px 1px;
        }
        /* Subencabezados N R1 R2 / N R */
        table.inner tr:nth-child(2) th {
            font-weight: 700;
            font-size: 5.1pt;
            background-color: #fff;
            border-bottom: 0.45pt solid #666;
        }
        table.inner tr:nth-child(2) th:not(:last-child) {
            border-right: 0.4pt solid #888;
        }
        /* Cuerpo: líneas verticales entre subceldas */
        table.inner tr.data td {
            font-size: 6pt;
            border-top: 0.35pt solid #bbb;
            border-right: 0.35pt solid #999;
        }
        table.inner tr.data td:last-child {
            border-right: none;
        }

        th.bay-col, td.bay-col {
            font-weight: 700;
            font-size: 5.1pt;
            text-align: center;
            padding: 2px 1px !important;
            line-height: 1.1;
            background-color: #fff;
        }
        td.bay-col {
            font-weight: 400;
            font-size: 5.5pt;
            background-color: #fff;
        }
        td.bay-prom {
            font-weight: 700;
            font-size: 5.5pt;
            background-color: #fff;
        }

        .disc { margin-top: 5mm; font-size: 7pt; line-height: 1.35; }
        .disc p { margin: 0 0 2px 0; font-weight: 400; }
        /* Inasistencias / sanciones: menos interlineado y menos aire entre renglones */
        .disc p.disc-item-tight {
            line-height: 1.05;
            margin: 0 0 0.5px 0;
        }
        .disc .disc-lbl { font-weight: 400; }
        .adeu { margin-top: 2.45mm; font-size: 6.8pt; }
        .adeu-title {
            font-weight: 700;
            margin: 0 0 2px 0;
            font-size: 6.9pt;
            letter-spacing: 0.02em;
        }
        .adeu-body { margin: 0; font-weight: 400; line-height: 1.02; text-align: left; }
    </style>
</head>
<body>
<div class="layer">
    @include('pdf.partials.header', ['header' => $pdfHeader ?? null])

    <p class="titulo">Consulta de Calificaciones</p>
    <p class="subtitulo">
        @if (! empty($consulta['anoLectivo']))
            Ciclo lectivo {{ $consulta['anoLectivo'] }}
        @endif
    </p>

    <p class="meta">
        <strong>{{ $consulta['alumnoLinea'] }}</strong>
        @if (trim($consulta['dni']) !== '')
            &nbsp;·&nbsp;D.N.I. {{ $consulta['dni'] }}
        @endif
        @if (trim($consulta['cursoLabel']) !== '')
            &nbsp;·&nbsp;{{ $consulta['cursoLabel'] }}
        @endif
    </p>

    @php
        $blank = "\u{00A0}";
        $ic = static function (object $row, string $col) use ($blank): string {
            $s = trim((string) ($row->{$col} ?? ''));
            return $s === '' ? $blank : $s;
        };
        $prom = static function (object $row) use ($blank): string {
            $s = trim((string) ($row->calif ?? ''));
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
         * Anchos en % en cada th/td (Dompdf).
         * Eval: 10% menos que 8.1% base, y un achique extra del bloque N+R1+R2; lo ahorrado extra va solo a Prom. Final.
         * El primer ahorro (8×0.81%) sigue repartido en Dic/Feb/Prom por igual (+2.16% c/u en coloquios y prom).
         */
        $wEvPctNarrow = 7.05;
        $wEvPctBase = 8.1 * 0.9;
        $wEv = number_format($wEvPctNarrow, 2, '.', '').'%';
        $freedEval = 8 * 8.1 * 0.1;
        $addColoq = $freedEval / 3;
        $toPromOnly = 8 * ($wEvPctBase - $wEvPctNarrow);
        $wEc = '19.54%';
        $wJis = '5.8%';
        $wDic = number_format(1.8 + $addColoq, 2, '.', '').'%';
        $wFeb = number_format(1.8 + $addColoq, 2, '.', '').'%';
        $wProm = number_format(1.26 + $addColoq + $toPromOnly, 2, '.', '').'%';
        $wCell = 'width:'.$wEc.';min-width:0;overflow:hidden;';
        $wEvCell = 'width:'.$wEv.';min-width:0;overflow:hidden;';
        $wJisCell = 'width:'.$wJis.';min-width:0;overflow:hidden;';
        $wDicCell = 'width:'.$wDic.';min-width:0;overflow:hidden;';
        $wFebCell = 'width:'.$wFeb.';min-width:0;overflow:hidden;';
        $wPromCell = 'width:'.$wProm.';min-width:0;overflow:hidden;';
    @endphp

    <div class="sheet-wrap">
    <table class="outer" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th class="bay-ec" style="{{ $wCell }}">Espacio<br>Curricular</th>
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
        @forelse ($consulta['rows'] as $row)
            <tr>
                <td class="bay-ec" style="{{ $wCell }}">{{ $row->espacio_curricular ?? '' }}</td>
                @for ($e = 1; $e <= 8; $e++)
                    @php
                        $b = ($e - 1) * 3 + 1;
                        $c1 = 'ic'.str_pad((string) $b, 2, '0', STR_PAD_LEFT);
                        $c2 = 'ic'.str_pad((string) ($b + 1), 2, '0', STR_PAD_LEFT);
                        $c3 = 'ic'.str_pad((string) ($b + 2), 2, '0', STR_PAD_LEFT);
                    @endphp
                    <td class="bay" style="{{ $wEvCell }}">
                        <table class="inner" cellspacing="0" width="100%">
                            <tr class="data">
                                <td>{{ $ic($row, $c1) }}</td>
                                <td>{{ $ic($row, $c2) }}</td>
                                <td>{{ $ic($row, $c3) }}</td>
                            </tr>
                        </table>
                    </td>
                @endfor
                <td class="bay" style="{{ $wJisCell }}">
                    <table class="inner" cellspacing="0" width="100%">
                        <tr class="data">
                            <td>{{ $ic($row, 'ic25') }}</td>
                            <td>{{ $ic($row, 'ic26') }}</td>
                        </tr>
                    </table>
                </td>
                <td class="bay" style="{{ $wJisCell }}">
                    <table class="inner" cellspacing="0" width="100%">
                        <tr class="data">
                            <td>{{ $ic($row, 'ic27') }}</td>
                            <td>{{ $ic($row, 'ic28') }}</td>
                        </tr>
                    </table>
                </td>
                <td class="bay bay-col" style="{{ $wDicCell }}">{{ $ic($row, 'dic') }}</td>
                <td class="bay bay-col" style="{{ $wFebCell }}">{{ $ic($row, 'feb') }}</td>
                <td class="bay bay-col bay-prom" style="{{ $wPromCell }}">{{ $prom($row) }}</td>
            </tr>
        @empty
            <tr>
                <td class="bay-ec" colspan="14" style="text-align:center;">Sin calificaciones registradas para esta matrícula.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    <div class="wm-overlay">
        <div class="wm">SIN VALOR LEGAL</div>
    </div>
    </div>

    @php
        $adeudadas = $consulta['materias_adeudadas'] ?? [];
    @endphp
    @if (count($adeudadas) > 0)
        <div class="adeu">
            <p class="adeu-title">MATERIAS PREVIAS:</p>
            <p class="adeu-body">@foreach ($adeudadas as $a){{ $a->linea }}@if (! $loop->last) - @endif@endforeach</p>
        </div>
    @endif

    @php
        $itemsBoletin = $consulta['items_boletin'] ?? [];
    @endphp
    @if (count($itemsBoletin) > 0)
        <div class="disc">
            @foreach ($itemsBoletin as $it)
                @php
                    $t = (float) ($it->total ?? 0);
                    $fuente = (string) ($it->fuente ?? '');
                    $esInas = ($fuente === 'inasistencias');
                    $itemTight = in_array($fuente, ['inasistencias', 'sanciones'], true);
                    $mostrar = $esInas ? (abs($t) >= 0.005) : ((int) round($t) !== 0);
                    $txt = $esInas
                        ? number_format($t, 2, ',', '')
                        : (string) (int) round($t);
                @endphp
                <p @class(['disc-item-tight' => $itemTight])><span class="disc-lbl">{{ $it->etiqueta }}:</span> @if ($mostrar){{ $txt }}@else{{ $blank }}@endif</p>
            @endforeach
        </div>
    @endif

</div>
</body>
</html>
