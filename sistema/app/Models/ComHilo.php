<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComHilo extends Model
{
    protected $table = 'com_hilos';
    public $timestamps = false;

    protected $fillable = [
        'asunto', 'cuerpo_inicial_id', 'scope',
        'id_legajo', 'id_curso', 'id_nivel', 'id_terlec',
        'creado_por_tipo', 'creado_por_id', 'creado_por_rol',
        'estado', 'familia_puede_responder', 'ultimo_mensaje_at',
    ];

    protected $casts = [
        'familia_puede_responder' => 'boolean',
        'ultimo_mensaje_at'       => 'datetime',
        'created_at'              => 'datetime',
        'updated_at'              => 'datetime',
    ];

    public function mensajes()
    {
        return $this->hasMany(ComMensaje::class, 'id_hilo')->orderBy('created_at');
    }

    public function participantes()
    {
        return $this->hasMany(ComHiloParticipante::class, 'id_hilo');
    }

    public function destinatarios()
    {
        return $this->hasMany(ComMensajeDestinatario::class, 'id_hilo');
    }

    public function legajo()
    {
        return $this->belongsTo(Legajo::class, 'id_legajo');
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class, 'id_curso', 'Id');
    }

    /** Etiqueta legible del scope */
    public function scopeLabel(): string
    {
        return match($this->scope) {
            'alumno'         => 'Un alumno',
            'varios_alumnos' => 'Varios alumnos',
            'curso'          => 'Un curso',
            'colegio'        => 'Todo el colegio',
            default          => ucfirst((string) $this->scope),
        };
    }

    /** Etiqueta del estado */
    public function estadoLabel(): string
    {
        return match($this->estado) {
            'abierto' => 'Abierto',
            'cerrado' => 'Cerrado',
            default   => ucfirst((string) $this->estado),
        };
    }

    /**
     * Si el hilo fue iniciado por la escuela y se marcó como solo informativo,
     * la familia no puede enviar respuestas en el cuaderno.
     */
    public function familiaPuedeEnviarRespuestas(): bool
    {
        if ($this->creado_por_tipo !== 'profesor') {
            return true;
        }

        return (bool) $this->familia_puede_responder;
    }

    public function esComunicadoInformativoEscuela(): bool
    {
        return $this->creado_por_tipo === 'profesor' && ! $this->familiaPuedeEnviarRespuestas();
    }
}
