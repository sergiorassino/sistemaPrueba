<?php

namespace App\Livewire\Calificaciones;

use App\Models\Curso;
use App\Support\PromedioAnualCalificaciones;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * Módulo UI: carga/edición masiva de calificaciones por curso + materia.
 *
 * Flujo:
 * 1) El usuario elige curso y materia (IDs reales de `materias.id`).
 * 2) Se listan filas de `calificaciones` para ese curso/materia/ciclo lectivo.
 * 3) Cada celda editable guarda con `saveCell()` (disparado desde Blade con `wire:blur` / `wire:change`).
 *
 * Seguridad:
 * - Todas las consultas/mutaciones se filtran por `schoolCtx()` (nivel + año lectivo) y por curso/materia elegidos.
 * - Antes de actualizar por `id`, se revalida que el registro pertenezca al alcance actual (anti-ID guessing).
 */
class CargaCalificaciones extends Component
{
    /** Curso seleccionado (`cursos.Id`) dentro del contexto de sesión. */
    public ?int $cursoId = null;

    /** Materia seleccionada (`materias.id`) dentro del curso/contexto de sesión. */
    public ?int $materiaId = null;

    /**
     * Filas renderizadas en la grilla.
     *
     * Clave: `calificaciones.id` (int). Esto permite updates O(1) y `wire:key` estable por fila.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $rows = [];

    public function mount(): void
    {
        // Entrada al módulo: forzar selección explícita de curso/materia.
        $this->cursoId = null;
        $this->materiaId = null;
        $this->rows = [];
    }

    public function updatedCursoId($value): void
    {
        // `wire:model.live` puede mandar string vacío: lo normalizamos a null.
        $this->cursoId = ((int) $value) > 0 ? (int) $value : null;

        // Al cambiar de curso, la materia deja de ser válida: reseteamos dependientes.
        $this->materiaId = null;
        $this->rows = [];
        $this->resetValidation();
    }

    public function updatedMateriaId($value): void
    {
        $this->materiaId = ((int) $value) > 0 ? (int) $value : null;
        $this->rows = [];
        $this->resetValidation();

        // Cuando ya hay curso+materia, cargamos la grilla (consulta única, orden estable).
        if ($this->cursoId && $this->materiaId) {
            $this->loadGrid();
        }
    }

    /**
     * Valida que curso/materia existan y pertenezcan al contexto institucional actual.
     *
     * Importante: si aún no hay selección completa, no hace nada (evita 404 en renders intermedios).
     */
    protected function ensureScopeOr404(): void
    {
        $ctx = schoolCtx();

        if (! $this->cursoId || ! $this->materiaId) {
            return;
        }

        $cursoOk = Curso::query()
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->where('Id', (int) $this->cursoId)
            ->exists();

        if (! $cursoOk) {
            abort(404);
        }

        $materiaOk = DB::table('materias')
            ->where('idNivel', (int) $ctx->idNivel)
            ->where('idTerlec', (int) $ctx->idTerlec)
            ->where('idCursos', (int) $this->cursoId)
            ->where('id', (int) $this->materiaId)
            ->exists();

        if (! $materiaOk) {
            abort(404);
        }
    }

