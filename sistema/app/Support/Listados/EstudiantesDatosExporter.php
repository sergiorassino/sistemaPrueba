<?php

namespace App\Support\Listados;

use App\Models\Curso;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Exporta el listado «ESTUDIANTES DATOS» en CSV (compatible con Excel).
 */
final class EstudiantesDatosExporter
{
    /** @var list<string> */
    public const ENCABEZADOS = [
        'Nº',
        'APELLIDO Y NOMBRES',
        'DNI',
        'CURSO y DIVISIÓN',
        'FECHA NACIMIENTO',
        'DOMICILIO',
        'GRUPO SANGUÍNEO',
        'TEL y DNI ADULTO RESPONSABLE*',
    ];

    /**
     * @param  list<int>  $matriculaIds
     * @return Collection<int, object>
     */
    public function filas(array $matriculaIds): Collection
    {
        $matriculaIds = EstudiantesDatosConsulta::filtrarMatriculaIdsEnContexto($matriculaIds);

        return $this->filasOrdenadas($matriculaIds);
    }

    /**
     * @return list<string|int>
     */
    public function filaCsv(object $alumno, int $numero): array
    {
        return [
            $numero,
            EstudiantesDatosConsulta::formatearApellidoNombre(
                (string) ($alumno->apellido ?? ''),
                (string) ($alumno->nombre ?? ''),
            ),
            trim((string) ($alumno->dni ?? '')),
            trim((string) ($alumno->curso_nombre ?? '')),
            EstudiantesDatosConsulta::formatearFechaNacimiento($alumno->fechnaci ?? null),
            (string) ($alumno->domicilio ?? ''),
            trim((string) ($alumno->grupo_sanguineo ?? '')),
            EstudiantesDatosConsulta::formatearTelDniResponsable(
                (string) ($alumno->nombremad ?? ''),
                (string) ($alumno->dnimad ?? ''),
                (string) ($alumno->telemad ?? ''),
            ),
        ];
    }

    /**
     * @param  list<int>  $matriculaIds
     * @return Collection<int, object>
     */
    private function filasOrdenadas(array $matriculaIds): Collection
    {
        if ($matriculaIds === []) {
            return collect();
        }

        $ctx = schoolCtx();
        $cursosPorId = EstudiantesDatosConsulta::cursosEnContexto()->keyBy(fn (Curso $c) => (int) $c->Id);

        $select = [
            'matricula.id as matricula_id',
            'matricula.idCursos as id_curso',
            'legajos.apellido',
            'legajos.nombre',
            'legajos.dni',
            'legajos.fechnaci',
            'legajos.callenum',
            'legajos.barrio',
            'legajos.localidad',
            'legajos.nombremad',
            'legajos.dnimad',
            'legajos.telemad',
        ];

        $columnaGs = EstudiantesDatosConsulta::columnaGrupoSanguineo();
        if ($columnaGs !== null) {
            $select[] = 'legajos.'.$columnaGs.' as grupo_sanguineo';
        }

        $rows = DB::table('matricula')
            ->join('legajos', 'legajos.id', '=', 'matricula.idLegajos')
            ->whereIn('matricula.id', $matriculaIds)
            ->where('matricula.idTerlec', (int) $ctx->idTerlec)
            ->where('matricula.idNivel', (int) $ctx->idNivel)
            ->where('matricula.idCondiciones', 1)
            ->whereNull('matricula.fechaBaja')
            ->select($select)
            ->get()
            ->keyBy(fn (object $row) => (int) $row->matricula_id);

        return collect($matriculaIds)
            ->map(function (int $id) use ($rows, $cursosPorId) {
                $row = $rows->get($id);
                if ($row === null) {
                    return null;
                }

                /** @var Curso|null $curso */
                $curso = $cursosPorId->get((int) $row->id_curso);
                $row->curso_nombre = $curso?->nombreParaListado() ?? '';
                $row->domicilio = EstudiantesDatosConsulta::formatearDomicilio(
                    (string) ($row->callenum ?? ''),
                    (string) ($row->barrio ?? ''),
                    (string) ($row->localidad ?? ''),
                );
                if (! property_exists($row, 'grupo_sanguineo')) {
                    $row->grupo_sanguineo = '';
                }

                return $row;
            })
            ->filter()
            ->values();
    }
}
