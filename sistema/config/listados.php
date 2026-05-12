<?php

return [

    /*
     | Título de la sección de listados en la UI y en el tab del navegador.
     | El colegio puede sobreescribir este valor desde config/tenant.php
     | usando la clave 'listados.titulo' (Nivel 1 de override, sin tocar código).
     |
     | Nota: tenantConfig('listados.titulo', ...) tiene prioridad sobre esta config.
     | Este default solo aplica si el colegio no define la clave en tenant.php.
     */
    'titulo' => 'Alumnos por curso',

    /*
     | Si true, muestra el selector "Regulares / Salidos / Todos" antes de generar
     | el PDF. Algunos colegios no usan este filtro (todos sus alumnos son regulares).
     */
    'mostrar_filtro_condicion' => true,

];
