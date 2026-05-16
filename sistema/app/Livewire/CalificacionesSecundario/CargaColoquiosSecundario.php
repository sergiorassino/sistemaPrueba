<?php

namespace App\Livewire\CalificacionesSecundario;

use App\Models\Curso;
use App\Support\CalificacionesColoquioSecundario;
use App\Support\Listados\ListadoCursoCondicionFiltro;
use App\Support\PromedioAnualCalificacionesSecundario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * Carga de coloquios Dic / Feb para alumnos regulares con módulos desaprobados o TEA.
 */
class CargaColoquiosSecundario extends Component
{
    /** `dic` (diciembre) o `feb` (febrero). */
    public string $periodo = CalificacionesColoquioSecundario::PERIODO_DICIEMBRE;

    public ?int $cursoId = null;

    public ?int $materiaId = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $rows = [];

    /**
     * @var list<string>
     */
    public array $notasPermitidasLista = [];

    public function mount(): void
    {
        $this->periodo = CalificacionesColoquioSecundario::PERIODO_DICIEMBRE;
        $this->cursoId = null;
        $this->materiaId = null;
        $this->rows = [];
        $this->resetNotasPermitidas();
    }

    public function updatedPeriodo($value): void
    {
        $this->periodo = CalificacionesColoquioSecundario::normalizarPeriodo(is_string($value) ? $value : null);
    }

    public function updatedCursoId($value): void
    {
        $this->cursoId = ((int) $value) > 0 ? (int) $value : null;
        $this->materiaId = null;
        $this->rows = [];
        $this->resetNotasPermitidas();
        $this->resetValidation();
    }

    public function updatedMateriaId($value): void
    {
        $this->materiaId = ((int) $value) > 0 ? (int) $value : null;
        $this->rows = [];
        $this->resetNotasPermitidas();
        $this->resetValidation();

        if ($this->cursoId && $this->materiaId) {
            $this->loadGrid();
        }
    }

    protected function campoActivo(): string
    {
        return CalificacionesColoquioSecundario::normalizarPeriodo($this->periodo);
    }

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

    public function loadGrid(): void
    {
        $this->rows = $this->fetchRowsSnapshot();
        $this->cargarNotasPermitidasParaNivelActual();
    }

