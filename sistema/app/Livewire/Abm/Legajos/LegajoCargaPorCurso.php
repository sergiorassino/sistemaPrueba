<?php

namespace App\Livewire\Abm\Legajos;

use App\Livewire\Abm\Legajos\Concerns\LegajoCargaPorCursoPanel;
use Livewire\Component;

class LegajoCargaPorCurso extends Component
{
    use LegajoCargaPorCursoPanel;

    public function mount(): void
    {
        abort_unless(tienePermiso(2), 403, 'Sin permiso para modificar legajos de estudiantes.');
        $this->refrescarCargaPorCursoCursosOpciones();
    }

    public function render()
    {
        return view('livewire.abm.legajos.carga-por-curso', $this->datosPanelCargaPorCurso())
            ->layout('layouts.app', ['pageTitle' => 'Carga de legajo por curso']);
    }
}
