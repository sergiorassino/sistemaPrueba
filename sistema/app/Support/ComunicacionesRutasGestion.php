<?php

namespace App\Support;

/**
 * Rutas y layout del cuaderno de comunicados para personal (secretaría o portal docente).
 */
final class ComunicacionesRutasGestion
{
    private const PORTAL_PREFIX = 'portalDocente.comunicaciones.';

    private const STAFF_PREFIX = 'comunicaciones.';

    public static function esPortalDocente(): bool
    {
        return request()->routeIs(self::PORTAL_PREFIX . '*');
    }

    public static function nombreRuta(string $accion): string
    {
        $base = self::esPortalDocente() ? self::PORTAL_PREFIX : self::STAFF_PREFIX;

        return $base . $accion;
    }

    /**
     * @param  array<string, mixed>|int|string  $parameters
     */
    public static function route(string $accion, array|int|string $parameters = []): string
    {
        return route(self::nombreRuta($accion), $parameters);
    }

    public static function layout(): string
    {
        return self::esPortalDocente() ? 'layouts.docente' : 'layouts.app';
    }
}
