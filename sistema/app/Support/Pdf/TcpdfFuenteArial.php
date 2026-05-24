<?php

namespace App\Support\Pdf;

use TCPDF;
use TCPDF_FONTS;

/**
 * Fuente Arial para PDFs TCPDF (UTF-8).
 *
 * Copiar en {@see storage_path('fonts/')}: `arial.ttf`, `arialbd.ttf` (opcional negrita).
 * En Windows también se busca en `C:\Windows\Fonts\`.
 * Si no hay TTF disponible, se usa `helvetica` (sin tildes completas).
 */
final class TcpdfFuenteArial
{
    private static bool $inicializado = false;

    private static string $regular = 'helvetica';

    private static ?string $bold = null;

    public static function aplicar(TCPDF $pdf, string $style = '', float $size = 10): void
    {
        self::boot();

        if ($style === 'B' && self::$bold !== null) {
            $pdf->SetFont(self::$bold, '', $size);

            return;
        }

        $pdf->SetFont(self::$regular, $style, $size);
    }

    public static function nombreRegular(): string
    {
        self::boot();

        return self::$regular;
    }

    private static function boot(): void
    {
        if (self::$inicializado) {
            return;
        }
        self::$inicializado = true;

        $dir = storage_path('fonts');
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $regularPath = self::resolverRuta('arial.ttf');
        if ($regularPath !== null) {
            $nombre = TCPDF_FONTS::addTTFfont($regularPath, 'TrueTypeUnicode', '', 32);
            if (is_string($nombre) && $nombre !== '') {
                self::$regular = $nombre;
            }
        }

        $boldPath = self::resolverRuta('arialbd.ttf');
        if ($boldPath !== null) {
            $nombre = TCPDF_FONTS::addTTFfont($boldPath, 'TrueTypeUnicode', '', 32);
            if (is_string($nombre) && $nombre !== '') {
                self::$bold = $nombre;
            }
        }
    }

    private static function resolverRuta(string $archivo): ?string
    {
        $candidatos = [
            storage_path('fonts/'.$archivo),
            base_path('resources/fonts/'.$archivo),
        ];

        if (PHP_OS_FAMILY === 'Windows') {
            $candidatos[] = 'C:\\Windows\\Fonts\\'.strtolower($archivo);
            $candidatos[] = 'C:\\Windows\\Fonts\\'.ucfirst($archivo);
        } else {
            $candidatos[] = '/usr/share/fonts/truetype/msttcorefonts/'.$archivo;
            $candidatos[] = '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf';
        }

        foreach ($candidatos as $path) {
            if (is_string($path) && $path !== '' && is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
