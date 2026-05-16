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
        /* Marca de agua: debe ir DESPUÃ‰S de la tabla en el HTML y en posiciÃ³n absoluta
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
         * top ~54% baja un poco el centro respecto al 50% geomÃ©trico para acercarlo al bloque de materias (tbody).
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
        .meta strong.meta-alumno { font-size: 8.5pt; }
        .meta strong.meta-curso { font-size: 6.5pt; font-weight: 700; }

        /* Tabla exterior: separaciÃ³n horizontal entre bloques redondeados */
        table.outer {
            position: relative;
            z-index: 0;
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            /* SeparaciÃ³n moderada: mucho border-spacing hace que Dompdf reparta mal los % */
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
        /* TÃ­tulo del bloque (Eval. n / JIS n) */
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
        /* Cuerpo: lÃ­neas verticales entre subceldas */
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
        /* Pie: texto (previas / Ã­tems) a la izquierda; firmas a la derecha en la misma franja vertical */
        .pie-footer { width: 100%; margin-top: 1.5mm; }
        .pie-footer-tabla {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .pie-footer-tabla td { vertical-align: bottom; padding: 0; border: 0; }
        .pie-texto { width: 38%; text-align: left; padding-right: 3mm; }
        .pie-firmas-cel { width: 62%; }
        .pie-footer--solo-firmas .pie-firmas-cel { width: 100%; }
        .disc--con-firmas { margin-top: 1.5mm; }
        .adeu--con-firmas { margin-top: 1mm; }
        .firma-bloque { margin: 0; padding: 0; }
        .pie-firmas-cel .firma-bloque { width: 100%; }
        .pie-footer--solo-firmas .firma-bloque {
            padding-left: 50mm;
            padding-right: 10mm;
        }
        .firma-tabla {
            border-collapse: collapse;
            width: auto;
            table-layout: auto;
        }
        .firma-sep {
            width: 40mm;
            min-width: 40mm;
            padding: 0;
            border: 0;
            font-size: 1pt;
            line-height: 1;
        }
        .pie-firmas-cel .firma-tabla {
            margin-left: auto;
            margin-right: 40mm;
        }
        .pie-footer--solo-firmas .firma-tabla {
            width: 100%;
            margin-left: 0;
            margin-right: 0;
        }
        .firma-tabla td {
            width: auto;
            vertical-align: top;
            padding: 0;
            border: 0;
        }
        .firma-linea {
            border-bottom: 0.55pt dotted #333;
            height: 7mm;
            width: 65mm;
            min-width: 65mm;
            display: block;
            margin: 0 auto;
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
