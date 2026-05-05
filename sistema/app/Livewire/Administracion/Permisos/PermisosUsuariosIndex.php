<?php

namespace App\Livewire\Administracion\Permisos;

use App\Models\PermisoUsuario;
use App\Models\Profesor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class PermisosUsuariosIndex extends Component
{
    public string $q = '';
    public ?int $profesorId = null;

    /** @var array<int,bool> */
    public array $permisos = [];

    public function mount(): void
    {
        abort_unless(tienePermiso(0), 403, 'Sin permiso para administrar permisos.');
    }

    public function updatedProfesorId($value): void
    {
        $id = is_numeric($value) ? (int) $value : null;
        $this->cargarProfesor($id);
    }

    public function seleccionarProfesor(int $id): void
    {
        $this->cargarProfesor($id);
    }

    private function cargarProfesor(?int $id): void
    {
        $this->resetValidation();
        $this->permisos = [];
        $this->profesorId = null;

        if (! $id) {
            return;
        }

        $profesor = Profesor::query()
            ->where('nivel', (int) (schoolCtx()->idNivel ?? 0))
            ->whereKey($id)
            ->firstOrFail(['id', 'dni', 'nombre', 'apellido', 'permisos', 'nivel']);

        $this->profesorId = (int) $profesor->id;

        $maxOrden = (int) (PermisoUsuario::query()->max('orden') ?? 0);
        $cadena = (string) ($profesor->permisos ?? '');
        if ($cadena === '') {
            $cadena = str_repeat('0', $maxOrden + 1);
        } elseif (strlen($cadena) <= $maxOrden) {
            $cadena = str_pad($cadena, $maxOrden + 1, '0', STR_PAD_RIGHT);
        }

        foreach (range(0, $maxOrden) as $orden) {
            $this->permisos[$orden] = isset($cadena[$orden]) && $cadena[$orden] === '1';
        }
    }

    public function togglePermiso(int $orden): void
    {
        abort_unless(tienePermiso(0), 403);

        $this->permisos[$orden] = ! ($this->permisos[$orden] ?? false);
    }

    public function guardar(): void
    {
        abort_unless(tienePermiso(0), 403);

        $key = 'permisos:save:' . (auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 30)) {
            session()->flash('success', 'Demasiados intentos. Espere un momento e intente nuevamente.');
            return;
        }
        RateLimiter::hit($key, 60);

        if (! $this->profesorId) {
            $this->addError('profesorId', 'Seleccione un usuario.');
            return;
        }

        $profesor = Profesor::query()
            ->where('nivel', (int) (schoolCtx()->idNivel ?? 0))
            ->whereKey($this->profesorId)
            ->firstOrFail(['id', 'permisos']);

        $maxOrden = (int) (PermisoUsuario::query()->max('orden') ?? 0);
        $chars = [];
        foreach (range(0, $maxOrden) as $orden) {
            $chars[] = ($this->permisos[$orden] ?? false) ? '1' : '0';
        }

        $profesor->update([
            'permisos' => implode('', $chars),
        ]);

        session()->flash('success', 'Permisos actualizados correctamente.');
    }

    /**
     * @return array{0:Collection<int,Profesor>,1:?Profesor,2:Collection<int,PermisoUsuario>,3:array<string,Collection<int,PermisoUsuario>>}
     */
    private function data(): array
    {
        $nivel = (int) (schoolCtx()->idNivel ?? 0);

        $usuarios = Profesor::query()
            ->where('nivel', $nivel)
            ->when(trim($this->q) !== '', function ($q) {
                $term = '%' . trim($this->q) . '%';
                $q->where(function ($w) use ($term) {
                    $w->where('apellido', 'like', $term)
                        ->orWhere('nombre', 'like', $term)
                        ->orWhere('dni', 'like', $term);
                });
            })
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->limit(200)
            ->get(['id', 'dni', 'nombre', 'apellido']);

        $profesorSeleccionado = null;
        if ($this->profesorId) {
            $profesorSeleccionado = Profesor::query()
                ->where('nivel', $nivel)
                ->whereKey($this->profesorId)
                ->first(['id', 'dni', 'nombre', 'apellido']);
        }

        $catalogo = PermisoUsuario::query()
            ->orderBy('orden')
            ->get(['id', 'orden', 'tema', 'descripcion']);

        $porTema = $catalogo->groupBy(function (PermisoUsuario $p) {
            $tema = trim((string) ($p->tema ?? ''));
            return $tema !== '' ? $tema : 'OTROS';
        });

        return [$usuarios, $profesorSeleccionado, $catalogo, $porTema];
    }

    public function render()
    {
        [$usuarios, $profesorSeleccionado, $catalogo, $porTema] = $this->data();

        return view('livewire.administracion.permisos.usuarios-index', [
            'usuarios' => $usuarios,
            'profesorSeleccionado' => $profesorSeleccionado,
            'catalogo' => $catalogo,
            'porTema' => $porTema,
        ])->layout('layouts.app', ['pageTitle' => 'Permisos de usuarios']);
    }
}

