<?php

namespace App\Support;

use App\Models\Matricula;
use Illuminate\Support\Facades\DB;

/**
 * Arma el dataset de consulta de calificaciones (nivel secundario, boletín PDF compartido).
 *
 * - Autogestión: {@see self::build()} con contexto alumno.
 * - Docentes/secretaría: {@see self::buildForMatriculaEnContextoEscolar()} con matrícula acotada al {@see schoolCtx()}.
 */
final class ConsultaCalificacionesAlumno
{
    /**
     * @return array{
     *     ok: bool,
     *     error?: string,
     *     matricula?: Matricula,
     *     anoLectivo: ?int,
     *     alumnoLinea: string,
     *     dni: string,
     *     cursoLabel: string,
     *     rows: list<object>,
     *     disciplina: array{amonestaciones: int, apercibimientos_orales: int, apercibimientos_escritos: int}
     * }
     */
    public static function build(): array
    {
        $ctx = studentCtx();
        if (! $ctx->isValid()) {
            return ['ok' => false, 'error' => 'Sesión inválida.', 'anoLectivo' => null, 'alumnoLinea' => '', 'dni' => '', 'cursoLabel' => '', 'rows' => [], 'disciplina' => self::disciplinaVacía()];
        }

        $idLegajo = (int) $ctx->idLegajo;
        $idNivel = (int) $ctx->idNivel;
        $idTerlec = (int) $ctx->idTerlec;

        /** @var Matricula|null $matricula */
        $matricula = Matricula::query()
            ->with(['legajo', 'curso.curplan', 'terlec'])
            ->where('idLegajos', $idLegajo)
            ->where('idNivel', $idNivel)
            ->where('idTerlec', $idTerlec)
            ->orderByDesc('id')
            ->first();

        if (! $matricula) {
            return [
                'ok' => false,
                'error' => 'No hay matrícula registrada para este ciclo lectivo. Contacte a secretaría.',
                'anoLectivo' => $ctx->terlecAno(),
                'alumnoLinea' => '',
                'dni' => '',
                'cursoLabel' => '',
                'rows' => [],
                'disciplina' => self::disciplinaVacía(),
            ];
        }

        return self::datasetDesdeMatricula($matricula);
    }

    /**
     * Misma salida que {@see build()} para una matrícula concreta, acotada al nivel y ciclo lectivo del contexto escolar actual.
     */
    public static function buildForMatriculaEnContextoEscolar(int $idMatricula): array
    {
        $ctx = schoolCtx();
        if (! $ctx->isValid()) {
            return ['ok' => false, 'error' => 'Sesión inválida.', 'anoLectivo' => null, 'alumnoLinea' => '', 'dni' => '', 'cursoLabel' => '', 'rows' => [], 'disciplina' => self::disciplinaVacía()];
        }

        if ($idMatricula <= 0) {
            return ['ok' => false, 'error' => 'Solicitud inválida.', 'anoLectivo' => $ctx->terlecAno(), 'alumnoLinea' => '', 'dni' => '', 'cursoLabel' => '', 'rows' => [], 'disciplina' => self::disciplinaVacía()];
        }

        /** @var Matricula|null $matricula */
        $matricula = Matricula::query()
            ->with(['legajo', 'curso.curplan', 'terlec'])
            ->where('id', $idMatricula)
            ->where('idNivel', (int) $ctx->idNivel)
            ->where('idTerlec', (int) $ctx->idTerlec)
            ->first();

        if (! $matricula) {
            return [
                'ok' => false,
                'error' => 'No se encontró la matrícula en este nivel y ciclo lectivo.',
                'anoLectivo' => $ctx->terlecAno(),
                'alumnoLinea' => '',
                'dni' => '',
                'cursoLabel' => '',
                'rows' => [],
                'disciplina' => self::disciplinaVacía(),
            ];
        }

        return self::datasetDesdeMatricula($matricula);
    }

