<?php

namespace App\Livewire\Listados;

use App\Models\Curso;
use App\Support\ListadoCursoCondicionFiltro;
use App\Support\ListadoCursoPdfFieldCatalog;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ListadoPorCurso extends Component
{
    /** IDs de curso en el panel derecho (orden = orden del listado). */
    public array $cursosElegidos = [];

    /** Selección actual en el listado izquierdo (multiselect). */
    public array $seleccionListaIzq = [];

    /** Selección actual en el listado derecho (multiselect). */
    public array $seleccionListaDer = [];

    /** @see ListadoCursoCondicionFiltro */
    public string $filtroCondicion = ListadoCursoCondicionFiltro::REGULARES;

    /** @var list<string> */
    public array $camposSeleccionados = ListadoCursoPdfFieldCatalog::DEFAULT_KEYS;

    public function updatedFiltroCondicion(mixed $value): void
    {
        $this->filtroCondicion = ListadoCursoCondicionFiltro::normalize(is_string($value) ? $value : null);
    }

    public function render()
    {
        $cursos = $this->queryCursos()->get(['Id', 'cursec', 'orden', 'idCurPlan', 'turno', 'c', 's']);

        $idsElegidosSet = collect($this->cursosElegidos)
            ->map(fn ($v) => (string) $v)
            ->filter()
            ->flip();

        $cursosIzquierda = $cursos->filter(fn (Curso $c) => ! $idsElegidosSet->has((string) $c->Id))->values();

        $cursosDerecha = collect($this->cursosElegidos)
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->map(fn (string $sid) => $cursos->firstWhere('Id', (int) $sid))
            ->filter();

        $camposPorGrupo = ListadoCursoPdfFieldCatalog::groupedForUi();

        return view('livewire.listados.por-curso', compact('cursos', 'cursosIzquierda', 'cursosDerecha', 'camposPorGrupo'))
            ->layout('layouts.app', ['pageTitle' => 'Listado por curso']);
    }

    /** Pasa a la derecha los cursos seleccionados a la izquierda (orden según listado). */
    public function pasarSeleccionADerecha(): void
    {
        $marcados = collect($this->seleccionListaIzq)->map(fn ($v) => (string) $v)->filter()->unique();
        if ($marcados->isEmpty()) {
            return;
        }

        $cursos = $this->queryCursos()->get(['Id', 'cursec', 'orden', 'idCurPlan', 'turno', 'c', 's']);
        $ya = collect($this->cursosElegidos)->map(fn ($v) => (string) $v)->flip();

        foreach ($cursos as $c) {
            $sid = (string) $c->Id;
            if ($marcados->contains($sid) && ! $ya->has($sid)) {
                $this->cursosElegidos[] = $sid;
                $ya->put($sid, true);
            }
        }

        $this->seleccionListaIzq = [];
    }

    /** Quita de la derecha los cursos seleccionados allí. */
    public function pasarSeleccionAIzquierda(): void
    {
        $quitar = collect($this->seleccionListaDer)->map(fn ($v) => (string) $v)->filter()->unique()->all();
        if ($quitar === []) {
            return;
        }

        $this->cursosElegidos = collect($this->cursosElegidos)
            ->map(fn ($v) => (string) $v)
            ->reject(fn (string $id) => in_array($id, $quitar, true))
            ->values()
            ->all();

        $this->seleccionListaDer = [];
    }

    /** Pasa todos los cursos disponibles a la derecha. */
    public function pasarTodosADerecha(): void
    {
        $cursos = $this->queryCursos()->get(['Id', 'cursec', 'orden', 'idCurPlan', 'turno', 'c', 's']);
        $ya = collect($this->cursosElegidos)->map(fn ($v) => (string) $v)->flip();

        foreach ($cursos as $c) {
            $sid = (string) $c->Id;
            if (! $ya->has($sid)) {
                $this->cursosElegidos[] = $sid;
                $ya->put($sid, true);
            }
        }

        $this->seleccionListaIzq = [];
        $this->seleccionListaDer = [];
    }

    /** Vacía el panel derecho. */
    public function pasarTodosAIzquierda(): void
    {
        $this->cursosElegidos = [];
        $this->seleccionListaIzq = [];
        $this->seleccionListaDer = [];
    }

    public function getPdfUrlProperty(): string
    {
        if (! $this->puedeGenerarPdf()) {
            return '#';
        }

        $campos = ListadoCursoPdfFieldCatalog::normalizeSelection($this->camposSeleccionados);
        $filtro = ListadoCursoCondicionFiltro::normalize($this->filtroCondicion);

        $ids = collect($this->cursosElegidos)
            ->filter(fn ($v) => $v !== '' && $v !== null)
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values();

        return route('listados.por-curso.pdf', [
            'cursos' => $ids->implode(','),
            'campos' => implode(',', $campos),
            'condicion' => $filtro,
        ]);
    }

    public function puedeGenerarPdf(): bool
    {
        return collect($this->cursosElegidos)
            ->filter(fn ($v) => $v !== '' && $v !== null)
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($id) => $id > 0)
            ->isNotEmpty();
    }

    public function seleccionarSoloDefecto(): void
    {
        $this->camposSeleccionados = ListadoCursoPdfFieldCatalog::DEFAULT_KEYS;
    }

    public function seleccionarTodos(): void
    {
        $this->camposSeleccionados = ListadoCursoPdfFieldCatalog::allowedKeys();
    }

    /** @return Builder<Curso> */
    protected function queryCursos(): Builder
    {
        return Curso::query()
            ->with('curplan')
            ->where('idNivel', schoolCtx()->idNivel)
            ->where('idTerlec', schoolCtx()->idTerlec)
            ->orderBy('orden')
            ->orderBy('cursec');
    }
}
