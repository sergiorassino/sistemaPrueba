<?php

/**
 * Ejemplo de migración específica del colegio (tenant).
 *
 * Renombrar con la fecha y descripción reales.
 * Ejecutar solo en este colegio:
 *   php artisan migrate --path=database/migrations/tenant
 *
 * IMPORTANTE: siempre usar Schema::hasColumn / Schema::hasTable para ser idempotente.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('legajos', 'columna_exclusiva')) {
            Schema::table('legajos', function (Blueprint $table) {
                $table->string('columna_exclusiva', 100)->nullable()->after('obs');
            });
        }
    }

    public function down(): void
    {
        // Informativo; no ejecutar en producción.
    }
};
