<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfesorAdministradorSeeder extends Seeder
{
    public function run(): void
    {
        $maxOrden = (int) DB::table('permisosusuarios')->max('orden');
        $permisosLength = max(1, min(100, $maxOrden + 1));
        $fullPermisos = str_repeat('1', $permisosLength);

        DB::table('profesores')->updateOrInsert(
            ['id' => 1],
            [
                'IdTipoProf' => 2,
                'apellido' => 'ADMINISTRADOR',
                'nombre' => 'GENERAL',
                'dni' => 13964667,
                'cuil' => '',
                'sexo' => 0,
                'nivel' => 2,
                'pwrd' => 'pasepase',
                'permisos' => $fullPermisos,
            ]
        );
    }
}
