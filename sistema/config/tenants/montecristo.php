<?php

/*
 | Configuración del cliente "Colegio Montecristo".
 | Sobreescribe solo las claves que difieren de config/tenant.php.
 |
 | Para probar localmente:
 |   1. Cambiar TENANT_SLUG=montecristo en .env
 |   2. Cambiar DB_DATABASE=se_montecristo en .env
 |   3. Ejecutar: php artisan config:clear && php artisan view:clear
 |   4. Recargar el navegador
 */

return [

    'nombre' => 'Colegio Montecristo',

    'listados' => [

        // Montecristo prefiere este nombre para la sección de listados.
        'titulo' => 'Nómina de alumnos',

        // Montecristo no usa el filtro de condición de matrícula en sus PDFs:
        // todos sus alumnos son regulares y no necesitan distinguir.
        'mostrar_filtro_condicion' => false,

    ],

];
