<?php

namespace App\Http\Controllers\CalificacionesSecundario;

use App\Http\Controllers\Controller;
use App\Support\ConsultaCalificacionesAlumno;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Boletín de consulta de calificaciones (secundario) para docentes/secretaría.
 * Comparte la vista PDF con la autogestión del estudiante.
 */
class ConsultaCalificacionesSecundarioPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $idMatricula = (int) $request->query('matricula', 0);
        $uid = (string) (auth()->id() ?? '');
        $key = 'staff-consulta-calificaciones-sec-pdf:'.$uid.':'.($request->ip() ?? '');
        if (RateLimiter::tooManyAttempts($key, 40)) {
            abort(429, 'Demasiadas solicitudes. Intente nuevamente en breve.');
        }
        RateLimiter::hit($key, 60);

        $data = ConsultaCalificacionesAlumno::buildForMatriculaEnContextoEscolar($idMatricula);
        if (! $data['ok']) {
            abort(404, $data['error'] ?? 'No disponible.');
        }

        $slugBase = trim((string) ($data['alumnoLinea'] ?? ''));
        $slug = Str::slug('consulta-calificaciones-secundario-'.$slugBase, '_');
        if ($slug === '') {
            $slug = 'consulta_calificaciones_secundario';
        }

        $pdf = Pdf::loadView('pdf.consulta-calificaciones-alumno', [
            'consulta' => $data,
            'pdfHeader' => schoolPdfHeaderData(),
        ])->setPaper('a4', 'landscape');

        $response = $pdf->stream($slug.'.pdf');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
