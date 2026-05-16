<?php

namespace App\Livewire\CalificacionesSecundario;

use App\Models\Curso;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Selección de curso y materia para imprimir la planilla de calificaciones (PDF).
 */
class PlanillaCalificacionesSecundario extends Component
{
    public ?int $cursoId = null;

    public ?int $materiaId = null;

    public function mount(): void
    {
        $this->cursoId = null;
        $this->materiaId = null;
    }

    public function updatedCursoId($value): void
    {
        $this->cursoId = ((int) $value) > 0 ? (int) $value : null;
        $this->materiaId = null;
    }

    public function updatedMateriaId($value): void
    {
        $this->materiaId = ((int) $value) > 0 ? (int) $value : null;
    }

    protected function ensureScopeOr404(): void
    {
        $ctx = schoolCtx();

        if (! $this->cursoId || ! $this->materiaId) {
            return;
        }

        $cursoOk = Curso::query()
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->where('Id', (int) $this->cursoId)
            ->exists();

        if (! $cursoOk) {
            abort(404);
        }

        $materiaOk = DB::table('materias')
            ->where('idNivel', (int) $ctx->idNivel)
            ->where('idTerlec', (int) $ctx->idTerlec)
            ->where('idCursos', (int) $this->cursoId)
            ->where('id', (int) $this->materiaId)
            ->exists();

        if (! $materiaOk) {
            abort(404);
        }
    }

    /**
     * @return Collection<int, mixed>
     */
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

    /**
     * @return Collection<int, mixed>
     */
    public function materias(): Collection
    {
        $ctx = schoolCtx();

        if (! $this->cursoId) {
            return collect();
        }

        return DB::table('materias')
            ->where('idNivel', (int) $ctx->idNivel)
            ->where('idTerlec', (int) $ctx->idTerlec)
            ->where('idCursos', (int) $this->cursoId)
            ->orderBy('ord')
            ->orderBy('id')
            ->get(['id', 'materia', 'abrev', 'ord']);
    }

    public function render()
    {
        $cursos = $this->cursos();
        $materias = $this->materias();

        $cursoLabel = $this->cursoId
            ? optional($cursos->firstWhere('Id', (int) $this->cursoId))->nombreParaListado()
            : null;

        $materiaLabel = $this->materiaId
            ? optional($materias->firstWhere('id', (int) $this->materiaId))->materia
            : null;

        $pdfUrl = null;
        if ($this->cursoId && $this->materiaId) {
            $this->ensureScopeOr404();
            $pdfUrl = route('calificacionesSecundario.planilla.pdf', [
                'curso' => $this->cursoId,
                'materia' => $this->materiaId,
            ]);
        }

        return view('livewire.calificaciones-secundario.planilla-calificaciones-secundario', compact(
            'cursos',
            'materias',
            'cursoLabel',
            'materiaLabel',
            'pdfUrl',
        ))
            ->layout('layouts.app', ['pageTitle' => 'Planilla de calificaciones']);
    }
}
