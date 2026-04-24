<?php

namespace App\Livewire\Parametrizacion;

use App\Models\CampoListadoAlumno;
use App\Services\CamposListadoAlumnosLegajosSync;
use Livewire\Component;

class CamposListadoAlumnosIndex extends Component
{
    public function sincronizarDesdeLegajos(CamposListadoAlumnosLegajosSync $sync): void
    {
        $n = $sync->sincronizarDesdeSchema();
        if ($n > 0) {
            session()->flash('status', "Se agregaron {$n} columna(s) nueva(s) desde la tabla legajos.");
        } else {
            session()->flash('status', 'No hay columnas nuevas en legajos respecto al listado registrado.');
        }
    }

    public function toggleVisible(int $id): void
    {
        $c = CampoListadoAlumno::query()->whereKey($id)->firstOrFail();
        $c->visible_listado = ! $c->visible_listado;
        $c->save();
    }

    public function render()
    {
        $campos = CampoListadoAlumno::query()
            ->orderBy('orden')
            ->orderBy('columna')
            ->get();

        return view('livewire.parametrizacion.campos-listado-alumnos-index', compact('campos'))
            ->layout('layouts.app', ['pageTitle' => 'Campos para listados (legajos)']);
    }
}
