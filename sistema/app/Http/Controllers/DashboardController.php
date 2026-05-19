<?php

namespace App\Http\Controllers;

use App\Comunicaciones\ComunicacionesRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $ctx = schoolCtx();
        $nombre = trim((Auth::user()->nombre ?? '').' '.(Auth::user()->apellido ?? ''));

        $bandeja = null;
        if (tienePermiso(3)) {
            $bandeja = ComunicacionesRepository::resumenBandejaProfesor(
                (int) $ctx->idProfesor,
                (int) $ctx->idNivel,
                (int) $ctx->idTerlec,
            );
        }

        return view('dashboard', [
            'nombreUsuario' => $nombre !== '' ? $nombre : 'Usuario',
            'bandeja'       => $bandeja,
        ]);
    }
}
