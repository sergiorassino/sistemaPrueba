<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComMensajeEnvio extends Model
{
    protected $table = 'com_mensajes_envios';
    public $timestamps = false;

    protected $fillable = [
        'id_mensaje_destinatario', 'medio', 'estado',
        'motivo', 'proveedor_msgid', 'enviado_at',
    ];

    protected $casts = [
        'enviado_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function destinatario()
    {
        return $this->belongsTo(ComMensajeDestinatario::class, 'id_mensaje_destinatario');
    }

    public function iconoMedio(): string
    {
        return match($this->medio) {
            'push'      => '🔔',
            'email'     => '✉',
            'whatsapp'  => '💬',
            default     => '?',
        };
    }

    public function estadoLabel(): string
    {
        return match($this->estado) {
            'enviado'      => 'Enviado',
            'fallido'      => 'Fallido',
            'pendiente'    => 'Pendiente',
            'no_aplicable' => 'No disponible',
            default        => ucfirst((string) $this->estado),
        };
    }
}
