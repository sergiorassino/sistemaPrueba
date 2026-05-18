<?php

namespace App\Livewire\Horarios;

use App\Support\HorariosProfesores;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class HorariosCargaIndex extends Component
{
    public ?int $profesorId = null;

    public ?int $materiaId = null;

    public ?int $cursoId = null;

    public ?string $avisoConflicto = null;

    /*
     * Depuración SQL en pantalla — desactivada (no ejecutar helpers en servidor salvo uso puntual).
     * Para reactivar: descomentar propiedades, líneas que las usan en updated* / alternarCelda / render,
     * y el bloque Blade `{{-- ... --}}` en horarios-carga-index.
     *
     * No sustituir esto por un `if` que oculte el panel pero siga llamando a HorariosProfesores::textoDepuracionSql*:
     * esas funciones ejecutan trabajo real (consultas armando el texto).
     *
     * public ?string $diagnosticoUltimaCeldaSql = null;
     * public bool $mostrarPanelSqlDepuracion = true;
     */

    public function mount(): void
    {
        $this->profesorId = null;
        $this->materiaId = null;
        $this->cursoId = null;
    }

    public function updatedProfesorId(): void
    {
        $this->materiaId = null;
        $this->cursoId = null;
        $this->avisoConflicto = null;
        // $this->diagnosticoUltimaCeldaSql = null;
    }

    public function updatedMateriaId(): void
    {
        $idMateria = (int) ($this->materiaId ?? 0);
        $asig = $this->asignaciones()->first(
            fn ($a) => (int) ($a->idMateria ?? 0) === $idMateria,
        );
        $this->cursoId = $asig ? (int) $asig->idCursos : null;
        $this->recargarGrilla();
        // $this->diagnosticoUltimaCeldaSql = null;
    }

    protected function recargarGrilla(): void
    {
        $this->avisoConflicto = null;
    }

    public function alternarCelda(string $dia, int $hora, int $indiceBloqueHorario = 0): void
    {
        $this->avisoConflicto = null;

        if (! $this->puedeEditarGrilla()) {
            return;
        }

        $marcadas = HorariosProfesores::celdasMarcadas(
            (int) $this->profesorId,
            (int) $this->materiaId,
            (int) $this->cursoId,
            max(0, min(1, $indiceBloqueHorario)),
        );
        $key = HorariosProfesores::celdaKeyLegacy($dia, $hora);
        $marcar = ! ($marcadas[$key] ?? false);

        $res = HorariosProfesores::alternarCelda(
            (int) $this->profesorId,
            (int) $this->materiaId,
            (int) $this->cursoId,
            $dia,
            $hora,
            $marcar,
            max(0, min(1, $indiceBloqueHorario)),
        );

        if (! $res['ok']) {
            $this->avisoConflicto = $res['mensaje'] ?? 'No se pudo actualizar el horario.';

            return;
        }

        $this->recargarGrilla();
    }

    public function puedeEditarGrilla(): bool
    {
        return ($this->profesorId ?? 0) > 0
            && ($this->materiaId ?? 0) > 0
            && ($this->cursoId ?? 0) > 0;
    }

    /**
     * @return Collection<int, object>
     */
    public function asignaciones(): Collection
    {
        if (($this->profesorId ?? 0) <= 0) {
            return collect();
        }

        return HorariosProfesores::asignacionesProfesor((int) $this->profesorId);
    }

    /**
     * @return Collection<int, object{id:int, label:string}>
     */
    public function profesores(): Collection
    {
        $idNivel = (int) (schoolCtx()->idNivel ?? 0);

        return DB::table('profesores as p')
            ->where(function ($w) {
                $w->whereNull('p.IdTipoProf')->orWhere('p.IdTipoProf', '<>', 1);
            })
            ->when($idNivel > 0, fn ($q) => $q->where(function ($w) use ($idNivel) {
                $w->where('p.nivel', $idNivel)->orWhereNull('p.nivel')->orWhere('p.nivel', 0);
            }))
            ->orderBy('p.apellido')
            ->orderBy('p.nombre')
            ->get(['p.id', 'p.apellido', 'p.nombre'])
            ->map(fn ($r) => (object) [
                'id' => (int) $r->id,
                'label' => trim(((string) $r->apellido).', '.((string) $r->nombre)),
            ]);
    }

    /**
     * @return list<array{indice:int, etiqueta:string, celdas: array<string, bool>}>
     */
    protected function grillasCarga(): array
    {
        if (! $this->puedeEditarGrilla()) {
            return [];
        }

        $asig = $this->asignaciones()->first(
            fn ($a) => (int) ($a->idMateria ?? 0) === (int) ($this->materiaId ?? 0),
        );
        if (! $asig) {
            return [];
        }
        $segmentos = HorariosProfesores::segmentosCargaHorarioDesdeTurnoNorm((int) $asig->idTurnoClase);
        $out = [];
        foreach ($segmentos as $seg) {
            $idx = (int) $seg['indice'];
            $out[] = [
                'indice' => $idx,
                'etiqueta' => (string) ($seg['etiqueta'] ?? ''),
                'celdas' => HorariosProfesores::celdasMarcadas(
                    (int) $this->profesorId,
                    (int) $this->materiaId,
                    (int) $this->cursoId,
                    $idx,
                ),
            ];
        }

        return $out;
    }

    public function render()
    {
        $asignacionActual = $this->asignaciones()->first(
            fn ($a) => (int) ($a->idMateria ?? 0) === (int) ($this->materiaId ?? 0),
        );

        $consultaSqlDepuracion = '';

        return view('livewire.horarios.horarios-carga-index', [
            'profesores' => $this->profesores(),
            'asignaciones' => $this->asignaciones(),
            'dias' => HorariosProfesores::diasActivosLegacy(),
            'horas' => range(1, HorariosProfesores::HORAS_POR_TURNO),
            'asignacionActual' => $asignacionActual,
            'grillasCarga' => $this->grillasCarga(),
            'consultaSqlDepuracion' => $consultaSqlDepuracion,
        ])->layout('layouts.app', ['pageTitle' => 'Carga de horarios']);
    }
}
