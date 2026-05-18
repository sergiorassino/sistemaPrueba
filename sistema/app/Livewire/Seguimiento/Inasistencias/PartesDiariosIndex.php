<?php

namespace App\Livewire\Seguimiento\Inasistencias;

use App\Models\Curso;
use App\Support\HorariosProfesores;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class PartesDiariosIndex extends Component
{
    /** IDs de curso marcados (`cursos.Id` como string). */
    public array $cursosSeleccionados = [];

    /** ID de turnos_clase; solo aplica si hay un único curso seleccionado */
    public ?int $turnoElegido = null;

    /** Fecha del impreso y base para el día de la semana del horario (Y-m-d) */
    public string $fecha = '';

    public function mount(): void
    {
        $this->fecha = now()->format('Y-m-d');
        $this->cursosSeleccionados = [];
    }

    public function updatedCursosSeleccionados(): void
    {
        $this->normalizarCursosSeleccionados();
        if (count($this->cursosSeleccionados) !== 1) {
            $this->turnoElegido = null;
        }
    }

    public function updatedTurnoElegido(mixed $value): void
    {
        if ($value === null || $value === '' || (int) $value <= 0) {
            $this->turnoElegido = null;
        } else {
            $this->turnoElegido = (int) $value;
        }
    }

    public function seleccionarTodosCursos(): void
    {
        $this->cursosSeleccionados = $this->cursosDelContexto()
            ->pluck('Id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    public function quitarTodosCursos(): void
    {
        $this->cursosSeleccionados = [];
        $this->turnoElegido = null;
    }

    protected function normalizarCursosSeleccionados(): void
    {
        $allowed = $this->cursosDelContexto()->pluck('Id')->map(fn ($id) => (int) $id)->all();

        $this->cursosSeleccionados = collect($this->cursosSeleccionados)
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($id) => $id > 0 && in_array($id, $allowed, true))
            ->unique()
            ->sort()
            ->values()
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    /** Etiqueta del día de la semana según {@see $fecha} (ISO, mismo criterio que el PDF). */
    private function etiquetaDiaDesdeFecha(): ?string
    {
        if ($this->fecha === '') {
            return null;
        }

        try {
            $d = (int) Carbon::createFromFormat('Y-m-d', $this->fecha)->startOfDay()->dayOfWeekIso;

            return HorariosProfesores::DIAS[$d] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function fechaValidaParaPdf(): bool
    {
        if ($this->fecha === '') {
            return false;
        }

        try {
            Carbon::createFromFormat('Y-m-d', $this->fecha)->startOfDay();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function puedeGenerarPdf(): bool
    {
        return collect($this->cursosSeleccionados)
                ->filter(fn ($v) => (int) $v > 0)
                ->isNotEmpty()
            && $this->fechaValidaParaPdf();
    }

    /** @return Collection<int, Curso> */
    private function cursosDelContexto(): Collection
    {
        return Curso::query()
            ->where('idNivel', schoolCtx()->idNivel)
            ->where('idTerlec', schoolCtx()->idTerlec)
            ->orderBy('orden')
            ->orderBy('cursec')
            ->get(['Id', 'cursec', 'orden', 'idTurnoClase', 'c', 's']);
    }

    /**
     * Turnos de clase aplicables al único curso seleccionado (selector opcional).
     *
     * @return list<int>
     */
    private function turnosParaUnicoCursoSeleccionado(): array
    {
        if (count($this->cursosSeleccionados) !== 1) {
            return [];
        }

        $id = (int) ($this->cursosSeleccionados[0] ?? 0);
        if ($id <= 0) {
            return [];
        }

        $curso = Curso::query()
            ->where('idNivel', schoolCtx()->idNivel)
            ->where('idTerlec', schoolCtx()->idTerlec)
            ->where('Id', $id)
            ->first(['Id', 'cursec', 'orden', 'idCurPlan', 'idTurnoClase', 'c', 's']);

        if (! $curso) {
            return [];
        }

        return HorariosProfesores::turnosParaImpresionCurso($curso);
    }

    public function render()
    {
        $cursos = $this->cursosDelContexto();
        $turnosCurso = $this->turnosParaUnicoCursoSeleccionado();
        $mostrarSelectorTurno = count($this->cursosSeleccionados) === 1 && count($turnosCurso) > 1;

        $cantidadSeleccionados = collect($this->cursosSeleccionados)
            ->filter(fn ($v) => (int) $v > 0)
            ->count();

        $pdfUrl = null;
        if ($this->puedeGenerarPdf()) {
            $ids = collect($this->cursosSeleccionados)
                ->map(fn ($v) => (int) $v)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values();

            $pdfUrl = route('seguimiento.partes-diarios.pdf', array_filter([
                'cursos' => $ids->implode(','),
                'fecha' => $this->fecha !== '' ? $this->fecha : null,
                'turnoElegido' => $mostrarSelectorTurno && $this->turnoElegido !== null && $this->turnoElegido > 0
                    ? $this->turnoElegido
                    : null,
            ]));
        }

        return view('livewire.seguimiento.inasistencias.partes-diarios-index', [
            'cursos' => $cursos,
            'turnosCurso' => $turnosCurso,
            'mostrarSelectorTurno' => $mostrarSelectorTurno,
            'etiquetaDiaFecha' => $this->etiquetaDiaDesdeFecha(),
            'puedeGenerarPdf' => $this->puedeGenerarPdf(),
            'cantidadSeleccionados' => $cantidadSeleccionados,
            'pdfUrl' => $pdfUrl,
        ])->layout('layouts.app', ['pageTitle' => 'Parte diario del preceptor']);
    }
}