    /**
     * Construye `$this->rows` leyendo `calificaciones` + datos mínimos de alumno para la UI.
     *
     * Mapeo de columnas (legacy):
     * - Eval 1..8: `ic01..ic24` (cada evaluación: N, R1, R2)
     * - JIS 1..2: `ic25..ic28` (N/R por bloque)
     * - Coloquios: `dic`, `feb`
     * - Promedio final persistido: `calif`
     * - TEA: `tea` (checkbox en UI; en BD es entero 0/1 según migración del proyecto)
     */
    public function loadGrid(): void
    {
        $this->ensureScopeOr404();

        $ctx = schoolCtx();

        // Join con `legajos` solo para mostrar nombre (no se edita desde acá).
        $califs = DB::table('calificaciones as c')
            ->join('legajos as l', 'l.id', '=', 'c.idLegajos')
            ->where('c.idTerlec', (int) $ctx->idTerlec)
            ->where('c.idCursos', (int) $this->cursoId)
            ->where('c.idMaterias', (int) $this->materiaId)
            ->orderByRaw('COALESCE(c.ord, 9999) asc')
            ->orderBy('l.apellido')
            ->orderBy('l.nombre')
            ->get([
                'c.id',
                'c.ord',
                'l.apellido',
                'l.nombre',
                'c.ic01', 'c.ic02', 'c.ic03',
                'c.ic04', 'c.ic05', 'c.ic06',
                'c.ic07', 'c.ic08', 'c.ic09',
                'c.ic10', 'c.ic11', 'c.ic12',
                'c.ic13', 'c.ic14', 'c.ic15',
                'c.ic16', 'c.ic17', 'c.ic18',
                'c.ic19', 'c.ic20', 'c.ic21',
                'c.ic22', 'c.ic23', 'c.ic24',
                'c.ic25', 'c.ic26', 'c.ic27', 'c.ic28',
                'c.dic', 'c.feb', 'c.tea', 'c.calif',
            ]);

        $out = [];
        foreach ($califs as $r) {
            $id = (int) $r->id;
            $out[$id] = [
                'id' => $id,
                'ord' => $r->ord,
                'alumno' => trim(((string) $r->apellido) . ', ' . ((string) $r->nombre)),
                'ic01' => (string) ($r->ic01 ?? ''),
                'ic02' => (string) ($r->ic02 ?? ''),
                'ic03' => (string) ($r->ic03 ?? ''),
                'ic04' => (string) ($r->ic04 ?? ''),
                'ic05' => (string) ($r->ic05 ?? ''),
                'ic06' => (string) ($r->ic06 ?? ''),
                'ic07' => (string) ($r->ic07 ?? ''),
                'ic08' => (string) ($r->ic08 ?? ''),
                'ic09' => (string) ($r->ic09 ?? ''),
                'ic10' => (string) ($r->ic10 ?? ''),
                'ic11' => (string) ($r->ic11 ?? ''),
                'ic12' => (string) ($r->ic12 ?? ''),
                'ic13' => (string) ($r->ic13 ?? ''),
                'ic14' => (string) ($r->ic14 ?? ''),
                'ic15' => (string) ($r->ic15 ?? ''),
                'ic16' => (string) ($r->ic16 ?? ''),
                'ic17' => (string) ($r->ic17 ?? ''),
                'ic18' => (string) ($r->ic18 ?? ''),
                'ic19' => (string) ($r->ic19 ?? ''),
                'ic20' => (string) ($r->ic20 ?? ''),
                'ic21' => (string) ($r->ic21 ?? ''),
                'ic22' => (string) ($r->ic22 ?? ''),
                'ic23' => (string) ($r->ic23 ?? ''),
                'ic24' => (string) ($r->ic24 ?? ''),
                'ic25' => (string) ($r->ic25 ?? ''),
                'ic26' => (string) ($r->ic26 ?? ''),
                'ic27' => (string) ($r->ic27 ?? ''),
                'ic28' => (string) ($r->ic28 ?? ''),
                'dic' => (string) ($r->dic ?? ''),
                'feb' => (string) ($r->feb ?? ''),
                'calif' => (string) ($r->calif ?? ''),
                'tea' => ((int) ($r->tea ?? 0)) === 1,
            ];
        }

        $this->rows = $out;
    }

    /**
     * Lista blanca de campos que el cliente puede intentar editar vía Livewire.
     *
     * Nota: aunque `calif` sea calculado automáticamente en muchos casos, lo dejamos editable
     * para compatibilidad/ajustes manuales (si el negocio lo requiere).
     *
     * @return list<string>
     */
    protected function editableFields(): array
    {
        return [
            'ic01', 'ic02', 'ic03', 'ic04', 'ic05', 'ic06', 'ic07', 'ic08', 'ic09', 'ic10',
            'ic11', 'ic12', 'ic13', 'ic14', 'ic15', 'ic16', 'ic17', 'ic18', 'ic19', 'ic20',
            'ic21', 'ic22', 'ic23', 'ic24', 'ic25', 'ic26', 'ic27', 'ic28',
            'dic', 'feb', 'calif', 'tea',
        ];
    }

    /**
     * Guarda una celda puntual en `calificaciones`.
     *
     * - Se ejecuta desde la vista en `blur` para inputs (sale del campo) y en `change` para checkbox.
     * - Luego de guardar notas de módulos (`ic01..ic28`), recalcula/persiste `calif` vía `syncPromedioAnual()`.
     */
    public function saveCell(int $id, string $field, mixed $value): void
    {
        // Rate limit suave: evita bursts si el usuario navega rápido con teclado.
        $key = 'calificaciones:carga:cell:' . (auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 240)) {
            return;
        }
        RateLimiter::hit($key, 60);

        $this->ensureScopeOr404();

        $field = trim($field);
        if (! in_array($field, $this->editableFields(), true)) {
            abort(400);
        }

        $ctx = schoolCtx();

        // Revalidación de alcance: el `id` debe pertenecer al curso/materia/ciclo actual.
        $exists = DB::table('calificaciones')
            ->where('id', $id)
            ->where('idTerlec', (int) $ctx->idTerlec)
            ->where('idCursos', (int) $this->cursoId)
            ->where('idMaterias', (int) $this->materiaId)
            ->exists();

        if (! $exists) {
            abort(404);
        }

        $value = is_string($value) ? trim($value) : $value;

        if ($field === 'tea') {
            // TEA: booleano persistido como 0/1 (coherente con checkbox).
            $payload = ['tea' => $value ? 1 : 0];
            DB::table('calificaciones')->where('id', $id)->update($payload);
            if (isset($this->rows[$id])) {
                $this->rows[$id]['tea'] = (bool) $value;
            }
            return;
        }

