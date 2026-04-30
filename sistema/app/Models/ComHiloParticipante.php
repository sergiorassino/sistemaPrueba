<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComHiloParticipante extends Model
{
    protected $table = 'com_hilos_participantes';
    public $timestamps = false;

    protected $fillable = [
        'id_hilo', 'tipo', 'id_profesor', 'id_legajo',
        'rol', 'vinculo', 'nombre_snapshot', 'dni_snapshot', 'agregado_at',
    ];

    protected $casts = [
        'agregado_at' => 'datetime',
    ];

    public function hilo()
    {
        return $this->belongsTo(ComHilo::class, 'id_hilo');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'id_profesor');
    }

    public function legajo()
    {
        return $this->belongsTo(Legajo::class, 'id_legajo');
    }
}
