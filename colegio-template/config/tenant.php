<?php

return [

    /*
     | Datos institucionales del colegio.
     | Usados en encabezados de PDFs, notificaciones y el layout.
     */
    'nombre'    => env('APP_NAME', 'Colegio'),
    'slug'      => env('TENANT_SLUG', 'colegio'),
    'localidad' => 'Ciudad',
    'logo'      => null,   // null = usar schoolLogoUrl() que lee de la BD (ento.logo_path)

    /*
     | Módulos activos para este colegio.
     |
     | Un módulo en false omite su participación en el menú, pero las rutas
     | y el ServiceProvider siguen registrados (el paquete está instalado).
     | Para desactivar rutas habría que no instalar el paquete en composer.json.
     */
    'modulos' => [
        'listados'       => true,
        'comunicaciones' => true,
        'disciplinario'  => false,
        'cuotas'         => false,
    ],

    /*
     | Overrides de configuración de módulos específicos.
     | Estos valores se mergean sobre los defaults del paquete en
     | TenantOverridesServiceProvider::mergeOverriddenModuleConfigs().
     |
     | Solo agregar las keys que realmente difieren del default del paquete.
     */
    'listados' => [
        // 'features' => [
        //     'por_materia' => false,
        // ],
    ],

    'comunicaciones' => [
        // 'familia_puede_responder' => true,
    ],

];
