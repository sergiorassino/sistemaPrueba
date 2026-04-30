<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComCanal extends Model
{
    protected $table = 'com_canales';
    public $timestamps = false;

    protected $fillable = [
        'rol_emisor', 'rol_receptor', 'puede_iniciar', 'puede_responder',
        'medios_permitidos', 'activo',
    ];

    protected $casts = [
        'puede_iniciar'    => 'boolean',
        'puede_responder'  => 'boolean',
        'medios_permitidos' => 'array',
        'activo'           => 'boolean',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

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
