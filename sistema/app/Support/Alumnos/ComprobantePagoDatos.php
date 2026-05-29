<?php

namespace App\Support\Alumnos;

use App\Models\CuotaGenerada;

/**
 * Datos para el comprobante de pago de aranceles (portal alumno).
 */
final class ComprobantePagoDatos
{
    /**
     * @return array<string, mixed>|null
     */
    public static function paraAutogestion(int $idCuotaGenerada): ?array
    {
        $registro = ArancelesEscolares::cuotaPendienteParaAutogestion($idCuotaGenerada);
        if ($registro === null) {
            return null;
        }

        return ComprobantePagoCalculo::paraCuotaGenerada($registro);
    }
}
