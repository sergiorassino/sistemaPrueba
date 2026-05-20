<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('permisos_ia')) {
            return;
        }

        DB::table('permisos_ia')->updateOrInsert(
            ['id' => 12],
            [
                'id' => 12,
                'orden' => 11,
                'tema' => 'LEGAJOS DOCENTES',
                'descripcion' => 'Crear, editar y eliminar legajos de docentes; asignar y quitar docentes en materias (ppc).',
            ]
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('permisos_ia')) {
            DB::table('permisos_ia')->where('id', 12)->delete();
        }
    }
};
