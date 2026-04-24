<?php

namespace App\Services;

use App\Models\CampoListadoAlumno;
use Illuminate\Support\Facades\Schema;

final class CamposListadoAlumnosLegajosSync
{
    /**
     * Inserta filas para columnas de `legajos` que existan en el esquema y aún no estén en `campos_listado_alumnos`.
     *
     * @return int cantidad de filas nuevas insertadas
     */
    public function sincronizarDesdeSchema(): int
    {
        if (! Schema::hasTable('legajos') || ! Schema::hasTable('campos_listado_alumnos')) {
            return 0;
        }

        $columnas = Schema::getColumnListing('legajos');
        $maxOrden = (int) CampoListadoAlumno::query()->max('orden');
        $insertados = 0;

        foreach ($columnas as $nombre) {
            if (in_array($nombre, CampoListadoAlumno::COLUMNAS_EXCLUIDAS, true)) {
                continue;
            }
            if (CampoListadoAlumno::query()->where('columna', $nombre)->exists()) {
                continue;
            }
            $maxOrden++;
            CampoListadoAlumno::create([
                'columna' => $nombre,
                'etiqueta' => null,
                'visible_listado' => true,
                'orden' => $maxOrden,
            ]);
            $insertados++;
        }

        return $insertados;
    }
}
