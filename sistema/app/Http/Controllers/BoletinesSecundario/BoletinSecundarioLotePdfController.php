<?php

namespace App\Http\Controllers\BoletinesSecundario;

use App\Http\Controllers\Controller;
use App\Support\BoletinSecundarioLoteParams;
use App\Support\ConsultaCalificacionesAlumno;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Informes de progreso escolar en un solo PDF (varias matrículas del mismo curso).
 */
class BoletinSecundarioLotePdfController extends Controller
{
    public function __invoke(Request $request)
    {
        @ini_set('memory_limit', '512M');
        set_time_limit(180);

        $uid = (string) (auth()->id() ?? '');
        $key = 'staff-boletin-secundario-lote-pdf:'.$uid.':'.($request->ip() ?? '');
        if (RateLimiter::tooManyAttempts($key, 15)) {
            abort(429, 'Demasiadas solicitudes. Intente nuevamente en breve.');
        }
        RateLimiter::hit($key, 60);

        $validated = Validator::make($request->query(), [
            'curso' => ['required', 'integer', 'min:1'],
            'matriculas' => ['required', 'string', 'max:4000'],
        ])->validate();

        $cursoId = (int) $validated['curso'];
        $ids = BoletinSecundarioLoteParams::resolverIdsMatriculas(
            trim((string) $validated['matriculas']),
            $cursoId,
        );

        if ($ids === []) {
            abort(404);
        }

        $consultas = [];
        foreach ($ids as $idMatricula) {
            $data = ConsultaCalificacionesAlumno::buildForMatriculaEnContextoEscolar($idMatricula);
            if ($data['ok']) {
                $consultas[] = $data;
            }
        }

        if ($consultas === []) {
            abort(404);
        }

        $cantidad = count($consultas);
        if ($cantidad === 1) {
            $slugBase = trim((string) ($consultas[0]['alumnoLinea'] ?? ''));
            $slug = Str::slug('informe-progreso-escolar-'.$slugBase, '_');
        } else {
            $slug = Str::slug('informes-progreso-escolar-'.$cantidad.'-alumnos', '_');
        }
        if ($slug === '') {
            $slug = 'informes_progreso_escolar';
        }

        $pdf = Pdf::loadView('pdf.boletines-secundario-lote', [
            'consultas' => $consultas,
            'pdfHeader' => schoolPdfHeaderData(),
            'tituloDocumento' => 'Informe de Progreso Escolar',
        ])->setPaper('a4', 'landscape');

        $response = $pdf->stream($slug.'.pdf');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
