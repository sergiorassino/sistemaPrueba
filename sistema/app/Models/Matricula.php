<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    protected $table = 'matricula';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'fechaMatricula' => 'date',
        'inscripto'      => 'boolean',
    ];

    public function legajo()
    {
        return $this->belongsTo(Legajo::class, 'idLegajos');
    }

    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'idNivel');
    }

    public function terlec()
    {
        return $this->belongsTo(Terlec::class, 'idTerlec');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'idCursos', 'Id');
    }
}
