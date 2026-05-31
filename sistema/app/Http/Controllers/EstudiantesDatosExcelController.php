<?php

namespace App\Http\Controllers;

use App\Support\Listados\EstudiantesDatosConsulta;
use App\Support\Listados\EstudiantesDatosExporter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EstudiantesDatosExcelController extends Controller
{
    public function __invoke(Request $request, EstudiantesDatosExporter $exporter): StreamedResponse
    {
        $key = 'estudiantes-datos-csv:'.(auth()->id() ?? $request->ip());
        if (RateLimiter::tooManyAttempts($key, 10)) {
            abort(429, 'Demasiadas solicitudes. Intente nuevamente en breve.');
        }
        RateLimiter::hit($key, 120);

        $ctx = schoolCtx();
        if ((int) $ctx->idNivel <= 0 || (int) $ctx->idTerlec <= 0) {
            abort(403);
        }

        $validated = Validator::make(
            ['matriculas' => $request->query('matriculas')],
            ['matriculas' => ['required', 'string', 'max:12000']],
        )->validate();

        $matriculaIds = collect(explode(',', trim((string) $validated['matriculas'])))
            ->map(fn ($v) => (int) trim((string) $v))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($matriculaIds === []) {
            abort(404);
        }

        $filas = $exporter->filas($matriculaIds);
        if ($filas->isEmpty()) {
            abort(404);
        }

        $nombre = EstudiantesDatosConsulta::nombreArchivo();

        return response()->streamDownload(function () use ($exporter, $filas) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            fprintf($out, "\xEF\xBB\xBF");
            fputcsv($out, EstudiantesDatosExporter::ENCABEZADOS, ';');

            $numero = 1;
            foreach ($filas as $alumno) {
                fputcsv($out, $exporter->filaCsv($alumno, $numero), ';');
                $numero++;
            }

            fclose($out);
        }, $nombre, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
