<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class InasistenciaValor extends Model
{
    protected $table = 'inasistencias_valores';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'concepto',
        'cantidad',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
    ];

    /**
     * IDs de tipo (`inasistencias.tipo`) que corresponden a educación física.
     *
     * @return Collection<int, string> claves normalizadas como string del id numérico
     */
    public static function idsEducacionFisica(): Collection
    {
        return Cache::remember('inasistencias_valores:ids_educacion_fisica', 3600, function () {
            return static::query()
                ->get(['id', 'concepto'])
                ->filter(fn (self $v) => static::conceptoEsEducacionFisica((string) ($v->concepto ?? '')))
                ->map(fn (self $v) => (string) (int) $v->id)
                ->values();
        });
    }

    public static function conceptoEsEducacionFisica(string $concepto): bool
    {
        $n = static::normalizarConcepto($concepto);
        if ($n === '') {
            return false;
        }

        if (str_contains($n, 'edfis') || str_contains($n, 'ed fis') || str_contains($n, 'ed. fis')) {
            return true;
        }

        return str_contains($n, 'educ') && str_contains($n, 'fis');
    }

    private static function normalizarConcepto(string $concepto): string
    {
        $s = mb_strtolower(trim($concepto));
        $s = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ü'], ['a', 'e', 'i', 'o', 'u', 'u'], $s);
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

        return $s;
    }
}
