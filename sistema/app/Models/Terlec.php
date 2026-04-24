<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Terlec extends Model
{
    protected $table = 'terlec';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'ano',
        'orden',
    ];

    protected $casts = [
        'ano'   => 'integer',
        'orden' => 'integer',
    ];

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'idTerlec');
    }

    public function cursos()
    {
        return $this->hasMany(Curso::class, 'idTerlec');
    }

    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden', 'asc');
    }
}
