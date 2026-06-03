<?php

namespace App\Support\Navegacion;

use App\Models\Profesor;
use App\Support\NivelSistema;
use App\Support\ProfesorMenuPortal;
use Illuminate\Support\Facades\Auth;

/**
 * Perfil de menú del portal de Secretaría según `school.idNivel` (identidad de login).
 *
 * @see docs/08-menus-de-navegacion.md
 */
final class MenuSecretariaPerfil
{
    public static function esAdministracion(): bool
    {
        return NivelSistema::esAdministracion((int) (schoolCtx()->idNivel ?? 0));
    }

    /** Oculta bloques pedagógicos (calificaciones, exámenes, etc.) en sesión Administración. */
    public static function ocultarGruposPedagogicos(): bool
    {
        return self::esAdministracion();
    }

    public static function muestraGrupoEstudiantes(): bool
    {
        return true;
    }

    /** Bloque exclusivo del nivel Administración (sin permiso_ia adicional). */
    public static function muestraGestionCuotas(): bool
    {
        return self::esAdministracion();
    }

    /** Bloque «Resúmenes» (Administración). */
    public static function muestraResumenes(): bool
    {
        return self::esAdministracion();
    }

    /** Bloque «Becas» (Administración). */
    public static function muestraBecas(): bool
    {
        return self::esAdministracion();
    }

    /**
     * Excel de viajes / salidas educativas: solo Menú de Secretaría en niveles pedagógicos (1–4).
     * No Administración, no Menú de Docentes ni de Alumnos (rutas bajo menu.portal:secretaria).
     */
    public static function muestraViajesSalidasEducativas(): bool
    {
        if (self::esAdministracion()) {
            return false;
        }

        $profesor = Auth::user();

        return ProfesorMenuPortal::usaMenuSecretaria($profesor instanceof Profesor ? $profesor : null);
    }

    public static function abortSiNoViajesSalidasEducativas(): void
    {
        abort_unless(
            self::muestraViajesSalidasEducativas(),
            403,
            'Viajes y salidas educativas solo están disponibles en el Menú de Secretaría (niveles pedagógicos).',
        );
    }
}
