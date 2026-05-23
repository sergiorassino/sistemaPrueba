<?php

namespace App\Livewire\Comunicaciones;

use App\Comunicaciones\CanalesPolicy;
use App\Comunicaciones\ComunicacionesRepository;
use App\Push\DestinatariosRepository;
use App\Support\ComunicacionesRutasGestion;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class NuevoComunicado extends Component
{
    /** estudiantes (familias) | docentes — null hasta que el usuario elija */
    public ?string $bloqueDestinatarios = null;

    /** alumnos: uno o varios · cursos: uno o varios · colegio */
    public string $tipoDestino = 'alumnos';

    /** profesores | preceptores — solo bloque docentes */
    public string $tipoDocenteLista = 'profesores';

    public string $asunto    = '';
    public string $contenido = '';

    /** Si la familia podrá responder en el cuaderno (solo a envíos desde la escuela). */
    public bool $familiaPuedeResponder = true;

    /** Si los docentes destinatarios podrán responder en el hilo (solo scope docentes; columna com_hilos.docentes_permite_respuestas). */
    public bool $docentesDestinatariosPuedenResponder = true;

    public array $alumnosSeleccionados = []; // [{id, label}]

    public array $cursosSeleccionados = []; // [{id, label}]

    public array $docentesSeleccionados = []; // [{id, label}]

    // —— Modal alumnos ——
    public bool $modalAlumnosAbierto = false;

    public string $modalAlumnosFiltro = '';

    /** @var list<array{id:int,label:string,dni:?string}> */
    public array $modalAlumnosLista = [];

    /** @var list<int|string> */
    public array $modalAlumnosMarcados = [];

    // —— Modal cursos ——
    public bool $modalCursosAbierto = false;

    public string $modalCursosFiltro = '';

    /** @var list<array{id:int,label:string}> */
    public array $modalCursosLista = [];

    /** @var list<int|string> */
    public array $modalCursosMarcados = [];

    // —— Modal docentes ——
    public bool $modalDocentesAbierto = false;

    public string $modalDocentesFiltro = '';

    /** @var list<array{id:int,label:string,dni:?string}> */
    public array $modalDocentesLista = [];

    /** @var list<int|string> */
    public array $modalDocentesMarcados = [];

    public function mount(): void
    {
        abort_unless(tienePermiso(3) && tienePermiso(4), 403, 'Sin permiso para iniciar comunicados.');
    }

    public function updatedModalAlumnosFiltro(): void
    {
        if ($this->modalAlumnosAbierto) {
            $this->recargarModalAlumnosLista();
        }
    }

    public function updatedModalCursosFiltro(): void
    {
        if ($this->modalCursosAbierto) {
            $this->recargarModalCursosLista();
        }
    }

    public function updatedModalDocentesFiltro(): void
    {
        if ($this->modalDocentesAbierto) {
            $this->recargarModalDocentesLista();
        }
    }

    public function abrirModalAlumnos(): void
    {
        $this->modalAlumnosAbierto   = true;
        $this->modalAlumnosFiltro    = '';
        $this->modalAlumnosMarcados  = array_map(fn ($a) => (int) $a['id'], $this->alumnosSeleccionados);
        $this->recargarModalAlumnosLista();
    }

    public function cerrarModalAlumnos(): void
    {
        $this->modalAlumnosAbierto = false;
    }

    public function aplicarModalAlumnos(): void
    {
        $labelsPorId = collect($this->modalAlumnosLista)->keyBy('id');
        $prev        = collect($this->alumnosSeleccionados)->keyBy('id');
        $out         = [];
        foreach (array_unique(array_map('intval', $this->modalAlumnosMarcados)) as $id) {
            if ($id <= 0) {
                continue;
            }
            $fromLista = $labelsPorId->get($id);
            if ($fromLista !== null) {
                $out[] = ['id' => $id, 'label' => (string) $fromLista['label']];

                continue;
            }
            $fromPrev = $prev->get($id);
            if ($fromPrev !== null) {
                $out[] = ['id' => $id, 'label' => (string) $fromPrev['label']];
            }
        }
        $this->alumnosSeleccionados = $out;
        $this->modalAlumnosAbierto  = false;
    }

    public function modalAlumnosSeleccionarTodosVisibles(): void
    {
        $ids = array_map(fn ($r) => (int) $r['id'], $this->modalAlumnosLista);
        $this->modalAlumnosMarcados = array_values(array_unique(array_merge(
            array_map('intval', $this->modalAlumnosMarcados),
            $ids
        )));
    }

    public function modalAlumnosQuitarVisibles(): void
    {
        $vis = array_flip(array_map(fn ($r) => (int) $r['id'], $this->modalAlumnosLista));
        $this->modalAlumnosMarcados = array_values(array_filter(
            array_map('intval', $this->modalAlumnosMarcados),
            fn (int $id) => ! isset($vis[$id])
        ));
    }

    public function abrirModalCursos(): void
    {
        $this->modalCursosAbierto  = true;
        $this->modalCursosFiltro   = '';
        $this->modalCursosMarcados = array_map(fn ($c) => (int) $c['id'], $this->cursosSeleccionados);
        $this->recargarModalCursosLista();
    }

    public function cerrarModalCursos(): void
    {
        $this->modalCursosAbierto = false;
    }

    public function aplicarModalCursos(): void
    {
        $labelsPorId = collect($this->modalCursosLista)->keyBy('id');
        $prev        = collect($this->cursosSeleccionados)->keyBy('id');
        $out         = [];
        foreach (array_unique(array_map('intval', $this->modalCursosMarcados)) as $id) {
            if ($id <= 0) {
                continue;
            }
            $fromLista = $labelsPorId->get($id);
            if ($fromLista !== null) {
                $out[] = ['id' => $id, 'label' => (string) $fromLista['label']];

                continue;
            }
            $fromPrev = $prev->get($id);
            if ($fromPrev !== null) {
                $out[] = ['id' => $id, 'label' => (string) $fromPrev['label']];
            }
        }
        $this->cursosSeleccionados = $out;
        $this->modalCursosAbierto = false;
    }

    public function modalCursosSeleccionarTodosVisibles(): void
    {
        $ids = array_map(fn ($r) => (int) $r['id'], $this->modalCursosLista);
        $this->modalCursosMarcados = array_values(array_unique(array_merge(
            array_map('intval', $this->modalCursosMarcados),
            $ids
        )));
    }

    public function modalCursosQuitarVisibles(): void
    {
        $vis = array_flip(array_map(fn ($r) => (int) $r['id'], $this->modalCursosLista));
        $this->modalCursosMarcados = array_values(array_filter(
            array_map('intval', $this->modalCursosMarcados),
            fn (int $id) => ! isset($vis[$id])
        ));
    }

    public function abrirModalDocentes(): void
    {
        $this->modalDocentesAbierto  = true;
        $this->modalDocentesFiltro   = '';
        $this->modalDocentesMarcados = array_map(fn ($d) => (int) $d['id'], $this->docentesSeleccionados);
        $this->recargarModalDocentesLista();
    }

    public function cerrarModalDocentes(): void
    {
        $this->modalDocentesAbierto = false;
    }

    public function aplicarModalDocentes(): void
    {
        $labelsPorId = collect($this->modalDocentesLista)->keyBy('id');
        $prev        = collect($this->docentesSeleccionados)->keyBy('id');
        $out         = [];
        foreach (array_unique(array_map('intval', $this->modalDocentesMarcados)) as $id) {
            if ($id <= 0) {
                continue;
            }
            $fromLista = $labelsPorId->get($id);
            if ($fromLista !== null) {
                $out[] = ['id' => $id, 'label' => (string) $fromLista['label']];

                continue;
            }
            $fromPrev = $prev->get($id);
            if ($fromPrev !== null) {
                $out[] = ['id' => $id, 'label' => (string) $fromPrev['label']];
            }
        }
        $this->docentesSeleccionados = $out;
        $this->modalDocentesAbierto  = false;
    }

    public function modalDocentesSeleccionarTodosVisibles(): void
    {
        $ids = array_map(fn ($r) => (int) $r['id'], $this->modalDocentesLista);
        $this->modalDocentesMarcados = array_values(array_unique(array_merge(
            array_map('intval', $this->modalDocentesMarcados),
            $ids
        )));
    }

    public function modalDocentesQuitarVisibles(): void
    {
        $vis = array_flip(array_map(fn ($r) => (int) $r['id'], $this->modalDocentesLista));
        $this->modalDocentesMarcados = array_values(array_filter(
            array_map('intval', $this->modalDocentesMarcados),
            fn (int $id) => ! isset($vis[$id])
        ));
    }

    public function removeAlumno(int $id): void
    {
        $this->alumnosSeleccionados = array_values(
            array_filter($this->alumnosSeleccionados, fn ($a) => (int) $a['id'] !== $id)
        );
    }

    public function removeCurso(int $id): void
    {
        $this->cursosSeleccionados = array_values(
            array_filter($this->cursosSeleccionados, fn ($c) => (int) $c['id'] !== $id)
        );
    }

    public function removeDocente(int $id): void
    {
        $this->docentesSeleccionados = array_values(
            array_filter($this->docentesSeleccionados, fn ($d) => (int) $d['id'] !== $id)
        );
    }

    public function updatedBloqueDestinatarios(): void
    {
        if ($this->bloqueDestinatarios === 'estudiantes') {
            $this->docentesSeleccionados = [];
            $this->tipoDocenteLista      = 'profesores';
            $this->cerrarModalDocentes();
        } elseif ($this->bloqueDestinatarios === 'docentes') {
            $this->alumnosSeleccionados = [];
            $this->cursosSeleccionados  = [];
            $this->cerrarModalAlumnos();
            $this->cerrarModalCursos();
        }
    }

    public function updatedTipoDocenteLista(): void
    {
        $this->docentesSeleccionados = [];
        $this->cerrarModalDocentes();
    }

    public function updatedTipoDestino(): void
    {
        $this->alumnosSeleccionados = [];
        $this->cursosSeleccionados  = [];
        $this->cerrarModalAlumnos();
        $this->cerrarModalCursos();
    }

    public function enviar(): void
    {
        abort_unless(tienePermiso(3) && tienePermiso(4), 403);

        $key = 'com:nuevo:' . (auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, config('comunicaciones.rate_limit_max', 20))) {
            $this->addError('contenido', 'Demasiados envíos. Espere un momento.');

            return;
        }
        RateLimiter::hit($key, config('comunicaciones.rate_limit_decay', 60));

        $rules = [
            'bloqueDestinatarios' => 'required|in:estudiantes,docentes',
            'asunto'              => 'required|string|max:' . config('comunicaciones.max_asunto', 200),
            'contenido'           => 'required|string|max:' . config('comunicaciones.max_contenido', 2000),
        ];
        if ($this->bloqueDestinatarios === 'estudiantes') {
            $rules['tipoDestino']           = 'required|in:alumnos,cursos,colegio';
            $rules['familiaPuedeResponder'] = 'boolean';
        } elseif ($this->bloqueDestinatarios === 'docentes') {
            $rules['tipoDocenteLista']                     = 'required|in:profesores,preceptores';
            $rules['docentesDestinatariosPuedenResponder'] = 'boolean';
        }
        $this->validate($rules);

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
        $nombreProfesor = trim("{$profesor->apellido}, {$profesor->nombre}");

        if ($this->bloqueDestinatarios === 'estudiantes') {
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
        } elseif ($this->bloqueDestinatarios === 'docentes') {
            $rolReceptorDoc = $this->tipoDocenteLista === 'preceptores' ? 'preceptor' : 'profesor';
            if (! CanalesPolicy::puedeIniciar($rolEmisor, $rolReceptorDoc)) {
                $etiq = $rolReceptorDoc === 'preceptor' ? 'preceptores' : 'profesores';
                $this->addError('contenido', "Su rol no tiene permiso para iniciar comunicados a {$etiq}.");

                return;
            }

            $idsPedidos = array_map(fn ($d) => (int) $d['id'], $this->docentesSeleccionados);
            $idsProf    = ComunicacionesRepository::filtrarIdsProfesoresPorRolNorm($idsPedidos, $idNivel, $rolReceptorDoc);
            $idsProf    = array_values(array_diff($idsProf, [$idProf]));

            if ($idsProf === []) {
                $this->addError('bloqueDestinatarios', 'Seleccione al menos un destinatario del nivel actual. No puede incluirse a usted mismo.');

                return;
            }

            $mediosCanal = CanalesPolicy::mediosPermitidos($rolEmisor, $rolReceptorDoc);
            if ($mediosCanal === []) {
                $this->addError('contenido', 'No hay medios habilitados para este tipo de envío. Revise la parametrización de canales.');

                return;
            }

            $hilo = ComunicacionesRepository::crearHiloConMensaje([
                'asunto'                   => $this->asunto,
                'contenido'                => $this->contenido,
                'scope'                    => 'docentes',
                'id_legajos'               => [],
                'id_curso'                 => null,
                'cursos_envio'             => null,
                'id_nivel'                 => $idNivel,
                'id_terlec'                => $idTerlec,
                'creado_por_tipo'          => 'profesor',
                'creado_por_id'            => $idProf,
                'creado_por_rol'           => $rolEmisor,
                'rol_receptor'             => $rolReceptorDoc,
                'vinculo_familiar'         => null,
                'nombre_remitente'         => $nombreProfesor,
                'dni_remitente'            => (string) ($profesor->dni ?? ''),
                'destinatarios_profesores' => $idsProf,
                'familia_puede_responder'  => true,
                'docentes_permite_respuestas' => $this->docentesDestinatariosPuedenResponder,
            ], $mediosCanal);
        } else {
            $this->addError('bloqueDestinatarios', 'Seleccione si el mensaje va a familias o a docentes.');

            return;
        }

        $idPrimerMensaje = (int) ($hilo->cuerpo_inicial_id ?? 0);
        if ($idPrimerMensaje > 0) {
            $waLinks = ComunicacionesRepository::enlacesWhatsappWaMeDelMensaje($idPrimerMensaje);
            if ($waLinks !== []) {
                session()->flash('whatsapp_wa_links', [
                    'hilo_id' => (int) $hilo->id,
                    'links'   => $waLinks,
                ]);
            }
        }

        session()->flash('success', 'Comunicado registrado. A continuación el detalle de cada envío por medio.');
        $this->redirectRoute(ComunicacionesRutasGestion::nombreRuta('informe-envio'), ['id' => $hilo->id]);
    }

    private function recargarModalAlumnosLista(): void
    {
        $ctx = schoolCtx();
        if (! $ctx->idNivel || ! $ctx->idTerlec) {
            $this->modalAlumnosLista = [];

            return;
        }
        $this->modalAlumnosLista = DestinatariosRepository::alumnosMatriculadosParaSelector(
            (int) $ctx->idNivel,
            (int) $ctx->idTerlec,
            $this->modalAlumnosFiltro,
            2500
        );
    }

    private function recargarModalCursosLista(): void
    {
        $ctx = schoolCtx();
        if (! $ctx->idNivel || ! $ctx->idTerlec) {
            $this->modalCursosLista = [];

            return;
        }
        $all = DestinatariosRepository::cursosDelContexto((int) $ctx->idNivel, (int) $ctx->idTerlec);
        $f   = mb_strtolower(trim($this->modalCursosFiltro));
        if ($f !== '') {
            $all = array_values(array_filter(
                $all,
                fn (array $c) => str_contains(mb_strtolower((string) ($c['label'] ?? '')), $f)
            ));
        }
        $this->modalCursosLista = $all;
    }

    private function recargarModalDocentesLista(): void
    {
        $ctx = schoolCtx();
        if (! $ctx->idNivel || ($this->bloqueDestinatarios ?? '') !== 'docentes') {
            $this->modalDocentesLista = [];

            return;
        }
        $rolNorm = $this->tipoDocenteLista === 'preceptores' ? 'preceptor' : 'profesor';
        $this->modalDocentesLista = ComunicacionesRepository::profesoresDelNivelParaSelector(
            (int) $ctx->idNivel,
            $rolNorm,
            $this->modalDocentesFiltro,
            800
        );
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
        $ctx = schoolCtx();
        $cursos = ($ctx->idNivel && $ctx->idTerlec)
            ? DestinatariosRepository::cursosDelContexto((int) $ctx->idNivel, (int) $ctx->idTerlec)
            : [];

        return view('comunicaciones::livewire.comunicaciones.nuevo-comunicado', [
            'cursos'       => $cursos,
            'maxContenido' => config('comunicaciones.max_contenido', 2000),
            'maxAsunto'    => config('comunicaciones.max_asunto', 200),
        ])->layout(ComunicacionesRutasGestion::layout(), ['pageTitle' => 'Nuevo Comunicado']);
    }
}
