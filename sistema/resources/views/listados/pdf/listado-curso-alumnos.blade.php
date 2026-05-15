<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 10mm 8mm 10mm 8mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #333; margin: 0; padding: 0; }
        .listado-ctx {
            margin: 2px 0 8px 0;
            padding: 2px 0 6px 0;
            color: #333;
        }
        .listado-ctx .ctx-titulo {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin: 0 0 4px 0;
            color: #111;
        }
        .listado-ctx .ctx-linea {
            font-size: 8.5pt;
            line-height: 1.5;
            margin: 0 0 3px 0;
        }
        .listado-ctx .ctx-linea:last-child { margin-bottom: 0; }
        .listado-ctx .ctx-item { white-space: nowrap; }
        .listado-ctx .ctx-sep { color: #888; padding: 0 10px; }
        table.tabla-alumnos { width: 100%; border-collapse: collapse; margin: 0; table-layout: auto; }
        th, td { border: 1px solid #ccc; padding: 1px 3px; text-align: left; vertical-align: top; word-wrap: break-word; }
        th { background: #C1D7DA; font-weight: bold; font-size: 7pt; line-height: 1.1; }
        td { font-size: 7pt; line-height: 1.1; }
        th.num {
            width: 20pt;
            max-width: 20pt;
            min-width: 20pt;
            padding: 1px 2px;
            text-align: center;
            white-space: nowrap;
            vertical-align: top;
        }
        td.num {
            width: 20pt;
            max-width: 20pt;
            min-width: 20pt;
            padding: 1px 2px;
            text-align: center;
            white-space: nowrap;
            font-size: 6.5pt;
            vertical-align: top;
        }
        th.col-cond, td.col-cond {
            font-size: 4.8pt;
            line-height: 1.1;
            text-align: right;
        }
        .salto { page-break-after: always; }
    </style>
</head>
<body>
    @php
        $alumnosPorHoja = (int) ($alumnosPorHoja ?? 35);
        $primeraHoja = true;
    @endphp

    @foreach ($bloques as $bloque)
        @php
            $alumnos = $bloque['alumnos'];
            $paginas = $alumnos->isEmpty()
                ? collect([collect()])
                : $alumnos->chunk($alumnosPorHoja);
        @endphp
        @foreach ($paginas as $paginaIdx => $alumnosPagina)
            @if (! $primeraHoja)
                <div class="salto"></div>
            @endif
            @php
                $primeraHoja = false;
                $inicioNum = (int) $paginaIdx * $alumnosPorHoja;
            @endphp
            <div class="hoja-listado">
                @include('pdf.partials.header', ['header' => $pdfHeader ?? null])

                <div class="listado-ctx">
                    <p class="ctx-titulo">Listado de estudiantes</p>
                    <p class="ctx-linea">
                        <span class="ctx-item"><strong>Nivel:</strong> {{ $nivelNombre }}</span>
                        <span class="ctx-sep">|</span>
                        <span class="ctx-item"><strong>Año lectivo:</strong> {{ $ano ?? '—' }}</span>
                    </p>
                    <p class="ctx-linea">
                        <span class="ctx-item"><strong>Condición:</strong> {{ $modoEstudiantesPdf }}</span>
                        <span class="ctx-sep">|</span>
                        <span class="ctx-item"><strong>Curso:</strong> {{ $bloque['cursoLabel'] }}</span>
                    </p>
                </div>

                <table class="tabla-alumnos">
                    <thead>
                        <tr>
                            <th class="num" style="width:20pt;max-width:20pt;min-width:20pt;">Nº</th>
                            @foreach ($columnasMeta as $col)
                                @php $esColCond = ($col['key'] === 'condiciones.condicion'); @endphp
                                <th class="{{ $esColCond ? 'col-cond' : '' }}">{{ $esColCond ? 'Cond.' : $col['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($alumnosPagina as $i => $a)
                            <tr>
                                <td class="num" style="width:20pt;max-width:20pt;min-width:20pt;">{{ $inicioNum + $i + 1 }}</td>
                                @foreach ($columnasMeta as $col)
                                    @php
                                        $esColCond = ($col['key'] === 'condiciones.condicion');
                                        $alias = $col['alias'];
                                        $v = $a->{$alias} ?? null;
                                        if ($v === null || $v === '') {
                                            $out = '—';
                                        } elseif ($col['key'] === 'legajos.sexo') {
                                            $out = \App\Models\Sexo::etiquetaParaValorAlmacenado($v) ?: '—';
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
            </div>
        @endforeach
    @endforeach
</body>
</html>
