<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComPermisosSeeder extends Seeder
{
    /**
     * Inserta los permisos del módulo de comunicaciones en permisosusuarios.
     *
     * Ordenes 51, 52, 53 (el máximo existente es 50 según el dump legacy).
     * El middleware `permiso:N` verifica posición N en profesores.permisos.
     */
    public function run(): void
    {
        $permisos = [
            [
                'id'          => 52,
                'orden'       => 51,
                'tema'        => 'COMUNICACIONES',
                'descripcion' => 'COMUNICACIONES: Permite ver la bandeja de comunicados y los hilos de conversación.',
            ],
            [
                'id'          => 53,
                'orden'       => 52,
                'tema'        => 'COMUNICACIONES',
                'descripcion' => 'COMUNICACIONES: Permite iniciar nuevos comunicados hacia familias.',
            ],
            [
                'id'          => 54,
                'orden'       => 53,
                'tema'        => 'COMUNICACIONES - CONFIG',
                'descripcion' => 'COMUNICACIONES: Permite administrar la configuración de canales (quién puede comunicarse con quién y por qué medios).',
            ],
        ];

        foreach ($permisos as $permiso) {
            DB::table('permisosusuarios')->updateOrInsert(
                ['id' => $permiso['id']],
                $permiso
            );
        }
    }
}
