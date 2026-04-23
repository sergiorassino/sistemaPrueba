<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    protected $table = 'calificaciones';
    public $timestamps = false;
    protected $guarded = [];

    public function legajo()
    {
        return $this->belongsTo(Legajo::class, 'idLegajos');
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
