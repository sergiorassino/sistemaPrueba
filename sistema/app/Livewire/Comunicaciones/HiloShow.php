<?php

namespace App\Livewire\Comunicaciones;

use App\Comunicaciones\CanalesPolicy;
use App\Comunicaciones\ComunicacionesRepository;
use App\Models\ComHilo;
use App\Models\ComMensaje;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class HiloShow extends Component
{
    public int $idHilo;
    public string $respuesta = '';
    public bool $mostrarFormRespuesta = false;

    public function mount(int $id): void
    {
        abort_unless(tienePermiso(51), 403, 'Sin permiso para ver comunicaciones.');

        $hilo = ComHilo::where('id', $id)
            ->where('id_nivel', (int) schoolCtx()->idNivel)
            ->where('id_terlec', (int) schoolCtx()->idTerlec)
            ->first();

        abort_if($hilo === null, 404);

        $this->idHilo = $id;
        $this->marcarLeido();
    }

    private function marcarLeido(): void
    {
        ComunicacionesRepository::marcarLeidoHiloProfesor($this->idHilo, (int) schoolCtx()->idProfesor);
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
        $hilo   = ComHilo::with(['mensajes.destinatarios.envios'])->findOrFail($this->idHilo);

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

        return view('livewire.comunicaciones.hilo-show', [
            'hilo'            => $hilo,
            'mensajesPorDia'  => $mensajesPorDia,
            'puedeResponder'  => $puedeResponder,
            'maxContenido'    => config('comunicaciones.max_contenido', 2000),
        ])->layout('layouts.app', ['pageTitle' => $hilo->asunto]);
    }
}
