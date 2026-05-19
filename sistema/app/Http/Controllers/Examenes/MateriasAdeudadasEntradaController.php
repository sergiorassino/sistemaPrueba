<?php

namespace App\Http\Controllers\Examenes;

use App\Http\Controllers\Controller;
use App\Support\Examenes\MateriasAdeudadasPreparacion;
use Illuminate\Http\RedirectResponse;

/**
 * Entrada desde el menú: dispara preparación y recálculo de condiciones en el módulo destino.
 */
class MateriasAdeudadasEntradaController extends Controller
{
    public function listado(): RedirectResponse
    {
        MateriasAdeudadasPreparacion::solicitarFormularioPreparacion(
            MateriasAdeudadasPreparacion::MODULO_LISTADO,
        );

        return redirect()->route('examenes.materias-adeudadas');
    }

    public function gestion(): RedirectResponse
    {
        MateriasAdeudadasPreparacion::solicitarFormularioPreparacion(
            MateriasAdeudadasPreparacion::MODULO_GESTION,
        );

        return redirect()->route('examenes.materias-adeudadas.gestion');
    }
}
