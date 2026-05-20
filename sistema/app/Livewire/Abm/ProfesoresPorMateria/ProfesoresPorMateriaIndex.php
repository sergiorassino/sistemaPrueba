<?php

namespace App\Livewire\Abm\ProfesoresPorMateria;

use App\Models\Curso;
use App\Models\Profesor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

/**
 * Asignación docente: la tabla ppc une materia y profesor (`materias.id`, `profesores.id`).
 * Cada vínculo es un registro con idMateria + idProfesor. El curso en pantalla viene de materias.idCursos;
 * al listar o mutar ppc para una materia concreta, la consulta pivota siempre en idMateria = id elegido.
 */
class ProfesoresPorMateriaIndex extends Component
{
    public ?int $cursoId = null;

    /** Materia seleccionada para panel derecho */
    public ?int $selectedMateriaId = null;

    /** Alta rápida: id de profesor a asignar */
    public ?int $nuevoProfesorId = null;

    /*
     * Depuración SQL en pantalla — desactivada (convención: `docs/05-preferencias-y-convenciones.md` §10).
     * Reactivar: descomentar propiedad, resets en updatedCursoId, línea en selectMateria, pasar valor en render()
     * y panel en profesores-por-materia/index.blade.php.
     *
     * public ?string $consultaEjecutadaClic = null;
     */

    public function mount(): void
    {
        $ctx = schoolCtx();

        $this->cursoId = (int) (Curso::query()
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->orderByRaw('COALESCE(orden, 9999) asc')
            ->orderBy('Id')
            ->value('Id') ?? 0) ?: null;

        $this->syncSelectedMateria();
    }

    public function updatedCursoId(): void
    {
        $this->nuevoProfesorId = null;
        // $this->consultaEjecutadaClic = null;
        $this->resetValidation();
        $this->syncSelectedMateria();
    }

    private function syncSelectedMateria(): void
    {
        $first = $this->materiasQuery()->orderBy('ord')->orderBy('id')->value('id');
        $this->selectedMateriaId = $first ? (int) $first : null;
    }

    private function materiasQuery()
    {
        $ctx = schoolCtx();

        $q = DB::table('materias')
            ->where('idNivel', (int) $ctx->idNivel)
            ->where('idTerlec', (int) $ctx->idTerlec);

        $cid = (int) ($this->cursoId ?? 0);
        if ($cid < 1) {
            return $q->whereRaw('1 = 0');
        }

        return $q->where('idCursos', $cid);
    }

    private function materiaEnContexto(int $idMateria): ?object
    {
        $ctx = schoolCtx();

        return DB::table('materias')
            ->where('id', $idMateria)
            ->where('idNivel', (int) $ctx->idNivel)
            ->where('idTerlec', (int) $ctx->idTerlec)
            ->first();
    }

    /** Base ppc para una materia: idMateria = PK de materias seleccionado en pantalla */
    private function queryPpcPorMateria(int $idMateria)
    {
        return DB::table('ppc')->where('idMateria', $idMateria);
    }

    /**
     * Verifica profesor existe y puede asignarse (no “Sin Rol” id tipo 1; respeta nivel de contexto como en otros módulos).
     */
    private function profesorElegibleParaAsignacion(int $idProfesor): bool
    {
        $ctx = schoolCtx();

        $q = Profesor::query()
            ->where('id', $idProfesor)
            ->where(function ($w) {
                $w->whereNull('IdTipoProf')->orWhere('IdTipoProf', '<>', 1);
            });

        if ((int) $ctx->idNivel > 0) {
            $n = (int) $ctx->idNivel;
            $q->where(function ($w) use ($n) {
                $w->where('nivel', $n)->orWhereNull('nivel')->orWhere('nivel', 0);
            });
        }

        return $q->exists();
    }

    /**
     * Docentes asignados: misma selección que al mostrar la materia en panel derecha tras `selectMateria`.
     * Parámetro: id de materia del formulario / clic (`materias.id` = `ppc.idMateria`).
     */
    public function asignadosPorMateria(int $idMateria): Collection
    {
        /*
         |   SELECT ppc.id          AS ppcId,
         |          p.id           AS idProfesor,
         |          p.apellido,
         |          p.nombre,
         |          p.IdTipoProf
         |     FROM ppc
         |     INNER JOIN profesores AS p ON p.id = ppc.idProfesor
         |    WHERE ppc.idMateria = ?
         | ORDER BY p.apellido ASC, p.nombre ASC, ppc.id ASC
         */
        $filas = DB::select(
            'SELECT ppc.id AS ppcId, p.id AS idProfesor, p.apellido, p.nombre, p.IdTipoProf '
            . 'FROM ppc '
            . 'INNER JOIN profesores AS p ON p.id = ppc.idProfesor '
            . 'WHERE ppc.idMateria = ? '
            . 'ORDER BY p.apellido ASC, p.nombre ASC, ppc.id ASC',
            [$idMateria],
        );

        return collect($filas)->map(fn ($r) => (object) [
            'ppcId' => (int) $r->ppcId,
            'idProfesor' => (int) $r->idProfesor,
            'apellido' => $r->apellido ?? null,
            'nombre' => $r->nombre ?? null,
            'IdTipoProf' => $r->IdTipoProf ?? null,
        ]);
    }

