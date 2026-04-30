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
     * @return \Illuminate\Support\Collection
     */
    public static function bandejaProfesor(
        int $idProfesor,
        int $idNivel,
        int $idTerlec,
        string $filtro = 'todos'
    ) {
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
            ->where('h.id_nivel', $idNivel)
            ->where('h.id_terlec', $idTerlec)
            ->leftJoin('com_mensajes_destinatarios as d', function ($j) use ($idProfesor) {
                $j->on('d.id_hilo', '=', 'h.id')
                  ->where('d.tipo_destinatario', 'profesor')
                  ->where('d.id_profesor', $idProfesor);
            })
            ->select([
                'h.id', 'h.asunto', 'h.scope', 'h.estado',
                'h.creado_por_tipo', 'h.creado_por_id', 'h.creado_por_rol',
                'h.familia_puede_responder',
                'h.ultimo_mensaje_at', 'h.created_at',
                DB::raw('SUM(CASE WHEN d.leido_at IS NULL AND d.id IS NOT NULL THEN 1 ELSE 0 END) as no_leidos'),
                DB::raw('SUM(CASE WHEN d.respondido_at IS NOT NULL THEN 1 ELSE 0 END) as respondidos'),
                DB::raw('COUNT(d.id) as total_dest'),
            ])
            ->groupBy('h.id', 'h.asunto', 'h.scope', 'h.estado', 'h.creado_por_tipo',
                      'h.creado_por_id', 'h.creado_por_rol', 'h.familia_puede_responder',
                      'h.ultimo_mensaje_at', 'h.created_at')
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
     * @return \Illuminate\Support\Collection
     */
    public static function bandejaFamilia(int $idLegajo, int $idNivel, int $idTerlec, string $filtro = 'todos')
    {
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
            ])
            ->groupBy('h.id', 'h.asunto', 'h.scope', 'h.estado', 'h.creado_por_tipo',
                      'h.creado_por_id', 'h.creado_por_rol', 'h.familia_puede_responder',
                      'h.ultimo_mensaje_at', 'h.created_at')
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
     * Crea un nuevo hilo con su primer mensaje y destinatarios.
     *
     * @param array{
     *   asunto: string,
     *   contenido: string,
     *   scope: string,
     *   id_legajos: list<int>,
     *   id_curso: ?int,
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
