<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Legajo extends Model
{
    protected $table = 'legajos';
    public $timestamps = false;
    protected $fillable = [
        'apellido', 'nombre', 'dni', 'cuil', 'fechnaci', 'sexo', 'nacion',
        'idFamilias', 'tipoalumno', 'legajo', 'libro', 'folio',
        'callenum', 'barrio', 'localidad', 'codpos',
        'ln_ciudad', 'ln_depto', 'ln_provincia', 'ln_pais',
        'telefono', 'email',
        'nombremad', 'dnimad', 'fechnacmad', 'nacionmad', 'estacivimad', 'domimad', 'ocupacmad', 'telemad', 'telecelmad', 'emailmad', 'vivemad',
        'nombrepad', 'dnipad', 'fechnacpad', 'nacionpad', 'estacivipad', 'domipad', 'ocupacpad', 'telepad', 'telecelpad', 'emailpad', 'vivepad',
        'nombretut', 'dnitut', 'teletut', 'emailtut', 'respAdmiNom', 'respAdmiDni',
        'escori', 'destino', 'obs', 'identif', 'vivecon', 'hermanos', 'ec_padres', 'parroquia',
        'needes', 'needes_detalle', 'certDisc', 'emeravis', 'retira', 'fechhora',
    ];

    protected $casts = [
        'fechnaci'    => 'date',
        'fechnacmad'  => 'date',
        'fechnacpad'  => 'date',
        'fechhora'    => 'datetime',
        'fechActDatos'=> 'datetime',
        'bloqmatr'    => 'boolean',
        'bloqadmi'    => 'boolean',
    ];

    public function familia()
    {
        return $this->belongsTo(Familia::class, 'idFamilias');
    }

    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'idnivel');
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'idLegajos');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'idLegajos');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->apellido . ', ' . $this->nombre);
    }

    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('apellido', 'like', "%{$termino}%")
              ->orWhere('nombre', 'like', "%{$termino}%")
              ->orWhere('dni', 'like', "%{$termino}%");
        });
    }
}
