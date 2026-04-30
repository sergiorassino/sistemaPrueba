<?php

namespace App\Livewire\Alumnos\Comunicaciones;

use App\Comunicaciones\CanalesPolicy;
use App\Comunicaciones\ComunicacionesRepository;
use App\Models\ComHilo;
use App\Models\Legajo;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class HiloShowFamilia extends Component
{
    public int $idHilo;
    public string $vinculo   = '';
    public string $respuesta = '';
    public bool $mostrarFormRespuesta = false;

    public function mount(int $id): void
    {
        $ctx = studentCtx();
        $hilo = ComHilo::where('id', $id)
            ->where('id_nivel', (int) $ctx->idNivel)
            ->where('id_terlec', (int) $ctx->idTerlec)
            ->first();

        abort_if($hilo === null, 404);

        $this->idHilo = $id;
        ComunicacionesRepository::marcarLeidoHiloFamilia($id, (int) $ctx->idLegajo);
    }

    public function responder(): void
    {
        $key = 'com:resp:fam:' . (auth('alumno')->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, config('comunicaciones.rate_limit_max', 20))) {
            $this->addError('respuesta', 'Demasiados envíos. Espere un momento.');
            return;
        }
        RateLimiter::hit($key, config('comunicaciones.rate_limit_decay', 60));

        $this->validate([
            'vinculo'   => 'required|in:madre,padre,tutor,resp_admin,otro',
            'respuesta' => 'required|string|max:' . config('comunicaciones.max_contenido', 2000),
        ]);

        $ctx      = studentCtx();
        $idLegajo = (int) $ctx->idLegajo;
        $legajo   = Legajo::find($idLegajo);

        // Determinar el rol receptor del hilo (el que creó el hilo desde la escuela)
        $hilo        = ComHilo::findOrFail($this->idHilo);
        $rolReceptor = (string) ($hilo->creado_por_rol ?? 'preceptor');

        if (! $hilo->familiaPuedeEnviarRespuestas()) {
            $this->addError('respuesta', 'Este comunicado es solo informativo; no admite respuestas.');
            return;
        }

        if (! CanalesPolicy::puedeResponder('familia', $rolReceptor)) {
            $this->addError('respuesta', 'No puede responder a este tipo de comunicado.');
            return;
        }

        $medios = CanalesPolicy::mediosPermitidos('familia', $rolReceptor);

        [$nombreSnap, $dniSnap] = $this->snapshotDatosFamiliares($legajo, $this->vinculo);

        ComunicacionesRepository::responder(
            idHilo: $this->idHilo,
            tipoRemitente: 'familia',
            idRemitente: $idLegajo,
            rolRemitente: 'familia',
            contenido: $this->respuesta,
            mediosCanal: $medios,
            vinculo: $this->vinculo,
            nombreSnapshot: $nombreSnap,
            dniSnapshot: $dniSnap
        );

        $this->respuesta            = '';
        $this->mostrarFormRespuesta = false;
        session()->flash('success', 'Respuesta enviada.');
    }

    private function snapshotDatosFamiliares(?Legajo $legajo, string $vinculo): array
    {
        if ($legajo === null) {
            return ['Familiar', ''];
        }
        return match ($vinculo) {
            'madre'      => [trim((string) $legajo->nombremad), (string) ($legajo->dnimad ?? '')],
            'padre'      => [trim((string) $legajo->nombrepad), (string) ($legajo->dnipad ?? '')],
            'tutor'      => [trim((string) $legajo->nombretut), (string) ($legajo->dnitut ?? '')],
            'resp_admin' => [trim((string) $legajo->respAdmiNom), (string) ($legajo->respAdmiDni ?? '')],
            default      => ['Familiar', ''],
        };
    }

    public function render()
    {
        $ctx  = studentCtx();
        $hilo = ComHilo::with(['mensajes.destinatarios.envios'])->findOrFail($this->idHilo);

        abort_if(
            $hilo->id_nivel !== (int) $ctx->idNivel || $hilo->id_terlec !== (int) $ctx->idTerlec,
            404
        );

        $rolHilo   = (string) ($hilo->creado_por_rol ?? 'preceptor');
        $puedeResp = $hilo->familiaPuedeEnviarRespuestas()
            && CanalesPolicy::puedeResponder('familia', $rolHilo);
        $mensajesPorDia = $hilo->mensajes->groupBy(fn ($m) => $m->created_at?->toDateString());

        return view('livewire.alumnos.comunicaciones.hilo-show-familia', [
            'hilo'           => $hilo,
            'mensajesPorDia' => $mensajesPorDia,
            'puedeResponder' => $puedeResp,
            'maxContenido'   => config('comunicaciones.max_contenido', 2000),
        ])->layout('layouts.alumno', ['pageTitle' => $hilo->asunto]);
    }
}
