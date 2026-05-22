<?php

namespace App\Support;

use App\Models\Profesor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Define qué menú lateral corresponde según `profesores.IdTipoProf` / `profesortipo`.
 *
 * @see docs/08-menus-de-navegacion.md
 */
final class ProfesorMenuPortal
{
    /** Rol «Profesor/a» en `profesortipo` → Menú de Docentes. */
    public const ID_TIPO_PROFESOR_AULA = 6;

    public static function usaMenuDocentes(?Profesor $profesor): bool
    {
        if (! $profesor) {
            return false;
        }

        return (int) ($profesor->IdTipoProf ?? 0) === self::ID_TIPO_PROFESOR_AULA;
    }

    public static function usaMenuSecretaria(?Profesor $profesor): bool
    {
        return ! self::usaMenuDocentes($profesor);
    }

    public static function rutaInicio(?Profesor $profesor = null): string
    {
        $profesor ??= Auth::user();

        return self::usaMenuDocentes($profesor instanceof Profesor ? $profesor : null)
            ? 'portalDocente.home'
            : 'dashboard';
    }

    /**
     * Redirección HTTP estándar (middleware, controladores). En Livewire usar
     * `$this->redirectRoute(self::rutaInicio($profesor), navigate: false)`.
     */
    public static function redirectInicio(?Profesor $profesor = null): RedirectResponse
    {
        return redirect()->route(self::rutaInicio($profesor));
    }
}
