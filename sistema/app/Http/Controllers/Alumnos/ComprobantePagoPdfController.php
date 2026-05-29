<?php

namespace App\Http\Controllers\Alumnos;

use App\Http\Controllers\Controller;
use App\Support\Alumnos\ArancelesEscolares;
use App\Support\Alumnos\ComprobantePagoDatos;
use App\Support\Alumnos\ComprobantePagoTcpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Comprobante de pago de aranceles en PDF para el alumno/familia en sesión.
 */
class ComprobantePagoPdfController extends Controller
{
    public function __invoke(Request $request, int $id)
    {
        abort_unless(tenantAutogestionArancelesEscolaresHabilitada(), 404);

        $key = 'alumnos-comprobante-pago-pdf:'.(auth('alumno')->id() ?? $request->ip());
        if (RateLimiter::tooManyAttempts($key, 30)) {
            abort(429, 'Demasiadas solicitudes. Intente nuevamente en breve.');
        }
        RateLimiter::hit($key, 60);

        $registro = ArancelesEscolares::cuotaPendienteParaAutogestion($id);
        if ($registro === null) {
            return response()->view('errors.alumno-pdf', [
                'mensaje' => 'No se encontró la cuota pendiente solicitada o ya no tiene saldo a abonar.',
            ], 422);
        }

        if (ArancelesEscolares::cuotaVencidaParaReimpresion($registro)) {
            return redirect()
                ->route('alumnos.aranceles-escolares')
                ->with('aranceles_cuota_vencida', ArancelesEscolares::mensajeCuotaVencidaReimpresion());
        }

        $datos = ComprobantePagoDatos::paraAutogestion($id);
        if ($datos === null) {
            return response()->view('errors.alumno-pdf', [
                'mensaje' => 'No se encontró la cuota pendiente solicitada o ya no tiene saldo a abonar.',
            ], 422);
        }

        $slug = Str::slug(
            'comprobante-pago-'.trim($datos['apellido'].'-'.$datos['nombre'].'-'.$id),
            '_',
        );
        if ($slug === '') {
            $slug = 'comprobante_pago_'.$id;
        }

        $pdf = ComprobantePagoTcpdf::generar($datos);

        return ComprobantePagoTcpdf::respuestaHttp($pdf, $slug.'.pdf');
    }
}
