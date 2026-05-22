<?php

namespace App\Http\Middleware;

use App\Models\Profesor;
use App\Support\ProfesorMenuPortal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe rutas al Menú de Secretaría o al Menú de Docentes según IdTipoProf.
 */
class EnsureMenuPortal
{
    public function handle(Request $request, Closure $next, string $portal): Response
    {
        $profesor = Auth::user();

        if (! $profesor instanceof Profesor) {
            return $next($request);
        }

        $esDocente = ProfesorMenuPortal::usaMenuDocentes($profesor);

        if ($portal === 'docente' && ! $esDocente) {
            return ProfesorMenuPortal::redirectInicio($profesor);
        }

        if ($portal === 'secretaria' && $esDocente) {
            return ProfesorMenuPortal::redirectInicio($profesor);
        }

        return $next($request);
    }
}
