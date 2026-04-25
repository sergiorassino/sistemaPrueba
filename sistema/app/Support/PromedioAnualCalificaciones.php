<?php

namespace App\Support;

/**
 * Cálculo reutilizable del promedio anual según módulos (Eval 1..8 y JIS 1..2).
 *
 * Regla (pedida):
 * - Por cada módulo se toma la MAYOR nota entre sus instancias (Eval: N/R1/R2, JIS: N/R).
 * - Se promedian solo los módulos que tienen al menos una nota numérica parseable.
 * - Si existe al menos un módulo con nota y alguno NO está aprobado, NO se escribe promedio (cadena vacía).
 * - Si todos los módulos con nota están aprobados, el promedio se representa como string con 2 decimales,
 *   salvo que sea 10 (en cuyo caso se muestra "10" sin decimales).
 *
 * Nota: el umbral de aprobación por defecto es 7. Si en el futuro depende del nivel/institución,
 * centralizar la configuración aquí o inyectarla desde el caller.
 */
final class PromedioAnualCalificaciones
{
    public const DEFAULT_NOTA_MINIMA_APROBACION = 7.0;

    /**
     * @param  array<string, mixed>  $row  Debe incluir ic01..ic28 como strings (vacío si no hay dato)
     * @return array{promedio: string, aprobado: bool, modulos_con_nota: int, modulos_aprobados: int, modulos_totales: int}
     */
    public static function calcular(array $row, float $notaMinimaAprobacion = self::DEFAULT_NOTA_MINIMA_APROBACION): array
    {
        // Cada “módulo” es un grupo de columnas legacy (`ic**`) que compiten entre sí (se toma el máximo numérico).
        // El doble array (`[['ic..']]`) deja lugar a futuros subgrupos sin reescribir el `foreach` principal.
        $modulos = [
            // Eval 1..8
            [['ic01', 'ic02', 'ic03']],
            [['ic04', 'ic05', 'ic06']],
            [['ic07', 'ic08', 'ic09']],
            [['ic10', 'ic11', 'ic12']],
            [['ic13', 'ic14', 'ic15']],
            [['ic16', 'ic17', 'ic18']],
            [['ic19', 'ic20', 'ic21']],
            [['ic22', 'ic23', 'ic24']],
            // JIS 1..2
            [['ic25', 'ic26']],
            [['ic27', 'ic28']],
        ];

        $suma = 0.0;
        // `conNota`: módulos donde hay al menos un valor numérico parseable (N/R1/R2, etc.).
        // `aprobadosConNota`: entre esos módulos, cuántos alcanzan `notaMinimaAprobacion` con su máximo.
        $conNota = 0;
        $aprobadosConNota = 0;

        foreach ($modulos as $grupo) {
            $campos = $grupo[0];
            $vals = [];
            foreach ($campos as $c) {
                // `null` = vacío o no numérico: no participa del máximo ni del promedio.
                $vals[] = self::parseNota($row[$c] ?? null);
            }

            $presentes = array_values(array_filter($vals, fn ($v) => $v !== null));
            if ($presentes === []) {
                // Módulo “sin datos”: no cuenta para el promedio ni para la regla de aprobación parcial.
                continue;
            }

            $conNota++;
            $max = max($presentes);

            if ($max >= $notaMinimaAprobacion) {
                $aprobadosConNota++;
            }

            // Importante: al promedio entra el máximo del módulo (no el promedio interno N/R1/R2).
            $suma += $max;
        }

        $totalModulos = count($modulos);

        if ($conNota === 0) {
            return [
                'promedio' => '',
                'aprobado' => false,
                'modulos_con_nota' => 0,
                'modulos_aprobados' => 0,
                'modulos_totales' => $totalModulos,
            ];
        }

        // Aprobación “estricta entre módulos con nota”: si hay alguno desaprobado, no mostramos promedio (cadena vacía).
        $aprobado = $aprobadosConNota === $conNota;
        $prom = $suma / $conNota;

        return [
            'promedio' => $aprobado ? self::formatNota($prom) : '',
            'aprobado' => $aprobado,
            'modulos_con_nota' => $conNota,
            'modulos_aprobados' => $aprobadosConNota,
            'modulos_totales' => $totalModulos,
        ];
    }

    private static function parseNota(mixed $raw): ?float
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

    private static function formatNota(float $v): string
    {
        $rounded = round($v, 2, PHP_ROUND_HALF_UP);

        // Regla UI: 10 sin decimales; resto con 2 decimales fijos.
        if (abs($rounded - 10.0) < 1e-9) {
            return '10';
        }

        return number_format($rounded, 2, '.', '');
    }
}
