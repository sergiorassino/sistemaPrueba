<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Mecanismo central de overrides del colegio.
 *
 * Registrado en bootstrap/providers.php.
 * Documentación: sistema/docs/09-template-colegio.md
 */
class TenantOverridesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
         | OVERRIDES DE SERVICIOS / COMPONENTES LIVEWIRE (Nivel 3)
         |
         | Enlazar la clase del colegio en reemplazo de la del paquete.
         | Usar solo cuando hay lógica de negocio diferente, no solo visual.
         |
         | Ejemplo: este colegio tiene un ListadoPorCurso extendido que
         | agrega la columna 'beca_municipal' al listado.
         |
         | $this->app->bind(
         |     \Se\ModuloListados\Livewire\ListadoPorCurso::class,
         |     \App\Custom\Listados\Livewire\ListadoPorCurso::class,
         | );
         */
    }

    public function boot(): void
    {
        /*
         | OVERRIDES DE VISTAS POR NAMESPACE (Nivel 2)
         |
         | prependNamespace hace que Laravel busque primero en la carpeta
         | local antes de usar la vista del paquete.
         |
         | Cualquier archivo en resources/custom/listados/ que tenga el mismo
         | nombre que una vista del paquete la pisa automáticamente.
         | Sin configuración extra; solo agregar el archivo.
         */
        View::prependNamespace('listados', resource_path('custom/listados'));
        // View::prependNamespace('comunicaciones', resource_path('custom/comunicaciones'));

        $this->mergeOverriddenModuleConfigs();
    }

    /**
     * Mergear config de módulos desde tenant.php sobre la del paquete.
     *
     * tenant.listados → se mergea en config('listados')
     * tenant.comunicaciones → se mergea en config('comunicaciones')
     */
    private function mergeOverriddenModuleConfigs(): void
    {
        $modulos = ['listados', 'comunicaciones', 'disciplinario', 'cuotas'];

        foreach ($modulos as $modulo) {
            $overrides = config("tenant.{$modulo}");
            if (is_array($overrides) && ! empty($overrides)) {
                config([$modulo => array_merge(
                    config($modulo, []),
                    $overrides,
                )]);
            }
        }
    }
}
