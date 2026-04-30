<?php

namespace App\Comunicaciones;

use App\Comunicaciones\Adapters\MailAdapter;
use App\Comunicaciones\Adapters\PushAdapter;
use App\Comunicaciones\Adapters\WhatsappAdapter;
use App\Models\ComMensaje;
use App\Models\ComMensajeDestinatario;
use App\Models\ComPreferencia;
use Illuminate\Support\Facades\DB;

class Distribuidor
{
    /**
     * Envía un mensaje a todos sus destinatarios, por los medios que
     * la combinación canal + preferencias del usuario permita.
     *
     * @param list<string> $mediosCanal Medios permitidos por el canal
     */
    public static function distribuir(ComMensaje $mensaje, array $mediosCanal): void
    {
        $mensaje->load(['hilo', 'destinatarios']);
        $nombreColegio = static::nombreColegio($mensaje);

        foreach ($mensaje->destinatarios as $destinatario) {
            $mediosUsuario = static::mediosActivosParaDestinatario($destinatario);
            $medios        = array_intersect($mediosCanal, $mediosUsuario);

            foreach ($medios as $medio) {
                match ($medio) {
                    'push'      => PushAdapter::enviar($destinatario, $mensaje, $nombreColegio),
                    'email'     => MailAdapter::enviar($destinatario, $mensaje, $nombreColegio),
                    'whatsapp'  => WhatsappAdapter::enviar($destinatario, $mensaje),
                    default     => null,
                };
            }
        }
    }

    /**
     * Medios activos según las preferencias del destinatario.
     * Si no existe registro en com_preferencias, se usa: push + email.
     *
     * @return list<string>
     */
    private static function mediosActivosParaDestinatario(ComMensajeDestinatario $dest): array
    {
        if ($dest->tipo_destinatario === 'familia' && $dest->id_legajo) {
            $pref = ComPreferencia::paraLegajo($dest->id_legajo);
            return $pref->exists ? $pref->mediosActivos() : ['push', 'email'];
        }

        if ($dest->tipo_destinatario === 'profesor' && $dest->id_profesor) {
            $pref = ComPreferencia::paraProfesor($dest->id_profesor);
            return $pref->exists ? $pref->mediosActivos() : ['push', 'email'];
        }

        return ['push', 'email'];
    }

    private static function nombreColegio(ComMensaje $mensaje): string
    {
        $idNivel = $mensaje->hilo?->id_nivel;
        if (! $idNivel) {
            return '';
        }
        $insti = DB::table('ento')->where('idNivel', $idNivel)->value('insti');
        return is_string($insti) ? trim($insti) : '';
    }
}
