<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuotaGenerada extends Model
{
    protected $table = 'cuotasgeneradas';

    public $timestamps = false;

    protected $fillable = [
        'idLegajos',
        'idCursos',
        'idCuotas',
        'idCuotastipo',
        'venc1',
        'venc2',
        'venc3',
        'faltapa',
        'nueVenc',
        'nroComp',
        'idCuotasbecas',
        'ultUpload',
    ];

    protected $casts = [
        'venc1' => 'date',
        'venc2' => 'date',
        'venc3' => 'date',
        'nueVenc' => 'date',
        'faltapa' => 'float',
    ];

    public function legajo()
    {
        return $this->belongsTo(Legajo::class, 'idLegajos');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'idCursos', 'Id');
    }

    public function cuota()
    {
        return $this->belongsTo(Cuota::class, 'idCuotas');
    }
}
