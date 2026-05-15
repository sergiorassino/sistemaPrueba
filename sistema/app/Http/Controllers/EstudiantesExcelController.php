<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use App\Support\Listados\EstudiantesExcelExporter;
use App\Support\Listados\EstudiantesExcelExportSpec;
use App\Support\Listados\ListadoCursoCondicionFiltro;
use App\Support\Listados\ListadoCursoExportParams;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EstudiantesExcelController extends Controller
{
    public function __invoke(Request $request, EstudiantesExcelExporter $exporter)
    {
        $key = 'estudiantes-excel:'.(auth()->id() ?? $request->ip());
        if (RateLimiter::tooManyAttempts($key, 10)) {
            abort(429, 'Demasiadas solicitudes. Intente nuevamente en breve.');
        }
        RateLimiter::hit($key, 120);

        $ctx = schoolCtx();
        $idNivel = (int) $ctx->idNivel;
        $idTerlec = (int) $ctx->idTerlec;

        if ($idNivel <= 0 || $idTerlec <= 0) {
            abort(403);
        }

        $spec = $this->resolverSpec($request, $idNivel, $idTerlec);

        $resultado = $exporter->build($idNivel, $idTerlec, $ctx->terlecAno(), $spec);
        $tempPath = $exporter->guardarEnTemporal($resultado['spreadsheet']);

        return response()->download($tempPath, $resultado['filename'], [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function resolverSpec(Request $request, int $idNivel, int $idTerlec): EstudiantesExcelExportSpec
    {
        $cursosInput = $request->query('cursos');
        if ($cursosInput === null || $cursosInput === '') {
            return new EstudiantesExcelExportSpec;
        }

        $validated = Validator::make(
            [
                'cursos' => $cursosInput,
                'campos' => $request->query('campos'),
                'condicion' => $request->query('condicion'),
            ],
            [
                'cursos' => ['required', 'string', 'max:8000'],
                'campos' => ['nullable', 'string', 'max:12000'],
                'condicion' => ['nullable', 'string', Rule::in(ListadoCursoCondicionFiltro::keys())],
            ]
        );

        if ($validated->fails()) {
            abort(404);
        }

        $data = $validated->validated();
        $filtroCondicion = ListadoCursoCondicionFiltro::normalize($data['condicion'] ?? null);

        $cursosPermitidos = Curso::query()
            ->where('idNivel', $idNivel)
            ->where('idTerlec', $idTerlec)
            ->get();

        if ($cursosPermitidos->isEmpty()) {
            abort(404);
        }

        $allowedById = $cursosPermitidos->keyBy(fn (Curso $c) => (int) $c->Id);
        $cursoIds = ListadoCursoExportParams::resolverIdsCursos(trim((string) $data['cursos']), $allowedById);

        if ($cursoIds === []) {
            abort(404);
        }

        $camposRaw = isset($data['campos']) && is_string($data['campos']) ? $data['campos'] : '';
        $pedidos = array_filter(array_map('trim', explode(',', $camposRaw)));
        $campos = ListadoCursoExportParams::normalizarCamposSeleccion($pedidos, $filtroCondicion);

        return new EstudiantesExcelExportSpec(
            cursoIds: $cursoIds,
            campoKeys: $campos,
            filtroCondicion: $filtroCondicion,
        );
    }
}
