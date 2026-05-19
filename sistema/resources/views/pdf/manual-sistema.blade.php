<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $meta['titulo'] }}</title>
    <style>
        @page { margin: 14mm 12mm 16mm 12mm; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            color: #222;
            line-height: 1.4;
        }
        h1 {
            font-size: 17pt;
            color: #40848D;
            margin: 0 0 4px 0;
            font-weight: 700;
        }
        h2 {
            font-size: 11pt;
            color: #333;
            margin: 16px 0 5px 0;
            padding-bottom: 3px;
            border-bottom: 1.2pt solid #40848D;
            page-break-after: avoid;
        }
        h3 {
            font-size: 9.5pt;
            color: #40848D;
            margin: 8px 0 3px 0;
            page-break-after: avoid;
        }
        h4 {
            font-size: 9pt;
            color: #333;
            margin: 0 0 2px 0;
            font-weight: 700;
        }
        .portada {
            text-align: center;
            padding: 28px 16px 24px 16px;
            margin-bottom: 14px;
            border: 1pt solid #C1D7DA;
            background: #f8fbfc;
        }
        .portada .sub { font-size: 11pt; color: #444; margin: 4px 0 10px 0; }
        .portada .meta { font-size: 8.5pt; color: #666; }
        .intro p { margin: 0 0 6px 0; text-align: justify; }
        .intro ul, .intro ol { margin: 4px 0 8px 0; padding-left: 18px; }
        .intro li { margin-bottom: 3px; }
        .seccion-desc {
            font-size: 8.5pt;
            color: #555;
            margin: 0 0 10px 0;
        }
        .modulo {
            margin-bottom: 10px;
            padding: 8px 10px 8px 10px;
            border: 0.75pt solid #C1D7DA;
            border-left: 3.5pt solid #40848D;
            page-break-inside: avoid;
        }
        .modulo-nombre {
            font-weight: 700;
            font-size: 10pt;
            color: #333;
            margin: 0 0 5px 0;
        }
        .modulo-meta {
            font-size: 8pt;
            color: #555;
            margin: 0 0 5px 0;
        }
        .modulo-meta strong { color: #40848D; }
        .modulo-objetivo {
            margin: 0 0 6px 0;
            text-align: justify;
        }
        .modulo-pasos-title,
        .modulo-consejos-title {
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #40848D;
            margin: 6px 0 3px 0;
        }
        .modulo ol, .modulo ul {
            margin: 0 0 4px 0;
            padding-left: 16px;
        }
        .modulo li {
            margin-bottom: 3px;
            text-align: justify;
        }
        .modulo-permiso {
            margin: 6px 0 0 0;
            font-size: 7.5pt;
            color: #666;
            font-style: italic;
        }
        .pie {
            margin-top: 16px;
            padding-top: 6px;
            border-top: 0.75pt solid #C1D7DA;
            font-size: 7.5pt;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="portada">
    <h1>{{ $meta['titulo'] }}</h1>
    <p class="sub">{{ $meta['subtitulo'] }}</p>
    @if(!empty($colegio))
        <p class="sub" style="font-weight:700;color:#222;">{{ $colegio }}</p>
    @endif
    <p class="meta">Versión {{ $meta['version'] }} · {{ $meta['generado'] }}</p>
</div>

<div class="intro">
    <h2>Cómo usar esta guía</h2>
    <p>{{ $intro['resumen'] }}</p>

    <h3>Antes de empezar</h3>
    <ol>
        @foreach($intro['antes_de_empezar'] as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ol>

    <h3>Portales de acceso</h3>
    @foreach($intro['portales'] as $portal)
        <p><strong>{{ $portal['titulo'] }}:</strong> {{ $portal['texto'] }}</p>
    @endforeach

    <h3>Consejos generales</h3>
    <ul>
        @foreach($intro['consejos_generales'] as $consejo)
            <li>{{ $consejo }}</li>
        @endforeach
    </ul>
</div>

@foreach($secciones as $seccion)
    <h2>{{ $seccion['seccion'] }}</h2>
    <p class="seccion-desc">{{ $seccion['descripcion'] }}</p>
    @foreach($seccion['modulos'] as $modulo)
        <div class="modulo">
            <p class="modulo-nombre">{{ $modulo['nombre'] }}</p>
            <p class="modulo-meta"><strong>Dónde está:</strong> {{ $modulo['menu'] }}</p>
            <p class="modulo-objetivo">{{ $modulo['objetivo'] }}</p>

            @if(!empty($modulo['pasos']))
                <p class="modulo-pasos-title">Pasos para usarlo</p>
                <ol>
                    @foreach($modulo['pasos'] as $paso)
                        <li>{{ $paso }}</li>
                    @endforeach
                </ol>
            @endif

            @if(!empty($modulo['consejos']))
                <p class="modulo-consejos-title">Consejos</p>
                <ul>
                    @foreach($modulo['consejos'] as $consejo)
                        <li>{{ $consejo }}</li>
                    @endforeach
                </ul>
            @endif

            @if(!empty($modulo['permiso']))
                <p class="modulo-permiso">Quién puede usarlo: {{ $modulo['permiso'] }}</p>
            @endif
        </div>
    @endforeach
@endforeach

<div class="pie">
    Manual generado el {{ $meta['generado'] }}. Si un ítem no aparece en su menú lateral, su usuario no tiene el permiso correspondiente.
    Para actualizar el contenido: menú «Manual del sistema» o comando php artisan se:manual-pdf
</div>

</body>
</html>
