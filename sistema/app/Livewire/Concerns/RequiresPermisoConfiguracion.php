<?php

namespace App\Livewire\Concerns;

trait RequiresPermisoConfiguracion
{
    public function bootRequiresPermisoConfiguracion(): void
    {
        abort_unless(tienePermiso(14), 403, 'Sin permiso para el módulo de configuración.');
    }
}
