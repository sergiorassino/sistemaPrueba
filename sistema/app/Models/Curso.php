<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $table = 'cursos';
    protected $primaryKey = 'Id';
    public $timestamps = false;
    protected $fillable = [
        'orden', 'idCurPlan', 'idTerlec', 'idNivel', 'cursec', 'c', 's', 'turno',
    ];

    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'idNivel');
    }

    public function terlec()
    {
        return $this->belongsTo(Terlec::class, 'idTerlec');
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'idCursos', 'Id');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'idCursos', 'Id');
    }
}
