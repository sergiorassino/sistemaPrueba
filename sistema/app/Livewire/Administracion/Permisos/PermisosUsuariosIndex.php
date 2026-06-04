<?php

namespace App\Livewire\Administracion\Permisos;

use App\Models\PermisoIa;
use App\Models\Profesor;
use App\Support\PermisosIaCatalog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class PermisosUsuariosIndex extends Component
{
    /** IdTipoProf que identifica «Sin Rol» en la tabla profesortipo. */
    private const ID_TIPO_SIN_ROL = 1;

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
        $this->reset('permisos');
        $this->profesorId = null;

        if (! $id) {
            return;
        }

        $profesor = Profesor::query()
            ->where('nivel', (int) (schoolCtx()->idNivel ?? 0))
            ->whereKey($id)
            ->firstOrFail(['id', 'dni', 'nombre', 'apellido', 'permisos_ia', 'nivel']);

        $this->profesorId = (int) $profesor->id;

        $maxOrden = max(
            (int) (PermisoIa::query()->max('orden') ?? 0),
            PermisosIaCatalog::maxOrden(),
        );
        $cadena = trim((string) ($profesor->permisos_ia ?? ''));
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

        if (! $this->profesorId) {
            $this->addError('profesorId', 'Seleccione un usuario.');

            return;
        }

        $key = 'permisos:toggle:' . (auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 60)) {
            session()->flash('success', 'Demasiados cambios seguidos. Espere un momento e intente nuevamente.');

            return;
        }
        RateLimiter::hit($key, 60);

        $this->permisos[$orden] = ! ($this->permisos[$orden] ?? false);
        $this->persistirPermisosCadena();

        session()->flash('success', 'Permiso guardado.');
    }

    private function persistirPermisosCadena(): void
    {
        $profesor = Profesor::query()
            ->where('nivel', (int) (schoolCtx()->idNivel ?? 0))
            ->whereKey($this->profesorId)
            ->firstOrFail(['id', 'permisos_ia']);

        $maxOrden = max(
            (int) (PermisoIa::query()->max('orden') ?? 0),
            PermisosIaCatalog::maxOrden(),
        );
        $chars = [];
        foreach (range(0, $maxOrden) as $orden) {
            $chars[] = ($this->permisos[$orden] ?? false) ? '1' : '0';
        }

        $profesor->forceFill(['permisos_ia' => implode('', $chars)])->save();

        if ((int) $profesor->id === (int) (schoolCtx()->idProfesor ?? 0)) {
            schoolCtx()->refreshProfesor();
        }
    }

    /**
     * @return array{0:Collection<int,Profesor>,1:?Profesor,2:Collection<int,PermisoIa>,3:array<string,Collection<int,PermisoIa>>}
     */
    private function data(): array
    {
        $nivel = (int) (schoolCtx()->idNivel ?? 0);

        $usuarios = Profesor::query()
            ->where('nivel', $nivel)
            ->where(function ($w) {
                $w->whereNull('IdTipoProf')
                    ->orWhere('IdTipoProf', '<>', self::ID_TIPO_SIN_ROL);
            })
            ->when(trim($this->q) !== '', function ($q) {
                $term = '%' . trim($this->q) . '%';
                $q->where(function ($w) use ($term) {
                    $w->where('apellido', 'like', $term)
                        ->orWhere('nombre', 'like', $term)
                        ->orWhere('dni', 'like', $term);
                });
            })
            ->with(['tipo:id,tipo'])
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->limit(200)
            ->get(['id', 'dni', 'nombre', 'apellido', 'IdTipoProf']);

        $profesorSeleccionado = null;
        if ($this->profesorId) {
            $profesorSeleccionado = Profesor::query()
                ->where('nivel', $nivel)
                ->whereKey($this->profesorId)
                ->with(['tipo:id,tipo'])
                ->first(['id', 'dni', 'nombre', 'apellido', 'IdTipoProf']);
        }

        $catalogo = PermisoIa::query()
            ->orderBy('orden')
            ->get(['id', 'orden', 'tema', 'descripcion']);

        $porTema = $catalogo
            ->groupBy(function (PermisoIa $p) {
                $tema = trim((string) ($p->tema ?? ''));

                return $tema !== '' ? $tema : 'OTROS';
            })
            ->map(fn (Collection $items) => $items->sortBy('orden')->values())
            ->sortKeysUsing(fn (string $a, string $b) => strcasecmp($a, $b));

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
        ])->layout(layoutMenuStaff(), ['pageTitle' => 'Permisos de Usuarios']);
    }
}