    /**
     * Texto listo para mostrar en la vista; valores sólo enteros (contexto + id materia tras validación).
     * Solo debe invocarse con depuración SQL activada en pantalla (`docs/05-preferencias-y-convenciones.md` §10).
     */
    private function textoConsultasEjecutadasAlElegirMateria(int $idMateria): string
    {
        $ctx = schoolCtx();
        $idM = abs((int) $idMateria);
        $idNivel = abs((int) $ctx->idNivel);
        $idTerlec = abs((int) $ctx->idTerlec);

        $q1 = <<<SQL
SELECT id, idCursos
  FROM materias
 WHERE id = {$idM}
   AND idNivel = {$idNivel}
   AND idTerlec = {$idTerlec}
 LIMIT 1
SQL;

        $q2 = <<<SQL
SELECT ppc.id AS ppcId,
       p.id AS idProfesor,
       p.apellido,
       p.nombre,
       p.IdTipoProf
  FROM ppc
 INNER JOIN profesores AS p ON p.id = ppc.idProfesor
 WHERE ppc.idMateria = {$idM}
 ORDER BY p.apellido ASC, p.nombre ASC, ppc.id ASC
SQL;

        return <<<TXT
Consultas ejecutadas al presionar esta materia (id materia = {$idM}; idNivel sesión = {$idNivel}; idTerlec sesión = {$idTerlec}):

────────────────────────────────────────────────────────────
① En la acción Livewire «selectMateria» (validar materia):

{$q1}

────────────────────────────────────────────────────────────
② En el render siguiente para armar «Docentes asignados» (panel derecho):

{$q2}

TXT;
    }

    public function selectMateria(int $id): void
    {
        /*
         | Consulta al hacer clic en el nombre de la materia (validar ciclo/nivel y curso):
         |
         |   SELECT id, idCursos
         |     FROM materias
         |    WHERE id = ?
         |      AND idNivel = ?
         |      AND idTerlec = ?
         |    LIMIT 1
         */
        $ctx = schoolCtx();

        /** @var object{id: mixed, idCursos: mixed}|null $m */
        $m = DB::selectOne(
            'SELECT id, idCursos FROM materias WHERE id = ? AND idNivel = ? AND idTerlec = ? LIMIT 1',
            [$id, (int) $ctx->idNivel, (int) $ctx->idTerlec],
        );

        if (! $m || (int) $m->idCursos !== (int) ($this->cursoId ?? 0)) {
            abort(404);
        }

        // $this->consultaEjecutadaClic = $this->textoConsultasEjecutadasAlElegirMateria((int) $id);
        $this->selectedMateriaId = $id;
        $this->nuevoProfesorId = null;
        $this->resetValidation();
    }

    public function agregarProfesor(): void
    {
        abort_unless(tienePermiso(11), 403, 'Sin permiso para asignar docentes a la materia.');

        $key = 'ppc-assign:' . (auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 40)) {
            session()->flash('error', 'Demasiados intentos. Espere un momento.');
            return;
        }
        RateLimiter::hit($key, 60);

        if ((int) ($this->cursoId ?? 0) < 1) {
            session()->flash('error', 'Seleccione un curso.');
            return;
        }

        $idMateria = (int) ($this->selectedMateriaId ?? 0);
        $idProf = (int) ($this->nuevoProfesorId ?? 0);

        $this->validate([
            'selectedMateriaId' => ['required', 'integer', 'min:1'],
            'nuevoProfesorId' => ['required', 'integer', 'min:1'],
        ], [], [
            'selectedMateriaId' => 'materia',
            'nuevoProfesorId' => 'docente',
        ]);

        $m = $this->materiaEnContexto($idMateria);
        if (! $m || ((int) ($m->idCursos ?? 0) !== (int) ($this->cursoId ?? 0))) {
            abort(404);
        }

        if (! $this->profesorElegibleParaAsignacion($idProf)) {
            session()->flash('error', 'El docente seleccionado no está disponible para asignación.');
            return;
        }

