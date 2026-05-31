<?php

namespace App\Support\Security;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use JsonException;

/**
 * Referencias opacas para rutas GET (PDF, descargas) sin IDs numéricos en la URL.
 *
 * El valor en la ruta es cifrado con APP_KEY; no es enumerable ni adivinable.
 * Siempre revalidar alcance en el controlador (sesión, schoolCtx, etc.).
 */
final class OpaqueRouteToken
{
    public const PURPOSE_COMPROBANTE_PAGO = 'alumnos.comprobante-pago';

    public static function forComprobantePagoCuota(int $idCuotaGenerada, int $idLegajo): string
    {
        return self::encode(self::PURPOSE_COMPROBANTE_PAGO, $idCuotaGenerada, $idLegajo);
    }

    /**
     * @return array{id: int, legajo: int}|null
     */
    public static function decode(string $ref, string $purpose): ?array
    {
        $ref = trim($ref);
        if ($ref === '') {
            return null;
        }

        try {
            $json = Crypt::decryptString(self::fromUrlSafe($ref));
            /** @var array{p?: string, i?: int, l?: int} $payload */
            $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|JsonException) {
            return null;
        }

        if (($payload['p'] ?? '') !== $purpose) {
            return null;
        }

        $id = (int) ($payload['i'] ?? 0);
        $legajo = (int) ($payload['l'] ?? 0);

        if ($id <= 0 || $legajo <= 0) {
            return null;
        }

        return ['id' => $id, 'legajo' => $legajo];
    }

    private static function encode(string $purpose, int $id, int $idLegajo): string
    {
        $payload = json_encode([
            'p' => $purpose,
            'i' => $id,
            'l' => $idLegajo,
        ], JSON_THROW_ON_ERROR);

        return self::toUrlSafe(Crypt::encryptString($payload));
    }

    private static function toUrlSafe(string $encrypted): string
    {
        return rtrim(strtr($encrypted, '+/', '-_'), '=');
    }

    private static function fromUrlSafe(string $ref): string
    {
        $b64 = strtr($ref, '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad > 0) {
            $b64 .= str_repeat('=', 4 - $pad);
        }

        return $b64;
    }
}
