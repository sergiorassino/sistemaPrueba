<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuotasImporte extends Model
{
    protected $table = 'cuotasimportes';

    public $timestamps = false;

    protected $fillable = [
        'idCuotas',
        'idCursos',
        'signo1v',
        'valor1v',
        'porcan1v',
        'signo2v',
        'valor2v',
        'porcan2v',
        'signo3v',
        'valor3v',
        'porcan3v',
        'signo4v',
        'valor4v',
        'porcan4v',
    ];
}
