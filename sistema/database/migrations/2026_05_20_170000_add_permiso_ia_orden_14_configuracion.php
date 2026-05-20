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
            ['id' => 15],
            [
                'id' => 15,
                'orden' => 14,
                'tema' => 'CONFIGURACIÓN',
                'descripcion' => 'Menú Configuración: términos lectivos, niveles, legajos, parámetros, planes, cursos, materias y notificaciones push.',
            ]
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('permisos_ia')) {
            DB::table('permisos_ia')->where('id', 15)->delete();
        }
    }
};
