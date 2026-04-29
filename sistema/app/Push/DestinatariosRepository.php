<?php

namespace App\Push;

use App\Models\Curso;
use Illuminate\Support\Facades\DB;

class DestinatariosRepository
{
    /**
     * @return list<array{id:int,label:string}>
     */
    public static function cursosDelContexto(int $idNivel, int $idTerlec): array
    {
        return Curso::query()
            ->where('idNivel', $idNivel)
            ->where('idTerlec', $idTerlec)
            ->orderByRaw('COALESCE(orden, 9999) asc')
            ->orderBy('idCurPlan')
            ->orderBy('Id')
            ->get()
            ->map(fn (Curso $c) => ['id' => (int) $c->Id, 'label' => $c->nombreParaListado()])
            ->all();
    }

    /**
     * Busca alumnos por apellido/nombre/dni dentro del nivel actual.
     *
     * @return list<array{id:int,label:string,dni:?string}>
     */
    public static function buscarAlumnos(int $idNivel, string $termino, int $limit = 20): array
    {
        $t = trim($termino);
        if ($t === '') {
            return [];
        }

        $q = DB::table('legajos as l')
            ->select(['l.id', 'l.apellido', 'l.nombre', 'l.dni'])
            ->where('l.idnivel', $idNivel)
            ->where(function ($w) use ($t) {
                $w->where('l.apellido', 'like', "%{$t}%")
                    ->orWhere('l.nombre', 'like', "%{$t}%")
                    ->orWhere('l.dni', 'like', "%{$t}%");
            })
            ->orderBy('l.apellido')
            ->orderBy('l.nombre')
            ->limit(max(1, min(50, $limit)));

        return $q->get()->map(function ($r) {
            $label = trim((string) $r->apellido . ', ' . (string) $r->nombre);
            $dni = $r->dni !== null ? (string) $r->dni : null;
            return ['id' => (int) $r->id, 'label' => $label, 'dni' => $dni];
        })->all();
    }

    /**
     * @return list<string> user_keys (legajos.id) de un curso del contexto.
     */
    public static function alumnosPorCurso(int $idNivel, int $idTerlec, int $idCurso): array
    {
        return DB::table('matricula as m')
            ->join('legajos as l', 'l.id', '=', 'm.idLegajos')
            ->where('m.idNivel', $idNivel)
            ->where('m.idTerlec', $idTerlec)
            ->where('m.idCursos', $idCurso)
            ->whereNotNull('m.idLegajos')
            ->distinct()
            ->orderBy('l.apellido')
            ->orderBy('l.nombre')
            ->pluck('m.idLegajos')
            ->map(fn ($v) => (string) $v)
            ->all();
    }

    /**
     * @return list<string> user_keys (legajos.id) de todo el colegio en el contexto (nivel + ciclo lectivo).
     */
    public static function alumnosDelColegio(int $idNivel, int $idTerlec): array
    {
        return DB::table('matricula as m')
            ->where('m.idNivel', $idNivel)
            ->where('m.idTerlec', $idTerlec)
            ->whereNotNull('m.idLegajos')
            ->distinct()
            ->pluck('m.idLegajos')
            ->map(fn ($v) => (string) $v)
            ->all();
    }

    /**
     * @param list<string> $userKeys
     * @return array<string,string> user_key => "Apellido, Nombre"
     */
    public static function nombresPorUserKeys(array $userKeys): array
    {
        if (empty($userKeys)) {
            return [];
        }

        $rows = DB::table('legajos')
            ->whereIn('id', $userKeys)
            ->get(['id', 'apellido', 'nombre']);

        $out = [];
        foreach ($rows as $r) {
            $out[(string) $r->id] = trim((string) $r->apellido . ', ' . (string) $r->nombre);
        }
        return $out;
    }
}

