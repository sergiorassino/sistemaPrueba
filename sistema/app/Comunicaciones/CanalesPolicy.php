<?php

namespace App\Comunicaciones;

use App\Models\ComCanal;
use App\Models\Profesor;
use Illuminate\Support\Facades\Cache;

class CanalesPolicy
{
    private const CACHE_TTL = 60; // segundos

    /**
     * Nivel activo: parámetro explícito, contexto de secretaría o portal familia.
     */
    public static function resolveIdNivel(?int $idNivel = null): int
    {
        if ($idNivel !== null && $idNivel > 0) {
            return $idNivel;
        }

        $fromSchool = (int) (schoolCtx()->idNivel ?? 0);
        if ($fromSchool > 0) {
            return $fromSchool;
        }

        return (int) (studentCtx()->idNivel ?? 0);
    }

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
     * Clasifica un `profesortipo.tipo` para el selector de destinatarios docentes
     * en «Nuevo comunicado» (botones Profesores / Personal).
     *
     * Distinto de {@see normalizarRolProfesor}: aquí se discrimina por cargo real
     * (p. ej. bibliotecario → Personal; ATP/DOE → Profesores).
     *
     * @return 'profesor'|'institucional'|null  null = excluir del selector (p. ej. «Sin Rol»).
     */
    public static function modoSelectorNuevoComunicadoDocente(?string $tipo): ?string
    {
        if ($tipo === null || trim($tipo) === '') {
            return null;
        }

        $t = mb_strtolower(trim($tipo));

        if (str_contains($t, 'sin rol')) {
            return null;
        }

        if (str_contains($t, 'direct') || str_contains($t, 'secret')) {
            return 'institucional';
        }
        if (str_contains($t, 'preceptor')) {
            return 'institucional';
        }
        if (str_contains($t, 'bibliotec')) {
            return 'institucional';
        }
        if (str_contains($t, 'no docente')) {
            return 'institucional';
        }

        if (str_contains($t, 'atp') || str_contains($t, 'doe')) {
            return 'profesor';
        }
        if (str_contains($t, 'profesor')) {
            return 'profesor';
        }

        return null;
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
     * Obtiene el canal entre dos roles para un nivel, con caché.
     */
    public static function obtenerCanal(string $rolEmisor, string $rolReceptor, ?int $idNivel = null): ?ComCanal
    {
        $idNivel = static::resolveIdNivel($idNivel);
        if ($idNivel <= 0) {
            return null;
        }

        $cacheKey = "com_canal:{$idNivel}:{$rolEmisor}:{$rolReceptor}";

        return Cache::remember($cacheKey, static::CACHE_TTL, function () use ($rolEmisor, $rolReceptor, $idNivel) {
            return ComCanal::query()
                ->where('id_nivel', $idNivel)
                ->where('rol_emisor', $rolEmisor)
                ->where('rol_receptor', $rolReceptor)
                ->where('activo', true)
                ->first();
        });
    }

    public static function puedeIniciar(string $rolEmisor, string $rolReceptor, ?int $idNivel = null): bool
    {
        return (bool) static::obtenerCanal($rolEmisor, $rolReceptor, $idNivel)?->puede_iniciar;
    }

    public static function puedeResponder(string $rolEmisor, string $rolReceptor, ?int $idNivel = null): bool
    {
        return (bool) static::obtenerCanal($rolEmisor, $rolReceptor, $idNivel)?->puede_responder;
    }

    /**
     * Medios permitidos por el canal (intersección con los activos en el sistema).
     *
     * @return list<string>
     */
    public static function mediosPermitidos(string $rolEmisor, string $rolReceptor, ?int $idNivel = null): array
    {
        $canal = static::obtenerCanal($rolEmisor, $rolReceptor, $idNivel);
        if ($canal === null) {
            return [];
        }
        $medios = $canal->medios_permitidos ?? [];
        $disponibles = ComCanal::mediosDisponibles();

        return array_values(array_intersect($medios, $disponibles));
    }

    /** Invalida la caché de un par de roles en un nivel */
    public static function invalidar(string $rolEmisor, string $rolReceptor, ?int $idNivel = null): void
    {
        $idNivel = static::resolveIdNivel($idNivel);
        if ($idNivel <= 0) {
            return;
        }

        Cache::forget("com_canal:{$idNivel}:{$rolEmisor}:{$rolReceptor}");
    }

    /**
     * Devuelve todos los roles receptores a los que un emisor puede iniciar conversación en un nivel.
     *
     * @return list<string>
     */
    public static function receptoresPermitidosParaIniciar(string $rolEmisor, ?int $idNivel = null): array
    {
        $idNivel = static::resolveIdNivel($idNivel);
        if ($idNivel <= 0) {
            return [];
        }

        return ComCanal::query()
            ->where('id_nivel', $idNivel)
            ->where('rol_emisor', $rolEmisor)
            ->where('puede_iniciar', true)
            ->where('activo', true)
            ->pluck('rol_receptor')
            ->all();
    }
}
