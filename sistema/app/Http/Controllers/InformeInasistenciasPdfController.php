<?php



namespace App\Http\Controllers;



use App\Models\Matricula;

use App\Support\InformeInasistencias;

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\RateLimiter;

use Illuminate\Support\Str;



class InformeInasistenciasPdfController extends Controller

{

    public function __invoke(Request $request, int $idMatricula)

    {

        $key = 'informe-inasistencias-pdf:'.(auth()->id() ?? $request->ip());

        if (RateLimiter::tooManyAttempts($key, 30)) {

            abort(429, 'Demasiadas solicitudes. Intente nuevamente en breve.');

        }

        RateLimiter::hit($key, 60);



        $ctx = schoolCtx();



        /** @var Matricula $matricula */

        $matricula = Matricula::query()

            ->with(['legajo', 'curso'])

            ->where('idNivel', $ctx->idNivel)

            ->where('idTerlec', $ctx->idTerlec)

            ->findOrFail($idMatricula);



        $idTipo = InformeInasistencias::tipoFiltroValido((int) $request->query('tipo', 0) ?: null);
        $desde = trim((string) $request->query('desde', ''));
        $hasta = trim((string) $request->query('hasta', ''));

        $datos = InformeInasistencias::datosPdf(
            $matricula,
            $idTipo,
            InformeInasistencias::anoLectivo(),
            $desde !== '' ? $desde : null,
            $hasta !== '' ? $hasta : null,
        );



        $slug = Str::slug('informe-inasistencias-'.$datos['alumnoLinea'], '_');

        if ($slug === '') {

            $slug = 'informe_inasistencias';

        }



        $pdf = Pdf::loadView('pdf.informe-inasistencias', [

            ...$datos,

            'pdfHeader' => schoolPdfHeaderData(),

        ])->setPaper('a4', 'portrait');



        return $pdf->stream($slug.'.pdf');

    }

}

