<?php

namespace App\Livewire\Comunicaciones;

use App\Comunicaciones\CanalesPolicy;
use App\Comunicaciones\ComunicacionesRepository;
use App\Push\DestinatariosRepository;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class NuevoComunicado extends Component
{
    public string $tipoDestino = 'alumno'; // alumno|varios_alumnos|curso|colegio
    public string $asunto      = '';
    public string $contenido   = '';

    /** Si la familia podrá responder en el cuaderno (solo aplica a envíos desde la escuela). */
    public bool $familiaPuedeResponder = true;

    // Búsqueda y selección de alumnos
    public string $alumnoSearch    = '';
    public array $alumnoResults    = [];
    public array $alumnosSeleccionados = []; // [{id, label}]

    // Curso
    public ?int $cursoId = null;

    public ?int $enviado = null; // id del hilo creado

    public function mount(): void
    {
        abort_unless(tienePermiso(51) && tienePermiso(52), 403, 'Sin permiso para iniciar comunicados.');
    }

    public function updatedAlumnoSearch(): void
    {
        $ctx = schoolCtx();
        if ($ctx->idNivel && trim($this->alumnoSearch) !== '') {
            $this->alumnoResults = DestinatariosRepository::buscarAlumnos(
                (int) $ctx->idNivel, $this->alumnoSearch, 15
            );
        } else {
            $this->alumnoResults = [];
        }
    }

    public function selectAlumno(int $id, string $label): void
    {
        if (! collect($this->alumnosSeleccionados)->contains('id', $id)) {
            $this->alumnosSeleccionados[] = ['id' => $id, 'label' => $label];
        }
        $this->alumnoSearch  = '';
        $this->alumnoResults = [];
    }

    public function removeAlumno(int $id): void
    {
        $this->alumnosSeleccionados = array_values(
            array_filter($this->alumnosSeleccionados, fn ($a) => $a['id'] !== $id)
        );
    }

    public function updatedTipoDestino(): void
    {
        $this->alumnosSeleccionados = [];
        $this->alumnoSearch         = '';
        $this->alumnoResults        = [];
        $this->cursoId              = null;
    }

    public function enviar(): void
    {
        abort_unless(tienePermiso(51) && tienePermiso(52), 403);

        $key = 'com:nuevo:' . (auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, config('comunicaciones.rate_limit_max', 20))) {
            $this->addError('contenido', 'Demasiados envíos. Espere un momento.');
            return;
        }
        RateLimiter::hit($key, config('comunicaciones.rate_limit_decay', 60));

        $this->validate([
            'tipoDestino'           => 'required|in:alumno,varios_alumnos,curso,colegio',
            'asunto'                => 'required|string|max:' . config('comunicaciones.max_asunto', 200),
            'contenido'             => 'required|string|max:' . config('comunicaciones.max_contenido', 2000),
            'familiaPuedeResponder' => 'boolean',
        ]);

        $ctx      = schoolCtx();
        $idNivel  = (int) $ctx->idNivel;
        $idTerlec = (int) $ctx->idTerlec;
        $idProf   = (int) $ctx->idProfesor;
        $profesor = $ctx->profesor();

        if ($profesor === null) {
            $this->addError('contenido', 'No se pudo identificar al usuario.');
            return;
        }

        $rolEmisor = CanalesPolicy::rolDeProfesor($profesor);
        if (! CanalesPolicy::puedeIniciar($rolEmisor, 'familia')) {
            $this->addError('contenido', 'Su rol no tiene permiso para iniciar comunicados a familias.');
            return;
        }

        // Resolver legajos destinatarios
        $idLegajos = match ($this->tipoDestino) {
            'alumno'         => $this->primerAlumnoIds(),
            'varios_alumnos' => $this->variasAlumnoIds(),
            'curso'          => $this->cursoIds($idNivel, $idTerlec),
            'colegio'        => $this->colegioIds($idNivel, $idTerlec),
            default          => [],
        };

        if (empty($idLegajos)) {
            $this->addError('tipoDestino', 'No hay destinatarios para enviar.');
            return;
        }

        $mediosCanal = CanalesPolicy::mediosPermitidos($rolEmisor, 'familia');

        $nombreProfesor = trim("{$profesor->apellido}, {$profesor->nombre}");

        $hilo = ComunicacionesRepository::crearHiloConMensaje([
            'asunto'                   => $this->asunto,
            'contenido'                => $this->contenido,
            'scope'                    => $this->tipoDestino,
            'id_legajos'               => $idLegajos,
            'id_curso'                 => $this->cursoId,
            'id_nivel'                 => $idNivel,
            'id_terlec'                => $idTerlec,
            'creado_por_tipo'          => 'profesor',
            'creado_por_id'            => $idProf,
            'creado_por_rol'           => $rolEmisor,
            'rol_receptor'             => 'familia',
            'vinculo_familiar'         => null,
            'nombre_remitente'         => $nombreProfesor,
            'dni_remitente'            => (string) ($profesor->dni ?? ''),
            'destinatarios_profesores' => [],
            'familia_puede_responder'  => $this->familiaPuedeResponder,
        ], $mediosCanal);

        $this->enviado = $hilo->id;
        $this->reset('asunto', 'contenido', 'alumnosSeleccionados', 'alumnoSearch', 'cursoId');
        $this->familiaPuedeResponder = true;
        session()->flash('success', 'Comunicado enviado correctamente.');
    }

    private function primerAlumnoIds(): array
    {
        if (empty($this->alumnosSeleccionados)) {
            return [];
        }
        return [(int) $this->alumnosSeleccionados[0]['id']];
    }

    private function variasAlumnoIds(): array
    {
        return array_map(fn ($a) => (int) $a['id'], $this->alumnosSeleccionados);
    }

    private function cursoIds(int $idNivel, int $idTerlec): array
    {
        if (! $this->cursoId) {
            return [];
        }
        return array_map('intval', DestinatariosRepository::alumnosPorCurso($idNivel, $idTerlec, (int) $this->cursoId));
    }

    private function colegioIds(int $idNivel, int $idTerlec): array
    {
        return array_map('intval', DestinatariosRepository::alumnosDelColegio($idNivel, $idTerlec));
    }

    public function render()
    {
        $ctx    = schoolCtx();
        $cursos = ($ctx->idNivel && $ctx->idTerlec)
            ? DestinatariosRepository::cursosDelContexto((int) $ctx->idNivel, (int) $ctx->idTerlec)
            : [];

        return view('livewire.comunicaciones.nuevo-comunicado', [
            'cursos' => $cursos,
            'maxContenido' => config('comunicaciones.max_contenido', 2000),
            'maxAsunto'    => config('comunicaciones.max_asunto', 200),
        ])->layout('layouts.app', ['pageTitle' => 'Nuevo Comunicado']);
    }
}
