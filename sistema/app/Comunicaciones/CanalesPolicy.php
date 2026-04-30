<?php

namespace App\Comunicaciones;

use App\Models\ComCanal;
use App\Models\Profesor;
use Illuminate\Support\Facades\Cache;

class CanalesPolicy
{
    private const CACHE_TTL = 60; // segundos

    /**
     * Normaliza el tipo de un profesor (desde profesortipo.tipo) al rol del canal.
     *
     * Los tipos legacy pueden ser: Directivo, Secretario, Preceptor, Profesor,
     * Bibliotecario, No Docente, etc.
     */
    public static function normalizarRolProfesor(?string $tipo): string
    {
        if ($tipo === null || $tipo === '') {
            return 'profesor';
        }

        $tipo = mb_strtolower(trim($tipo));

        if (str_contains($tipo, 'direct') || str_contains($tipo, 'secret')) {
            return 'directivo';
        }
        if (str_contains($tipo, 'preceptor') || str_contains($tipo, 'preceptora')) {
            return 'preceptor';
        }

        return 'profesor';
    }

    /**
     * Normaliza el rol de un Profesor model.
     */
    public static function rolDeProfesor(Profesor $profesor): string
    {
        $tipo = (string) ($profesor->tipo?->tipo ?? '');
        return static::normalizarRolProfesor($tipo);
    }

    /**
     * Obtiene el canal entre dos roles, con caché.
     */
    public static function obtenerCanal(string $rolEmisor, string $rolReceptor): ?ComCanal
    {
        $cacheKey = "com_canal:{$rolEmisor}:{$rolReceptor}";

        return Cache::remember($cacheKey, static::CACHE_TTL, function () use ($rolEmisor, $rolReceptor) {
            return ComCanal::query()
                ->where('rol_emisor', $rolEmisor)
                ->where('rol_receptor', $rolReceptor)
                ->where('activo', true)
                ->first();
        });
    }

    public static function puedeIniciar(string $rolEmisor, string $rolReceptor): bool
    {
        return (bool) static::obtenerCanal($rolEmisor, $rolReceptor)?->puede_iniciar;
    }

    public static function puedeResponder(string $rolEmisor, string $rolReceptor): bool
    {
        return (bool) static::obtenerCanal($rolEmisor, $rolReceptor)?->puede_responder;
    }

    /**
     * Medios permitidos por el canal (intersección con los activos en el sistema).
     *
     * @return list<string>
     */
    public static function mediosPermitidos(string $rolEmisor, string $rolReceptor): array
    {
        $canal = static::obtenerCanal($rolEmisor, $rolReceptor);
        if ($canal === null) {
            return [];
        }
        $medios = $canal->medios_permitidos ?? [];
        $disponibles = ComCanal::mediosDisponibles();
        return array_values(array_intersect($medios, $disponibles));
    }

    /** Invalida la caché de un par de roles */
    public static function invalidar(string $rolEmisor, string $rolReceptor): void
    {
        Cache::forget("com_canal:{$rolEmisor}:{$rolReceptor}");
    }

    /**
     * Devuelve todos los roles receptores a los que un emisor puede iniciar conversación.
     *
     * @return list<string>
     */
    public static function receptoresPermitidosParaIniciar(string $rolEmisor): array
    {
        return ComCanal::query()
            ->where('rol_emisor', $rolEmisor)
            ->where('puede_iniciar', true)
            ->where('activo', true)
            ->pluck('rol_receptor')
            ->all();
    }
}
