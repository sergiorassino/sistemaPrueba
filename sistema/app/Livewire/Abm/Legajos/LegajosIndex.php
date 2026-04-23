<?php

namespace App\Livewire\Abm\Legajos;

use App\Models\Familia;
use App\Models\Legajo;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LegajosIndex extends Component
{
    use WithPagination;

    // List state
    public string $search        = '';
    public bool   $soloMatricula = false;
    public ?int   $focusId       = null;

    public bool   $showConfirm  = false;
    public ?int   $deleteId     = null;
    public string $deleteInfo   = '';
    public function mount(): void
    {
        $focus = request()->integer('focus');
        $this->focusId = $focus > 0 ? $focus : null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $l = Legajo::findOrFail($id);

        $countMatricula      = DB::table('matricula')->where('idLegajos', $id)->count();
        $countCalificaciones = DB::table('calificaciones')->where('idLegajos', $id)->count();
        $countIef            = DB::table('ief')->where('idLegajos', $id)->count();
        $countApf            = DB::table('apf')->where('idLegajos', $id)->count();
        $countVarios         = DB::table('variosalumnos')->where('idLegajos', $id)->count();

        $total = $countMatricula + $countCalificaciones + $countIef + $countApf + $countVarios;

        if ($total > 0) {
            $detail = collect([
                $countMatricula      ? "{$countMatricula} matrículas"          : null,
                $countCalificaciones ? "{$countCalificaciones} calificaciones"  : null,
                $countIef            ? "{$countIef} registros IEF"             : null,
                $countApf            ? "{$countApf} vínculos familiares"        : null,
                $countVarios         ? "{$countVarios} datos varios"            : null,
            ])->filter()->implode(', ');

            $this->deleteInfo = "No se puede eliminar el legajo de {$l->apellido}, {$l->nombre} porque tiene: {$detail}.";
            $this->deleteId   = null;
        } else {
            $this->deleteId   = $id;
            $this->deleteInfo = "¿Confirma eliminar el legajo de {$l->apellido}, {$l->nombre}?";
        }

        $this->showConfirm = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            $l = Legajo::findOrFail($this->deleteId);
            $nombre = "{$l->apellido}, {$l->nombre}";
            $l->delete();
            session()->flash('success', "Legajo de {$nombre} eliminado.");
        }

        $this->showConfirm = false;
        $this->reset('deleteId', 'deleteInfo');
    }

    public function render()
    {
        $idTerlec = schoolCtx()->idTerlec;

        $query = Legajo::with([
            'familia',
            'matriculas' => function ($q) {
                $q->with(['terlec', 'curso', 'condicion'])
                    ->orderByDesc(
                        DB::raw('(SELECT COALESCE(ano, 0) FROM terlec WHERE terlec.id = matricula.idTerlec LIMIT 1)')
                    )
                    ->orderByDesc('matricula.id');
            },
        ]);

        if ($this->search !== '') {
            $query->buscar($this->search);
        }

        if ($this->soloMatricula) {
            $query->whereHas('matriculas', fn ($q) => $q->where('idTerlec', $idTerlec));
        }

        $legajos  = $query->orderBy('apellido')->orderBy('nombre')->paginate(25);

        return view('livewire.abm.legajos.index2', compact('legajos'))
            ->layout('layouts.app', ['pageTitle' => 'Legajos de Estudiantes']);
    }
}
