<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ento extends Model
{
    protected $table = 'ento';

    protected $primaryKey = 'idNivel';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'idNivel',

        // Institucional (legacy)
        'insti',
        'cue',
        'ee',
        'cuit',
        'categoria',
        'direccion',
        'localidad',
        'departamento',
        'provincia',
        'telefono',
        'mail',
        'replegal',

        // Logo (nuevo)
        'logo_path',
        'logo_original_name',
    ];
}

