<?php

namespace App\Livewire\Parametrizacion;

use App\Comunicaciones\CanalesPolicy;
use App\Models\ComCanal;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ComCanalesIndex extends Component
{
    // Edición inline de un canal
    public ?int $editandoId = null;
    public bool $editPuedeIniciar   = false;
    public bool $editPuedeResponder = false;
    public array $editMedios        = [];
    public bool $editActivo         = true;

    public function mount(): void
    {
        abort_unless(tienePermiso(53), 403, 'Sin permiso para administrar canales de comunicación.');
    }

    public function iniciarEdicion(int $id): void
    {
        $canal = ComCanal::findOrFail($id);
        $this->editandoId       = $id;
        $this->editPuedeIniciar = $canal->puede_iniciar;
        $this->editPuedeResponder = $canal->puede_responder;
        $this->editMedios       = $canal->medios_permitidos ?? [];
        $this->editActivo       = $canal->activo;
    }

    public function cancelarEdicion(): void
    {
        $this->editandoId = null;
    }

    public function guardar(): void
    {
        abort_unless(tienePermiso(53), 403);

        $this->validate([
            'editPuedeIniciar'   => 'boolean',
            'editPuedeResponder' => 'boolean',
            'editMedios'         => 'array',
            'editMedios.*'       => 'string|in:push,email,whatsapp',
            'editActivo'         => 'boolean',
        ]);

        $canal = ComCanal::findOrFail($this->editandoId);
        $canal->update([
            'puede_iniciar'    => $this->editPuedeIniciar,
            'puede_responder'  => $this->editPuedeResponder,
            'medios_permitidos' => array_values(array_unique($this->editMedios)),
            'activo'           => $this->editActivo,
        ]);

        // Invalida caché de este canal
        Cache::forget("com_canal:{$canal->rol_emisor}:{$canal->rol_receptor}");
        Cache::forget("com_canal:{$canal->rol_receptor}:{$canal->rol_emisor}");

        $this->editandoId = null;
        session()->flash('success', 'Canal actualizado correctamente.');
    }

    public function toggleMedio(string $medio): void
    {
        if (in_array($medio, $this->editMedios, true)) {
            $this->editMedios = array_values(array_filter($this->editMedios, fn ($m) => $m !== $medio));
        } else {
            $this->editMedios[] = $medio;
        }
    }

    public function render()
    {
        $canales = ComCanal::query()->orderBy('rol_emisor')->orderBy('rol_receptor')->get();
        $etiquetas = ComCanal::etiquetasRoles();
        $mediosDisponibles = ComCanal::mediosDisponibles();

        return view('livewire.parametrizacion.com-canales-index', [
            'canales'          => $canales,
            'etiquetas'        => $etiquetas,
            'mediosDisponibles' => $mediosDisponibles,
        ])->layout('layouts.app', ['pageTitle' => 'Canales de Comunicación']);
    }
}
