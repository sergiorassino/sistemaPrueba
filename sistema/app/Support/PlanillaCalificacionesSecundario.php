<?php

namespace App\Support;

use App\Models\Curso;
use App\Support\Listados\ListadoCursoCondicionFiltro;
use Illuminate\Support\Facades\DB;

/**
 * Planilla de calificaciones por curso y materia (nivel secundario, impresión PDF).
 */
final class PlanillaCalificacionesSecundario
{
    /** Cantidad de filas de referencia para calibrar el alto de celda en una hoja A4 vertical. */
    public const FILAS_REFERENCIA_DISENO = 35;

    /** Factor sobre padding / interlineado de filas de datos (1.44 ≈ +44 % sobre base compacta). */
    public const FACTOR_ALTO_FILA = 1.44;

    /** Puntos extra sumados a las fuentes de la grilla de datos. */
    public const EXTRA_FONT_PT = 1.0;

    /**
     * @return array{
     *     cursoLabel: string,
     *     materiaLabel: string,
     *     profesoresLinea: string,
     *     ano: int|null,
     *     filas: list<array<string, mixed>>
     * }
     */
    public static function build(int $cursoId, int $materiaId): array
    {
        $ctx = schoolCtx();

        $curso = Curso::query()
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->where('Id', $cursoId)
            ->first(['Id', 'cursec', 'orden', 'idCurPlan', 'turno', 'c', 's']);

        if (! $curso) {
            abort(404);
        }

        $materia = DB::table('materias')
            ->where('idNivel', (int) $ctx->idNivel)
            ->where('idTerlec', (int) $ctx->idTerlec)
            ->where('idCursos', $cursoId)
            ->where('id', $materiaId)
            ->first(['id', 'materia']);

        if (! $materia) {
            abort(404);
        }

        $idsCondicionesRegulares = ListadoCursoCondicionFiltro::idCondicionesParaQuery(
            ListadoCursoCondicionFiltro::REGULARES
        );

        $califs = DB::table('calificaciones as c')
            ->join('legajos as l', 'l.id', '=', 'c.idLegajos')
            ->join('matricula as m', 'm.idLegajos', '=', 'l.id')
            ->where('m.idCursos', $cursoId)
            ->where('m.idTerlec', (int) $ctx->idTerlec)
            ->where('m.idNivel', (int) $ctx->idNivel)
            ->whereIn('m.idCondiciones', $idsCondicionesRegulares)
            ->whereNull('m.fechaBaja')
            ->where('c.idTerlec', (int) $ctx->idTerlec)
            ->where('c.idCursos', $cursoId)
            ->where('c.idMaterias', $materiaId)
            ->orderByRaw('COALESCE(c.ord, 9999) asc')
            ->orderBy('l.apellido')
            ->orderBy('l.nombre')
            ->get([
                'c.ord',
                'l.apellido',
                'l.nombre',
                'c.ic01', 'c.ic02', 'c.ic03', 'c.ic04', 'c.ic05', 'c.ic06',
                'c.ic07', 'c.ic08', 'c.ic09', 'c.ic10', 'c.ic11', 'c.ic12',
                'c.ic13', 'c.ic14', 'c.ic15', 'c.ic16', 'c.ic17', 'c.ic18',
                'c.ic19', 'c.ic20', 'c.ic21', 'c.ic22', 'c.ic23', 'c.ic24',
                'c.ic25', 'c.ic26', 'c.ic27', 'c.ic28',
                'c.dic', 'c.feb', 'c.calif',
            ]);

        $filas = [];
        foreach ($califs as $r) {
            $row = [
                'ord' => $r->ord,
                'alumno' => trim(((string) $r->apellido).', '.((string) $r->nombre)),
            ];
            foreach ([
                'ic01', 'ic02', 'ic03', 'ic04', 'ic05', 'ic06', 'ic07', 'ic08', 'ic09', 'ic10',
                'ic11', 'ic12', 'ic13', 'ic14', 'ic15', 'ic16', 'ic17', 'ic18', 'ic19', 'ic20',
                'ic21', 'ic22', 'ic23', 'ic24', 'ic25', 'ic26', 'ic27', 'ic28',
                'dic', 'feb',
            ] as $c) {
                $row[$c] = (string) ($r->{$c} ?? '');
            }
            // Pr. final: solo valor persistido en `calif` (ver docs/05 §7 — no calcular promedios aquí).
            $row['prom'] = trim((string) ($r->calif ?? ''));
            $filas[] = $row;
        }

        return [
            'cursoLabel' => $curso->nombreParaListado(),
            'materiaLabel' => trim((string) ($materia->materia ?? '')),
            'profesoresLinea' => self::profesoresLinea($materiaId),
            'ano' => $ctx->terlecAno(),
            'filas' => $filas,
        ];
    }

    public static function profesoresLinea(int $idMateria): string
    {
        $profes = DB::table('ppc as ppc')
            ->join('profesores as p', 'p.id', '=', 'ppc.idProfesor')
            ->where('ppc.idMateria', $idMateria)
            ->orderBy('p.apellido')
            ->orderBy('p.nombre')
            ->get(['p.apellido', 'p.nombre']);

        if ($profes->isEmpty()) {
            return '—';
        }

        return $profes
            ->map(fn ($p) => trim(mb_strtoupper((string) $p->apellido).' '.mb_strtoupper((string) $p->nombre)))
            ->filter(fn ($s) => $s !== '')
            ->implode(' — ');
    }

    /**
     * Calcula alto de fila y tamaños de fuente para que todos los alumnos entren en una sola hoja A4.
     * Con ~35 filas el alto coincide con el diseño de referencia; con más filas (p. ej. 38) se compacta.
     *
     * @return array{
     *     cantidad: int,
     *     fontDataPt: float,
     *     fontEcPt: float,
     *     fontColPt: float,
     *     espacioFilasPx: float,
     *     paddingCeldaVertPx: float,
     *     lineHeightFila: float
     * }
     */
    public static function metricasLayoutFilas(int $cantidadFilas): array
    {
        $n = max(1, $cantidadFilas);
        $ratio = min(1.0, self::FILAS_REFERENCIA_DISENO / $n);
        $f = self::FACTOR_ALTO_FILA;
        $espacioBase = $n > self::FILAS_REFERENCIA_DISENO ? 0.35 : 0.65;

        $extraFont = self::EXTRA_FONT_PT;

        return [
            'cantidad' => $cantidadFilas,
            'fontDataPt' => round(max(4.4, 5.2 * $ratio) + $extraFont, 1),
            'fontEcPt' => round(max(4.3, 5.1 * $ratio) + $extraFont, 1),
            'fontColPt' => round(max(4.4, 5.0 * $ratio) + $extraFont, 1),
            'espacioFilasPx' => round($espacioBase * $f, 2),
            'paddingCeldaVertPx' => round(1 * $f, 1),
            'lineHeightFila' => round(1 * $f, 2),
        ];
    }
}
