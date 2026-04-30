<?php

namespace App\Comunicaciones\Adapters;

use App\Comunicaciones\Mail\ComunicadoMail;
use App\Models\ComMensaje;
use App\Models\ComMensajeDestinatario;
use App\Models\ComMensajeEnvio;
use App\Models\ComPreferencia;
use App\Models\Legajo;
use App\Models\Profesor;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailAdapter
{
    public static function enviar(
        ComMensajeDestinatario $destinatario,
        ComMensaje $mensaje,
        string $nombreColegio = ''
    ): ComMensajeEnvio {
        $email = static::resolverEmail($destinatario);

        if ($email === null || $email === '') {
            return static::registrar($destinatario, 'no_aplicable', 'Sin dirección de correo disponible');
        }

        try {
            Mail::to($email)->send(new ComunicadoMail($mensaje, $destinatario, $nombreColegio));
            return static::registrar($destinatario, 'enviado');
        } catch (Throwable $e) {
            return static::registrar($destinatario, 'fallido', mb_substr($e->getMessage(), 0, 250));
        }
    }

    /**
     * Determina el email de contacto según el tipo de destinatario.
     *
     * Para familias: usa el vinculo_contacto de las preferencias para elegir el email correcto.
     * Para profesores: usa profesores.email.
     */
    private static function resolverEmail(ComMensajeDestinatario $destinatario): ?string
    {
        if ($destinatario->tipo_destinatario === 'profesor' && $destinatario->id_profesor) {
            $prof = Profesor::find($destinatario->id_profesor);
            return static::primerEmail([$prof?->email ?? null, $prof?->emailInsti ?? null]);
        }

        if ($destinatario->tipo_destinatario === 'familia' && $destinatario->id_legajo) {
            $legajo = Legajo::find($destinatario->id_legajo);
            if ($legajo === null) {
                return null;
            }

            $pref = ComPreferencia::paraLegajo($destinatario->id_legajo);
            $vinculo = $pref->exists ? $pref->vinculo_contacto : null;

            $candidatos = [];

            if ($vinculo === 'madre' || $vinculo === null) {
                $candidatos[] = $legajo->emailmad ?? null;
            }
            if ($vinculo === 'padre' || $vinculo === null) {
                $candidatos[] = $legajo->emailpad ?? null;
            }
            if ($vinculo === 'tutor') {
                $candidatos[] = $legajo->emailtut ?? null;
            }
            // email general del legajo como fallback
            $candidatos[] = $legajo->email ?? null;

            return static::primerEmail($candidatos);
        }

        return null;
    }

    /** Retorna el primer email no vacío de la lista */
    private static function primerEmail(array $candidatos): ?string
    {
        foreach ($candidatos as $email) {
            $e = trim((string) ($email ?? ''));
            if ($e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL)) {
                return $e;
            }
        }
        return null;
    }

    private static function registrar(
        ComMensajeDestinatario $destinatario,
        string $estado,
        ?string $motivo = null
    ): ComMensajeEnvio {
        return ComMensajeEnvio::create([
            'id_mensaje_destinatario' => $destinatario->id,
            'medio'                   => 'email',
            'estado'                  => $estado,
            'motivo'                  => $motivo,
            'enviado_at'              => $estado === 'enviado' ? now() : null,
        ]);
    }
}