        // Validación por tipo de columna (límites coherentes con columnas VARCHAR legacy).
        $rules = [
            'value' => match ($field) {
                'dic', 'feb' => ['nullable', 'string', 'max:10'],
                'calif' => ['nullable', 'string', 'max:5'],
                default => ['nullable', 'string', 'max:15'],
            },
        ];
        Validator::make(
            ['value' => $value],
            $rules,
            [],
            ['value' => $field],
        )->validate();

        // Persistencia del campo editado (string) + espejo en memoria para re-render inmediato.
        DB::table('calificaciones')->where('id', $id)->update([$field => (string) ($value ?? '')]);
        if (isset($this->rows[$id])) {
            $this->rows[$id][$field] = (string) ($value ?? '');
        }

        // Promedio anual: solo depende de módulos (Eval/JIS). No recalculamos al editar Dic/Feb/TEA.
        if ($this->debeRecalcularPromedioAnual($field)) {
            $this->syncPromedioAnual($id);
        }
    }

    /**
     * Define si un campo disparó cambios en módulos que impactan el promedio anual.
     */
    protected function debeRecalcularPromedioAnual(string $field): bool
    {
        // Solo módulos (Eval/JIS). No recalcula por TEA ni por campos finales (Dic/Feb/Pr.Final).
        return preg_match('/^ic(0[1-9]|1[0-9]|2[0-8])$/', $field) === 1;
    }

    /**
     * Recalcula y persiste `calif` en función de `ic01..ic28` ya guardados en BD.
     *
     * Importante: relee desde DB para evitar inconsistencias si hubiera más de un update encadenado.
     */
    protected function syncPromedioAnual(int $id): void
    {
        $ctx = schoolCtx();

        // Tomamos solo los campos necesarios para el cálculo (menos ruido y menos datos movidos).
        $row = DB::table('calificaciones')
            ->where('id', $id)
            ->where('idTerlec', (int) $ctx->idTerlec)
            ->where('idCursos', (int) $this->cursoId)
            ->where('idMaterias', (int) $this->materiaId)
            ->first([
                'ic01', 'ic02', 'ic03', 'ic04', 'ic05', 'ic06', 'ic07', 'ic08', 'ic09', 'ic10',
                'ic11', 'ic12', 'ic13', 'ic14', 'ic15', 'ic16', 'ic17', 'ic18', 'ic19', 'ic20',
                'ic21', 'ic22', 'ic23', 'ic24', 'ic25', 'ic26', 'ic27', 'ic28',
            ]);

        if (! $row) {
            return;
        }

        $arr = [];
        foreach ([
            'ic01', 'ic02', 'ic03', 'ic04', 'ic05', 'ic06', 'ic07', 'ic08', 'ic09', 'ic10',
            'ic11', 'ic12', 'ic13', 'ic14', 'ic15', 'ic16', 'ic17', 'ic18', 'ic19', 'ic20',
            'ic21', 'ic22', 'ic23', 'ic24', 'ic25', 'ic26', 'ic27', 'ic28',
        ] as $k) {
            $arr[$k] = (string) ($row->{$k} ?? '');
        }

        $prom = PromedioAnualCalificaciones::calcular($arr);
        $calif = (string) ($prom['promedio'] ?? '');

        DB::table('calificaciones')->where('id', $id)->update(['calif' => $calif]);

        if (isset($this->rows[$id])) {
            $this->rows[$id]['calif'] = $calif;
        }
    }

    /**
     * Cursos disponibles para el nivel + ciclo lectivo activos en sesión.
     *
     * @return Collection<int, mixed>
     */
    public function cursos(): Collection
    {
        $ctx = schoolCtx();

        return Curso::query()
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->orderByRaw('COALESCE(orden, 9999) asc')
            ->orderBy('Id')
            ->get(['Id', 'cursec', 'orden', 'idCurPlan', 'turno', 'c', 's']);
    }

    /**
     * Materias del curso seleccionado (tabla `materias`), filtradas por contexto.
     *
     * @return Collection<int, mixed>
     */
    public function materias(): Collection
    {
        $ctx = schoolCtx();

        if (! $this->cursoId) {
            return collect();
        }

        return DB::table('materias')
            ->where('idNivel', (int) $ctx->idNivel)
            ->where('idTerlec', (int) $ctx->idTerlec)
            ->where('idCursos', (int) $this->cursoId)
            ->orderBy('ord')
            ->orderBy('id')
            ->get(['id', 'materia', 'abrev', 'ord']);
    }

    public function render()
    {
        $cursos = $this->cursos();
        $materias = $this->materias();

        // Textos auxiliares para el encabezado informativo (no son fuente de verdad; vienen de los mismos datasets).
        $cursoLabel = $this->cursoId
            ? optional($cursos->firstWhere('Id', (int) $this->cursoId))->cursec
            : null;

        $materiaLabel = $this->materiaId
            ? optional($materias->firstWhere('id', (int) $this->materiaId))->materia
            : null;

        return view('livewire.calificaciones.carga-calificaciones', compact('cursos', 'materias', 'cursoLabel', 'materiaLabel'))
            ->layout('layouts.app', ['pageTitle' => 'Carga de calificaciones']);
    }
}

