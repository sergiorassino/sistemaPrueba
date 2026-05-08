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

        /*
         | Si true, el menú y el dashboard enlazan al módulo listadoPorCurso_v1.2
         | (pantalla simple: apellido, nombre, DNI) en lugar del listado con PDF.
         */
        'usar_listado_por_curso_v12' => false,

        /*
         | Título del ítem de menú / tarjeta cuando está activo listadoPorCurso_v1.2.
         */
        'titulo_listado_por_curso_v12' => 'Listado por curso',

        /*
         | Selector explícito de versión para el acceso desde el sidebar/dashboard.
         |
         | Valores sugeridos:
         | - 'v1.0' : módulo original (listados con PDF)  -> route('listados.por-curso')
         | - 'v1.2' : módulo alternativo existente        -> route('listadoPorCurso.v1_2')
         | - 'v2.0' : módulo nuevo/independiente          -> route('listadosV2.por-curso')
         |
         | Regla: esto debe ser "declarativo" por tenant, para que el cambio en A
         | no afecte B/C y quede auditable en config.
         */
        'por_curso_version' => 'v1.0',

    ],

    /*
     | Módulo: Seguimiento disciplinario
     */
    'disciplinario' => [

        /*
         | Selector de versión para el sidebar/dashboard.
         |
         | Valores:
         | - 'v1.0' : módulo original en app/ -> route('seguimiento.disciplinario')
         | - 'v2.0' : paquete independiente   -> route('disciplinarioV2.index')
         */
        'version' => 'v1.0',

    ],

    /*
     | Sidebar: versionado por módulo (para tooltips y auditoría visual).
     |
     | Cada ítem del sidebar puede mostrar su versión propia en el tooltip:
     |  "Nombre del ítem vX.Y".
     |
     | Nota: si no se define un módulo acá, el layout hace fallback a
     | tenant.sidebar.ui_version (si existiera) o 'v1.0'.
     */
    'sidebar' => [
        'modulos' => [
            'core' => 'v1.0',
            'estudiantes' => 'v1.0',
            'listados' => 'v1.0',
            'push' => 'v1.0',
            'cuaderno_comunicados' => 'v1.0',
            'calificaciones' => 'v1.0',
            'comunicaciones' => 'v1.0',
            'disciplinario' => 'v1.0',
            'configuracion' => 'v1.0',
        ],
    ],

];
