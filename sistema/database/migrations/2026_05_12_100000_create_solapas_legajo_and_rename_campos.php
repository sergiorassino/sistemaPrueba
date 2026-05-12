<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Tabla de solapas del legajo ─────────────────────────────────────
        Schema::create('solapas_legajo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60);
            $table->string('slug', 30)->unique();
            $table->unsignedSmallInteger('orden')->default(0);
        });

        DB::table('solapas_legajo')->insert([
            ['nombre' => 'Alumno',      'slug' => 'alumno',    'orden' => 1],
            ['nombre' => 'Domicilio',   'slug' => 'domicilio', 'orden' => 2],
            ['nombre' => 'Madre',       'slug' => 'madre',     'orden' => 3],
            ['nombre' => 'Padre',       'slug' => 'padre',     'orden' => 4],
            ['nombre' => 'Tutor',       'slug' => 'tutor',     'orden' => 5],
            ['nombre' => 'Escolaridad', 'slug' => 'escolar',   'orden' => 6],
        ]);

        // ── 2. Renombrar campos_listado_alumnos → campos_legajo ────────────────
        if (Schema::hasTable('campos_listado_alumnos') && ! Schema::hasTable('campos_legajo')) {
            Schema::rename('campos_listado_alumnos', 'campos_legajo');
        } elseif (! Schema::hasTable('campos_legajo')) {
            Schema::create('campos_legajo', function (Blueprint $table) {
                $table->id();
                $table->string('columna', 80);
                $table->string('etiqueta', 100)->nullable();
                $table->boolean('visible_listado')->default(true);
                $table->unsignedInteger('orden')->default(0);
            });
        }

        // ── 3. Nuevas columnas: solapa + orden dentro de la solapa ─────────────
        Schema::table('campos_legajo', function (Blueprint $table) {
            $table->foreignId('solapa_legajo_id')
                  ->nullable()
                  ->after('orden')
                  ->constrained('solapas_legajo')
                  ->nullOnDelete();
            $table->unsignedSmallInteger('orden_en_solapa')
                  ->default(0)
                  ->after('solapa_legajo_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('campos_legajo')) {
            Schema::table('campos_legajo', function (Blueprint $table) {
                $table->dropForeign(['solapa_legajo_id']);
                $table->dropColumn(['solapa_legajo_id', 'orden_en_solapa']);
            });

            if (! Schema::hasTable('campos_listado_alumnos')) {
                Schema::rename('campos_legajo', 'campos_listado_alumnos');
            }
        }

        Schema::dropIfExists('solapas_legajo');
    }
};