    /**
     * Legajos con TEA en cualquier materia del curso (recuperan todas las materias).
     *
     * @return array<int, true> idLegajos
     */
    protected function legajosConTeaEnCurso(int $cursoId, int $idTerlec): array
    {
        $ids = DB::table('calificaciones')
            ->where('idTerlec', $idTerlec)
            ->where('idCursos', $cursoId)
            ->where('tea', 1)
            ->distinct()
            ->pluck('idLegajos');

        $map = [];
        foreach ($ids as $id) {
            $map[(int) $id] = true;
        }

        return $map;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fetchRowsSnapshot(): array
    {
        $this->ensureScopeOr404();

        $ctx = schoolCtx();
        $idTerlec = (int) $ctx->idTerlec;
        $cursoId = (int) $this->cursoId;
        $materiaId = (int) $this->materiaId;

        $idsCondicionesRegulares = ListadoCursoCondicionFiltro::idCondicionesParaQuery(
            ListadoCursoCondicionFiltro::REGULARES
        );

        $teaPorLegajo = $this->legajosConTeaEnCurso($cursoId, $idTerlec);

        $califs = DB::table('calificaciones as c')
            ->join('legajos as l', 'l.id', '=', 'c.idLegajos')
            ->join('matricula as m', function ($join) use ($cursoId, $idTerlec, $ctx, $idsCondicionesRegulares) {
                $join->on('m.idLegajos', '=', 'l.id')
                    ->where('m.idCursos', $cursoId)
                    ->where('m.idTerlec', $idTerlec)
                    ->where('m.idNivel', (int) $ctx->idNivel)
                    ->whereIn('m.idCondiciones', $idsCondicionesRegulares)
                    ->whereNull('m.fechaBaja');
            })
            ->where('c.idTerlec', $idTerlec)
            ->where('c.idCursos', $cursoId)
            ->where('c.idMaterias', $materiaId)
            ->orderByRaw('COALESCE(c.ord, 9999) asc')
            ->orderBy('l.apellido')
            ->orderBy('l.nombre')
            ->get([
                'c.id',
                'c.ord',
                'c.idLegajos',
                'l.apellido',
                'l.nombre',
                'c.ic01', 'c.ic02', 'c.ic03', 'c.ic04', 'c.ic05', 'c.ic06', 'c.ic07', 'c.ic08', 'c.ic09', 'c.ic10',
                'c.ic11', 'c.ic12', 'c.ic13', 'c.ic14', 'c.ic15', 'c.ic16', 'c.ic17', 'c.ic18', 'c.ic19', 'c.ic20',
                'c.ic21', 'c.ic22', 'c.ic23', 'c.ic24', 'c.ic25', 'c.ic26', 'c.ic27', 'c.ic28',
                'c.dic', 'c.feb', 'c.tea', 'c.calif',
            ]);

        $out = [];
        foreach ($califs as $r) {
            $idLegajo = (int) $r->idLegajos;
            $teaEnCurso = isset($teaPorLegajo[$idLegajo]);

            $rowData = [
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
                'tea' => (int) ($r->tea ?? 0),
            ];

            if (! CalificacionesColoquioSecundario::esElegible($rowData, $teaEnCurso)) {
                continue;
            }

            $id = (int) $r->id;
            $dic = (string) ($r->dic ?? '');
            $dicAprobado = CalificacionesColoquioSecundario::notaColoquioAprobada($dic);
            $out[$id] = [
                'id' => $id,
                'ord' => $r->ord,
                'alumno' => trim(((string) $r->apellido).', '.((string) $r->nombre)),
                'dic' => $dic,
                'feb' => (string) ($r->feb ?? ''),
                'calif' => (string) ($r->calif ?? ''),
                'motivo' => CalificacionesColoquioSecundario::motivoElegibilidad($rowData, $teaEnCurso),
                'dic_aprobado' => $dicAprobado,
                'feb_inhabilitado' => $dicAprobado,
            ];
        }

        return $out;
    }

    protected function resetNotasPermitidas(): void
    {
        $this->notasPermitidasLista = [];
    }

    protected function cargarNotasPermitidasParaNivelActual(): void
    {
        $this->resetNotasPermitidas();

        $ctx = schoolCtx();
        $notas = DB::table('notaspermitidas')
            ->where('idNivel', (int) $ctx->idNivel)
            ->pluck('nota');

        foreach ($notas as $n) {
            $clave = trim((string) $n);
            if ($clave === '') {
                continue;
            }
            if (! in_array($clave, $this->notasPermitidasLista, true)) {
                $this->notasPermitidasLista[] = $clave;
            }
        }
    }

    protected function notasPermitidasActiva(): bool
    {
        return $this->notasPermitidasLista !== [];
    }

    protected function notaPermitidaParaCatalogoActual(string $nota): bool
    {
        if ($nota === '') {
            return true;
        }

        return in_array($nota, $this->notasPermitidasLista, true);
    }

    public function saveCell(int $id, string $field, mixed $value): void
    {
        $key = 'calificacionesSecundario:coloquios:cell:'.(auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 240)) {
            return;
        }
        RateLimiter::hit($key, 60);

        $this->ensureScopeOr404();

        $field = trim($field);
        $campoActivo = $this->campoActivo();
        if ($field !== $campoActivo || ! in_array($field, CalificacionesColoquioSecundario::periodos(), true)) {
            abort(400);
        }

        $ctx = schoolCtx();

        $exists = DB::table('calificaciones')
            ->where('id', $id)
            ->where('idTerlec', (int) $ctx->idTerlec)
            ->where('idCursos', (int) $this->cursoId)
            ->where('idMaterias', (int) $this->materiaId)
            ->exists();

        if (! $exists) {
            abort(404);
        }

        if (! isset($this->rows[$id])) {
            abort(404);
        }

        if ($field === CalificacionesColoquioSecundario::PERIODO_FEBRERO
            && (bool) ($this->rows[$id]['feb_inhabilitado'] ?? false)) {
            return;
        }

        $value = is_string($value) ? trim($value) : $value;

        Validator::make(
            ['value' => $value],
            ['value' => ['nullable', 'string', 'max:10']],
            [],
            ['value' => $field],
        )->validate();

        $strVal = (string) ($value ?? '');
        if ($this->notasPermitidasActiva() && $strVal !== '' && ! $this->notaPermitidaParaCatalogoActual($strVal)) {
            $guardado = (string) (DB::table('calificaciones')
                ->where('id', $id)
                ->where('idTerlec', (int) $ctx->idTerlec)
                ->where('idCursos', (int) $this->cursoId)
                ->where('idMaterias', (int) $this->materiaId)
                ->value($field) ?? '');
            $this->rows[$id][$field] = $guardado;

            return;
        }

        DB::table('calificaciones')->where('id', $id)->update([$field => $strVal]);
        $this->rows[$id][$field] = $strVal;
        $this->aplicarCalifTrasColoquio($id, $field, $strVal);
        $this->resetErrorBag('cell.'.$id.'.'.$field);
    }

