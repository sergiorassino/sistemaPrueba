<?php

namespace App\Http\Controllers\CalificacionesSecundario;

use App\Http\Controllers\Controller;
use App\Support\PlanillaCalificacionesSecundario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class PlanillaCalificacionesPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $key = 'planilla-calificaciones-pdf:'.(auth()->id() ?? $request->ip());
        if (RateLimiter::tooManyAttempts($key, 30)) {
            abort(429, 'Demasiadas solicitudes. Intente nuevamente en breve.');
        }
        RateLimiter::hit($key, 60);

        $validated = Validator::make($request->query(), [
            'curso' => ['required', 'integer', 'min:1'],
            'materia' => ['required', 'integer', 'min:1'],
        ])->validate();

        $cursoId = (int) $validated['curso'];
        $materiaId = (int) $validated['materia'];

        $data = PlanillaCalificacionesSecundario::build($cursoId, $materiaId);
        $filas = $data['filas'];
        $layoutFilas = PlanillaCalificacionesSecundario::metricasLayoutFilas(count($filas));

        $slug = Str::slug(
            'planilla-calificaciones-'.($data['materiaLabel'] ?? '').'-'.($data['cursoLabel'] ?? ''),
            '_'
        );
        if ($slug === '') {
            $slug = 'planilla_calificaciones';
        }

        $pdf = Pdf::loadView('pdf.planilla-calificaciones', [
            'pdfHeader' => schoolPdfHeaderData(),
            'filas' => $filas,
            'layoutFilas' => $layoutFilas,
            'cursoLabel' => $data['cursoLabel'],
            'materiaLabel' => $data['materiaLabel'],
            'profesoresLinea' => $data['profesoresLinea'],
            'ano' => $data['ano'],
        ])->setPaper('a4', 'portrait');

        $response = $pdf->stream($slug.'.pdf');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
