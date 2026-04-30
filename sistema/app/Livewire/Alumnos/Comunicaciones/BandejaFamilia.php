<?php

namespace App\Livewire\Alumnos\Comunicaciones;

use App\Comunicaciones\ComunicacionesRepository;
use Livewire\Component;

class BandejaFamilia extends Component
{
    public string $filtro = 'todos'; // todos|no_leidos|respondidos

    public function render()
    {
        $ctx      = studentCtx();
        $idLegajo = (int) $ctx->idLegajo;
        $idNivel  = (int) $ctx->idNivel;
        $idTerlec = (int) $ctx->idTerlec;

        $hilos = ComunicacionesRepository::bandejaFamilia(
            $idLegajo, $idNivel, $idTerlec, $this->filtro
        );

        return view('livewire.alumnos.comunicaciones.bandeja-familia', [
            'hilos' => $hilos,
        ])->layout('layouts.alumno', ['pageTitle' => 'Comunicaciones']);
    }
}
