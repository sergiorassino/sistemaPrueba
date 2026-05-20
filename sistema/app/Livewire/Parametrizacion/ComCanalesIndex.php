<?php

namespace App\Livewire\Parametrizacion;

use App\Livewire\Concerns\RequiresPermisoConfiguracion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Models\ComCanal;

class ComCanalesIndex extends Component
{
    use RequiresPermisoConfiguracion;

    // Edición inline de un canal
    public ?int $editandoId = null;
    public bool $editPuedeIniciar   = false;
    public bool $editPuedeResponder = false;
    public array $editMedios        = [];
    public bool $editActivo         = true;

    // Alta de canal
    public bool $mostrandoFormNuevo = false;
    public string $nuevoRolEmisor = 'profesor';
    public string $nuevoRolReceptor = 'familia';
    public bool $nuevoPuedeIniciar = false;
    public bool $nuevoPuedeResponder = false;
    public array $nuevoMedios = [];
    public bool $nuevoActivo = true;

    /** Modal confirmar borrado de canal */
    public bool $showConfirmEliminar = false;
    public ?int $eliminarId = null;
    public string $eliminarEtiqueta = '';

    public function mount(): void
    {
        abort_unless(tienePermiso(5), 403, 'Sin permiso para administrar canales de comunicación.');
    }

    public function abrirFormNuevo(): void
    {
        $this->cancelarEdicion();
        $this->mostrandoFormNuevo = true;
        $this->nuevoRolEmisor = 'profesor';
        $this->nuevoRolReceptor = 'familia';
        $this->nuevoPuedeIniciar = false;
        $this->nuevoPuedeResponder = false;
        $this->nuevoMedios = ['push', 'email'];
        $this->nuevoActivo = true;
    }

    public function cancelarFormNuevo(): void
    {
        $this->mostrandoFormNuevo = false;
    }

    public function guardarNuevo(): void
    {
        abort_unless(tienePermiso(5), 403);

        $roles = ComCanal::rolesClave();

        $this->validate([
            'nuevoRolEmisor'       => ['required', 'string', Rule::in($roles)],
            'nuevoRolReceptor'     => ['required', 'string', Rule::in($roles)],
            'nuevoPuedeIniciar'    => 'boolean',
            'nuevoPuedeResponder'  => 'boolean',
            'nuevoMedios'          => 'array',
            'nuevoMedios.*'        => 'string|in:push,email,whatsapp',
            'nuevoActivo'          => 'boolean',
        ], [], [
            'nuevoRolEmisor'   => 'emisor',
            'nuevoRolReceptor' => 'receptor',
        ]);

        if ($this->nuevoRolEmisor === $this->nuevoRolReceptor) {
            $this->addError('nuevoRolReceptor', 'El emisor y el receptor deben ser distintos.');

            return;
        }

        $yaExiste = ComCanal::query()
            ->where('rol_emisor', $this->nuevoRolEmisor)
            ->where('rol_receptor', $this->nuevoRolReceptor)
            ->exists();

        if ($yaExiste) {
            $this->addError('nuevoRolReceptor', 'Ya existe un canal para esta combinación de emisor y receptor.');

            return;
        }

        $canal = new ComCanal([
            'rol_emisor'        => $this->nuevoRolEmisor,
            'rol_receptor'      => $this->nuevoRolReceptor,
            'puede_iniciar'     => $this->nuevoPuedeIniciar,
            'puede_responder'   => $this->nuevoPuedeResponder,
            'medios_permitidos' => array_values(array_unique($this->nuevoMedios)),
            'activo'            => $this->nuevoActivo,
        ]);
        $canal->created_at = now();
        $canal->updated_at = now();
        $canal->save();

        $this->mostrandoFormNuevo = false;
        session()->flash('success', 'Canal creado correctamente.');
    }

    public function toggleMedioNuevo(string $medio): void
    {
        if (in_array($medio, $this->nuevoMedios, true)) {
            $this->nuevoMedios = array_values(array_filter($this->nuevoMedios, fn ($m) => $m !== $medio));
        } else {
            $this->nuevoMedios[] = $medio;
        }
    }

    public function iniciarEdicion(int $id): void
    {
        $this->cancelarFormNuevo();
        $canal = ComCanal::findOrFail($id);
        $this->editandoId         = $id;
        $this->editPuedeIniciar   = $canal->puede_iniciar;
        $this->editPuedeResponder = $canal->puede_responder;
        $this->editMedios         = $canal->medios_permitidos ?? [];
        $this->editActivo         = $canal->activo;
    }

    public function cancelarEdicion(): void
    {
        $this->editandoId = null;
    }

    public function guardar(): void
    {
        abort_unless(tienePermiso(5), 403);

        $this->validate([
            'editPuedeIniciar'   => 'boolean',
            'editPuedeResponder' => 'boolean',
            'editMedios'         => 'array',
            'editMedios.*'       => 'string|in:push,email,whatsapp',
            'editActivo'         => 'boolean',
        ]);

        $canal = ComCanal::findOrFail($this->editandoId);
        $canal->update([
            'puede_iniciar'     => $this->editPuedeIniciar,
            'puede_responder'   => $this->editPuedeResponder,
            'medios_permitidos' => array_values(array_unique($this->editMedios)),
            'activo'            => $this->editActivo,
        ]);

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

    public function confirmarEliminar(int $id): void
    {
        abort_unless(tienePermiso(5), 403);

        $canal = ComCanal::findOrFail($id);
        $etiquetas = ComCanal::etiquetasRoles();
        $de = $etiquetas[$canal->rol_emisor] ?? $canal->rol_emisor;
        $para = $etiquetas[$canal->rol_receptor] ?? $canal->rol_receptor;

        $this->cancelarFormNuevo();
        if ($this->editandoId === $id) {
            $this->cancelarEdicion();
        }

        $this->eliminarId = $id;
        $this->eliminarEtiqueta = "{$de} → {$para}";
        $this->showConfirmEliminar = true;
    }

    public function cerrarConfirmEliminar(): void
    {
        $this->showConfirmEliminar = false;
        $this->eliminarId = null;
        $this->eliminarEtiqueta = '';
    }

    public function eliminarCanal(): void
    {
        abort_unless(tienePermiso(5), 403);

        if ($this->eliminarId === null) {
            $this->cerrarConfirmEliminar();

            return;
        }

        $canal = ComCanal::findOrFail($this->eliminarId);
        $rolEmisor = $canal->rol_emisor;
        $rolReceptor = $canal->rol_receptor;

        $canal->delete();

        Cache::forget("com_canal:{$rolEmisor}:{$rolReceptor}");
        Cache::forget("com_canal:{$rolReceptor}:{$rolEmisor}");

        if ($this->editandoId === $this->eliminarId) {
            $this->cancelarEdicion();
        }

        $this->cerrarConfirmEliminar();
        session()->flash('success', 'Canal eliminado correctamente.');
    }

    public function render()
    {
        $canales = ComCanal::query()->orderBy('rol_emisor')->orderBy('rol_receptor')->get();
        $etiquetas = ComCanal::etiquetasRoles();
        $mediosDisponibles = ComCanal::mediosDisponibles();

        return view('comunicaciones::livewire.parametrizacion.com-canales-index', [
            'canales'           => $canales,
            'etiquetas'         => $etiquetas,
            'mediosDisponibles' => $mediosDisponibles,
        ])->layout('layouts.app', ['pageTitle' => 'Canales de Comunicación']);
    }
}
