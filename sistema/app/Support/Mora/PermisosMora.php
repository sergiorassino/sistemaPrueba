<?php

namespace App\Support\Mora;

/**
 * Módulo «Gestión de mora» (solo sesión nivel Administración, `niveles.id = 5`).
 *
 * El acceso al menú y a la ruta índice no usa permiso_ia: basta con entrar como Administración.
 */
final class PermisosMora
{
    public static function puedeAccederModulo(): bool
    {
        return schoolEsAdministracion();
    }
}
