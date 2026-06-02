<?php

namespace App\Support\Cuotas;

use App\Models\Cuota;
use App\Models\CuotasImporte;
use Illuminate\Validation\Rule;

/**
 * Reglas y catálogos del ABM de importes por curso (`cuotasimportes`).
 */
final class CuotasImportesCatalog
{
    /** @return array<string, string> */
    public static function opcionesSigno(): array
    {
        return [
            '-' => 'Bonificación (−)',
            '+' => 'Interés (+)',
        ];
    }

    /** @return array<string, string> */
    public static function opcionesPorcan(): array
    {
        return [
            '%' => '%',
            '$' => '$',
        ];
    }

    public static function idTerlecActivo(): int
    {
        return CuotasPlantillaCatalog::idTerlecActivo();
    }

    public static function cuotaDelCicloOrFail(int $idCuotas): Cuota
    {
        return Cuota::query()
            ->whereKey($idCuotas)
            ->where('idTerlec', self::idTerlecActivo())
            ->firstOrFail();
    }

    public static function importeDelCicloOrFail(int $id, int $idCuotas): CuotasImporte
    {
        return CuotasImporte::query()
            ->whereKey($id)
            ->where('idCuotas', $idCuotas)
            ->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function reglasFila(string $key, array $data): array
    {
        $signos = array_keys(self::opcionesSigno());
        $porcans = array_keys(self::opcionesPorcan());

        return [
            "draft.{$key}.importe" => ['required', 'string', 'max:24'],
            "draft.{$key}.signo1v" => ['required', 'string', Rule::in($signos)],
            "draft.{$key}.valor1v" => ['required', 'string', 'max:16'],
            "draft.{$key}.porcan1v" => ['required', 'string', Rule::in($porcans)],
            "draft.{$key}.signo2v" => ['required', 'string', Rule::in($signos)],
            "draft.{$key}.valor2v" => ['required', 'string', 'max:16'],
            "draft.{$key}.porcan2v" => ['required', 'string', Rule::in($porcans)],
            "draft.{$key}.signo3v" => ['required', 'string', Rule::in($signos)],
            "draft.{$key}.valor3v" => ['required', 'string', 'max:16'],
            "draft.{$key}.porcan3v" => ['required', 'string', Rule::in($porcans)],
            "draft.{$key}.signo4v" => ['required', 'string', Rule::in($signos)],
            "draft.{$key}.valor4v" => ['required', 'string', 'max:16'],
            "draft.{$key}.porcan4v" => ['required', 'string', Rule::in($porcans)],
        ];
    }

    /**
     * @param  array<string, mixed>  $draftRow
     * @return array<string, mixed>
     */
    public static function payloadDesdeDraft(array $draftRow): array
    {
        return [
            'importe' => CuotasFormato::parseImporte((string) ($draftRow['importe'] ?? '')),
            'signo1v' => (string) ($draftRow['signo1v'] ?? '-'),
            'valor1v' => self::parseValorCampo($draftRow['valor1v'] ?? 0),
            'porcan1v' => (string) ($draftRow['porcan1v'] ?? '%'),
            'signo2v' => (string) ($draftRow['signo2v'] ?? '+'),
            'valor2v' => self::parseValorCampo($draftRow['valor2v'] ?? 0),
            'porcan2v' => (string) ($draftRow['porcan2v'] ?? '%'),
            'signo3v' => (string) ($draftRow['signo3v'] ?? '+'),
            'valor3v' => self::parseValorCampo($draftRow['valor3v'] ?? 0),
            'porcan3v' => (string) ($draftRow['porcan3v'] ?? '%'),
            'signo4v' => (string) ($draftRow['signo4v'] ?? '+'),
            'valor4v' => self::parseValorCampo($draftRow['valor4v'] ?? 0),
            'porcan4v' => (string) ($draftRow['porcan4v'] ?? '%'),
        ];
    }

    public static function parseValorCampo(mixed $valor): float
    {
        return round(CuotasFormato::parseImporte(is_numeric($valor) ? (string) $valor : (string) $valor), 2);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function validarMontos(array $data, string $keyPrefix = ''): void
    {
        $importe = CuotasFormato::parseImporte((string) ($data['importe'] ?? ''));
        if ($importe < 0 || $importe > 99999999.99) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $keyPrefix.'importe' => 'El importe debe estar entre 0 y 99.999.999,99.',
            ]);
        }

        foreach ([1, 2, 3, 4] as $n) {
            $campo = "valor{$n}v";
            $v = self::parseValorCampo($data[$campo] ?? 0);
            if ($v < 0 || $v > 999999.99) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    $keyPrefix.$campo => 'El valor debe estar entre 0 y 999.999,99.',
                ]);
            }
        }
    }
}
