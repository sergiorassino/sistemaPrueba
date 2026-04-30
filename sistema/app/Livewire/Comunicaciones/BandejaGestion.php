<?php

namespace App\Livewire\Comunicaciones;

use App\Comunicaciones\ComunicacionesRepository;
use Livewire\Component;

class BandejaGestion extends Component
{
    public string $filtro = 'todos'; // todos|no_leidos|respondidos

    public function mount(): void
    {
        abort_unless(tienePermiso(51), 403, 'Sin permiso para ver comunicaciones.');
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
            $idProf, $idNivel, $idTerlec, $this->filtro
        );

        return view('livewire.comunicaciones.bandeja-gestion', [
            'hilos' => $hilos,
        ])->layout('layouts.app', ['pageTitle' => 'Comunicaciones']);
    }
}
