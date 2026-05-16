<?php

namespace App\Support;

/**
 * Coloquios de recuperación (diciembre / febrero) — nivel secundario.
 *
 * Elegibilidad: alumno regular con fila en `calificaciones` para la materia y
 * (algún módulo desaprobado en esa materia o TEA activo en el curso).
 */
final class CalificacionesColoquioSecundario
{
    public const PERIODO_DICIEMBRE = 'dic';

    public const PERIODO_FEBRERO = 'feb';

    /** @return list<string> */
    public static function periodos(): array
    {
        return [self::PERIODO_DICIEMBRE, self::PERIODO_FEBRERO];
    }

    public static function normalizarPeriodo(?string $value): string
    {
        $v = strtolower(trim((string) $value));

        return in_array($v, self::periodos(), true) ? $v : self::PERIODO_DICIEMBRE;
    }

    public static function etiquetaPeriodo(string $periodo): string
    {
        return match (self::normalizarPeriodo($periodo)) {
            self::PERIODO_FEBRERO => 'Febrero',
            default => 'Diciembre',
        };
    }

    /**
     * @param  array<string, mixed>  $row  Fila `calificaciones` (ic01..ic28, tea, etc.)
     */
    public static function tieneAlgunBloqueDesaprobado(array $row, float $notaMinima = PromedioAnualCalificacionesSecundario::DEFAULT_NOTA_MINIMA_APROBACION): bool
    {
        foreach (PromedioAnualCalificacionesSecundario::modulosPlanilla() as $mod) {
            if (PromedioAnualCalificacionesSecundario::bloqueDesaprobado($mod['campos'], $row, $notaMinima)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function motivoElegibilidad(array $row, bool $teaEnCurso): string
    {
        if ($teaEnCurso || ((int) ($row['tea'] ?? 0)) === 1) {
            return 'TEA';
        }

        if (self::tieneAlgunBloqueDesaprobado($row)) {
            return 'Módulos desaprobados';
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function esElegible(array $row, bool $teaEnCurso): bool
    {
        return $teaEnCurso
            || ((int) ($row['tea'] ?? 0)) === 1
            || self::tieneAlgunBloqueDesaprobado($row);
    }

    public static function parseNotaColoquio(mixed $raw): ?float
    {
        if ($raw === null) {
            return null;
        }

        $s = trim((string) $raw);
        if ($s === '') {
            return null;
        }

        $s = str_replace(',', '.', $s);
        if (! is_numeric($s)) {
            return null;
        }

        return (float) $s;
    }

    public static function notaColoquioAprobada(
        mixed $nota,
        float $notaMinima = PromedioAnualCalificacionesSecundario::DEFAULT_NOTA_MINIMA_APROBACION,
    ): bool {
        $n = self::parseNotaColoquio($nota);

        return $n !== null && $n >= $notaMinima;
    }

    /** Valor a persistir en `calificaciones.calif` cuando el coloquio aprueba. */
    public static function califDesdeNotaColoquio(mixed $nota): string
    {
        $n = self::parseNotaColoquio($nota);
        if ($n === null) {
            return '';
        }

        return PromedioAnualCalificacionesSecundario::formatPromedioDisplay((string) $n);
    }
}
