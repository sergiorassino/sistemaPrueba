<?php

if (! extension_loaded('gd')) {
    fwrite(STDERR, "PHP GD extension required.\n");
    exit(1);
}

/**
 * @return array{0: string, 1: int}|null
 */
function resolveBoldFont(): ?array
{
    $candidates = [
        'C:/Windows/Fonts/arialbd.ttf',
        'C:/Windows/Fonts/segoeuib.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
    ];

    foreach ($candidates as $path) {
        if (is_readable($path)) {
            return [$path, 14];
        }
    }

    return null;
}

function writeSePng(string $path, int $r, int $g, int $b): void
{
    $size = 32;
    $im = imagecreatetruecolor($size, $size);
    imagealphablending($im, false);
    imagesavealpha($im, true);
    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $transparent);
    imagealphablending($im, true);

    $color = imagecolorallocate($im, $r, $g, $b);
    $font = resolveBoldFont();

    if ($font !== null) {
        [$fontFile, $fontSize] = $font;
        $box = imagettfbbox($fontSize, 0, $fontFile, 'SE');
        $textWidth = abs($box[2] - $box[0]);
        $textHeight = abs($box[7] - $box[1]);
        $x = (int) (($size - $textWidth) / 2) - (int) $box[0];
        $y = (int) (($size + $textHeight) / 2) - (int) $box[1];
        imagettftext($im, $fontSize, 0, $x, $y, $color, $fontFile, 'SE');
    } else {
        $builtIn = 5;
        $text = 'SE';
        $tw = imagefontwidth($builtIn) * strlen($text);
        $th = imagefontheight($builtIn);
        imagestring($im, $builtIn, (int) (($size - $tw) / 2), (int) (($size - $th) / 2), $text, $color);
    }

    imagesavealpha($im, true);
    imagepng($im, $path);
    imagedestroy($im);
}

$dir = __DIR__.'/../public/img';
writeSePng($dir.'/favicon-se-32-light.png', 64, 132, 141);
writeSePng($dir.'/favicon-se-32-dark.png', 255, 255, 255);

echo "Generated favicon PNGs in public/img/\n";
