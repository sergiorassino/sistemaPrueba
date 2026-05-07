<?php

/*
 | Configuración del cliente "NSSC".
 | Sobreescribe solo las claves que difieren de config/tenant.php.
 |
 | Para probar localmente:
 |   1. php artisan se:switch nssc   (ajusta TENANT_SLUG=nssc y DB_DATABASE=ia_nssc)
 |   2. Recargar el navegador
 |
 | BD: ia_nssc — base de datos nueva, sin legacy ScriptCase.
 */

return [

    'nombre'    => 'NSSC',
    'slug'      => 'nssc',
    'localidad' => '',

    'modulos' => [
        'listados'       => true,
        'comunicaciones' => true,
        'disciplinario'  => false,
        'cuotas'         => false,
    ],

    'listados' => [
        // Sin overrides por ahora. Completar si NSSC difiere del default.
    ],

    'comunicaciones' => [
        // Sin overrides por ahora.
    ],

];
