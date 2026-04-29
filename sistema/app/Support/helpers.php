<?php

use App\Support\SchoolContext;
use App\Support\StudentContext;
use App\Models\Ento;
use Illuminate\Support\Facades\Storage;

if (! function_exists('schoolCtx')) {
    function schoolCtx(): SchoolContext
    {
        return app(SchoolContext::class);
    }
}

if (! function_exists('studentCtx')) {
    function studentCtx(): StudentContext
    {
        return app(StudentContext::class);
    }
}

if (! function_exists('tienePermiso')) {
    function tienePermiso(int $orden): bool
    {
        $permisos = schoolCtx()->profesor()?->permisos ?? '';
        return isset($permisos[$orden]) && $permisos[$orden] === '1';
    }
}

if (! function_exists('schoolLogoUrl')) {
    function schoolLogoUrl(): ?string
    {
        static $memo = null;
        static $done = false;

        if ($done) {
            return $memo;
        }
        $done = true;

        $idNivel = (int) (schoolCtx()->idNivel ?? 0);
        if ($idNivel <= 0) {
            return null;
        }

        $path = Ento::query()
            ->where('idNivel', $idNivel)
            ->value('logo_path');

        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $memo = Storage::disk('public')->url($path);
        return $memo;
    }
}

if (! function_exists('studentLogoUrl')) {
    function studentLogoUrl(): ?string
    {
        static $memo = null;
        static $done = false;

        if ($done) {
            return $memo;
        }
        $done = true;

        $idNivel = (int) (studentCtx()->idNivel ?? 0);
        if ($idNivel <= 0) {
            return null;
        }

        $path = Ento::query()
            ->where('idNivel', $idNivel)
            ->value('logo_path');

        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $memo = Storage::disk('public')->url($path);
        return $memo;
    }
}

if (! function_exists('schoolPdfHeaderData')) {
    /**
     * Datos institucionales para encabezados de PDFs (Dompdf).
     *
     * @return array{insti:string,direccion:string,localidad:string,cue:string,ee:string,logo_file:?string}
     */
    function schoolPdfHeaderData(): array
    {
        static $memo = null;
        static $done = false;

        if ($done) {
            /** @var array $memo */
            return $memo;
        }
        $done = true;

        $idNivel = (int) (schoolCtx()->idNivel ?? 0);
        if ($idNivel <= 0) {
            $memo = [
                'insti' => '',
                'direccion' => '',
                'localidad' => '',
                'cue' => '',
                'ee' => '',
                'logo_file' => null,
            ];
            return $memo;
        }

        $ento = Ento::query()
            ->where('idNivel', $idNivel)
            ->first(['insti', 'direccion', 'localidad', 'cue', 'ee', 'logo_path']);

        $insti = trim((string) ($ento?->insti ?? ''));
        $direccion = trim((string) ($ento?->direccion ?? ''));
        $localidad = trim((string) ($ento?->localidad ?? ''));
        $cue = trim((string) ($ento?->cue ?? ''));
        $ee = trim((string) ($ento?->ee ?? ''));

        $logoFile = null;
        $logoPath = trim((string) ($ento?->logo_path ?? ''));
        if ($logoPath !== '') {
            $abs = Storage::disk('public')->path($logoPath);
            if (is_string($abs) && $abs !== '' && file_exists($abs)) {
                $logoFile = $abs;
            }
        }

        $memo = [
            'insti' => $insti,
            'direccion' => $direccion,
            'localidad' => $localidad,
            'cue' => $cue,
            'ee' => $ee,
            'logo_file' => $logoFile,
        ];

        return $memo;
    }
}

if (! function_exists('notificaciones_push_enviar')) {
    /**
     * Enviar notificación push a uno o más legajos (user_key).
     *
     * @param list<string|int>|null $userKeys null = no enviar (requiere lista explícita)
     * @return array{ok:bool,sent:int,failed:int,errors:list<string>,sent_user_keys?:list<string>,failed_user_keys?:array<string,string>}
     */
    function notificaciones_push_enviar(string $title, string $body, ?string $url = null, ?array $userKeys = null, ?string $nombreColegio = null): array
    {
        $url = $url ?? url('/');

        if ($userKeys === null) {
            return ['ok' => false, 'sent' => 0, 'failed' => 0, 'errors' => ['Faltan destinatarios']];
        }

        $keys = array_values(array_filter(array_map(fn ($v) => trim((string) $v), $userKeys), fn ($v) => $v !== ''));
        $result = \App\Push\WebPushService::sendToUsers($keys, $title, $body, $url, $nombreColegio);

        return [
            'ok' => true,
            'sent' => $result['ok'],
            'failed' => $result['fail'],
            'errors' => $result['errors'],
            'sent_user_keys' => $result['sent_user_keys'] ?? [],
            'failed_user_keys' => $result['failed_user_keys'] ?? [],
        ];
    }
}
