<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ppc extends Model
{
    protected $table = 'ppc';

    public $timestamps = false;

    protected $fillable = [
        'idMateria',
        'idProfesor',
        'idSituRevis',
    ];
}
