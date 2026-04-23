<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfesorTipo extends Model
{
    protected $table = 'profesortipo';
    public $timestamps = false;
    protected $guarded = [];

    public function profesores()
    {
        return $this->hasMany(Profesor::class, 'IdTipoProf');
    }
}
