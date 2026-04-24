<?php

use App\Support\SchoolContext;

if (! function_exists('schoolCtx')) {
    function schoolCtx(): SchoolContext
    {
        return app(SchoolContext::class);
    }
}

if (! function_exists('tienePermiso')) {
    function tienePermiso(int $orden): bool
    {
        $permisos = schoolCtx()->profesor()?->permisos ?? '';
        return isset($permisos[$orden]) && $permisos[$orden] === '1';
    }
}
