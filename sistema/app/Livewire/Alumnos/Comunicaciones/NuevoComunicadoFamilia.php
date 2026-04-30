<?php

namespace App\Livewire\Alumnos\Comunicaciones;

use App\Comunicaciones\CanalesPolicy;
use App\Comunicaciones\ComunicacionesRepository;
use App\Models\ComMensaje;
use App\Models\Legajo;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class NuevoComunicadoFamilia extends Component
{
    public string $vinculo   = '';  // madre|padre|tutor|resp_admin|otro
    public string $asunto    = '';
    public string $contenido = '';
    public string $rolReceptor = ''; // preceptor|directivo

    // Destinatarios escolares disponibles según rol seleccionado
    public array $destinatariosDisponibles = [];
    public ?int $idDestinatario = null;

    public ?int $enviado = null;

    public array $vinculos = [
        'madre'      => 'Madre',
        'padre'      => 'Padre',
        'tutor'      => 'Tutor/a',
        'resp_admin' => 'Responsable Administrativo/a',
        'otro'       => 'Otro responsable',
    ];

    public function mount(): void
    {
        // Roles receptores a los que la familia puede iniciar conversación
        $this->rolesReceptoresPermitidos = CanalesPolicy::receptoresPermitidosParaIniciar('familia');
    }

    public array $rolesReceptoresPermitidos = [];

    public function updatedRolReceptor(): void
    {
        $this->idDestinatario = null;
        $this->destinatariosDisponibles = [];

        if ($this->rolReceptor === '') {
            return;
        }

        $ctx = studentCtx();
        $idNivel  = (int) $ctx->idNivel;
        $idLegajo = (int) $ctx->idLegajo;
        $idTerlec = (int) $ctx->idTerlec;

        if ($this->rolReceptor === 'preceptor') {
            $this->destinatariosDisponibles = ComunicacionesRepository::preceptoresDeCurso(
                $idLegajo, $idNivel, $idTerlec
            );
        } elseif ($this->rolReceptor === 'directivo') {
            $this->destinatariosDisponibles = ComunicacionesRepository::profesoresPorRol($idNivel, 'directivo');
        }
    }

    public function enviar(): void
    {
        $key = 'com:nuevo:fam:' . (auth('alumno')->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, config('comunicaciones.rate_limit_max', 20))) {
            $this->addError('contenido', 'Demasiados envíos. Espere un momento.');
            return;
        }
        RateLimiter::hit($key, config('comunicaciones.rate_limit_decay', 60));

        $this->validate([
            'vinculo'       => 'required|in:madre,padre,tutor,resp_admin,otro',
            'rolReceptor'   => 'required|in:preceptor,profesor,directivo',
            'idDestinatario'=> 'required|integer',
            'asunto'        => 'required|string|max:' . config('comunicaciones.max_asunto', 200),
            'contenido'     => 'required|string|max:' . config('comunicaciones.max_contenido', 2000),
        ]);

        if (! CanalesPolicy::puedeIniciar('familia', $this->rolReceptor)) {
            $this->addError('rolReceptor', 'La familia no puede iniciar conversaciones con ese rol en este momento.');
            return;
        }

        $ctx      = studentCtx();
        $idLegajo = (int) $ctx->idLegajo;
        $idNivel  = (int) $ctx->idNivel;
        $idTerlec = (int) $ctx->idTerlec;

        $legajo = Legajo::find($idLegajo);
        [$nombreSnap, $dniSnap] = $this->snapshotDatosFamiliares($legajo, $this->vinculo);

        $mediosCanal = CanalesPolicy::mediosPermitidos('familia', $this->rolReceptor);

        $hilo = ComunicacionesRepository::crearHiloConMensaje([
            'asunto'                   => $this->asunto,
            'contenido'                => $this->contenido,
            'scope'                    => 'alumno',
            'id_legajos'               => [],
            'id_curso'                 => null,
            'id_nivel'                 => $idNivel,
            'id_terlec'                => $idTerlec,
            'creado_por_tipo'          => 'familia',
            'creado_por_id'            => $idLegajo,
            'creado_por_rol'           => 'familia',
            'rol_receptor'             => $this->rolReceptor,
            'vinculo_familiar'         => $this->vinculo,
            'nombre_remitente'         => $nombreSnap,
            'dni_remitente'            => $dniSnap,
            'destinatarios_profesores' => [$this->idDestinatario],
            'familia_puede_responder'  => true,
        ], $mediosCanal);

        $this->enviado = $hilo->id;
        $this->reset('asunto', 'contenido', 'vinculo', 'rolReceptor', 'idDestinatario');
        session()->flash('success', 'Comunicado enviado.');
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
        return view('livewire.alumnos.comunicaciones.nuevo-comunicado-familia', [
            'maxContenido' => config('comunicaciones.max_contenido', 2000),
            'maxAsunto'    => config('comunicaciones.max_asunto', 200),
        ])->layout('layouts.alumno', ['pageTitle' => 'Nuevo Comunicado']);
    }
}
