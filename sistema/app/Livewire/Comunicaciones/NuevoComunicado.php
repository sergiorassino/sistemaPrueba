<?php

namespace App\Livewire\Comunicaciones;

use App\Comunicaciones\CanalesPolicy;
use App\Comunicaciones\ComunicacionesRepository;
use App\Push\DestinatariosRepository;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class NuevoComunicado extends Component
{
    /** alumnos: uno o varios · cursos: uno o varios · colegio */
    public string $tipoDestino = 'alumnos';

    public string $asunto    = '';
    public string $contenido = '';

    /** Si la familia podrá responder en el cuaderno (solo aplica a envíos desde la escuela). */
    public bool $familiaPuedeResponder = true;

    // Búsqueda y selección de alumnos
    public string $alumnoSearch = '';
    public array $alumnoResults = [];
    public array $alumnosSeleccionados = []; // [{id, label}]

    // Cursos (uno o varios)
    public array $cursosSeleccionados = []; // [{id, label}]
    public string $cursoPick = '';

    public ?int $enviado = null; // id del hilo creado

    public function mount(): void
    {
        abort_unless(tienePermiso(51) && tienePermiso(52), 403, 'Sin permiso para iniciar comunicados.');
    }

    public function updatedAlumnoSearch(): void
    {
        $ctx = schoolCtx();
        if ($ctx->idNivel && $ctx->idTerlec && trim($this->alumnoSearch) !== '') {
            $this->alumnoResults = DestinatariosRepository::buscarAlumnos(
                (int) $ctx->idNivel,
                (int) $ctx->idTerlec,
                $this->alumnoSearch,
                15
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

    public function updatedCursoPick(mixed $value): void
    {
        $value = $value === null ? '' : (string) $value;
        if ($value === '' || $value === '0') {
            return;
        }
        $id = (int) $value;
        $ctx = schoolCtx();
        if (! $ctx->idNivel || ! $ctx->idTerlec) {
            $this->cursoPick = '';

            return;
        }
        $cursos = DestinatariosRepository::cursosDelContexto((int) $ctx->idNivel, (int) $ctx->idTerlec);
        $row    = collect($cursos)->firstWhere('id', $id);
        if ($row && ! collect($this->cursosSeleccionados)->contains('id', $id)) {
            $this->cursosSeleccionados[] = ['id' => $row['id'], 'label' => $row['label']];
        }
        $this->cursoPick = '';
    }

    public function removeCurso(int $id): void
    {
        $this->cursosSeleccionados = array_values(
            array_filter($this->cursosSeleccionados, fn ($c) => $c['id'] !== $id)
        );
    }

    public function updatedTipoDestino(): void
    {
        $this->alumnosSeleccionados = [];
        $this->alumnoSearch         = '';
        $this->alumnoResults        = [];
        $this->cursosSeleccionados  = [];
        $this->cursoPick            = '';
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
            'tipoDestino'           => 'required|in:alumnos,cursos,colegio',
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

        $scopePersistido = null;
        $idCursoGuardar  = null;
        $cursosEnvio     = null;

        $idLegajos = match ($this->tipoDestino) {
            'alumnos' => $this->variasAlumnoIds(),
            'cursos'  => $this->cursosLegajosIds($idNivel, $idTerlec),
            'colegio' => $this->colegioIds($idNivel, $idTerlec),
            default   => [],
        };

        if ($this->tipoDestino === 'alumnos') {
            if (empty($idLegajos)) {
                $this->addError('tipoDestino', 'Seleccione al menos un alumno.');

                return;
            }
            $scopePersistido = count($idLegajos) === 1 ? 'alumno' : 'varios_alumnos';
        } elseif ($this->tipoDestino === 'cursos') {
            if (empty($this->cursosSeleccionados)) {
                $this->addError('tipoDestino', 'Seleccione al menos un curso.');

                return;
            }
            if (empty($idLegajos)) {
                $this->addError('tipoDestino', 'No hay alumnos matriculados en los cursos elegidos.');

                return;
            }
            $idsCursos       = array_map(fn ($c) => (int) $c['id'], $this->cursosSeleccionados);
            $scopePersistido = count($idsCursos) === 1 ? 'curso' : 'varios_cursos';
            $idCursoGuardar  = $idsCursos[0];
            $cursosEnvio     = array_values(array_map(
                fn (array $c) => ['id' => (int) $c['id'], 'label' => trim((string) ($c['label'] ?? ''))],
                $this->cursosSeleccionados
            ));
        } else {
            $scopePersistido = 'colegio';
            $idCursoGuardar  = null;
        }

        if (empty($idLegajos)) {
            $this->addError('tipoDestino', 'No hay destinatarios para enviar.');

            return;
        }

        $mediosCanal = CanalesPolicy::mediosPermitidos($rolEmisor, 'familia');

        $nombreProfesor = trim("{$profesor->apellido}, {$profesor->nombre}");

        $hilo = ComunicacionesRepository::crearHiloConMensaje([
            'asunto'                   => $this->asunto,
            'contenido'                => $this->contenido,
            'scope'                    => $scopePersistido,
            'id_legajos'               => $idLegajos,
            'id_curso'                 => $idCursoGuardar,
            'cursos_envio'             => $cursosEnvio,
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
        $this->reset('asunto', 'contenido', 'alumnosSeleccionados', 'alumnoSearch', 'cursosSeleccionados', 'cursoPick');
        $this->familiaPuedeResponder = true;
        session()->flash('success', 'Comunicado enviado correctamente.');
    }

    private function variasAlumnoIds(): array
    {
        return array_map(fn ($a) => (int) $a['id'], $this->alumnosSeleccionados);
    }

    /**
     * @return list<int>
     */
    private function cursosLegajosIds(int $idNivel, int $idTerlec): array
    {
        if (empty($this->cursosSeleccionados)) {
            return [];
        }
        $ids = [];
        foreach ($this->cursosSeleccionados as $c) {
            $porCurso = DestinatariosRepository::alumnosPorCurso($idNivel, $idTerlec, (int) $c['id']);
            foreach ($porCurso as $lk) {
                $ids[] = (int) $lk;
            }
        }

        return array_values(array_unique($ids));
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
            'cursos'       => $cursos,
            'maxContenido' => config('comunicaciones.max_contenido', 2000),
            'maxAsunto'    => config('comunicaciones.max_asunto', 200),
        ])->layout('layouts.app', ['pageTitle' => 'Nuevo Comunicado']);
    }
}
