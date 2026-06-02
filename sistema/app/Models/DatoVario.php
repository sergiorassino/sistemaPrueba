<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatoVario extends Model
{
    protected $table = 'datosvarios';

    public $timestamps = false;

    protected $fillable = [
        'ultimoComprobante',
    ];

    protected $casts = [
        'ultimoComprobante' => 'integer',
    ];
}
