<?php

namespace App\Livewire\Abm\Cursos;

use App\Models\Curso;
use App\Models\Curplan;
use App\Models\Matplan;
use App\Models\Nivel;
use App\Models\Plan;
use App\Models\Terlec;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CursosIndex extends Component
{
    public bool $showConfirm = false;

    public ?int $deleteId = null;
    public string $deleteInfo = '';

    /**
     * Edición inline por fila: $editingId + $draft[<Id>][<field>]
     */
    public ?int $editingId = null;
    public array $draft = [];

    public function startEdit(int $id): void
    {
        $ctx = schoolCtx();

        // Seguridad: sólo permite editar cursos del contexto actual
        $curso = Curso::query()
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->findOrFail($id);

        $this->editingId = $curso->Id;

        $this->draft[$curso->Id] = [
            'orden' => $curso->orden,
            'idCurPlan' => (int) $curso->idCurPlan,
            'idTerlec' => (int) $curso->idTerlec,
            'idNivel' => (int) $curso->idNivel,
            'cursec' => (string) ($curso->cursec ?? ''),
            'c' => (string) ($curso->c ?? ''),
            's' => (string) ($curso->s ?? ''),
            'turno' => (string) ($curso->turno ?? ''),
        ];

        $this->resetValidation();
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetValidation();
    }

    protected function rowRules(int $id): array
    {
        $ctx = schoolCtx();

        // ids válidos para el nivel actual (CurPlan depende de Plan->idNivel)
        $planesIds = Plan::query()
            ->where('idNivel', $ctx->idNivel)
            ->pluck('id');

        $curplanIds = Curplan::query()
            ->whereIn('idPlan', $planesIds)
            ->pluck('id')
            ->all();

        return [
            "draft.$id.orden" => ['nullable', 'integer', 'min:0', 'max:999'],
            "draft.$id.idCurPlan" => ['required', 'integer', Rule::in($curplanIds)],
            "draft.$id.idTerlec" => ['required', 'integer', 'exists:terlec,id'],
            "draft.$id.idNivel" => ['required', 'integer', 'exists:niveles,id'],
            "draft.$id.cursec" => ['nullable', 'string', 'max:30'],
            "draft.$id.c" => ['nullable', 'string', 'max:1'],
            "draft.$id.s" => ['nullable', 'string', 'max:1'],
            "draft.$id.turno" => ['nullable', 'string', 'max:20'],
        ];
    }

    public function saveRow(int $id): void
    {
        $key = 'cursos:inline-row:' . (auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 60)) {
            $this->addError("draft.$id.idCurPlan", 'Demasiados intentos. Espere un momento e intente nuevamente.');
            return;
        }
        RateLimiter::hit($key, 60);

        $this->validate($this->rowRules($id));

        $ctx = schoolCtx();

        /** @var Curso $curso */
        $curso = Curso::query()
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->findOrFail($id);

        $d = $this->draft[$id] ?? [];
        $newCurplan = (int) ($d['idCurPlan'] ?? 0);
        $newTerlec = (int) ($d['idTerlec'] ?? 0);
        $newNivel = (int) ($d['idNivel'] ?? 0);

        // Dependencias: si hay matrículas / calificaciones, NO permitir re-crear materias del año
        if ((int) $curso->idCurPlan !== $newCurplan) {
            $countMatriculas = DB::table('matricula')->where('idCursos', (int) $curso->Id)->count();
            $countCalificaciones = DB::table('calificaciones')->where('idCursos', (int) $curso->Id)->count();

            if (($countMatriculas + $countCalificaciones) > 0) {
                $detail = collect([
                    $countMatriculas ? "{$countMatriculas} matrículas" : null,
                    $countCalificaciones ? "{$countCalificaciones} calificaciones" : null,
                ])->filter()->implode(', ');

                session()->flash('error', "No se puede cambiar el curso modelo porque el curso ya tiene: {$detail}. Para evitar inconsistencias, no se re-crearon materias.");
                // Mantener la fila en modo edición
                return;
            }
        }

        try {
            DB::transaction(function () use ($curso, $d, $newCurplan, $newTerlec, $newNivel) {
                // 1) CurPlan: cambia y resincroniza materias
                if ((int) $curso->idCurPlan !== $newCurplan) {
                    $curso->update(['idCurPlan' => $newCurplan]);

                    DB::table('materias')
                        ->where('idCursos', (int) $curso->Id)
                        ->where('idTerlec', (int) $curso->idTerlec)
                        ->where('idNivel', (int) $curso->idNivel)
                        ->delete();

                    $matplan = Matplan::query()
                        ->where('idCurPlan', $newCurplan)
                        ->orderBy('ord')
                        ->orderBy('id')
                        ->get(['id', 'ord', 'matPlanMateria', 'abrev']);

                    $rows = $matplan->map(fn ($m) => [
                        'ord' => (int) $m->ord,
                        'idCurPlan' => $newCurplan,
                        'idMatPlan' => (int) $m->id,
                        'idNivel' => (int) $curso->idNivel,
                        'idCursos' => (int) $curso->Id,
                        'idTerlec' => (int) $curso->idTerlec,
                        'materia' => (string) $m->matPlanMateria,
                        'abrev' => $m->abrev !== null && trim((string) $m->abrev) !== '' ? (string) $m->abrev : null,
                        'cierre1e' => 0,
                        'cierre2e' => 0,
                    ])->all();

                    if (! empty($rows)) {
                        DB::table('materias')->insert($rows);
                    }
                }

                // 2) Terlec / Nivel: también actualiza materias
                if ((int) $curso->idTerlec !== $newTerlec) {
                    DB::table('materias')
                        ->where('idCursos', (int) $curso->Id)
                        ->update(['idTerlec' => $newTerlec]);
                    $curso->update(['idTerlec' => $newTerlec]);
                }

                if ((int) $curso->idNivel !== $newNivel) {
                    DB::table('materias')
                        ->where('idCursos', (int) $curso->Id)
                        ->update(['idNivel' => $newNivel]);
                    $curso->update(['idNivel' => $newNivel]);
                }

                $rawOrden = $d['orden'] ?? null;
                $rawCursec = $d['cursec'] ?? null;
                $rawC = $d['c'] ?? null;
                $rawS = $d['s'] ?? null;
                $rawTurno = $d['turno'] ?? null;

                $payload = [
                    'orden' => ($rawOrden === '' || $rawOrden === null) ? null : (int) $rawOrden,
                    'cursec' => trim((string) $rawCursec) !== '' ? trim((string) $rawCursec) : null,
                    'c' => trim((string) $rawC) !== '' ? trim((string) $rawC) : null,
                    's' => trim((string) $rawS) !== '' ? trim((string) $rawS) : null,
                    'turno' => trim((string) $rawTurno) !== '' ? trim((string) $rawTurno) : null,
                ];

                $curso->update($payload);
            });
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', 'No se pudo guardar el curso por dependencias u otro error. No se aplicaron cambios.');
            return;
        }

        // Si cambió idTerlec / idNivel puede "salir" del listado actual: igual cerramos edición
        $this->editingId = null;
        $this->resetValidation();
    }

    public function createQuick(): void
    {
        $key = 'cursos:create:' . (auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 30)) {
            session()->flash('success', 'Demasiados intentos. Espere un momento e intente nuevamente.');
            return;
        }
        RateLimiter::hit($key, 60);

        $ctx = schoolCtx();

        $planesIds = Plan::query()
            ->where('idNivel', $ctx->idNivel)
            ->pluck('id');

        $curplanId = (int) (Curplan::query()
            ->whereIn('idPlan', $planesIds)
            ->orderBy('idPlan')
            ->orderBy('curPlanCurso')
            ->value('id') ?? 0);

        if ($curplanId <= 0) {
            session()->flash('success', 'No hay cursos modelo disponibles para este nivel. Cree un CurPlan primero.');
            return;
        }

        DB::transaction(function () use ($ctx, $curplanId) {
            /** @var Curso $curso */
            $curso = Curso::create([
                'orden' => null,
                'idCurPlan' => $curplanId,
                'idTerlec' => (int) $ctx->idTerlec,
                'idNivel' => (int) $ctx->idNivel,
                'cursec' => null,
                'c' => null,
                's' => null,
                'turno' => null,
            ]);

            $matplan = Matplan::query()
                ->where('idCurPlan', $curplanId)
                ->orderBy('ord')
                ->orderBy('id')
                ->get(['id', 'ord', 'matPlanMateria', 'abrev']);

            $rows = $matplan->map(fn ($m) => [
                'ord' => (int) $m->ord,
                'idCurPlan' => $curplanId,
                'idMatPlan' => (int) $m->id,
                'idNivel' => (int) $ctx->idNivel,
                'idCursos' => (int) $curso->Id,
                'idTerlec' => (int) $ctx->idTerlec,
                'materia' => (string) $m->matPlanMateria,
                'abrev' => $m->abrev !== null && trim((string) $m->abrev) !== '' ? (string) $m->abrev : null,
                'cierre1e' => 0,
                'cierre2e' => 0,
            ])->all();

            if (! empty($rows)) {
                DB::table('materias')->insert($rows);
            }
        });

        session()->flash('success', 'Curso creado (en blanco) y materias copiadas del curso modelo.');
    }

    public function confirmDelete(int $id): void
    {
        $ctx = schoolCtx();

        $curso = Curso::query()
            ->with('curplan')
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->findOrFail($id);

        $countMatriculas = DB::table('matricula')->where('idCursos', $curso->Id)->count();
        $countCalificaciones = DB::table('calificaciones')->where('idCursos', $curso->Id)->count();

        if (($countMatriculas + $countCalificaciones) > 0) {
            $detail = collect([
                $countMatriculas ? "{$countMatriculas} matrículas" : null,
                $countCalificaciones ? "{$countCalificaciones} calificaciones" : null,
            ])->filter()->implode(', ');

            $this->deleteInfo = "No se puede eliminar el curso porque tiene: {$detail}.";
            $this->deleteId = null;
        } else {
            $label = $curso->nombreParaListado();
            $this->deleteId = (int) $curso->Id;
            $this->deleteInfo = "¿Confirma eliminar el curso \"{$label}\"? (Se eliminarán también sus materias del año)";
        }

        $this->showConfirm = true;
    }

    public function delete(): void
    {
        $key = 'cursos:delete:' . (auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 10)) {
            session()->flash('success', 'Demasiados intentos. Espere un momento e intente nuevamente.');
            $this->showConfirm = false;
            $this->reset('deleteId', 'deleteInfo');
            return;
        }
        RateLimiter::hit($key, 60);

        if ($this->deleteId) {
            $ctx = schoolCtx();

            $curso = Curso::query()
                ->where('idNivel', $ctx->idNivel)
                ->where('idTerlec', $ctx->idTerlec)
                ->findOrFail($this->deleteId);

            DB::transaction(function () use ($curso) {
                DB::table('materias')->where('idCursos', $curso->Id)->delete();
                $curso->delete();
            });

            session()->flash('success', "Curso \"{$curso->nombreParaListado()}\" eliminado.");
        }

        $this->showConfirm = false;
        $this->reset('deleteId', 'deleteInfo');
    }

    public function render()
    {
        $ctx = schoolCtx();

        $cursos = Curso::query()
            ->with(['curplan.plan', 'terlec', 'nivel'])
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->orderByRaw('COALESCE(orden, 9999) asc')
            ->orderBy('idCurPlan')
            ->orderBy('Id')
            ->get();

        $planesIds = Plan::query()
            ->where('idNivel', $ctx->idNivel)
            ->pluck('id');

        $curplanes = Curplan::query()
            ->with('plan')
            ->whereIn('idPlan', $planesIds)
            ->orderBy('idPlan')
            ->orderBy('curPlanCurso')
            ->get();

        $terlecs = Terlec::query()
            ->orderBy('orden')
            ->orderBy('ano')
            ->get(['id', 'ano']);

        $niveles = Nivel::query()
            ->orderBy('id')
            ->get(['id', 'nivel', 'abrev']);

        return view('livewire.abm.cursos.index', compact('cursos', 'curplanes', 'terlecs', 'niveles'))
            ->layout('layouts.app', ['pageTitle' => 'Gestión de Cursos / Grados / Salas']);
    }
}

