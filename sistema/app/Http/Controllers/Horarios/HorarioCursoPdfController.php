<?php

namespace App\Http\Controllers\Horarios;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Support\HorariosProfesores;
use App\Support\Listados\ListadoCursoExportParams;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
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

        $cursosInput = $request->query('cursos');
        if (($cursosInput === null || $cursosInput === '') && $request->filled('curso')) {
            $cursosInput = (string) (int) $request->query('curso');
        }

        $validated = Validator::make(
            ['cursos' => $cursosInput],
            ['cursos' => ['required', 'string', 'max:8000']],
        )->validate();

        $ctx = schoolCtx();
        $cursosPermitidos = Curso::query()
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->orderBy('orden')
            ->orderBy('cursec')
            ->get(['Id', 'cursec', 'orden', 'idCurPlan', 'idTurnoClase', 'c', 's']);

        if ($cursosPermitidos->isEmpty()) {
            abort(404);
        }

        $allowedById = $cursosPermitidos->keyBy(fn (Curso $c) => (int) $c->Id);
        $cursoIds = ListadoCursoExportParams::resolverIdsCursos(trim((string) $validated['cursos']), $allowedById);

        if ($cursoIds === []) {
            abort(404);
        }

        $activos = HorariosProfesores::turnosActivos();
        $forzado = (int) $request->query('turno', 0);
        $subtitulo = schoolCtx()->nivelNombre().' · Ciclo '.schoolCtx()->terlecAno();

        $paginas = [];
        foreach ($cursoIds as $cursoId) {
            $curso = $allowedById->get($cursoId);
            if ($curso === null) {
                continue;
            }
            $tituloCurso = 'Horario — '.$curso->nombreParaListado();
            $turnos = self::turnosParaCurso($curso, $forzado, $activos);
            foreach ($turnos as $idTurnoClase) {
                $paginas[] = [
                    'titulo' => $tituloCurso,
                    'subtitulo' => $subtitulo,
                    'tituloTurno' => HorariosProfesores::nombreTurnoClase($idTurnoClase),
                    'grilla' => HorariosProfesores::grillaCurso($cursoId, $idTurnoClase),
                ];
            }
        }

        if ($paginas === []) {
            abort(404);
        }

        $tituloPdf = count($cursoIds) === 1
            ? $paginas[0]['titulo']
            : 'Horarios por curso';

        $slug = count($cursoIds) === 1
            ? (Str::slug($paginas[0]['titulo'], '_') ?: 'horario_curso')
            : 'horarios_cursos';

        $pdf = Pdf::loadView('pdf.horario-grid', [
            'pdfHeader' => schoolPdfHeaderData(),
            'titulo' => $tituloPdf,
            'subtitulo' => $subtitulo,
            'paginas' => $paginas,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream($slug.'.pdf');
    }

    /**
     * @param  list<int>  $activos
     * @return list<int>
     */
    private static function turnosParaCurso(Curso $curso, int $forzado, array $activos): array
    {
        if ($forzado > 0 && in_array($forzado, $activos, true)) {
            if (HorariosProfesores::esTurnoClaseDobleJornada($forzado)) {
                [$ma, $ta] = HorariosProfesores::idsTurnoClaseBandasMananaTarde();
                $turnos = array_values(array_filter([$ma, $ta], fn (int $t) => in_array($t, $activos, true)));

                return $turnos !== [] ? $turnos : [$ma, $ta];
            }

            return [$forzado];
        }

        return HorariosProfesores::turnosParaImpresionCurso($curso);
    }
}
