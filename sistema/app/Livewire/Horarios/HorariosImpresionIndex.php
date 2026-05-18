<?php

namespace App\Livewire\Horarios;

use App\Models\Curso;
use App\Support\HorariosProfesores;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class HorariosImpresionIndex extends Component
{
    /*
     * Depuración SQL en pantalla — desactivada (mismo criterio que HorariosCargaIndex).
     * Para reactivar: descomentar propiedad, bloque en render() y Blade en horarios-impresion-index.
     *
     * public bool $mostrarPanelSqlImpresionCurso = true;
     */

    /** curso|profesor */
    public string $modo = 'curso';

    public ?int $cursoId = null;

    public ?int $profesorId = null;

    public function updatedModo(): void
    {
        $this->cursoId = null;
        $this->profesorId = null;
    }

    public function pdfUrl(): ?string
    {
        if ($this->modo === 'curso' && ($this->cursoId ?? 0) > 0) {
            return route('horarios.pdf.curso', ['curso' => $this->cursoId]);
        }
        if ($this->modo === 'profesor' && ($this->profesorId ?? 0) > 0) {
            return route('horarios.pdf.profesor', ['profesor' => $this->profesorId]);
        }

        return null;
    }

    /**
     * @return Collection<int, Curso>
     */
    public function cursos(): Collection
    {
        $ctx = schoolCtx();

        return Curso::query()
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->orderBy('orden')
            ->orderBy('cursec')
            ->get(['Id', 'cursec', 'orden', 'idCurPlan', 'turno', 'c', 's']);
    }

    /**
     * @return Collection<int, object{id:int, label:string}>
     */
    public function profesores(): Collection
    {
        $ctx = schoolCtx();
        $idNivel = (int) ($ctx->idNivel ?? 0);

        return DB::table('profesores as p')
            ->whereExists(function ($q) use ($idNivel, $ctx) {
                $q->selectRaw('1')
                    ->from('ppc')
                    ->join('materias as m', 'm.id', '=', 'ppc.idMateria')
                    ->whereColumn('ppc.idProfesor', 'p.id')
                    ->where('m.idNivel', $idNivel)
                    ->where('m.idTerlec', (int) $ctx->idTerlec);
            })
            ->orderBy('p.apellido')
            ->orderBy('p.nombre')
            ->get(['p.id', 'p.apellido', 'p.nombre'])
            ->map(fn ($r) => (object) [
                'id' => (int) $r->id,
                'label' => trim(((string) $r->apellido).', '.((string) $r->nombre)),
            ]);
    }

    public function render()
    {
        $consultaSqlImpresionCurso = '';
        // if ($this->modo === 'curso' && ($this->cursoId ?? 0) > 0) {
        //     $consultaSqlImpresionCurso = HorariosProfesores::textoDepuracionSqlImpresionHorarioCurso((int) $this->cursoId);
        // }

        return view('livewire.horarios.horarios-impresion-index', [
            'cursos' => $this->cursos(),
            'profesores' => $this->profesores(),
            'pdfUrl' => $this->pdfUrl(),
            'consultaSqlImpresionCurso' => $consultaSqlImpresionCurso,
        ])->layout('layouts.app', ['pageTitle' => 'Impresión de horarios']);
    }
}
