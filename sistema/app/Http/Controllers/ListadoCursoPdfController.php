<?php

namespace App\Http\Controllers;

use App\Models\CampoListadoAlumno;
use App\Models\Curso;
use App\Support\ListadoCursoCondicionFiltro;
use App\Support\ListadoCursoPdfFieldCatalog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ListadoCursoPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $key = 'listado-curso-pdf:'.(auth()->id() ?? $request->ip());
        if (RateLimiter::tooManyAttempts($key, 30)) {
            abort(429, 'Demasiadas solicitudes. Intente nuevamente en breve.');
        }
        RateLimiter::hit($key, 60);

        $cursosInput = $request->query('cursos');
        if (($cursosInput === null || $cursosInput === '') && $request->filled('curso')) {
            $cursosInput = (string) (int) $request->query('curso');
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
        $cursosParam = trim((string) $data['cursos']);
        $camposRaw = isset($data['campos']) && is_string($data['campos']) ? $data['campos'] : '';
        $filtroCondicion = ListadoCursoCondicionFiltro::normalize($data['condicion'] ?? null);

        $ctx = schoolCtx();

        $cursosPermitidos = Curso::query()
            ->with('curplan')
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->orderBy('orden')
            ->orderBy('cursec')
            ->get();

        if ($cursosPermitidos->isEmpty()) {
            abort(404);
        }

        $allowedById = $cursosPermitidos->keyBy(fn (Curso $c) => (int) $c->Id);

        $cursoIds = $this->resolverIdsCursos($cursosParam, $allowedById);
        if ($cursoIds === []) {
            abort(404);
        }

        $pedidos = array_filter(array_map('trim', explode(',', $camposRaw)));
        $campos = ListadoCursoPdfFieldCatalog::normalizeSelection($pedidos);

        $claveCondicionCatalogo = 'condiciones.condicion';
        if (ListadoCursoCondicionFiltro::forzarColumnaCondicionEnPdf($filtroCondicion)) {
            $campos = array_values(array_filter(
                $campos,
                fn (string $c): bool => $c !== $claveCondicionCatalogo
            ));
            $campos[] = $claveCondicionCatalogo;
        }

        $campos = CampoListadoAlumno::aplicarVisibilidadListadoPdf($campos);

        $select = array_merge(
            ['matricula.idCursos as __id_curso'],
            ListadoCursoPdfFieldCatalog::selectExpressions($campos)
        );

        $idsCondiciones = ListadoCursoCondicionFiltro::idCondicionesParaQuery($filtroCondicion);

        $query = DB::table('matricula')
            ->join('legajos', 'legajos.id', '=', 'matricula.idLegajos')
            ->whereIn('matricula.idCursos', $cursoIds)
            ->whereIn('matricula.idCondiciones', $idsCondiciones)
            ->where('matricula.idTerlec', $ctx->idTerlec)
            ->where('matricula.idNivel', $ctx->idNivel)
            ->whereNull('matricula.fechaBaja')
            ->orderBy('matricula.idCursos')
            ->orderBy('legajos.apellido')
            ->orderBy('legajos.nombre');

        if (ListadoCursoPdfFieldCatalog::needsCondicionesJoin($campos)) {
            $query->leftJoin('condiciones', 'condiciones.id', '=', 'matricula.idCondiciones');
        }

        $columnasMeta = ListadoCursoPdfFieldCatalog::columnsForPdf($campos);

        $filas = $query->select($select)->get();
        $porCurso = $filas->groupBy(fn ($r) => (int) $r->__id_curso);

        $bloques = [];
        foreach ($cursosPermitidos as $c) {
            if (! in_array((int) $c->Id, $cursoIds, true)) {
                continue;
            }
            $bloques[] = [
                'cursoLabel' => $c->nombreParaListado(),
                'alumnos' => $porCurso->get((int) $c->Id, collect()),
            ];
        }

        $nivelNombre = $ctx->nivelNombre();
        $ano = $ctx->terlecAno();

        $modoEstudiantesPdf = ListadoCursoCondicionFiltro::etiquetaModoEstudiantesPdf($filtroCondicion);

        $slug = Str::slug('listado-estudiantes-'.($ano ?? ''), '_');
        if ($slug === '') {
            $slug = 'listado_estudiantes';
        }

        $orientation = count($campos) > 7 ? 'landscape' : 'portrait';

        $pdf = Pdf::loadView('pdf.listado-curso-alumnos', [
            'bloques' => $bloques,
            'modoEstudiantesPdf' => $modoEstudiantesPdf,
            'nivelNombre' => $nivelNombre,
            'ano' => $ano,
            'columnasMeta' => $columnasMeta,
            'pdfHeader' => schoolPdfHeaderData(),
        ])->setPaper('a4', $orientation);

        return $pdf->stream($slug.'.pdf');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Curso>  $allowedById
     * @return list<int>
     */
    private function resolverIdsCursos(string $cursosParam, Collection $allowedById): array
    {
        $parsed = collect(explode(',', $cursosParam))
            ->map(fn ($v) => (int) trim((string) $v))
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $out = [];
        foreach ($parsed as $id) {
            if ($allowedById->has($id) && ! in_array($id, $out, true)) {
                $out[] = $id;
            }
        }

        if (count($out) > 200) {
            return [];
        }

        return $out;
    }
}
