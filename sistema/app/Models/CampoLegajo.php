<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class CampoLegajo extends Model
{
    protected $table = 'campos_legajo';

    public $timestamps = false;

    protected $fillable = [
        'columna',
        'etiqueta',
        'visible_listado',
        'orden',
        'solapa_legajo_id',
        'orden_en_solapa',
    ];

    protected $casts = [
        'visible_listado'  => 'boolean',
        'orden'            => 'integer',
        'solapa_legajo_id' => 'integer',
        'orden_en_solapa'  => 'integer',
    ];

    /** Columnas de `legajos` excluidas de la parametrización (seguridad). */
    public const COLUMNAS_EXCLUIDAS = ['pwrd', 'telecelmad', 'telecelpad'];

    /**
     * Apellido, nombre y DNI no se parametrizan: siempre en la solapa Alumno del formulario.
     *
     * @var list<string>
     */
    public const COLUMNAS_FIJAS_ALUMNO = ['apellido', 'nombre', 'dni'];

    public function solapa(): BelongsTo
    {
        return $this->belongsTo(SolapaLegajo::class, 'solapa_legajo_id');
    }

    // ─── Legajo ABM ───────────────────────────────────────────────────────────

    /**
     * Columnas activas para el formulario del legajo (aquellas con solapa asignada).
     * Devuelve null si no hay parametrización activa (tabla vacía o sin asignaciones).
     *
     * @return list<string>|null
     */
    public static function columnasActivasParaLegajo(): ?array
    {
        if (! Schema::hasTable('campos_legajo') || ! static::query()->exists()) {
            return null;
        }

        $cols = static::query()
            ->whereNotNull('solapa_legajo_id')
            ->whereNotIn('columna', self::COLUMNAS_FIJAS_ALUMNO)
            ->orderBy('solapa_legajo_id')
            ->orderBy('orden_en_solapa')
            ->orderBy('columna')
            ->pluck('columna')
            ->map(fn ($c) => (string) $c)
            ->values()
            ->all();

        return $cols !== [] ? $cols : null;
    }

    /**
     * Columnas por slug de solapa, en orden `orden_en_solapa` (sin apellido/nombre/dni).
     *
     * @return array<string, list<array{columna: string, etiqueta: ?string}>>
     */
    public static function camposPorSolapaSlugOrdenados(): array
    {
        if (! Schema::hasTable('campos_legajo') || ! Schema::hasTable('solapas_legajo')) {
            return [];
        }

        $rows = static::query()
            ->whereNotNull('solapa_legajo_id')
            ->whereNotIn('columna', self::COLUMNAS_FIJAS_ALUMNO)
            ->join('solapas_legajo', 'solapas_legajo.id', '=', 'campos_legajo.solapa_legajo_id')
            ->orderBy('solapas_legajo.orden')
            ->orderBy('campos_legajo.orden_en_solapa')
            ->orderBy('campos_legajo.columna')
            ->get(['campos_legajo.columna', 'campos_legajo.etiqueta', 'solapas_legajo.slug']);

        $map = [];
        foreach ($rows as $r) {
            $slug = (string) $r->slug;
            if (! isset($map[$slug])) {
                $map[$slug] = [];
            }
            $map[$slug][] = [
                'columna'  => (string) $r->columna,
                'etiqueta' => $r->etiqueta !== null && $r->etiqueta !== '' ? (string) $r->etiqueta : null,
            ];
        }

        return $map;
    }

    // ─── Listado PDF ──────────────────────────────────────────────────────────

    /**
     * Quita del listado PDF las claves `legajos.*` sin solapa asignada.
     * La visibilidad en PDF se deriva de tener solapa asignada (solapa_legajo_id IS NOT NULL).
     *
     * @param  list<string>  $keys
     * @return list<string>
     */
    public static function aplicarVisibilidadListadoPdf(array $keys): array
    {
        if (! Schema::hasTable('campos_legajo') || ! static::query()->exists()) {
            return $keys;
        }

        $ocultas = static::query()
            ->where('visible_listado', false)
            ->whereNotIn('columna', self::COLUMNAS_FIJAS_ALUMNO)
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

    /** @return list<string>|null nombres de columna visibles para la UI del PDF; null = no aplicar filtro. */
    public static function columnasLegajosVisiblesParaUi(): ?array
    {
        if (! Schema::hasTable('campos_legajo') || ! static::query()->exists()) {
            return null;
        }

        $cols = static::query()
            ->where('visible_listado', true)
            ->whereNotIn('columna', self::COLUMNAS_FIJAS_ALUMNO)
            ->orderBy('orden')
            ->orderBy('columna')
            ->pluck('columna')
            ->map(fn ($c) => (string) $c)
            ->values()
            ->all();

        return array_values(array_unique(array_merge(self::COLUMNAS_FIJAS_ALUMNO, $cols)));
    }
}
