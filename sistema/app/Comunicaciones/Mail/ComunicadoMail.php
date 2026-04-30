<?php

namespace App\Comunicaciones\Mail;

use App\Models\ComMensaje;
use App\Models\ComMensajeDestinatario;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ComunicadoMail extends Mailable
{
    public function __construct(
        public readonly ComMensaje $mensaje,
        public readonly ComMensajeDestinatario $destinatario,
        public readonly string $nombreColegio = '',
    ) {}

    public function envelope(): Envelope
    {
        $asunto = $this->mensaje->hilo?->asunto ?? 'Comunicado escolar';
        $prefijo = $this->nombreColegio !== '' ? "[{$this->nombreColegio}] " : '';

        return new Envelope(subject: $prefijo . $asunto);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.comunicaciones.comunicado');
    }
}
