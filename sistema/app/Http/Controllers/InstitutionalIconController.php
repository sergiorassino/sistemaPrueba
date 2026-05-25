<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Monograma SE para /favicon.ico (PNG; la pestaña no usa bien solo SVG en Windows).
 */
class InstitutionalIconController extends Controller
{
    public function __invoke(Request $request): Response|BinaryFileResponse
    {
        $preferDark = $request->header('Sec-CH-Prefers-Color-Scheme') === 'dark'
            || (is_string($request->query('theme')) && $request->query('theme') === 'dark');

        $filename = $preferDark ? 'favicon-se-32-dark.png' : 'favicon-se-32-light.png';

        return response()->file(public_path('img/'.$filename), [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=3600, must-revalidate',
        ]);
    }
}
