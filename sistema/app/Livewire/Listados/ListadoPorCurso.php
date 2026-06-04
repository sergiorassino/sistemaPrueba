<?php

namespace App\Livewire\Listados;

use App\Models\CampoLegajo;
use App\Support\Listados\ListadoCursoCondicionFiltro;
use App\Support\Listados\ListadoCursoConsulta;
use App\Support\Listados\ListadoCursoPdfFieldCatalog;
use Illuminate\Support\Collection;
use Livewire\Component;

class ListadoPorCurso extends Component
{
    /** @var list<string> */
    public array $cursosElegidos = [];

    public string $filtroCursos = '';

    /** @see ListadoCursoCondicionFiltro */
    public string $filtroCondicion = ListadoCursoCondicionFiltro::REGULARES;

    /** @var list<string> */
    public array $camposSeleccionados = ListadoCursoPdfFieldCatalog::DEFAULT_KEYS;

    public function mount(): void
    {
        $this->camposSeleccionados = CampoLegajo::aplicarVisibilidadListadoPdf($this->camposSeleccionados);
    }

    public function updatedFiltroCondicion(mixed $value): void
    {
        $this->filtroCondicion = ListadoCursoCondicionFiltro::normalize(is_string($value) ? $value : null);
    }

    public function quitarCurso(int $idCurso): void
    {
        $key = (string) $idCurso;
        $this->cursosElegidos = array_values(array_filter(
            $this->cursosElegidos,
            fn (string $id) => $id !== $key,
        ));
    }

    public function seleccionarTodosCursos(): void
    {
        $this->cursosElegidos = $this->idsCursosPermitidosComoString()->keys()->all();
    }

    public function quitarTodosCursos(): void
    {
        $this->cursosElegidos = [];
    }

    public function marcarNivel(int $idNivel): void
    {
        $ids = $this->idsCursosDelNivel($idNivel);
        $this->cursosElegidos = array_values(array_unique(array_merge(
            $this->cursosElegidos,
            $ids,
        )));
    }

    public function quitarNivel(int $idNivel): void
    {
        $quitar = array_flip($this->idsCursosDelNivel($idNivel));
        $this->cursosElegidos = array_values(array_filter(
            $this->cursosElegidos,
            fn (string $id) => ! isset($quitar[$id]),
        ));
    }

    public function render()
    {
        $cursos = ListadoCursoConsulta::cursosPermitidosEnContexto();

        $filtro = mb_strtolower(trim($this->filtroCursos));
        $seleccionadosFlip = array_flip($this->cursosElegidos);
        $cantidadSeleccionados = count($this->cursosElegidos);

        $cursosPorNivel = [];
        foreach ($cursos as $curso) {
            $etiqueta = ListadoCursoConsulta::etiquetaCursoConNivel($curso);
            if ($filtro !== '' && ! str_contains(mb_strtolower($etiqueta), $filtro)) {
                continue;
            }

            $idNivel = (int) ($curso->idNivel ?? 0);
            $key = (string) $idNivel;
            if (! isset($cursosPorNivel[$key])) {
                $cursosPorNivel[$key] = [
                    'idNivel' => $idNivel,
                    'nivelNombre' => trim((string) ($curso->nivel?->nivel ?? 'Sin nivel')),
                    'cursos' => [],
                    'total' => 0,
                    'seleccionados' => 0,
                ];
            }

            $idCursoStr = (string) (int) $curso->Id;
            $marcado = isset($seleccionadosFlip[$idCursoStr]);
            $cursosPorNivel[$key]['cursos'][] = [
                'id' => (int) $curso->Id,
                'etiqueta' => $etiqueta,
                'etiquetaCorta' => $curso->nombreParaListado(),
            ];
            $cursosPorNivel[$key]['total']++;
            if ($marcado) {
                $cursosPorNivel[$key]['seleccionados']++;
            }
        }

        $etiquetasPorId = $cursos->mapWithKeys(fn ($c) => [
            (string) (int) $c->Id => ListadoCursoConsulta::etiquetaCursoConNivel($c),
        ]);

        $cursosSeleccionadosResumen = collect($this->cursosElegidos)
            ->map(fn (string $id) => [
                'id' => (int) $id,
                'label' => (string) ($etiquetasPorId[$id] ?? ''),
            ])
            ->filter(fn (array $r) => $r['label'] !== '')
            ->values()
            ->all();

        $camposPorGrupo = ListadoCursoPdfFieldCatalog::groupedForUiPorSolapas();

        return view('listados::livewire.listados.por-curso', [
            'cursos' => $cursos,
            'cursosPorNivel' => array_values($cursosPorNivel),
            'cantidadSeleccionados' => $cantidadSeleccionados,
            'cursosSeleccionadosResumen' => $cursosSeleccionadosResumen,
            'camposPorGrupo' => $camposPorGrupo,
        ])->layout(layoutMenuStaff(), ['pageTitle' => 'Alumnos por curso']);
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

    /** Exportación completa: todos los cursos y columnas de solapas del legajo. */
    public function getExcelUrlCompletoProperty(): string
    {
        return route('listados.exportar-excel');
    }

    /** Misma selección que el PDF: cursos elegidos, columnas marcadas y condición. */
    public function getExcelUrlSeleccionProperty(): string
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

        return route('listados.exportar-excel', [
            'cursos' => $ids->implode(','),
            'campos' => implode(',', $campos),
            'condicion' => $filtro,
        ]);
    }

    public function puedeExportarExcelCompleto(): bool
    {
        return ListadoCursoConsulta::cursosPermitidosEnContexto()->isNotEmpty();
    }

    public function seleccionarSoloDefecto(): void
    {
        $this->camposSeleccionados = CampoLegajo::aplicarVisibilidadListadoPdf(ListadoCursoPdfFieldCatalog::DEFAULT_KEYS);
    }

    public function seleccionarTodos(): void
    {
        $soloLegajos = CampoLegajo::columnasLegajosVisiblesParaUi();
        $this->camposSeleccionados = collect(ListadoCursoPdfFieldCatalog::allowedKeys())
            ->filter(function (string $k) use ($soloLegajos) {
                if (! str_starts_with($k, 'legajos.')) {
                    return false;
                }
                if ($soloLegajos === null) {
                    return true;
                }

                return in_array(substr($k, strlen('legajos.')), $soloLegajos, true);
            })
            ->values()
            ->all();
    }

    /** @return Collection<string, int> */
    private function idsCursosPermitidosComoString(): Collection
    {
        return ListadoCursoConsulta::cursosPermitidosEnContexto()
            ->pluck('Id')
            ->mapWithKeys(fn ($id) => [(string) (int) $id => (int) $id]);
    }

    /** @return list<string> */
    private function idsCursosDelNivel(int $idNivel): array
    {
        if ($idNivel < 1) {
            return [];
        }

        return ListadoCursoConsulta::cursosPermitidosEnContexto()
            ->filter(fn ($c) => (int) ($c->idNivel ?? 0) === $idNivel)
            ->map(fn ($c) => (string) (int) $c->Id)
            ->values()
            ->all();
    }
}
