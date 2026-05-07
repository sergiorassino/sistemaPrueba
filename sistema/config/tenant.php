<?php

/*
 | Configuración del cliente (tenant) activo.
 |
 | Este archivo define los valores por defecto seguros del sistema.
 | TenantOverridesServiceProvider carga encima el archivo
 | config/tenants/{TENANT_SLUG}.php, sobreescribiendo solo lo que difiere.
 |
 | En desarrollo local: cambiar TENANT_SLUG en .env para probar otro cliente.
 | En producción: cada repo de colegio tiene su propio config/tenants/{slug}.php
 |                con los valores específicos de ese cliente.
 */

return [

    /*
     | Identificador del cliente. Se carga desde el .env.
     | Nunca modificar directamente aquí; usar TENANT_SLUG en .env.
     */
    'slug' => env('TENANT_SLUG', 'default'),

    /*
     | Nombre institucional. Usado en encabezados y PDFs cuando
     | no hay dato en la tabla ento (fallback).
     */
    'nombre' => 'Colegio',

    // =========================================================
    // Configuración por módulo
    // =========================================================

    'listados' => [

        /*
         | Título que aparece en el hero de la pantalla de listados
         | y en el título del tab del navegador.
         */
        'titulo' => 'Alumnos por curso',

        /*
         | Si true, muestra el selector "Regulares / Salidos / Todas"
         | antes del PDF. Algunos colegios no usan este filtro.
         */
        'mostrar_filtro_condicion' => true,

    ],

];