        $dup = $this->queryPpcPorMateria($idMateria)->where('idProfesor', $idProf)->exists();

        if ($dup) {
            session()->flash('error', 'Ese docente ya está asignado a la materia.');
            return;
        }

        try {
            DB::table('ppc')->insert([
                'idMateria' => $idMateria,
                'idProfesor' => $idProf,
            ]);
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'No se pudo guardar la asignación.');
            return;
        }

        $this->nuevoProfesorId = null;
        session()->flash('success', 'Docente asignado.');
    }

    public function quitarProfesor(int $ppcId): void
    {
        abort_unless(tienePermiso(11), 403, 'Sin permiso para quitar asignaciones de docentes.');

        $key = 'ppc-unassign:' . (auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 40)) {
            session()->flash('error', 'Demasiados intentos. Espere un momento.');
            return;
        }
        RateLimiter::hit($key, 60);

        if ((int) ($this->cursoId ?? 0) < 1) {
            abort(404);
        }

        $ppcRow = DB::table('ppc')->where('id', $ppcId)->first(['id', 'idMateria']);
        if (! $ppcRow || (int) ($ppcRow->idMateria ?? 0) < 1) {
            abort(404);
        }

        $m = $this->materiaEnContexto((int) $ppcRow->idMateria);
        if (! $m || (int) ($m->idCursos ?? 0) !== (int) $this->cursoId) {
            abort(404);
        }

        try {
            DB::table('ppc')->where('id', $ppcId)->delete();
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'No se pudo quitar la asignación.');
            return;
        }

        session()->flash('success', 'Asignación eliminada.');
    }

    /**
     * Lista para el select: elegibles según tipo y nivel, sin los ya asignados a la materia activa.
     */
    public function profesoresDisponiblesParaAgregar(?int $idMateria): \Illuminate\Support\Collection
    {
        $ctx = schoolCtx();

        $idMateria = (int) ($idMateria ?? 0);
        $assigned = $idMateria > 0
            ? $this->queryPpcPorMateria($idMateria)->pluck('idProfesor')->map(fn ($v) => (int) $v)->all()
            : [];

        $q = Profesor::query()
            ->where(function ($w) {
                $w->whereNull('IdTipoProf')->orWhere('IdTipoProf', '<>', 1);
            });

        if ((int) $ctx->idNivel > 0) {
            $n = (int) $ctx->idNivel;
            $q->where(function ($w) use ($n) {
                $w->where('nivel', $n)->orWhereNull('nivel')->orWhere('nivel', 0);
            });
        }

        if ($assigned !== []) {
            $q->whereNotIn('id', $assigned);
        }

        return $q
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get(['id', 'apellido', 'nombre']);
    }

    public function render()
    {
        $ctx = schoolCtx();

        $cursos = Curso::query()
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->orderByRaw('COALESCE(orden, 9999) asc')
            ->orderBy('Id')
            ->with('turnoClase')
            ->get(['Id', 'cursec', 'c', 's', 'idTurnoClase']);

        $materias = $this->materiasQuery()
            ->orderBy('ord')
            ->orderBy('id')
            ->get(['id', 'ord', 'idCursos', 'materia', 'abrev']);

        $countsAsignaciones = [];
        if ($materias->isNotEmpty()) {
            $materiaIds = $materias->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            $rows = DB::table('ppc')
                ->whereIn('idMateria', $materiaIds)
                ->selectRaw('idMateria, COUNT(*) AS c')
                ->groupBy('idMateria')
                ->get();

            foreach ($rows as $r) {
                $countsAsignaciones[(int) $r->idMateria] = (int) $r->c;
            }
        }

        $asignados = collect();
        if ($this->selectedMateriaId) {
            $asignados = $this->asignadosPorMateria((int) $this->selectedMateriaId);
        }

        $elegiblesParaSelect = $this->profesoresDisponiblesParaAgregar((int) ($this->selectedMateriaId ?? 0));

        $selectedMateria = null;
        if ($this->selectedMateriaId) {
            $selectedMateria = $materias->firstWhere('id', (int) $this->selectedMateriaId);
        }

        // Depuración SQL: con propiedad activa, pasar `'consultaEjecutadaClic' => $this->consultaEjecutadaClic` (§10 docs).
        return view('livewire.abm.profesores-por-materia.index', compact(
            'cursos',
            'materias',
            'countsAsignaciones',
            'asignados',
            'elegiblesParaSelect',
            'selectedMateria',
        ) + ['consultaEjecutadaClic' => ''])
            ->layout('layouts.app', ['pageTitle' => 'Docentes por materia y curso']);
    }
}
