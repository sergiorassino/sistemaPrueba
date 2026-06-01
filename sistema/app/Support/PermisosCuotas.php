<?php

namespace App\Support;

/**
 * Módulo «Gestión de cuotas» (solo sesión nivel Administración, `niveles.id = 5`).
 *
 * El acceso al menú y a la ruta índice no usa permiso_ia: basta con entrar como Administración.
 * Los permisos del catálogo (orden 2, etc.) aplicarán a acciones concretas cuando existan pantallas.
 */
final class PermisosCuotas
{
    public static function puedeAccederModulo(): bool
    {
        return schoolEsAdministracion();
    }
}
