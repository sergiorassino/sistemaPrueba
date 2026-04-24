<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CampoListadoAlumno extends Model
{
    protected $table = 'campos_listado_alumnos';

    public $timestamps = false;

    protected $fillable = [
        'columna',
        'etiqueta',
        'visible_listado',
        'orden',
    ];

    protected $casts = [
        'visible_listado' => 'boolean',
        'orden' => 'integer',
    ];

    /** Columnas de `legajos` que no deben existir en esta tabla (seguridad). */
    public const COLUMNAS_EXCLUIDAS = ['pwrd'];

    /**
     * Quita del listado PDF las claves `legajos.*` marcadas como no visibles.
     *
     * @param  list<string>  $keys
     * @return list<string>
     */
    public static function aplicarVisibilidadListadoPdf(array $keys): array
    {
        if (! Schema::hasTable('campos_listado_alumnos') || ! static::query()->exists()) {
            return $keys;
        }

        $ocultas = static::query()
            ->where('visible_listado', false)
            ->pluck('columna')
            ->all();
        if ($ocultas === []) {
            return $keys;
        }

        $set = array_flip($ocultas);
        $out = [];
        foreach ($keys as $k) {
            if (str_starts_with($k, 'legajos.')) {
                $col = substr($k, strlen('legajos.'));
                if (isset($set[$col])) {
                    continue;
                }
            }
            $out[] = $k;
        }

        if ($out !== []) {
            return $out;
        }

        foreach (['legajos.apellido', 'legajos.nombre', 'legajos.dni'] as $k) {
            if (str_starts_with($k, 'legajos.')) {
                $col = substr($k, strlen('legajos.'));
                if (isset($set[$col])) {
                    continue;
                }
            }

            return [$k];
        }

        return ['matricula.nroMatricula'];
    }

    /** @return list<string>|null nombres de columna visibles; null = no aplicar filtro (tabla vacía). */
    public static function columnasLegajosVisiblesParaUi(): ?array
    {
        if (! Schema::hasTable('campos_listado_alumnos') || ! static::query()->exists()) {
            return null;
        }

        return static::query()
            ->where('visible_listado', true)
            ->orderBy('orden')
            ->orderBy('columna')
            ->pluck('columna')
            ->map(fn ($c) => (string) $c)
            ->values()
            ->all();
    }
}