    /**
     * @return array{
     *     ok: true,
     *     matricula: Matricula,
     *     anoLectivo: ?int,
     *     alumnoLinea: string,
     *     dni: string,
     *     cursoLabel: string,
     *     rows: list<object>,
     *     disciplina: array{amonestaciones: int, apercibimientos_orales: int, apercibimientos_escritos: int}
     * }
     */
    private static function datasetDesdeMatricula(Matricula $matricula): array
    {
        $legajo = $matricula->legajo;
        $alumnoLinea = trim(((string) ($legajo?->apellido ?? '')).' '.((string) ($legajo?->nombre ?? '')));
        $dni = trim((string) ($legajo?->dni ?? ''));
        $cursoLabel = $matricula->curso?->nombreParaListado() ?? '';
        $anoLectivo = $matricula->terlec?->ano;

        $idMat = (int) $matricula->id;
        $idLegajo = (int) $matricula->idLegajos;
        $idCurso = (int) $matricula->idCursos;
        $idTerlec = (int) $matricula->idTerlec;

        $cols = [
            'c.ic01', 'c.ic02', 'c.ic03', 'c.ic04', 'c.ic05', 'c.ic06', 'c.ic07', 'c.ic08', 'c.ic09', 'c.ic10',
            'c.ic11', 'c.ic12', 'c.ic13', 'c.ic14', 'c.ic15', 'c.ic16', 'c.ic17', 'c.ic18', 'c.ic19', 'c.ic20',
            'c.ic21', 'c.ic22', 'c.ic23', 'c.ic24', 'c.ic25', 'c.ic26', 'c.ic27', 'c.ic28',
            'c.dic', 'c.feb', 'c.calif',
        ];

        $rows = DB::table('calificaciones as c')
            ->join('materias as m', function ($join) {
                $join->on('m.id', '=', 'c.idMaterias')
                    ->on('m.idTerlec', '=', 'c.idTerlec');
            })
            ->where('c.idTerlec', $idTerlec)
            ->where(function ($q) use ($idMat, $idLegajo, $idCurso) {
                $q->where('c.idMatricula', $idMat)
                    ->orWhere(function ($q2) use ($idLegajo, $idCurso) {
                        $q2->whereNull('c.idMatricula')
                            ->where('c.idLegajos', $idLegajo)
                            ->where('c.idCursos', $idCurso);
                    });
            })
            ->orderByRaw('COALESCE(c.ord, 9999) asc')
            ->orderBy('m.materia')
            ->get(array_merge(['m.materia as espacio_curricular'], $cols));

        return [
            'ok' => true,
            'matricula' => $matricula,
            'anoLectivo' => $anoLectivo !== null ? (int) $anoLectivo : null,
            'alumnoLinea' => $alumnoLinea,
            'dni' => $dni,
            'cursoLabel' => $cursoLabel,
            'rows' => $rows->all(),
            'disciplina' => self::resumenDisciplina($idMat),
        ];
    }

    /**
     * @return array{amonestaciones: int, apercibimientos_orales: int, apercibimientos_escritos: int}
     */
    private static function disciplinaVacía(): array
    {
        return [
            'amonestaciones' => 0,
            'apercibimientos_orales' => 0,
            'apercibimientos_escritos' => 0,
        ];
    }

    /**
     * @return array{amonestaciones: int, apercibimientos_orales: int, apercibimientos_escritos: int}
     */
    private static function resumenDisciplina(int $idMatricula): array
    {
        $out = self::disciplinaVacía();
        if ($idMatricula <= 0) {
            return $out;
        }

        $items = DB::table('sanciones')
            ->leftJoin('sanciontipo', 'sanciontipo.id', '=', 'sanciones.idTipoSancion')
            ->where('sanciones.idMatricula', $idMatricula)
            ->selectRaw('LOWER(COALESCE(sanciontipo.tipo, "")) as tipo_l')
            ->selectRaw('COALESCE(sanciones.cantidad, 1) as cant')
            ->get();

        foreach ($items as $r) {
            $t = (string) ($r->tipo_l ?? '');
            $c = (int) ($r->cant ?? 1);
            if ($c < 1) {
                $c = 1;
            }
            if (str_contains($t, 'amonest')) {
                $out['amonestaciones'] += $c;
            } elseif (str_contains($t, 'apercib')) {
                if (str_contains($t, 'escrit')) {
                    $out['apercibimientos_escritos'] += $c;
                } elseif (str_contains($t, 'oral') || str_contains($t, 'verbal')) {
                    $out['apercibimientos_orales'] += $c;
                } else {
                    $out['apercibimientos_orales'] += $c;
                }
            }
        }

        return $out;
    }
}
