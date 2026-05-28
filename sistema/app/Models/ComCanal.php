<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComCanal extends Model
{
    protected $table = 'com_canales';
    public $timestamps = false;

    protected $fillable = [
        'id_nivel', 'rol_emisor', 'rol_receptor', 'puede_iniciar', 'puede_responder',
        'medios_permitidos', 'activo',
    ];

    protected $casts = [
        'puede_iniciar'     => 'boolean',
        'puede_responder'   => 'boolean',
        'activo'            => 'boolean',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /**
     * @param  array<int, string>|string|null  $value
     */
    public function setMediosPermitidosAttribute(mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['medios_permitidos'] = null;

            return;
        }

        if (is_string($value)) {
            $this->attributes['medios_permitidos'] = $value;

            return;
        }

        $lista = array_values(array_unique(array_filter(
            is_array($value) ? $value : [],
            static fn ($m) => is_string($m) && $m !== ''
        )));

        $this->attributes['medios_permitidos'] = json_encode($lista, JSON_THROW_ON_ERROR);
    }

    /**
     * @return list<string>
     */
    public function getMediosPermitidosAttribute(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return array_values($value);
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? array_values($decoded) : [];
    }

    /**
     * Etiquetas legibles para la UI.
     *
     * @return array<string,string>
     */
    public static function etiquetasRoles(): array
    {
        return [
            'directivo' => 'Directivo / Secretario',
            'preceptor' => 'Preceptor',
            'profesor'  => 'Profesor',
            'familia'   => 'Familia',
        ];
    }

    /** @return list<string> */
    public static function rolesClave(): array
    {
        return array_keys(static::etiquetasRoles());
    }

    public static function etiquetaRol(string $rol): string
    {
        return static::etiquetasRoles()[$rol] ?? ucfirst($rol);
    }

    /** Medios disponibles en el sistema */
    public static function mediosDisponibles(): array
    {
        return ['push', 'email', 'whatsapp'];
    }
}
