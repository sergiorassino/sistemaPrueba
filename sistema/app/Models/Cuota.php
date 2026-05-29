<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    protected $table = 'cuotas';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    public function cuotasGeneradas()
    {
        return $this->hasMany(CuotaGenerada::class, 'idCuotas');
    }
}
