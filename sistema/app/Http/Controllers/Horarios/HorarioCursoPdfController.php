<?php

namespace App\Http\Controllers\Horarios;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Support\HorariosProfesores;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class HorarioCursoPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $key = 'horario-curso-pdf:'.(auth()->id() ?? $request->ip());
        if (RateLimiter::tooManyAttempts($key, 30)) {
            abort(429);
        }
        RateLimiter::hit($key, 60);

        $cursoId = (int) $request->query('curso', 0);
        if ($cursoId <= 0) {
            abort(404);
        }

        $ctx = schoolCtx();
        $curso = Curso::query()
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->where('Id', $cursoId)
            ->first(['Id', 'cursec', 'orden', 'idCurPlan', 'idTurnoClase', 'c', 's']);

        if (! $curso) {
            abort(404);
        }

        $activos = HorariosProfesores::turnosActivos();
        $forzado = (int) $request->query('turno', 0);
        if ($forzado > 0 && in_array($forzado, $activos, true)) {
            if (HorariosProfesores::esTurnoClaseDobleJornada($forzado)) {
                [$ma, $ta] = HorariosProfesores::idsTurnoClaseBandasMananaTarde();
                $turnos = array_values(array_filter([$ma, $ta], fn (int $t) => in_array($t, $activos, true)));
                if ($turnos === []) {
                    $turnos = [$ma, $ta];
                }
            } else {
                $turnos = [$forzado];
            }
        } else {
            $turnos = HorariosProfesores::turnosParaImpresionCurso($curso);
        }
        $paginas = [];
        foreach ($turnos as $idTurnoClase) {
            $paginas[] = [
                'tituloTurno' => HorariosProfesores::nombreTurnoClase($idTurnoClase),
                'grilla' => HorariosProfesores::grillaCurso($cursoId, $idTurnoClase),
            ];
        }

        $slug = Str::slug('horario-'.$curso->nombreParaListado(), '_') ?: 'horario_curso';

        $pdf = Pdf::loadView('pdf.horario-grid', [
            'pdfHeader' => schoolPdfHeaderData(),
            'titulo' => 'Horario — '.$curso->nombreParaListado(),
            'subtitulo' => schoolCtx()->nivelNombre().' · Ciclo '.schoolCtx()->terlecAno(),
            'paginas' => $paginas,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream($slug.'.pdf');
    }
}
