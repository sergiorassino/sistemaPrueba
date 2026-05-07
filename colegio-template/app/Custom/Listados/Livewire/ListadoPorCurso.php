<?php

namespace App\Custom\Listados\Livewire;

use Se\ModuloListados\Livewire\ListadoPorCurso as Base;

/**
 * Override específico de este colegio — Listado por Curso.
 *
 * Registrar en TenantOverridesServiceProvider::register():
 *
 *   $this->app->bind(
 *       \Se\ModuloListados\Livewire\ListadoPorCurso::class,
 *       \App\Custom\Listados\Livewire\ListadoPorCurso::class,
 *   );
 *
 * Retirar cuando el comportamiento personalizado entre al paquete canónico.
 */
class ListadoPorCurso extends Base
{
    /**
     * Ejemplo: agregar una columna exclusiva de este colegio al listado.
     * Descomentar y adaptar según necesidad real.
     */
    // protected function columnasDefault(): array
    // {
    //     return [...parent::columnasDefault(), 'legajos.beca_municipal'];
    // }
}
