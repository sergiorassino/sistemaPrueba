<?php

namespace App\Livewire\Comunicaciones;

use Livewire\Component;
use App\Comunicaciones\ComunicacionesRepository;

class BandejaGestion extends Component
{
    public string $filtro = 'todos'; // todos|no_leidos
    public string $periodo = 'actual'; // actual|historico

    public function mount(): void
    {
        abort_unless(tienePermiso(51), 403, 'Sin permiso para ver comunicaciones.');
    }

    public function hydrate(): void
    {
        if (! in_array($this->filtro, ['todos', 'no_leidos'], true)) {
            $this->filtro = 'todos';
        }
    }

    public function updatedFiltro(): void
    {
        // Reactivo: Livewire re-renderiza automáticamente
    }

    public function render()
    {
        $ctx       = schoolCtx();
        $idProf    = (int) $ctx->idProfesor;
        $idNivel   = (int) $ctx->idNivel;
        $idTerlec  = (int) $ctx->idTerlec;

        $hilos = ComunicacionesRepository::bandejaProfesor(
            $idProf,
            $idNivel,
            $idTerlec,
            $this->filtro,
            'todos',
            $this->periodo !== 'historico'
        );

        return view('comunicaciones::livewire.comunicaciones.bandeja-gestion', [
            'hilos' => $hilos,
        ])->layout('layouts.app', ['pageTitle' => 'Comunicaciones']);
    }
}
