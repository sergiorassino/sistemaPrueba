<?php

namespace App\Support\Cuotas;

use App\Models\CuotasMes;
use App\Models\CuotasTipo;
use App\Models\Terlec;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Catálogos y reglas del ABM de plantillas (`cuotas`).
 */
final class CuotasPlantillaCatalog
{
    /** @return array<int, string> */
    public static function opcionesSinConBeca(): array
    {
        return [
            0 => 'No aplica Beca',
            1 => 'Aplica Beca',
        ];
    }

    public static function idTerlecActivo(): int
    {
        return (int) schoolCtx()->idTerlec;
    }

    /**
     * @return Collection<int, Terlec>
     */
    public static function terlecsParaSelector(): Collection
    {
        $id = self::idTerlecActivo();

        return Terlec::query()
            ->whereKey($id)
            ->get(['id', 'ano']);
    }

    /**
     * @return Collection<int, CuotasMes>
     */
    public static function mesesOrdenados(): Collection
    {
        return CuotasMes::query()->orderBy('id')->get(['id', 'mes']);
    }

    /**
     * @return Collection<int, CuotasTipo>
     */
    public static function tiposOrdenados(): Collection
    {
        return CuotasTipo::query()->orderBy('id')->get(['id', 'nombre']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function reglasFila(string $key, array $data): array
    {
        $mesIds = self::mesesOrdenados()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $tipoIds = self::tiposOrdenados()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $sinIds = array_map('intval', array_keys(self::opcionesSinConBeca()));

        return [
            "draft.{$key}.nombre" => ['required', 'string', 'max:120'],
            "draft.{$key}.idCuotasmeses" => ['required', 'integer', Rule::in($mesIds)],
            "draft.{$key}.idCuotastipo" => ['required', 'integer', Rule::in($tipoIds)],
            "draft.{$key}.idTerlec" => ['required', 'integer', 'in:'.self::idTerlecActivo()],
            "draft.{$key}.venc1" => ['required', 'date'],
            "draft.{$key}.venc2" => ['nullable', 'date'],
            "draft.{$key}.venc3" => ['nullable', 'date'],
            "draft.{$key}.sinConBeca" => ['required', 'integer', Rule::in($sinIds)],
            "draft.{$key}.orden" => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public static function esFilaNueva(string|int $key): bool
    {
        return is_string($key) && str_starts_with($key, 'new_');
    }
}
