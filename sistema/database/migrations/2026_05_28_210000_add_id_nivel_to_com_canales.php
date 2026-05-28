<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('com_canales')) {
            return;
        }

        if (! Schema::hasColumn('com_canales', 'id_nivel')) {
            Schema::table('com_canales', function (Blueprint $table) {
                $table->unsignedInteger('id_nivel')->nullable()->after('id');
            });
        }

        $niveles = DB::table('niveles')->orderBy('id')->pluck('id');

        if ($niveles->isNotEmpty()) {
            $sinNivel = DB::table('com_canales')->whereNull('id_nivel')->get();

            foreach ($sinNivel as $canal) {
                $primero = true;
                foreach ($niveles as $idNivel) {
                    if ($primero) {
                        DB::table('com_canales')
                            ->where('id', $canal->id)
                            ->update(['id_nivel' => $idNivel]);
                        $primero = false;
                    } else {
                        DB::table('com_canales')->insert([
                            'id_nivel'          => $idNivel,
                            'rol_emisor'        => $canal->rol_emisor,
                            'rol_receptor'      => $canal->rol_receptor,
                            'puede_iniciar'     => $canal->puede_iniciar,
                            'puede_responder'   => $canal->puede_responder,
                            'medios_permitidos' => $canal->medios_permitidos,
                            'activo'            => $canal->activo,
                            'created_at'        => $canal->created_at ?? now(),
                            'updated_at'        => now(),
                        ]);
                    }
                }
            }

            if (Schema::hasColumn('com_canales', 'id_nivel')) {
                DB::statement('ALTER TABLE `com_canales` MODIFY `id_nivel` INT UNSIGNED NOT NULL');
            }
        }

        if (static::indexExists('com_canales', 'uq_canal_par')) {
            Schema::table('com_canales', function (Blueprint $table) {
                $table->dropUnique('uq_canal_par');
            });
        }

        if (! static::indexExists('com_canales', 'uq_canal_nivel_par')) {
            Schema::table('com_canales', function (Blueprint $table) {
                $table->unique(['id_nivel', 'rol_emisor', 'rol_receptor'], 'uq_canal_nivel_par');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('com_canales') || ! Schema::hasColumn('com_canales', 'id_nivel')) {
            return;
        }

        if (static::indexExists('com_canales', 'uq_canal_nivel_par')) {
            Schema::table('com_canales', function (Blueprint $table) {
                $table->dropUnique('uq_canal_nivel_par');
            });
        }

        foreach (DB::table('com_canales')->select('rol_emisor', 'rol_receptor')->distinct()->get() as $par) {
            $ids = DB::table('com_canales')
                ->where('rol_emisor', $par->rol_emisor)
                ->where('rol_receptor', $par->rol_receptor)
                ->orderBy('id_nivel')
                ->pluck('id');
            if ($ids->count() > 1) {
                DB::table('com_canales')->whereIn('id', $ids->slice(1)->all())->delete();
            }
        }

        Schema::table('com_canales', function (Blueprint $table) {
            $table->dropColumn('id_nivel');
        });

        if (! static::indexExists('com_canales', 'uq_canal_par')) {
            Schema::table('com_canales', function (Blueprint $table) {
                $table->unique(['rol_emisor', 'rol_receptor'], 'uq_canal_par');
            });
        }
    }

    private static function indexExists(string $table, string $index): bool
    {
        $row = DB::selectOne(
            'SELECT 1 AS ok FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?
             LIMIT 1',
            [$table, $index]
        );

        return $row !== null;
    }
};
