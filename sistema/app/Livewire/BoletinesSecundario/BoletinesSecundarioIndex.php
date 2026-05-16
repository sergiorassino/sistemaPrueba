<?php

namespace App\Livewire\BoletinesSecundario;

use App\Models\Curso;
use App\Models\Matricula;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Boletines / informe de progreso escolar (nivel secundario):
 * elige curso y genera el PDF oficial por matrícula.
 */
class BoletinesSecundarioIndex extends Component
{
    /** Curso seleccionado (`cursos.Id`) dentro del contexto de sesión. */
    public ?int $cursoId = null;

    public function updatedCursoId(mixed $value): void
    {
        $this->cursoId = ((int) $value) > 0 ? (int) $value : null;
    }

    /**
     * @return Collection<int, Matricula>
     */
    public function matriculasDelCurso(): Collection
    {
        if (! $this->cursoId) {
            return collect();
        }

        $ctx = schoolCtx();

        $cursoOk = Curso::query()
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->where('Id', (int) $this->cursoId)
            ->exists();

        if (! $cursoOk) {
            return collect();
        }

        return Matricula::query()
            ->with('legajo')
            ->where('idCursos', (int) $this->cursoId)
            ->where('idNivel', (int) $ctx->idNivel)
            ->where('idTerlec', (int) $ctx->idTerlec)
            ->get()
            ->sortBy(function (Matricula $m) {
                $a = mb_strtolower((string) ($m->legajo?->apellido ?? ''));
                $n = mb_strtolower((string) ($m->legajo?->nombre ?? ''));

                return [$a, $n];
            })
            ->values();
    }

    public function cursos(): Collection
    {
        $ctx = schoolCtx();

        return Curso::query()
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->orderByRaw('COALESCE(orden, 9999) asc')
            ->orderBy('Id')
            ->get(['Id', 'cursec', 'orden', 'idCurPlan', 'turno', 'c', 's']);
    }

    public function render()
    {
        return view('livewire.boletines-secundario.index', [
            'cursos' => $this->cursos(),
            'matriculas' => $this->matriculasDelCurso(),
        ])
            ->layout('layouts.app', ['pageTitle' => 'Boletines (secundario) v1.0']);
    }
}
