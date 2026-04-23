<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! schoolCtx()->isValid()) {
            return redirect()->route('login')
                ->with('error', 'Por favor inicie sesión y seleccione nivel y año lectivo.');
        }

        return $next($request);
    }
}
