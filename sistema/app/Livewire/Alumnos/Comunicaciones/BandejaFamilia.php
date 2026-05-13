<?php

namespace App\Livewire\Alumnos\Comunicaciones;

use Livewire\Component;
use App\Comunicaciones\ComunicacionesRepository;

class BandejaFamilia extends Component
{
    public string $filtro = 'todos'; // todos|no_leidos

    public function hydrate(): void
    {
        if (! in_array($this->filtro, ['todos', 'no_leidos'], true)) {
            $this->filtro = 'todos';
        }
    }

    public function render()
    {
        $ctx      = studentCtx();
        $idLegajo = (int) $ctx->idLegajo;
        $idNivel  = (int) $ctx->idNivel;
        $idTerlec = (int) $ctx->idTerlec;

        $hilos = ComunicacionesRepository::bandejaFamilia(
            $idLegajo,
            $idNivel,
            $idTerlec,
            $this->filtro,
            'todos',
            true
        );

        return view('comunicaciones::livewire.alumnos.comunicaciones.bandeja-familia', [
            'hilos' => $hilos,
        ])->layout('layouts.alumno', ['pageTitle' => 'Comunicaciones']);
    }
}
