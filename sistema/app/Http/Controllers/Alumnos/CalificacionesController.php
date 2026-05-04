<?php

namespace App\Http\Controllers\Alumnos;

use App\Http\Controllers\Controller;
use App\Support\ConsultaCalificacionesAlumno;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Única salida del módulo: boletín en PDF (A4 apaisado), sin pantalla duplicada.
 */
class CalificacionesController extends Controller
{
    public function __invoke(Request $request)
    {
        $key = 'alumnos-consulta-calificaciones-pdf:'.(auth('alumno')->id() ?? $request->ip());
        if (RateLimiter::tooManyAttempts($key, 20)) {
            abort(429, 'Demasiadas solicitudes. Intente nuevamente en breve.');
        }
        RateLimiter::hit($key, 60);

        $data = ConsultaCalificacionesAlumno::build();
        if (! $data['ok']) {
            abort(404, $data['error'] ?? 'No disponible.');
        }

        $slugBase = trim((string) ($data['alumnoLinea'] ?? ''));
        $slug = Str::slug('consulta-calificaciones-'.$slugBase, '_');
        if ($slug === '') {
            $slug = 'consulta_calificaciones';
        }

        $pdf = Pdf::loadView('pdf.consulta-calificaciones-alumno', [
            'consulta' => $data,
            'pdfHeader' => studentPdfHeaderData(),
        ])->setPaper('a4', 'landscape');

        $response = $pdf->stream($slug.'.pdf');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
