<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Profesor extends Authenticatable
{
    protected $table = 'profesores';
    public $timestamps = false;
    protected $fillable = [
        'nombre',
        'apellido',
        'dni',
        'pwrd',
        'nivel',
        'permisos',
        'ult_idNivel',
        'ult_idTerlec',
        'fechnaci',
        'apto',
        'escalafonD',
        'escalafonE',
        'IdTipoProf',
    ];
    protected $hidden = ['pwrd'];

    protected $casts = [
        'fechnaci'   => 'date',
        'apto'       => 'date',
        'escalafonD' => 'date',
        'escalafonE' => 'date',
    ];

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthPassword(): string
    {
        return (string) $this->pwrd;
    }

    public function tipo()
    {
        return $this->belongsTo(ProfesorTipo::class, 'IdTipoProf');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->apellido . ', ' . $this->nombre);
    }
}
