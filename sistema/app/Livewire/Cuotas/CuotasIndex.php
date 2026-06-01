<?php

namespace App\Livewire\Cuotas;

use App\Support\Cuotas\GestionAranceles;
use App\Support\PermisosCuotas;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Búsqueda de estudiante — Gestión de aranceles (Administración).
 */
class CuotasIndex extends Component
{
    use WithPagination;

    public const SESSION_BUSQUEDA = 'cuotas_index_busqueda';

    public string $search = '';

    /** @var array<string, array{except?: mixed, as?: string}> */
    protected $queryString = [
        'search' => ['except' => '', 'as' => 'buscar'],
    ];

    public function mount(): void
    {
        abort_unless(PermisosCuotas::puedeAccederModulo(), 403, 'El módulo de aranceles está disponible solo en el nivel Administración.');
        $this->persistirBusquedaEnSesion();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->persistirBusquedaEnSesion();
    }

    public static function urlIndiceConBusquedaGuardada(): string
    {
        $buscar = trim((string) session(self::SESSION_BUSQUEDA, ''));

        return $buscar !== ''
            ? route('cuotas.index', ['buscar' => $buscar])
            : route('cuotas.index');
    }

    private function persistirBusquedaEnSesion(): void
    {
        session([self::SESSION_BUSQUEDA => $this->search]);
    }

    public function render()
    {
        $legajos = trim($this->search) !== ''
            ? GestionAranceles::buscarLegajos($this->search)
            : null;

        return view('livewire.cuotas.index', [
            'legajos' => $legajos,
        ])->layout('layouts.app', ['pageTitle' => 'Gestión de aranceles']);
    }
}
