<?php

namespace App\Livewire\Comunicaciones;

use Livewire\Component;
use App\Comunicaciones\ComunicacionesRepository;

class BandejaRevision extends Component
{
    public string $filtro = 'todos'; // todos|no_leidos
    public string $direccion = 'todos'; // todos|recibidos|enviados
    public string $periodo = 'actual'; // actual|historico

    public int $idProfesorObjetivo;
    public ?string $profesorObjetivoLabel = null;

    public string $profesorSearch = '';
    public array $profesorResults = [];

    public function mount(): void
    {
        abort_unless(tienePermiso(51) && tienePermiso(56), 403, 'Sin permiso para revisar comunicaciones.');

        $ctx = schoolCtx();
        $this->idProfesorObjetivo = (int) $ctx->idProfesor;
        $prof = $ctx->profesor();
        $this->profesorObjetivoLabel = $prof ? trim("{$prof->apellido}, {$prof->nombre}") : null;
    }

    public function hydrate(): void
    {
        if (! in_array($this->filtro, ['todos', 'no_leidos'], true)) {
            $this->filtro = 'todos';
        }
    }

    public function updatedProfesorSearch(): void
    {
        $ctx = schoolCtx();
        $this->profesorResults = ComunicacionesRepository::buscarProfesores(
            (int) $ctx->idNivel,
            $this->profesorSearch,
            15
        );
    }

    public function selectProfesor(int $id, string $label): void
    {
        $this->idProfesorObjetivo = (int) $id;
        $this->profesorObjetivoLabel = trim($label);
        $this->profesorSearch = '';
        $this->profesorResults = [];
    }

    public function render()
    {
        $ctx      = schoolCtx();
        $idNivel  = (int) $ctx->idNivel;
        $idTerlec = (int) $ctx->idTerlec;

        $dir = in_array($this->direccion, ['todos', 'recibidos', 'enviados'], true) ? $this->direccion : 'todos';

        $hilos = ComunicacionesRepository::bandejaProfesor(
            (int) $this->idProfesorObjetivo,
            $idNivel,
            $idTerlec,
            $this->filtro,
            $dir === 'todos' ? 'todos' : $dir,
            $this->periodo !== 'historico'
        );

        return view('comunicaciones::livewire.comunicaciones.bandeja-revision', [
            'hilos' => $hilos,
        ])->layout('layouts.app', ['pageTitle' => 'Comunicaciones · Revisión']);
    }
}