    protected function aplicarCalifTrasColoquio(int $id, string $field, string $notaColoquio): void
    {
        if (CalificacionesColoquioSecundario::notaColoquioAprobada($notaColoquio)) {
            $calif = CalificacionesColoquioSecundario::califDesdeNotaColoquio($notaColoquio);
            DB::table('calificaciones')->where('id', $id)->update(['calif' => $calif]);
            $this->rows[$id]['calif'] = $calif;

            if ($field === CalificacionesColoquioSecundario::PERIODO_DICIEMBRE) {
                $this->rows[$id]['dic_aprobado'] = true;
                $this->rows[$id]['feb_inhabilitado'] = true;
            }

            return;
        }

        if ($field === CalificacionesColoquioSecundario::PERIODO_DICIEMBRE) {
            $this->rows[$id]['dic_aprobado'] = false;
            $this->rows[$id]['feb_inhabilitado'] = false;
        }

        $this->syncPromedioAnualDesdeModulos($id);
    }

    protected function syncPromedioAnualDesdeModulos(int $id): void
    {
        $ctx = schoolCtx();

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

        $prom = PromedioAnualCalificacionesSecundario::calcular($arr);
        $calif = (string) ($prom['promedio'] ?? '');

        DB::table('calificaciones')->where('id', $id)->update(['calif' => $calif]);

        if (isset($this->rows[$id])) {
            $this->rows[$id]['calif'] = $calif;
        }
    }

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
        $campoActivo = $this->campoActivo();
        $etiquetaPeriodo = CalificacionesColoquioSecundario::etiquetaPeriodo($campoActivo);

        $cursoLabel = $this->cursoId
            ? optional($cursos->firstWhere('Id', (int) $this->cursoId))->cursec
            : null;

        $materiaLabel = $this->materiaId
            ? optional($materias->firstWhere('id', (int) $this->materiaId))->materia
            : null;

        $notasPermitidasLista = $this->notasPermitidasLista;
        $notasPermitidasActiva = $this->notasPermitidasActiva();

        return view(
            'livewire.calificaciones-secundario.carga-coloquios-secundario',
            compact(
                'cursos',
                'materias',
                'cursoLabel',
                'materiaLabel',
                'campoActivo',
                'etiquetaPeriodo',
                'notasPermitidasLista',
                'notasPermitidasActiva',
            ),
        )
            ->layout('layouts.app', ['pageTitle' => 'Coloquios Dic / Feb (secundario)']);
    }
}
