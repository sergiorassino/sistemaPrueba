<?php

namespace App\Livewire\Abm\LegajosProfesor;

use App\Models\Profesor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class LegajosProfesorIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $focusId = null;

    public bool $showConfirm = false;

    public ?int $deleteId = null;

    public string $deleteInfo = '';

    public bool $puedeEliminar = true;

    public function mount(): void
    {
        $focus = request()->integer('focus');
        $this->focusId = $focus > 0 ? $focus : null;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    protected function scopedProfesorOrFail(int $id): Profesor
    {
        return Profesor::query()
            ->delNivel((int) schoolCtx()->idNivel)
            ->whereKey($id)
            ->firstOrFail();
    }

    public function confirmDelete(int $id): void
    {
        $p = $this->scopedProfesorOrFail($id);
        $deps = $this->dependenciasParaBorrar($id);

        if ($deps !== []) {
            $modulos = collect($deps)
                ->map(fn (int $cant, string $modulo) => "{$modulo} ({$cant})")
                ->implode(', ');
            $this->puedeEliminar = false;
            $this->deleteId = null;
            $this->deleteInfo = "No se puede eliminar el legajo de {$p->apellido}, {$p->nombre} porque tiene: {$modulos}.";
        } else {
            $this->puedeEliminar = true;
            $this->deleteId = $id;
            $this->deleteInfo = "¿Confirma eliminar el legajo de {$p->apellido}, {$p->nombre} en este nivel?";
        }

        $this->showConfirm = true;
    }

    public function delete(): void
    {
        $key = 'legajos-profesor:delete:'.(auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 10)) {
            session()->flash('success', 'Demasiados intentos. Espere un momento e intente nuevamente.');
            $this->showConfirm = false;
            $this->reset('deleteId', 'deleteInfo', 'puedeEliminar');

            return;
        }
        RateLimiter::hit($key, 60);

        if ($this->deleteId && $this->puedeEliminar) {
            $deps = $this->dependenciasParaBorrar($this->deleteId);
            if ($deps !== []) {
                $this->puedeEliminar = false;
                $this->showConfirm = true;

                return;
            }

            $p = $this->scopedProfesorOrFail($this->deleteId);
            $nombre = "{$p->apellido}, {$p->nombre}";
            $p->delete();
            session()->flash('success', "Legajo de {$nombre} eliminado.");
        }

        $this->showConfirm = false;
        $this->reset('deleteId', 'deleteInfo', 'puedeEliminar');
        $this->puedeEliminar = true;
    }

    /**
     * @return array<string, int>
     */
    private function dependenciasParaBorrar(int $id): array
    {
        $checks = [
            'ppc' => ['col' => 'idProfesor', 'label' => 'Asignación por materia'],
        ];

        if (Schema::hasTable('licencias')) {
            $checks['licencias'] = ['col' => 'idPersonal', 'label' => 'Licencias'];
        }

        $deps = [];
        foreach ($checks as $tabla => $meta) {
            if (! Schema::hasTable($tabla)) {
                continue;
            }
            $cant = (int) DB::table($tabla)->where($meta['col'], $id)->count();
            if ($cant > 0) {
                $deps[$meta['label']] = $cant;
            }
        }

        return $deps;
    }

    public function render()
    {
        $idNivel = (int) (schoolCtx()->idNivel ?? 0);

        $query = Profesor::query()
            ->with('tipo')
            ->delNivel($idNivel > 0 ? $idNivel : null);

        if ($this->search !== '') {
            $query->buscar($this->search);
        }

        $profesores = $query->orderBy('apellido')->orderBy('nombre')->paginate(25);

        return view('livewire.abm.legajos-profesor.index', compact('profesores'))
            ->layout('layouts.app', ['pageTitle' => 'Legajos del docente']);
    }
}
