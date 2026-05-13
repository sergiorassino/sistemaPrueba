<?php

/*
 | Override de tenant para Colegio Montecristo.
 |
 | Requiere ENABLE_TENANT_OVERRIDES=true y TENANT_SLUG=montecristo.
 | Alternativa: definir solo ALUMNO_ARANCELES_AULICA_URL en .env en ese despliegue.
 */

return [
    'autogestion' => [
        'aranceles_aulica_url' => 'https://familia.aulica.com.ar/login?idCompany=953',
    ],
];
