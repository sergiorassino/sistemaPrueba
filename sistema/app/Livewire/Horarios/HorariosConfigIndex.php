<?php

namespace App\Livewire\Horarios;

use App\Support\HorariosProfesores;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class HorariosConfigIndex extends Component
{
    /** @var array<int, bool> */
    public array $turnosMarcados = [];

    /** @var array<int, bool> */
    public array $diasMarcados = [];

    public int $turnoReloj = 1;

    /** @var array<int, string> */
    public array $relojHoras = [];

    public function mount(): void
    {
        $turnos = HorariosProfesores::turnosActivos();
        $dias = HorariosProfesores::diasActivos();

        $this->turnosMarcados = [];
        foreach (HorariosProfesores::catalogoTurnosClase() as $turno) {
            $id = (int) $turno->id;
            $this->turnosMarcados[$id] = in_array($id, $turnos, true);
        }

        $this->diasMarcados = [];
        foreach (array_keys(HorariosProfesores::DIAS) as $id) {
            $this->diasMarcados[$id] = in_array($id, $dias, true);
        }

        $this->turnoReloj = $turnos[0] ?? 1;
        $this->cargarReloj();
    }

    public function updatedTurnoReloj(): void
    {
        $activos = HorariosProfesores::turnosActivos();
        $t = (int) $this->turnoReloj;
        if (! in_array($t, $activos, true)) {
            $this->turnoReloj = $activos[0] ?? 1;
        }
        $this->cargarReloj();
    }

    public function cargarReloj(): void
    {
        $this->relojHoras = HorariosProfesores::relojPorTurnoClase((int) $this->turnoReloj);
    }

    public function guardarConfig(): void
    {
        $key = 'horarios-config:save:'.(auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 20)) {
            $this->addError('turnosMarcados', 'Demasiados intentos. Espere un momento.');

            return;
        }
        RateLimiter::hit($key, 60);

        $idNivel = (int) (schoolCtx()->idNivel ?? 0);
        if ($idNivel <= 0) {
            abort(403);
        }

        $turnos = collect($this->turnosMarcados)
            ->filter(fn ($v) => $v)
            ->keys()
            ->map(fn ($k) => (int) $k)
            ->values()
            ->all();

        $dias = collect($this->diasMarcados)
            ->filter(fn ($v) => $v)
            ->keys()
            ->map(fn ($k) => (int) $k)
            ->values()
            ->all();

        if ($turnos === []) {
            $this->addError('turnosMarcados', 'Debe haber al menos un turno activo.');

            return;
        }
        if ($dias === []) {
            $this->addError('diasMarcados', 'Debe haber al menos un día de clase.');

            return;
        }

        HorariosProfesores::guardarConfig($idNivel, $turnos, $dias);
        session()->flash('horarios_ok', 'Configuración de horarios guardada.');

        $this->cargarReloj();
    }

    public function guardarReloj(): void
    {
        $key = 'horarios-reloj:save:'.(auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 30)) {
            $this->addError('relojHoras', 'Demasiados intentos. Espere un momento.');

            return;
        }
        RateLimiter::hit($key, 60);

        $idNivel = (int) (schoolCtx()->idNivel ?? 0);
        if ($idNivel <= 0) {
            abort(403);
        }

        $turno = (int) $this->turnoReloj;
        if (! in_array($turno, HorariosProfesores::turnosActivos(), true)) {
            $this->addError('turnoReloj', 'Turno no habilitado.');

            return;
        }

        $horas = [];
        for ($h = 1; $h <= HorariosProfesores::HORAS_POR_TURNO; $h++) {
            $horas[$h] = trim((string) ($this->relojHoras[$h] ?? ''));
        }

        HorariosProfesores::guardarReloj($idNivel, $turno, $horas);
        session()->flash('horarios_ok', 'Horario reloj guardado para '.HorariosProfesores::nombreTurnoClase($turno).'.');
    }

    public function render()
    {
        return view('livewire.horarios.horarios-config-index', [
            'turnosClase' => HorariosProfesores::catalogoTurnosClase(),
            'dias' => HorariosProfesores::DIAS,
            'turnosActivos' => HorariosProfesores::turnosActivos(),
        ])->layout('layouts.app', ['pageTitle' => 'Configuración de horarios']);
    }
}
