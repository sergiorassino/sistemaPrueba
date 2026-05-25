<?php

if (! extension_loaded('gd')) {
    fwrite(STDERR, "PHP GD extension required.\n");
    exit(1);
}

/**
 * Fuente cuadrada / institucional (Arial Black o Narrow) para PNG de pestaña.
 *
 * @return array{0: string, 1: int}|null
 */
function resolveBoldFont(): ?array
{
    $candidates = [
        ['C:/Windows/Fonts/ariblk.ttf', 28],
        ['C:/Windows/Fonts/ARIALNB.TTF', 28],
        ['C:/Windows/Fonts/arialnb.ttf', 28],
        ['C:/Windows/Fonts/arialn.ttf', 26],
        ['C:/Windows/Fonts/arialbd.ttf', 26],
        ['C:/Windows/Fonts/segoeuib.ttf', 26],
        ['/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', 26],
    ];

    foreach ($candidates as [$path, $fontSize]) {
        if (is_readable($path)) {
            return [$path, $fontSize];
        }
    }

    return null;
}

/**
 * Anillo sólido (más legible que imageellipse con grosor fino a 16px de pestaña).
 */
function drawRing($im, int $cx, int $cy, int $outerDiameter, int $innerDiameter, int $color, int $transparent): void
{
    imagealphablending($im, true);
    imagefilledellipse($im, $cx, $cy, $outerDiameter, $outerDiameter, $color);
    imagefilledellipse($im, $cx, $cy, $innerDiameter, $innerDiameter, $transparent);
}

/**
 * @param  array{ring: array{0: int, 1: int, 2: int}, text: array{0: int, 1: int, 2: int}}  $palette
 */
function writeSePng(string $path, array $palette): void
{
    $size = 64;
    $im = imagecreatetruecolor($size, $size);
    imagealphablending($im, false);
    imagesavealpha($im, true);
    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $transparent);
    imagealphablending($im, true);

    [$ringR, $ringG, $ringB] = $palette['ring'];
    [$textR, $textG, $textB] = $palette['text'];
    $ringColor = imagecolorallocate($im, $ringR, $ringG, $ringB);
    $textColor = imagecolorallocate($im, $textR, $textG, $textB);
    $cx = (int) ($size / 2);
    $cy = $cx;

    drawRing($im, $cx, $cy, 60, 40, $ringColor, $transparent);

    $font = resolveBoldFont();

    if ($font !== null) {
        [$fontFile, $fontSize] = $font;
        $box = imagettfbbox($fontSize, 0, $fontFile, 'SE');
        $textWidth = abs($box[2] - $box[0]);
        $textHeight = abs($box[7] - $box[1]);
        $x = (int) (($size - $textWidth) / 2) - (int) $box[0];
        $y = (int) (($size + $textHeight) / 2) - (int) $box[1];
        imagettftext($im, $fontSize, 0, $x, $y, $textColor, $fontFile, 'SE');
    } else {
        $builtIn = 5;
        $text = 'SE';
        $tw = imagefontwidth($builtIn) * strlen($text);
        $th = imagefontheight($builtIn);
        imagestring($im, $builtIn, (int) (($size - $tw) / 2), (int) (($size - $th) / 2), $text, $textColor);
    }

    imagealphablending($im, false);
    imagesavealpha($im, true);
    imagepng($im, $path);
    imagedestroy($im);
}

$dir = __DIR__.'/../public/img';

// Tema claro del navegador: anillo marca + letras Jet oscuras (pestaña/blanco del SO).
writeSePng($dir.'/favicon-se-32-light.png', [
    'ring' => [64, 132, 141],
    'text' => [51, 51, 51],
]);

// Tema oscuro del navegador: anillo y letras blancas.
writeSePng($dir.'/favicon-se-32-dark.png', [
    'ring' => [255, 255, 255],
    'text' => [255, 255, 255],
]);

echo "Generated favicon PNGs in public/img/\n";
