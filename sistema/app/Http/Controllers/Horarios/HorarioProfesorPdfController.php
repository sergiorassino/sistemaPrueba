<?php

namespace App\Http\Controllers\Horarios;

use App\Http\Controllers\Controller;
use App\Support\HorariosProfesores;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class HorarioProfesorPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $key = 'horario-profesor-pdf:'.(auth()->id() ?? $request->ip());
        if (RateLimiter::tooManyAttempts($key, 30)) {
            abort(429);
        }
        RateLimiter::hit($key, 60);

        $profesorId = (int) $request->query('profesor', 0);
        if ($profesorId <= 0) {
            abort(404);
        }

        $prof = DB::table('profesores')
            ->where('id', $profesorId)
            ->first(['id', 'apellido', 'nombre']);

        if (! $prof) {
            abort(404);
        }

        if (! HorariosProfesores::asignacionesProfesor($profesorId)->isNotEmpty()) {
            abort(404);
        }

        $nombre = trim(((string) $prof->apellido).', '.((string) $prof->nombre));
        $turnos = HorariosProfesores::turnosParaImpresionProfesor($profesorId);

        $paginas = [];
        foreach ($turnos as $idTurnoClase) {
            $paginas[] = [
                'tituloTurno' => HorariosProfesores::nombreTurnoClase($idTurnoClase),
                'grilla' => HorariosProfesores::grillaProfesorParaImpresion($profesorId, $idTurnoClase),
            ];
        }

        $slug = Str::slug('horario-'.$nombre, '_') ?: 'horario_profesor';

        $pdf = Pdf::loadView('pdf.horario-grid', [
            'pdfHeader' => schoolPdfHeaderData(),
            'titulo' => 'Horario docente — '.$nombre,
            'subtitulo' => schoolCtx()->nivelNombre().' · Ciclo '.schoolCtx()->terlecAno(),
            'paginas' => $paginas,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream($slug.'.pdf');
    }
}
