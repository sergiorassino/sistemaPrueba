<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Legajo extends Authenticatable
{
    protected $table = 'legajos';

    public $timestamps = false;

    /**
     * Permite columnas extra por colegio (p. ej. telealte1_nom) sin listarlas todas en fillable.
     * Mass assignment solo bloquea identificador y contraseña.
     */
    protected $guarded = ['id', 'pwrd'];

    protected $hidden = ['pwrd'];

    protected $casts = [
        'fechnaci' => 'date',
        'fechnacmad' => 'date',
        'fechnacpad' => 'date',
        'fechhora' => 'datetime',
        'fechActDatos' => 'datetime',
        'bloqmatr' => 'boolean',
        'bloqadmi' => 'boolean',
    ];

    public function familia()
    {
        return $this->belongsTo(Familia::class, 'idFamilias');
    }

    public function sexoCatalogo()
    {
        return $this->belongsTo(Sexo::class, 'sexo');
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

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthPassword(): string
    {
        return (string) ($this->pwrd ?? '');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->apellido.', '.$this->nombre);
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
