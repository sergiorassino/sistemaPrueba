<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComPreferencia extends Model
{
    protected $table = 'com_preferencias';
    public $timestamps = false;

    protected $fillable = [
        'tipo_usuario', 'id_legajo', 'id_profesor',
        'vinculo_contacto', 'push', 'email', 'whatsapp',
    ];

    protected $casts = [
        'push'       => 'boolean',
        'email'      => 'boolean',
        'whatsapp'   => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function legajo()
    {
        return $this->belongsTo(Legajo::class, 'id_legajo');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'id_profesor');
    }

    /** Retorna las preferencias de un legajo, o valores por defecto si no existe registro */
    public static function paraLegajo(int $idLegajo): static
    {
        return static::firstOrNew(
            ['tipo_usuario' => 'familia', 'id_legajo' => $idLegajo],
            ['push' => true, 'email' => true, 'whatsapp' => false]
        );
    }

    /** Retorna las preferencias de un profesor, o valores por defecto si no existe registro */
    public static function paraProfesor(int $idProfesor): static
    {
        return static::firstOrNew(
            ['tipo_usuario' => 'profesor', 'id_profesor' => $idProfesor],
            ['push' => true, 'email' => true, 'whatsapp' => false]
        );
    }

    /** Lista de medios activos según las preferencias */
    public function mediosActivos(): array
    {
        $medios = [];
        if ($this->push)     $medios[] = 'push';
        if ($this->email)    $medios[] = 'email';
        if ($this->whatsapp) $medios[] = 'whatsapp';
        return $medios;
    }
}
