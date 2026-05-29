<?php

/*
 | San Francisco de Asís — personalización declarada en repo (no en .env).
 |
 | Requiere TENANT_SLUG=sanfranciscoasis en el despliegue de ese colegio.
 */

return [
    'autogestion' => [
        'actualizacion_datos' => [
            'habilitado' => true,
            'implementacion' => 'sanfranciscoasis',
        ],
        'ficha_matricula' => [
            'habilitado' => true,
            'implementacion' => 'sanfranciscoasis',
        ],
    ],
];
