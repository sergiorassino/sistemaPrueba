<?php

namespace App\Push;

use Illuminate\Support\Facades\DB;

class PushMensajeEnviadoRepository
{
    /**
     * @param list<string> $userKeysRequested
     * @param list<string> $sentUserKeys
     * @param array<string,string> $failedUserKeys
     */
    public static function guardar(
        string $titulo,
        string $cuerpo,
        string $url,
        array $userKeysRequested,
        array $sentUserKeys,
        array $failedUserKeys,
        ?string $tipoDestino = null,
        ?int $idTerlec = null,
        ?string $idUsuarioEnvio = null,
    ): int {
        $idMensaje = (int) DB::table('push_mensajes_enviados')->insertGetId([
            'titulo' => $titulo,
            'cuerpo' => $cuerpo,
            'url' => $url !== '' ? $url : './',
            'tipo_destino' => $tipoDestino,
            'id_terlec' => $idTerlec,
            'id_usuario_envio' => $idUsuarioEnvio,
            'created_at' => DB::raw('CURRENT_TIMESTAMP'),
        ]);

        if ($idMensaje <= 0) {
            return 0;
        }

        $sentSet = array_fill_keys($sentUserKeys, true);
        $rows = [];
        foreach ($userKeysRequested as $uk) {
            $uk = (string) $uk;
            $estado = isset($sentSet[$uk]) ? 'enviado' : 'no_enviado';
            $rows[] = [
                'id_mensaje' => $idMensaje,
                'user_key' => $uk,
                'estado' => $estado,
                'motivo' => $estado === 'no_enviado' ? ($failedUserKeys[$uk] ?? null) : null,
            ];
        }

        if (! empty($rows)) {
            DB::table('push_mensajes_destinatarios')->insert($rows);
        }

        return $idMensaje;
    }

    /**
     * @return list<array{id:int,titulo:string,cuerpo:string,url:?string,created_at:?string}>
     */
    public static function listarPorUserKey(string $userKey): array
    {
        if ($userKey === '') {
            return [];
        }

        return DB::table('push_mensajes_enviados as m')
            ->join('push_mensajes_destinatarios as d', function ($join) use ($userKey) {
                $join->on('d.id_mensaje', '=', 'm.id')->where('d.user_key', '=', $userKey);
            })
            ->orderByDesc('m.id')
            ->get(['m.id', 'm.titulo', 'm.cuerpo', 'm.url', 'm.created_at'])
            ->map(function ($r) {
                return [
                    'id' => (int) $r->id,
                    'titulo' => (string) $r->titulo,
                    'cuerpo' => (string) $r->cuerpo,
                    'url' => is_string($r->url) && $r->url !== '' ? $r->url : null,
                    'created_at' => $r->created_at ? (string) $r->created_at : null,
                ];
            })
            ->all();
    }

    /**
     * @return array{id:int,titulo:string,cuerpo:string,url:?string,created_at:?string}|null
     */
    public static function getByIdForUserKey(int $id, string $userKey): ?array
    {
        if ($userKey === '') {
            return null;
        }

        $r = DB::table('push_mensajes_enviados as m')
            ->join('push_mensajes_destinatarios as d', function ($join) use ($userKey) {
                $join->on('d.id_mensaje', '=', 'm.id')->where('d.user_key', '=', $userKey);
            })
            ->where('m.id', $id)
            ->first(['m.id', 'm.titulo', 'm.cuerpo', 'm.url', 'm.created_at']);

        if (! $r) {
            return null;
        }

        return [
            'id' => (int) $r->id,
            'titulo' => (string) $r->titulo,
            'cuerpo' => (string) $r->cuerpo,
            'url' => is_string($r->url) && $r->url !== '' ? $r->url : null,
            'created_at' => $r->created_at ? (string) $r->created_at : null,
        ];
    }
}

