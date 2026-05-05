<?php

namespace App\Comunicaciones;

use App\Models\ComHilo;
use App\Models\ComHiloParticipante;
use App\Models\ComMensaje;
use App\Models\ComMensajeDestinatario;
use App\Models\ComPreferencia;
use App\Models\Legajo;
use App\Models\Profesor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ComunicacionesRepository
{
    /**
     * Bandeja del profesor: hilos donde es creador o destinatario,
     * con contadores de no_leidos y respondidos.
     *
     * @param  string  $direccion  recibidos|enviados — hilos no iniciados por el profesor vs iniciados por él
     * @return \Illuminate\Support\Collection
     */
    public static function bandejaProfesor(
        int $idProfesor,
        int $idNivel,
        int $idTerlec,
        string $filtro = 'todos',
        string $direccion = 'recibidos'
    ) {
        $direccion = in_array($direccion, ['recibidos', 'enviados'], true) ? $direccion : 'todos';

        $query = DB::table('com_hilos as h')
            ->where(function ($q) use ($idProfesor) {
                $q->where(function ($q2) use ($idProfesor) {
                    $q2->where('h.creado_por_tipo', 'profesor')
                       ->where('h.creado_por_id', $idProfesor);
                })->orWhereExists(function ($sub) use ($idProfesor) {
                    $sub->select(DB::raw(1))
                        ->from('com_mensajes_destinatarios as d2')
                        ->whereColumn('d2.id_hilo', 'h.id')
                        ->where('d2.tipo_destinatario', 'profesor')
                        ->where('d2.id_profesor', $idProfesor);
                });
            })
            ->when($direccion === 'recibidos', function ($q) use ($idProfesor) {
                $q->where(function ($w) use ($idProfesor) {
                    $w->where('h.creado_por_tipo', '!=', 'profesor')
                        ->orWhere('h.creado_por_id', '!=', $idProfesor);
                });
            })
            ->when($direccion === 'enviados', function ($q) use ($idProfesor) {
                $q->where('h.creado_por_tipo', 'profesor')
                    ->where('h.creado_por_id', $idProfesor);
            })
            ->where('h.id_nivel', $idNivel)
            ->where('h.id_terlec', $idTerlec)
            ->leftJoin('com_mensajes_destinatarios as d', function ($j) use ($idProfesor) {
                $j->on('d.id_hilo', '=', 'h.id')
                  ->where('d.tipo_destinatario', 'profesor')
                  ->where('d.id_profesor', $idProfesor);
            });

        $select = [
            'h.id', 'h.asunto', 'h.scope', 'h.estado',
            'h.creado_por_tipo', 'h.creado_por_id', 'h.creado_por_rol',
            'h.familia_puede_responder',
            'h.id_curso', 'h.cursos_envio',
            'h.ultimo_mensaje_at', 'h.created_at',
            DB::raw('SUM(CASE WHEN d.leido_at IS NULL AND d.id IS NOT NULL THEN 1 ELSE 0 END) as no_leidos'),
            DB::raw('SUM(CASE WHEN d.respondido_at IS NOT NULL THEN 1 ELSE 0 END) as respondidos'),
            DB::raw('COUNT(d.id) as total_dest'),
            DB::raw("CASE WHEN h.creado_por_tipo = 'profesor' AND h.creado_por_id = {$idProfesor} THEN 'enviado' ELSE 'recibido' END as direccion"),
            DB::raw('(SELECT COUNT(*) FROM com_mensajes mx WHERE mx.id_hilo = h.id) as mensajes_count'),
        ];

        // Para "Para:" (enviados) necesitamos destinatarios del mensaje inicial.
        // Se seleccionan siempre para permitir una bandeja unificada (todos).
        $select[] = DB::raw('(SELECT m.contenido FROM com_mensajes m WHERE m.id = h.cuerpo_inicial_id LIMIT 1) as cuerpo_inicial_contenido');
        $select[] = DB::raw("(SELECT COUNT(DISTINCT d0.id_legajo) FROM com_mensajes_destinatarios d0 WHERE d0.id_mensaje = h.cuerpo_inicial_id AND d0.tipo_destinatario = 'familia' AND d0.id_legajo IS NOT NULL) as destinatarios_familia_count");
        $select[] = DB::raw("(SELECT GROUP_CONCAT(DISTINCT NULLIF(TRIM(d0.nombre_snapshot), '') ORDER BY d0.id_legajo SEPARATOR '||') FROM com_mensajes_destinatarios d0 WHERE d0.id_mensaje = h.cuerpo_inicial_id AND d0.tipo_destinatario = 'familia') as destinatarios_nombres_concat");
        $select[] = DB::raw("(SELECT CASE WHEN TRIM(COALESCE(c.cursec, '')) <> '' THEN TRIM(c.cursec) ELSE TRIM(COALESCE(cp.curPlanCurso, 'Curso')) END FROM cursos c LEFT JOIN curplan cp ON cp.id = c.idCurPlan WHERE c.Id = h.id_curso LIMIT 1) as curso_envio_label");

        // Datos del remitente del primer mensaje (útil para "Recibidos")
        $select[] = DB::raw('(SELECT m0.tipo_remitente FROM com_mensajes m0 WHERE m0.id = h.cuerpo_inicial_id LIMIT 1) as cuerpo_inicial_tipo');
        $select[] = DB::raw('(SELECT m0.nombre_remitente_snapshot FROM com_mensajes m0 WHERE m0.id = h.cuerpo_inicial_id LIMIT 1) as cuerpo_inicial_nombre');
        $select[] = DB::raw('(SELECT m0.vinculo_familiar FROM com_mensajes m0 WHERE m0.id = h.cuerpo_inicial_id LIMIT 1) as cuerpo_inicial_vinculo');

        $query->select($select)
            ->groupBy('h.id', 'h.asunto', 'h.scope', 'h.estado', 'h.creado_por_tipo',
                      'h.creado_por_id', 'h.creado_por_rol', 'h.familia_puede_responder',
                      'h.ultimo_mensaje_at', 'h.created_at', 'h.cuerpo_inicial_id', 'h.id_curso', 'h.cursos_envio')
            ->orderByDesc('h.ultimo_mensaje_at');

        if ($filtro === 'no_leidos') {
            $query->havingRaw('SUM(CASE WHEN d.leido_at IS NULL AND d.id IS NOT NULL THEN 1 ELSE 0 END) > 0');
        } elseif ($filtro === 'respondidos') {
            $query->havingRaw('SUM(CASE WHEN d.respondido_at IS NOT NULL THEN 1 ELSE 0 END) > 0');
        }

        return $query->get();
    }

    /**
     * Bandeja de la familia: hilos donde el legajo es creador o destinatario.
     *
     * @param  string  $direccion  todos|recibidos|enviados — unificar bandeja o filtrar por origen
     * @return \Illuminate\Support\Collection
     */
    public static function bandejaFamilia(
        int $idLegajo,
        int $idNivel,
        int $idTerlec,
        string $filtro = 'todos',
        string $direccion = 'todos'
    ) {
        $direccion = in_array($direccion, ['todos', 'recibidos', 'enviados'], true)
            ? $direccion
            : 'todos';

        $query = DB::table('com_hilos as h')
            ->where(function ($q) use ($idLegajo) {
                $q->where(function ($q2) use ($idLegajo) {
                    $q2->where('h.creado_por_tipo', 'familia')
                       ->where('h.creado_por_id', $idLegajo);
                })->orWhereExists(function ($sub) use ($idLegajo) {
                    $sub->select(DB::raw(1))
                        ->from('com_mensajes_destinatarios as d2')
                        ->whereColumn('d2.id_hilo', 'h.id')
                        ->where('d2.tipo_destinatario', 'familia')
                        ->where('d2.id_legajo', $idLegajo);
                });
            })
            ->when($direccion === 'recibidos', function ($q) use ($idLegajo) {
                $q->where(function ($w) use ($idLegajo) {
                    $w->where('h.creado_por_tipo', '!=', 'familia')
                        ->orWhere('h.creado_por_id', '!=', $idLegajo);
                });
            })
            ->when($direccion === 'enviados', function ($q) use ($idLegajo) {
                $q->where('h.creado_por_tipo', 'familia')
                    ->where('h.creado_por_id', $idLegajo);
            })
            ->where('h.id_nivel', $idNivel)
            ->where('h.id_terlec', $idTerlec)
            ->leftJoin('com_mensajes_destinatarios as d', function ($j) use ($idLegajo) {
                $j->on('d.id_hilo', '=', 'h.id')
                  ->where('d.tipo_destinatario', 'familia')
                  ->where('d.id_legajo', $idLegajo);
            })
            ->select([
                'h.id', 'h.asunto', 'h.scope', 'h.estado',
                'h.creado_por_tipo', 'h.creado_por_id', 'h.creado_por_rol',
                'h.familia_puede_responder',
                'h.ultimo_mensaje_at', 'h.created_at',
                DB::raw('SUM(CASE WHEN d.leido_at IS NULL AND d.id IS NOT NULL THEN 1 ELSE 0 END) as no_leidos'),
                DB::raw('SUM(CASE WHEN d.respondido_at IS NOT NULL THEN 1 ELSE 0 END) as respondidos'),
                DB::raw("CASE WHEN h.creado_por_tipo = 'familia' AND h.creado_por_id = {$idLegajo} THEN 'enviado' ELSE 'recibido' END as direccion"),
                DB::raw('(SELECT COUNT(*) FROM com_mensajes mx WHERE mx.id_hilo = h.id) as mensajes_count'),
                DB::raw('(SELECT m.contenido FROM com_mensajes m WHERE m.id = h.cuerpo_inicial_id LIMIT 1) as cuerpo_inicial_contenido'),
                DB::raw("(SELECT COUNT(DISTINCT d0.id_profesor) FROM com_mensajes_destinatarios d0 WHERE d0.id_mensaje = h.cuerpo_inicial_id AND d0.tipo_destinatario = 'profesor' AND d0.id_profesor IS NOT NULL) as destinatarios_prof_count"),
                DB::raw("(SELECT GROUP_CONCAT(DISTINCT NULLIF(TRIM(d0.nombre_snapshot), '') ORDER BY d0.id_profesor SEPARATOR '||') FROM com_mensajes_destinatarios d0 WHERE d0.id_mensaje = h.cuerpo_inicial_id AND d0.tipo_destinatario = 'profesor') as destinatarios_prof_nombres_concat"),
                DB::raw('(SELECT m0.tipo_remitente FROM com_mensajes m0 WHERE m0.id = h.cuerpo_inicial_id LIMIT 1) as cuerpo_inicial_tipo'),
                DB::raw('(SELECT m0.nombre_remitente_snapshot FROM com_mensajes m0 WHERE m0.id = h.cuerpo_inicial_id LIMIT 1) as cuerpo_inicial_nombre'),
                DB::raw('(SELECT m0.vinculo_familiar FROM com_mensajes m0 WHERE m0.id = h.cuerpo_inicial_id LIMIT 1) as cuerpo_inicial_vinculo'),
            ])
            ->groupBy('h.id', 'h.asunto', 'h.scope', 'h.estado', 'h.creado_por_tipo',
                      'h.creado_por_id', 'h.creado_por_rol', 'h.familia_puede_responder',
                      'h.ultimo_mensaje_at', 'h.created_at', 'h.cuerpo_inicial_id')
            ->orderByDesc('h.ultimo_mensaje_at');

        if ($filtro === 'no_leidos') {
            $query->havingRaw('SUM(CASE WHEN d.leido_at IS NULL AND d.id IS NOT NULL THEN 1 ELSE 0 END) > 0');
        } elseif ($filtro === 'respondidos') {
            $query->havingRaw('SUM(CASE WHEN d.respondido_at IS NOT NULL THEN 1 ELSE 0 END) > 0');
        }

        return $query->get();
    }

    /**
     * Marca como leído todos los mensajes de un hilo para un destinatario.
     */
    public static function marcarLeidoHiloProfesor(int $idHilo, int $idProfesor): void
    {
        ComMensajeDestinatario::query()
            ->where('id_hilo', $idHilo)
            ->where('tipo_destinatario', 'profesor')
            ->where('id_profesor', $idProfesor)
            ->whereNull('leido_at')
            ->update(['leido_at' => now()]);
    }

    public static function marcarLeidoHiloFamilia(int $idHilo, int $idLegajo): void
    {
        ComMensajeDestinatario::query()
            ->where('id_hilo', $idHilo)
            ->where('tipo_destinatario', 'familia')
            ->where('id_legajo', $idLegajo)
            ->whereNull('leido_at')
            ->update(['leido_at' => now()]);
    }

    /**
     * Marca un mensaje concreto como no leído para el profesor (solo mensajes recibidos desde familia).
     */
    public static function marcarNoLeidoMensajeProfesor(
        int $idMensaje,
        int $idHilo,
        int $idProfesor,
        int $idNivel,
        int $idTerlec
    ): bool {
        if (! ComHilo::query()
            ->where('id', $idHilo)
            ->where('id_nivel', $idNivel)
            ->where('id_terlec', $idTerlec)
            ->exists()) {
            return false;
        }

        $msg = ComMensaje::query()
            ->where('id', $idMensaje)
            ->where('id_hilo', $idHilo)
            ->where('tipo_remitente', 'familia')
            ->first();

        if ($msg === null) {
            return false;
        }

        $affected = ComMensajeDestinatario::query()
            ->where('id_mensaje', $idMensaje)
            ->where('id_hilo', $idHilo)
            ->where('tipo_destinatario', 'profesor')
            ->where('id_profesor', $idProfesor)
            ->whereNotNull('leido_at')
            ->update(['leido_at' => null]);

        return $affected > 0;
    }

    /**
     * Marca un mensaje concreto como no leído para la familia (solo mensajes recibidos desde la escuela).
     */
    public static function marcarNoLeidoMensajeFamilia(
        int $idMensaje,
        int $idHilo,
        int $idLegajo,
        int $idNivel,
        int $idTerlec
    ): bool {
        if (! ComHilo::query()
            ->where('id', $idHilo)
            ->where('id_nivel', $idNivel)
            ->where('id_terlec', $idTerlec)
            ->exists()) {
            return false;
        }

        $msg = ComMensaje::query()
            ->where('id', $idMensaje)
            ->where('id_hilo', $idHilo)
            ->where('tipo_remitente', 'profesor')
            ->first();

        if ($msg === null) {
            return false;
        }

        $affected = ComMensajeDestinatario::query()
            ->where('id_mensaje', $idMensaje)
            ->where('id_hilo', $idHilo)
            ->where('tipo_destinatario', 'familia')
            ->where('id_legajo', $idLegajo)
            ->whereNotNull('leido_at')
            ->update(['leido_at' => null]);

        return $affected > 0;
    }

    /**
     * Crea un nuevo hilo con su primer mensaje y destinatarios.
     *
     * @param array{
     *   asunto: string,
     *   contenido: string,
     *   scope: string,
     *   id_legajos: list<int>,
     *   id_curso: ?int,
     *   cursos_envio: ?list<array{id:int,label:string}>,
     *   id_nivel: int,
     *   id_terlec: int,
     *   creado_por_tipo: string,
     *   creado_por_id: int,
     *   creado_por_rol: string,
     *   rol_receptor: string,
     *   vinculo_familiar: ?string,
     *   nombre_remitente: ?string,
     *   dni_remitente: ?string,
     *   destinatarios_profesores: list<int>,
     *   familia_puede_responder?: bool,
     * } $datos
     * @param list<string> $mediosCanal
     */
    public static function crearHiloConMensaje(array $datos, array $mediosCanal): ComHilo
    {
        return DB::transaction(function () use ($datos, $mediosCanal) {
            // 1. Hilo
            $hilo = ComHilo::create([
                'asunto'                  => $datos['asunto'],
                'scope'                   => $datos['scope'],
                'id_legajo'               => $datos['id_legajos'][0] ?? null,
                'id_curso'                => $datos['id_curso'] ?? null,
                'cursos_envio'            => $datos['cursos_envio'] ?? null,
                'id_nivel'                => $datos['id_nivel'],
                'id_terlec'               => $datos['id_terlec'],
                'creado_por_tipo'         => $datos['creado_por_tipo'],
                'creado_por_id'           => $datos['creado_por_id'],
                'creado_por_rol'          => $datos['creado_por_rol'],
                'estado'                  => 'abierto',
                'familia_puede_responder' => (bool) ($datos['familia_puede_responder'] ?? true),
                'ultimo_mensaje_at'       => now(),
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);

            // 2. Primer mensaje
            $mensaje = ComMensaje::create([
                'id_hilo'                   => $hilo->id,
                'tipo_remitente'            => $datos['creado_por_tipo'],
                'id_profesor'               => $datos['creado_por_tipo'] === 'profesor' ? $datos['creado_por_id'] : null,
                'id_legajo'                 => $datos['creado_por_tipo'] === 'familia' ? $datos['creado_por_id'] : null,
                'rol_remitente'             => $datos['creado_por_rol'],
                'vinculo_familiar'          => $datos['vinculo_familiar'] ?? null,
                'nombre_remitente_snapshot' => $datos['nombre_remitente'] ?? null,
                'dni_remitente_snapshot'    => $datos['dni_remitente'] ?? null,
                'contenido'                 => $datos['contenido'],
                'fecha'                     => now()->toDateString(),
                'hora'                      => now()->toTimeString(),
                'created_at'                => now(),
            ]);

            // Vincula el primer mensaje al hilo
            $hilo->update(['cuerpo_inicial_id' => $mensaje->id]);

            // 3. Destinatarios: familias (legajos)
            foreach ($datos['id_legajos'] as $idLegajo) {
                $legajo = Legajo::find($idLegajo);
                ComMensajeDestinatario::create([
                    'id_mensaje'        => $mensaje->id,
                    'id_hilo'           => $hilo->id,
                    'tipo_destinatario' => 'familia',
                    'id_legajo'         => $idLegajo,
                    'rol_destinatario'  => 'familia',
                    'nombre_snapshot'   => $legajo ? trim("{$legajo->apellido}, {$legajo->nombre}") : null,
                    'dni_snapshot'      => $legajo?->dni ?? null,
                ]);
            }

            // 4. Destinatarios: profesores (cuando la familia escribe a la escuela)
            foreach (($datos['destinatarios_profesores'] ?? []) as $idProf) {
                $prof = Profesor::find($idProf);
                ComMensajeDestinatario::create([
                    'id_mensaje'        => $mensaje->id,
                    'id_hilo'           => $hilo->id,
                    'tipo_destinatario' => 'profesor',
                    'id_profesor'       => $idProf,
                    'rol_destinatario'  => $datos['rol_receptor'],
                    'nombre_snapshot'   => $prof ? trim("{$prof->apellido}, {$prof->nombre}") : null,
                    'dni_snapshot'      => $prof?->dni ?? null,
                ]);
            }

            // 5. Distribuir por medios
            $mensaje->load('hilo');
            \App\Comunicaciones\Distribuidor::distribuir($mensaje, $mediosCanal);

            return $hilo;
        });
    }

    /**
     * Agrega una respuesta a un hilo existente y actualiza estados.
     *
     * @param list<string> $mediosCanal
     */
    public static function responder(
        int $idHilo,
        string $tipoRemitente,
        int $idRemitente,
        string $rolRemitente,
        string $contenido,
        array $mediosCanal,
        ?string $vinculo = null,
        ?string $nombreSnapshot = null,
        ?string $dniSnapshot = null,
        ?int $idMensajePadre = null
    ): ComMensaje {
        return DB::transaction(function () use (
            $idHilo, $tipoRemitente, $idRemitente, $rolRemitente,
            $contenido, $mediosCanal, $vinculo, $nombreSnapshot, $dniSnapshot, $idMensajePadre
        ) {
            $hilo = ComHilo::findOrFail($idHilo);

            $mensaje = ComMensaje::create([
                'id_hilo'                   => $idHilo,
                'id_mensaje_padre'          => $idMensajePadre,
                'tipo_remitente'            => $tipoRemitente,
                'id_profesor'               => $tipoRemitente === 'profesor' ? $idRemitente : null,
                'id_legajo'                 => $tipoRemitente === 'familia' ? $idRemitente : null,
                'rol_remitente'             => $rolRemitente,
                'vinculo_familiar'          => $tipoRemitente === 'familia' ? $vinculo : null,
                'nombre_remitente_snapshot' => $nombreSnapshot,
                'dni_remitente_snapshot'    => $dniSnapshot,
                'contenido'                 => $contenido,
                'fecha'                     => now()->toDateString(),
                'hora'                      => now()->toTimeString(),
                'created_at'                => now(),
            ]);

            // Marca como respondido en los destinatarios del hilo que correspondan
            // (el que acaba de responder marcó su propia entrada como respondida)
            if ($tipoRemitente === 'profesor') {
                ComMensajeDestinatario::query()
                    ->where('id_hilo', $idHilo)
                    ->where('tipo_destinatario', 'profesor')
                    ->where('id_profesor', $idRemitente)
                    ->whereNull('respondido_at')
                    ->update([
                        'respondido_at'       => now(),
                        'id_mensaje_respuesta' => $mensaje->id,
                    ]);
            } else {
                ComMensajeDestinatario::query()
                    ->where('id_hilo', $idHilo)
                    ->where('tipo_destinatario', 'familia')
                    ->where('id_legajo', $idRemitente)
                    ->whereNull('respondido_at')
                    ->update([
                        'respondido_at'       => now(),
                        'id_mensaje_respuesta' => $mensaje->id,
                    ]);
            }

            // Crear destinatarios para la respuesta (al revés del original)
            static::crearDestinatariosRespuesta($hilo, $mensaje, $tipoRemitente, $idRemitente);

            // Actualiza timestamp del hilo
            $hilo->update(['ultimo_mensaje_at' => now()]);

            // Distribuir
            $mensaje->load('hilo');
            \App\Comunicaciones\Distribuidor::distribuir($mensaje, $mediosCanal);

            return $mensaje;
        });
    }

    /**
     * Para una respuesta, los destinatarios son los participantes del hilo
     * del tipo opuesto al remitente.
     */
    private static function crearDestinatariosRespuesta(
        ComHilo $hilo,
        ComMensaje $mensaje,
        string $tipoRemitente,
        int $idRemitente
    ): void {
        $tipoDestino = $tipoRemitente === 'profesor' ? 'familia' : 'profesor';

        // Usar participantes del hilo del tipo opuesto
        $participantes = ComHiloParticipante::query()
            ->where('id_hilo', $hilo->id)
            ->where('tipo', $tipoDestino)
            ->get();

        if ($participantes->isEmpty()) {
            // Si no hay participantes registrados, crear destinatarios desde
            // los que enviaron mensajes en este hilo del tipo opuesto
            $idsEnHilo = ComMensaje::query()
                ->where('id_hilo', $hilo->id)
                ->where('tipo_remitente', $tipoDestino)
                ->when($tipoDestino === 'profesor', fn ($q) => $q->whereNotNull('id_profesor'))
                ->when($tipoDestino === 'familia',  fn ($q) => $q->whereNotNull('id_legajo'))
                ->distinct()
                ->get($tipoDestino === 'profesor' ? ['id_profesor'] : ['id_legajo']);

            foreach ($idsEnHilo as $row) {
                $id = $tipoDestino === 'profesor' ? $row->id_profesor : $row->id_legajo;
                static::insertarDestinatario($mensaje, $hilo->id, $tipoDestino, (int) $id);
            }
        } else {
            foreach ($participantes as $p) {
                $id = $tipoDestino === 'profesor' ? (int) $p->id_profesor : (int) $p->id_legajo;
                static::insertarDestinatario($mensaje, $hilo->id, $tipoDestino, $id);
            }
        }
    }

    private static function insertarDestinatario(
        ComMensaje $mensaje,
        int $idHilo,
        string $tipo,
        int $id
    ): void {
        if ($tipo === 'profesor') {
            $prof = Profesor::find($id);
            ComMensajeDestinatario::create([
                'id_mensaje'        => $mensaje->id,
                'id_hilo'           => $idHilo,
                'tipo_destinatario' => 'profesor',
                'id_profesor'       => $id,
                'rol_destinatario'  => 'profesor',
                'nombre_snapshot'   => $prof ? trim("{$prof->apellido}, {$prof->nombre}") : null,
                'dni_snapshot'      => $prof?->dni ?? null,
            ]);
        } else {
            $legajo = Legajo::find($id);
            ComMensajeDestinatario::create([
                'id_mensaje'        => $mensaje->id,
                'id_hilo'           => $idHilo,
                'tipo_destinatario' => 'familia',
                'id_legajo'         => $id,
                'rol_destinatario'  => 'familia',
                'nombre_snapshot'   => $legajo ? trim("{$legajo->apellido}, {$legajo->nombre}") : null,
                'dni_snapshot'      => $legajo?->dni ?? null,
            ]);
        }
    }

    /**
     * Retorna profesores del nivel/terlec de un rol específico.
     *
     * @return list<array{id:int,label:string,rol:string}>
     */
    public static function profesoresPorRol(int $idNivel, string $rol): array
    {
        return DB::table('profesores as p')
            ->join('profesortipo as pt', 'pt.id', '=', 'p.IdTipoProf')
            ->where('p.nivel', $idNivel)
            ->get(['p.id', 'p.apellido', 'p.nombre', 'pt.tipo'])
            ->filter(function ($r) use ($rol) {
                return CanalesPolicy::normalizarRolProfesor((string) $r->tipo) === $rol;
            })
            ->map(fn ($r) => [
                'id'    => (int) $r->id,
                'label' => trim("{$r->apellido}, {$r->nombre}"),
                'rol'   => $rol,
            ])
            ->values()
            ->all();
    }

    /**
     * Retorna el preceptor(es) del curso de un legajo.
     *
     * @return list<array{id:int,label:string,rol:string}>
     */
    public static function preceptoresDeCurso(int $idLegajo, int $idNivel, int $idTerlec): array
    {
        // Busca en matricula el idCursos del legajo en el terlec actual
        $idCurso = DB::table('matricula')
            ->where('idLegajos', $idLegajo)
            ->where('idNivel', $idNivel)
            ->where('idTerlec', $idTerlec)
            ->value('idCursos');

        if (! $idCurso) {
            return [];
        }

        // Por ahora retorna todos los preceptores del nivel
        return static::profesoresPorRol($idNivel, 'preceptor');
    }
}
