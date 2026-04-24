<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $dumpPath = base_path('../bd_con_datos.sql');

        if (! is_file($dumpPath)) {
            throw new \RuntimeException("No se encontro el dump en: {$dumpPath}");
        }

        $sql = file_get_contents($dumpPath);

        if ($sql === false) {
            throw new \RuntimeException("No se pudo leer el dump en: {$dumpPath}");
        }

        preg_match_all('/CREATE TABLE IF NOT EXISTS `[^`]+`.*?;/si', $sql, $matches);
        $createStatements = $matches[0] ?? [];

        if ($createStatements === []) {
            throw new \RuntimeException('No se encontraron sentencias CREATE TABLE en el dump.');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($createStatements as $statement) {
            DB::unprepared($this->sanitizeLegacyCreateTable($statement));
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        $dumpPath = base_path('../bd_con_datos.sql');

        if (! is_file($dumpPath)) {
            return;
        }

        $sql = file_get_contents($dumpPath);

        if ($sql === false) {
            return;
        }

        preg_match_all('/CREATE TABLE IF NOT EXISTS `([^`]+)`/i', $sql, $tableMatches);
        $tables = array_values(array_unique($tableMatches[1] ?? []));

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach (array_reverse($tables) as $table) {
            DB::unprepared("DROP TABLE IF EXISTS `{$table}`;");
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function sanitizeLegacyCreateTable(string $statement): string
    {
        // MySQL 8+ exige que columnas referenciadas por FK sean UNIQUE/PK.
        // El dump legacy tiene algunas FKs invalidas para motores actuales.
        $withoutFks = preg_replace('/^\s*CONSTRAINT\s+`[^`]+`\s+FOREIGN KEY.*$/mi', '', $statement);
        $withoutFks = $withoutFks ?? $statement;

        // Limpia comas colgantes antes del cierre.
        $withoutFks = preg_replace('/,\s*\)/m', "\n)", $withoutFks);

        return $withoutFks ?? $statement;
    }
};
