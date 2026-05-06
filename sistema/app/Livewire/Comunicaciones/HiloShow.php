<?php

namespace App\Livewire\Comunicaciones;

use App\Comunicaciones\CanalesPolicy;
use App\Comunicaciones\ComunicacionesRepository;
use App\Models\ComHilo;
use App\Models\ComMensaje;
use App\Models\ComMensajeDestinatario;
use App\Models\ComMensajeEnvio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class HiloShow extends Component
{
    public int $idHilo;
    public string $respuesta = '';
    public bool $mostrarFormRespuesta = false;

    public bool $modalBorrarAbierto = false;

    /** @var int|null */
    public ?int $modalBorrarMensajeId = null;

    public bool $modalBorrarEliminaHiloCompleto = false;

    public function mount(int $id): void
    {
        abort_unless(tienePermiso(51), 403, 'Sin permiso para ver comunicaciones.');

        $ctx = schoolCtx();

        $hilo = ComHilo::where('id', $id)
            ->where('id_nivel', (int) $ctx->idNivel)
            ->where('id_terlec', (int) $ctx->idTerlec)
            ->first();

        abort_if($hilo === null, 404);

        // Seguridad: sin permiso de revisión, solo puede abrir hilos donde participa
        // (creador o destinatario en el nivel/terlec del contexto).
        if (! tienePermiso(56)) {
            $puede = ComunicacionesRepository::profesorPuedeVerHilo(
                (int) $hilo->id,
                (int) $ctx->idProfesor,
                (int) $ctx->idNivel,
                (int) $ctx->idTerlec
            );
            abort_unless($puede, 403, 'Sin permiso para ver este hilo.');
        }

        $this->idHilo = $id;
        $this->marcarLeido();
    }

    private function marcarLeido(): void
    {
        ComunicacionesRepository::marcarLeidoHiloProfesor($this->idHilo, (int) schoolCtx()->idProfesor);
    }

    public function marcarMensajeNoLeido(int $idMensaje): void
    {
        abort_unless(tienePermiso(51), 403, 'Sin permiso para ver comunicaciones.');

        $key = 'com:unread:' . (auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 40)) {
            $this->addError('marcarNoLeido', 'Demasiadas acciones. Espere un momento.');

            return;
        }
        RateLimiter::hit($key, 60);

        $ctx = schoolCtx();
        $ok  = ComunicacionesRepository::marcarNoLeidoMensajeProfesor(
            $idMensaje,
            $this->idHilo,
            (int) $ctx->idProfesor,
            (int) $ctx->idNivel,
            (int) $ctx->idTerlec
        );

        if (! $ok) {
            $this->addError('marcarNoLeido', 'No se pudo marcar como no leído.');

            return;
        }

        $this->resetErrorBag('marcarNoLeido');
        session()->now('success', 'Mensaje marcado como no leído.');
    }

    /**
     * @return array{puede:bool,motivo:string}
     */
    public function infoBorradoMensaje(ComMensaje $msg, ?int $cuerpoInicialId = null, ?int $mensajesEnHilo = null): array
    {
        $ctx = schoolCtx();
        $idProf = (int) $ctx->idProfesor;

        if ((int) $msg->id_hilo !== (int) $this->idHilo) {
            return ['puede' => false, 'motivo' => 'Mensaje fuera del hilo.'];
        }

        if ($cuerpoInicialId === null) {
            $cuerpoInicialId = (int) (ComHilo::query()
                ->where('id', (int) $this->idHilo)
                ->where('id_nivel', (int) $ctx->idNivel)
                ->where('id_terlec', (int) $ctx->idTerlec)
                ->value('cuerpo_inicial_id') ?? 0);
        }

        if ($mensajesEnHilo === null) {
            $mensajesEnHilo = (int) ComMensaje::query()
                ->where('id_hilo', (int) $this->idHilo)
                ->count();
        }

        if ($cuerpoInicialId === (int) $msg->id && $mensajesEnHilo > 1) {
            return ['puede' => false, 'motivo' => 'No se puede borrar el mensaje inicial si ya hay mensajes posteriores.'];
        }

        if (property_exists($msg, 'respuestas_count') && (int) $msg->respuestas_count > 0) {
            return ['puede' => false, 'motivo' => 'No se puede borrar un mensaje que tiene respuestas.'];
        }

        $esPropio = $msg->tipo_remitente === 'profesor' && (int) $msg->id_profesor === $idProf;
        if ($esPropio) {
            if (! tienePermiso(54)) {
                return ['puede' => false, 'motivo' => 'Sin permiso para borrar mensajes propios.'];
            }
            return ['puede' => true, 'motivo' => ''];
        }

        if (! tienePermiso(55)) {
            return ['puede' => false, 'motivo' => 'Sin permiso para borrar mensajes ajenos.'];
        }

        return ['puede' => true, 'motivo' => ''];
    }

    public function puedeBorrarMensaje(ComMensaje $msg): bool
    {
        return (bool) ($this->infoBorradoMensaje($msg)['puede'] ?? false);
    }

    public function abrirModalBorrar(int $idMensaje): void
    {
        abort_unless(tienePermiso(51), 403);

        $ctx = schoolCtx();

        $hilo = ComHilo::query()
            ->where('id', (int) $this->idHilo)
            ->where('id_nivel', (int) $ctx->idNivel)
            ->where('id_terlec', (int) $ctx->idTerlec)
            ->first();

        abort_if($hilo === null, 404);

        $msg = ComMensaje::query()
            ->where('id', (int) $idMensaje)
            ->where('id_hilo', (int) $hilo->id)
            ->withCount('respuestas')
            ->first();

        if ($msg === null) {
            $this->addError('modalBorrar', 'No se encontró el mensaje.');
            return;
        }

        $cant = (int) ComMensaje::query()->where('id_hilo', (int) $hilo->id)->count();
        $info = $this->infoBorradoMensaje($msg, (int) ($hilo->cuerpo_inicial_id ?? 0), $cant);
        if (! $info['puede']) {
            $this->addError('modalBorrar', $info['motivo']);
            return;
        }

        $this->resetErrorBag('modalBorrar');

        $this->modalBorrarMensajeId = (int) $msg->id;
        $this->modalBorrarEliminaHiloCompleto = ((int) ($hilo->cuerpo_inicial_id ?? 0) === (int) $msg->id) && $cant === 1;
        $this->modalBorrarAbierto = true;
    }

    public function cerrarModalBorrar(): void
    {
        $this->modalBorrarAbierto = false;
        $this->modalBorrarMensajeId = null;
        $this->modalBorrarEliminaHiloCompleto = false;
        $this->resetErrorBag('modalBorrar');
    }

    public function confirmarModalBorrar(): void
    {
        $id = $this->modalBorrarMensajeId;
        $this->cerrarModalBorrar();

        if ($id === null) {
            return;
        }

        $this->borrarMensaje($id);
    }

    public function borrarMensaje(int $idMensaje): void
    {
        abort_unless(tienePermiso(51), 403);

        $key = 'com:del:' . (auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 10)) {
            session()->flash('success', 'Demasiadas acciones. Espere un momento.');
            return;
        }
        RateLimiter::hit($key, 60);

        $ctx = schoolCtx();
        $idProf = (int) $ctx->idProfesor;

        $hilo = ComHilo::query()
            ->where('id', (int) $this->idHilo)
            ->where('id_nivel', (int) $ctx->idNivel)
            ->where('id_terlec', (int) $ctx->idTerlec)
            ->first();

        abort_if($hilo === null, 404);

        $msg = ComMensaje::query()
            ->where('id', (int) $idMensaje)
            ->where('id_hilo', (int) $hilo->id)
            ->withCount('respuestas')
            ->first();

        abort_if($msg === null, 404);

        $borrarHilo = false;
        if ((int) $hilo->cuerpo_inicial_id === (int) $msg->id) {
            $cant = ComMensaje::query()
                ->where('id_hilo', (int) $hilo->id)
                ->count();
            abort_if($cant > 1, 403, 'No se puede borrar el mensaje inicial si ya hay mensajes posteriores.');
            $borrarHilo = true; // si el inicial era el único mensaje, se elimina el hilo completo
        }

        abort_if((int) $msg->respuestas_count > 0, 403, 'No se puede borrar un mensaje que tiene respuestas.');

        $esPropio = $msg->tipo_remitente === 'profesor' && (int) $msg->id_profesor === $idProf;
        if ($esPropio) {
            abort_unless(tienePermiso(54), 403, 'Sin permiso para borrar mensajes propios.');
        } else {
            abort_unless(tienePermiso(55), 403, 'Sin permiso para borrar mensajes ajenos.');
        }

        DB::transaction(function () use ($msg, $hilo, $borrarHilo) {
            if ($borrarHilo) {
                // Cascadas FK borran mensajes, destinatarios, envíos y participantes.
                ComHilo::query()
                    ->where('id', (int) $hilo->id)
                    ->delete();
                return;
            }

            $destIds = ComMensajeDestinatario::query()
                ->where('id_mensaje', (int) $msg->id)
                ->pluck('id')
                ->map(fn ($v) => (int) $v)
                ->all();

            if (count($destIds)) {
                ComMensajeEnvio::query()
                    ->whereIn('id_mensaje_destinatario', $destIds)
                    ->delete();
            }

            ComMensajeDestinatario::query()
                ->where('id_mensaje', (int) $msg->id)
                ->delete();

            ComMensaje::query()
                ->where('id', (int) $msg->id)
                ->delete();

            $ultimo = ComMensaje::query()
                ->where('id_hilo', (int) $hilo->id)
                ->max('created_at');

            $hilo->update([
                'ultimo_mensaje_at' => $ultimo ?? $hilo->created_at ?? now(),
            ]);
        });

        if ($borrarHilo) {
            session()->flash('success', 'Hilo eliminado.');
            $this->redirectRoute('comunicaciones.index');
            return;
        }

        session()->flash('success', 'Mensaje borrado.');
    }

    public function responder(): void
    {
        abort_unless(tienePermiso(51), 403);

        $key = 'com:resp:' . (auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, config('comunicaciones.rate_limit_max', 20))) {
            $this->addError('respuesta', 'Demasiados envíos. Espere un momento.');
            return;
        }
        RateLimiter::hit($key, config('comunicaciones.rate_limit_decay', 60));

        $this->validate([
            'respuesta' => 'required|string|max:' . config('comunicaciones.max_contenido', 2000),
        ]);

        $ctx      = schoolCtx();
        $idProf   = (int) $ctx->idProfesor;
        $profesor = $ctx->profesor();

        abort_if($profesor === null, 403);

        $rolEmisor  = CanalesPolicy::rolDeProfesor($profesor);
        $rolReceptor = 'familia';

        if (! CanalesPolicy::puedeResponder($rolEmisor, $rolReceptor)) {
            $this->addError('respuesta', 'Su rol no puede responder a este comunicado.');
            return;
        }

        $medios   = CanalesPolicy::mediosPermitidos($rolEmisor, $rolReceptor);
        $nombreProf = trim("{$profesor->apellido}, {$profesor->nombre}");

        ComunicacionesRepository::responder(
            idHilo: $this->idHilo,
            tipoRemitente: 'profesor',
            idRemitente: $idProf,
            rolRemitente: $rolEmisor,
            contenido: $this->respuesta,
            mediosCanal: $medios,
            nombreSnapshot: $nombreProf,
            dniSnapshot: (string) ($profesor->dni ?? '')
        );

        $this->respuesta              = '';
        $this->mostrarFormRespuesta   = false;
        session()->flash('success', 'Respuesta enviada.');
    }

    public function render()
    {
        $ctx    = schoolCtx();
        $hilo   = ComHilo::with([
            'mensajes' => function ($q) {
                $q->withCount('respuestas')
                    ->with(['destinatarios.envios', 'hilo'])
                    ->orderBy('created_at');
            },
        ])->findOrFail($this->idHilo);

        // Verificar acceso al nivel/terlec
        abort_if(
            $hilo->id_nivel !== (int) $ctx->idNivel || $hilo->id_terlec !== (int) $ctx->idTerlec,
            404
        );

        // Verificar si puede responder
        $profesor        = $ctx->profesor();
        $rolEmisor       = $profesor ? CanalesPolicy::rolDeProfesor($profesor) : null;
        $puedeResponder  = $rolEmisor !== null && CanalesPolicy::puedeResponder($rolEmisor, 'familia');

        // Agrupar mensajes por fecha
        $mensajesPorDia = $hilo->mensajes->groupBy(fn ($m) => $m->created_at?->toDateString());

        $paraCompleto = null;
        if ($hilo->creado_por_tipo === 'profesor') {
            if ($hilo->scope === 'colegio') {
                $paraCompleto = 'Todo el colegio';
            } elseif (in_array($hilo->scope, ['curso', 'varios_cursos'], true)) {
                $labels = [];
                if (is_array($hilo->cursos_envio)) {
                    foreach ($hilo->cursos_envio as $row) {
                        if (is_array($row) && isset($row['label']) && trim((string) $row['label']) !== '') {
                            $labels[] = trim((string) $row['label']);
                        }
                    }
                }
                if (count($labels) === 0 && $hilo->id_curso) {
                    $cursoLabel = DB::table('cursos as c')
                        ->leftJoin('curplan as cp', 'cp.id', '=', 'c.idCurPlan')
                        ->where('c.Id', (int) $hilo->id_curso)
                        ->value(DB::raw("CASE WHEN TRIM(COALESCE(c.cursec, '')) <> '' THEN TRIM(c.cursec) ELSE TRIM(COALESCE(cp.curPlanCurso, 'Curso')) END"));
                    if ($cursoLabel !== null && trim((string) $cursoLabel) !== '') {
                        $labels[] = trim((string) $cursoLabel);
                    }
                }
                $paraCompleto = count($labels) ? implode(' · ', $labels) : 'Cursos';
            } else {
                $nombres = ComMensajeDestinatario::query()
                    ->where('id_mensaje', (int) $hilo->cuerpo_inicial_id)
                    ->where('tipo_destinatario', 'familia')
                    ->whereNotNull('nombre_snapshot')
                    ->orderBy('id_legajo')
                    ->pluck('nombre_snapshot')
                    ->map(fn ($s) => trim((string) $s))
                    ->filter(fn ($s) => $s !== '')
                    ->unique()
                    ->values()
                    ->all();
                $paraCompleto = count($nombres) ? implode(' · ', $nombres) : '—';
            }
        }

        return view('livewire.comunicaciones.hilo-show', [
            'hilo'               => $hilo,
            'mensajesPorDia'     => $mensajesPorDia,
            'puedeResponder'     => $puedeResponder,
            'maxContenido'       => config('comunicaciones.max_contenido', 2000),
            'paraCompleto'       => $paraCompleto,
            'idProfesorSesion'   => (int) $ctx->idProfesor,
        ])->layout('layouts.app', ['pageTitle' => $hilo->asunto]);
    }
}
