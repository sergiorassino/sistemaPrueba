<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Carga la configuración y los overrides del cliente (tenant) activo.
 *
 * Flujo:
 *  1. Lee TENANT_SLUG del .env.
 *  2. Si existe config/tenants/{slug}.php, mergea sus valores sobre los
 *     defaults de config/tenant.php (array_replace_recursive: las claves del
 *     archivo del cliente ganan; lo que no aparece se hereda del default).
 *  3. Prepende el path de vistas custom del cliente en el namespace del paquete,
 *     para que un archivo en resources/views/custom/{slug}/listados/ pise
 *     la vista correspondiente del módulo listados (resources/views/listados).
 *
 * Override Nivel 1 — config (sin código):
 *   config('tenant.listados.titulo')
 *   config('tenant.listados.mostrar_filtro_condicion')
 *
 * Override Nivel 2 — vista (solo Blade):
 *   Crear resources/views/custom/{slug}/listados/livewire/listados/por-curso.blade.php
 *   → pisa la vista 'listados::livewire.listados.por-curso' del módulo en app.
 *
 *   Hook de snippet (sin namespace):
 *   @includeIf('custom.' . tenantConfig('slug') . '.listados.hero-badge')
 *   → resuelve resources/views/custom/{slug}/listados/hero-badge.blade.php
 *
 * Override Nivel 3 — lógica PHP:
 *   Descomentar el ejemplo en register() y crear la clase en app/Custom/.
 */
class TenantOverridesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Permite apagar overrides (merge de config/tenants/*.php y vistas custom) sin tocar
        // el identificador del cliente: `tenant.slug` sigue viniendo de config/tenant.php
        // (TENANT_SLUG), necesario p. ej. para rutas de disco compartidas (logos por colegio).
        $enabled = filter_var(env('ENABLE_TENANT_OVERRIDES', false), FILTER_VALIDATE_BOOL);
        if (! $enabled) {
            return;
        }

        $slug = env('TENANT_SLUG', 'default');
        $tenantFile = config_path("tenants/{$slug}.php");

        if (file_exists($tenantFile)) {
            $overrides = require $tenantFile;

            config([
                'tenant' => array_replace_recursive(
                    config('tenant', []),
                    $overrides,
                    // slug siempre del .env, no sobreescribible desde el archivo
                    ['slug' => $slug],
                ),
            ]);
        } else {
            // Al menos fijar el slug aunque no haya archivo de overrides.
            config(['tenant.slug' => $slug]);
        }

        /*
         | NIVEL 3 — Override de servicio o componente Livewire.
         | Descomentar y adaptar cuando un cliente necesite lógica PHP distinta.
         |
         | Ejemplo: este colegio tiene su propio ListadoPorCurso que agrega la
         | columna 'beca_municipal' al catálogo de campos del PDF.
         |
         | if ($slug === 'montecristo') {
         |     $this->app->bind(
         |         \App\Livewire\Listados\ListadoPorCurso::class,
         |         \App\Custom\Montecristo\Listados\ListadoPorCurso::class,
         |     );
         | }
         */
    }

    public function boot(): void
    {
        $enabled = filter_var(env('ENABLE_TENANT_OVERRIDES', false), FILTER_VALIDATE_BOOL);
        if (! $enabled) {
            return;
        }

        $slug = config('tenant.slug', 'default');

        /*
         | NIVEL 2 — Override de vistas por namespace del paquete.
         |
         | Cualquier archivo en resources/views/custom/{slug}/listados/ que tenga
         | el mismo path que una vista del paquete la pisa automáticamente.
         |
         | Ejemplo: resources/views/custom/montecristo/listados/livewire/listados/por-curso.blade.php
         | → pisa 'listados::livewire.listados.por-curso' (vistas en resources/views/listados).
         |
         | Los hooks @includeIf('custom.{slug}.listados.hero-badge') también
         | resuelven desde esta carpeta (mismo directorio base).
         */
        View::prependNamespace('listados', resource_path("views/custom/{$slug}/listados"));
    }
}
