<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campos_listado_alumnos', function (Blueprint $table) {
            $table->id();
            $table->string('columna', 64)->unique();
            $table->string('etiqueta', 150)->nullable();
            $table->boolean('visible_listado')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
        });

        if (! Schema::hasTable('legajos')) {
            return;
        }

        $columnas = Schema::getColumnListing('legajos');
        $nuncaListar = ['pwrd'];
        $orden = 0;
        foreach ($columnas as $nombre) {
            if (in_array($nombre, $nuncaListar, true)) {
                continue;
            }
            DB::table('campos_listado_alumnos')->insert([
                'columna' => $nombre,
                'etiqueta' => null,
                'visible_listado' => true,
                'orden' => ++$orden,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('campos_listado_alumnos');
    }
};
