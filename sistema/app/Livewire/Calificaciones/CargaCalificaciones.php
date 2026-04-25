<?php

namespace App\Livewire\Calificaciones;

use App\Models\Curso;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class CargaCalificaciones extends Component
{
    public ?int $cursoId = null;
    public ?int $materiaId = null;

    /** @var array<int, array<string, mixed>> */
    public array $rows = [];

    public function mount(): void
    {
        $ctx = schoolCtx();

        $this->cursoId = (int) (Curso::query()
            ->where('idNivel', $ctx->idNivel)
            ->where('idTerlec', $ctx->idTerlec)
            ->orderByRaw('COALESCE(orden, 9999) asc')
            ->orderBy('Id')
            ->value('Id') ?? 0) ?: null;
    }

    public function updatedCursoId($value): void
    {
        $this->cursoId = ((int) $value) > 0 ? (int) $value : null;
        $this->materiaId = null;
        $this->rows = [];
        $this->resetValidation();
    }

    public function updatedMateriaId($value): void
    {
        $this->materiaId = ((int) $value) > 0 ? (int) $value : null;
        $this->rows = [];
        $this->resetValidation();

        if ($this->cursoId && $this->materiaId) {
            $this->loadGrid();
        }
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
        $this->ensureScopeOr404();

        $ctx = schoolCtx();

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

    /** @return list<string> */
    protected function editableFields(): array
    {
        return [
            'ic01', 'ic02', 'ic03', 'ic04', 'ic05', 'ic06', 'ic07', 'ic08', 'ic09', 'ic10',
            'ic11', 'ic12', 'ic13', 'ic14', 'ic15', 'ic16', 'ic17', 'ic18', 'ic19', 'ic20',
            'ic21', 'ic22', 'ic23', 'ic24', 'ic25', 'ic26', 'ic27', 'ic28',
            'dic', 'feb', 'calif', 'tea',
        ];
    }

    public function saveCell(int $id, string $field, mixed $value): void
    {
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
            $payload = ['tea' => $value ? 1 : 0];
            DB::table('calificaciones')->where('id', $id)->update($payload);
            if (isset($this->rows[$id])) {
                $this->rows[$id]['tea'] = (bool) $value;
            }
            return;
        }

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

        DB::table('calificaciones')->where('id', $id)->update([$field => (string) ($value ?? '')]);
        if (isset($this->rows[$id])) {
            $this->rows[$id][$field] = (string) ($value ?? '');
        }
    }

    /** @return Collection<int, mixed> */
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

    /** @return Collection<int, mixed> */
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

