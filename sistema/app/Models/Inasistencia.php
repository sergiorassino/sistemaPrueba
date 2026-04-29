<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inasistencia extends Model
{
    protected $table = 'inasistencias';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'idMatricula',
        'fecha',
        'cantidad',
        'tipo',
        'just',
        'obs',
    ];

    protected $casts = [
        'fecha' => 'date',
        'cantidad' => 'decimal:2',
    ];

    public function matricula()
    {
        return $this->belongsTo(Matricula::class, 'idMatricula');
    }
}

