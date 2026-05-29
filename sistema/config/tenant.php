<?php

/*
 | Valores por defecto de `config('tenant.*')` para todos los despliegues.
 |
 | Personalización por colegio: archivo versionado `config/tenants/{TENANT_SLUG}.php`
 | (merge recursivo sobre este array). Así cada cliente documenta en git qué difiere
 | y un colegio nuevo puede partir copiando el archivo del más parecido.
 |
 | - slug: se toma de TENANT_SLUG en el entorno (identifica despliegue / BD / archivo tenants).
 | - nombre: fallback del nombre institucional si no hay dato en `ento`.
 | - autogestion: definir por colegio en `config/tenants/{slug}.php` cuando corresponda.
 */

return [

    'slug' => env('TENANT_SLUG', 'default'),

    'nombre' => 'Colegio',

    /**
     * Portal alumno / familia — enlaces y módulos opcionales.
     * Activar solo en `config/tenants/{slug}.php` cuando corresponda.
     */
    'autogestion' => [
        'aranceles_aulica_url' => null,

        /**
         * Actualización de datos personales del legajo (portal familia).
         * `implementacion`: clave de variante en código (ej. sanfranciscoasis).
         */
        'actualizacion_datos' => [
            'habilitado' => false,
            'implementacion' => null,
        ],

        /**
         * Impresión de ficha de matrícula en PDF (portal familia).
         * `implementacion`: clave de variante en código (ej. sanfranciscoasis).
         */
        'ficha_matricula' => [
            'habilitado' => false,
            'implementacion' => null,
        ],

        /**
         * Listado de cuotas pendientes y comprobante de pago (portal familia).
         * `implementacion`: clave de variante en código (ej. sanfranciscoasis).
         */
        'aranceles_escolares' => [
            'habilitado' => false,
            'implementacion' => null,
            /**
             * Banner + PDF de adhesión a débito automático (opcional, por tenant).
             * `banner`: ruta bajo `public/` servida con asset().
             * `formulario_pdf`: ruta bajo `resources/` servida por ruta autenticada.
             */
            'debito_automatico' => [
                'banner' => null,
                'formulario_pdf' => null,
            ],
            /**
             * Banner de medios de pago debajo del listado de cuotas (opcional, por tenant).
             * `banner`: ruta bajo `public/` servida con asset().
             * `url`: enlace al hacer clic en la imagen.
             */
            'medios_pago' => [
                'banner' => null,
                'url' => null,
            ],
            /** URL del servicio SIRO para QR (legacy obtenerQR). Opcional. */
            'siro_qr_url' => null,
        ],
    ],

    /**
     * Boletín / consulta de calificaciones (secundario).
     * Activar solo en `config/tenants/{slug}.php` para colegios que usan régimen TM.
     */
    'boletin' => [
        'mostrar_tercer_materia' => false,
    ],

    /**
     * Menú de Docentes: Cuaderno de Seguimiento Áulico (secundario).
     * Activar solo en `config/tenants/{slug}.php` para colegios que lo usan.
     */
    'portal_docente' => [
        'cuaderno_seguimiento_aulico' => false,
    ],

];
