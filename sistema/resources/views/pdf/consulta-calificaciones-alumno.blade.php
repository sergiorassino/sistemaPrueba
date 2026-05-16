{{-- Boletín de calificaciones (secundario): misma plantilla para autogestión del estudiante y para docentes/secretaría. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    @include('pdf.partials.consulta-calificaciones-alumno-styles')
</head>
<body>
@include('pdf.partials.consulta-calificaciones-alumno-sheet', [
    'consulta' => $consulta,
    'pdfHeader' => $pdfHeader ?? null,
    'tituloDocumento' => $tituloDocumento ?? 'Consulta de Calificaciones',
    'mostrarMarcaAgua' => $mostrarMarcaAgua ?? true,
    'mostrarFirmas' => $mostrarFirmas ?? false,
])
</body>
</html>
