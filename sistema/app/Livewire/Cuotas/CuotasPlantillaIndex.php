<?php

namespace App\Livewire\Cuotas;

use App\Models\Cuota;
use App\Support\Cuotas\CuotasPlantillaCatalog;
use App\Support\PermisosCuotas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

/**
 * ABM de plantillas de cuotas (`cuotas`) del ciclo lectivo activo.
 */
class CuotasPlantillaIndex extends Component
{
    public string $search = '';

    /** @var array<string|int, array<string, mixed>> */
    public array $draft = [];

    /** Evita reentrada cuando `updated()` dispara tras persistir. */
    public bool $persistiendo = false;

    /** @var array<string, string> Hash del último payload guardado por fila (estado Livewire). */
    public array $ultimoGuardadoHashes = [];

    public function mount(): void
    {
        abort_unless(PermisosCuotas::puedeAccederModulo(), 403);
        $this->cargarFilas();
    }

    public function updatedSearch(): void
    {
        // Solo filtra en la vista; no recarga desde BD.
    }

    public function updated($property): void
    {
        if ($this->persistiendo || $property === 'search') {
            return;
        }

        if (! preg_match('/^draft\.([^.]+)\.([^.]+)$/', (string) $property, $coincidencias)) {
            return;
        }

        if ($coincidencias[2] === 'idTerlec') {
            return;
        }

        $this->saveRowField($coincidencias[1]);
    }

    public function addNew(): void
    {
        // Sin puntos: Livewire usa dot-notation en wire:model (draft.{key}.campo).
        $key = 'new_'.bin2hex(random_bytes(8));
        $maxOrden = (int) collect($this->draft)
            ->pluck('orden')
            ->map(fn ($v) => (int) $v)
            ->max();

        $this->draft[$key] = $this->filaVacia($maxOrden + 1);
        $this->resetValidation();
    }

    public function saveRowField(string $key): void
    {
        abort_unless(PermisosCuotas::puedeAccederModulo(), 403);

        if ($this->persistiendo || ! isset($this->draft[$key])) {
            return;
        }

        $this->normalizarFechasVacias($key);

        $hash = $this->hashFila($key);
        if (($this->ultimoGuardadoHashes[$key] ?? '') === $hash) {
            return;
        }

        $rateKey = 'cuotas-plantilla:save:'.(auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($rateKey, 120)) {
            $this->addError("draft.{$key}.nombre", 'Demasiados intentos. Espere un momento e intente nuevamente.');

            return;
        }
        RateLimiter::hit($rateKey, 60);

        $this->persistiendo = true;

        try {
            $this->validate(CuotasPlantillaCatalog::reglasFila($key, $this->draft[$key]));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->persistiendo = false;

            return;
        }

        $payload = $this->payloadDesdeDraft($key);

        try {
            if (CuotasPlantillaCatalog::esFilaNueva($key)) {
                $cuota = Cuota::query()->create($payload);
                unset($this->draft[$key], $this->ultimoGuardadoHashes[$key]);
                $nuevaClave = (string) $cuota->id;
                $this->draft[$nuevaClave] = $this->filaDesdeModelo($cuota);
                $this->ultimoGuardadoHashes[$nuevaClave] = $this->hashFila($nuevaClave);
            } else {
                $id = (int) $key;
                $cuota = $this->cuotaDelCicloOrFail($id);
                $cuota->update($payload);
                $this->draft[(string) $cuota->id] = $this->filaDesdeModelo($cuota->fresh());
                $this->ultimoGuardadoHashes[(string) $cuota->id] = $this->hashFila((string) $cuota->id);
            }

            $this->resetValidation();
        } finally {
            $this->persistiendo = false;
        }
    }

    public function deleteRow(string $key): void
    {
        abort_unless(PermisosCuotas::puedeAccederModulo(), 403);

        if (CuotasPlantillaCatalog::esFilaNueva($key)) {
            unset($this->draft[$key]);

            return;
        }

        $rateKey = 'cuotas-plantilla:delete:'.(auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($rateKey, 20)) {
            $this->dispatch('se-swal-error', mensaje: 'Demasiados intentos. Espere un momento e intente nuevamente.');

            return;
        }
        RateLimiter::hit($rateKey, 60);

        $id = (int) $key;
        $cuota = $this->cuotaDelCicloOrFail($id);

        $generadas = (int) DB::table('cuotasgeneradas')->where('idCuotas', $id)->count();
        if ($generadas > 0) {
            $this->dispatch(
                'se-swal-error',
                mensaje: "No se puede eliminar la cuota «{$cuota->nombre}» porque tiene {$generadas} registro(s) en cuotas generadas.",
            );

            return;
        }

        $nombre = (string) $cuota->nombre;
        $cuota->delete();
        unset($this->draft[$key]);
        $this->dispatch('se-swal-exito', mensaje: "Cuota «{$nombre}» eliminada.");
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    public function filasVisibles(): array
    {
        $q = mb_strtolower(trim($this->search));
        if ($q === '') {
            return $this->draft;
        }

        return array_filter(
            $this->draft,
            function (array $row) use ($q): bool {
                $nombre = mb_strtolower((string) ($row['nombre'] ?? ''));

                return str_contains($nombre, $q);
            },
        );
    }

    public function render()
    {
        $ano = (int) schoolCtx()->terlecAno();

        return view('livewire.cuotas.plantilla-index', [
            'filas' => $this->filasVisibles(),
            'terlecs' => CuotasPlantillaCatalog::terlecsParaSelector(),
            'meses' => CuotasPlantillaCatalog::mesesOrdenados(),
            'tipos' => CuotasPlantillaCatalog::tiposOrdenados(),
            'opcionesBeca' => CuotasPlantillaCatalog::opcionesSinConBeca(),
            'ano' => $ano,
        ])->layout('layouts.app', ['pageTitle' => "Crear / Editar Cuotas — {$ano}"]);
    }

    private function cargarFilas(): void
    {
        $this->draft = [];
        $idTerlec = CuotasPlantillaCatalog::idTerlecActivo();

        $cuotas = Cuota::query()
            ->where('idTerlec', $idTerlec)
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        foreach ($cuotas as $cuota) {
            $clave = (string) $cuota->id;
            $this->draft[$clave] = $this->filaDesdeModelo($cuota);
            $this->ultimoGuardadoHashes[$clave] = $this->hashFila($clave);
        }
    }

    private function hashFila(string $key): string
    {
        if (! isset($this->draft[$key])) {
            return '';
        }

        return hash('xxh128', json_encode($this->payloadDesdeDraft($key), JSON_THROW_ON_ERROR));
    }

    private function cuotaDelCicloOrFail(int $id): Cuota
    {
        return Cuota::query()
            ->whereKey($id)
            ->where('idTerlec', CuotasPlantillaCatalog::idTerlecActivo())
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    private function filaDesdeModelo(Cuota $cuota): array
    {
        return [
            'id' => (int) $cuota->id,
            'idTerlec' => (int) $cuota->idTerlec,
            'nombre' => (string) ($cuota->nombre ?? ''),
            'idCuotasmeses' => (int) $cuota->idCuotasmeses,
            'idCuotastipo' => (int) $cuota->idCuotastipo,
            'venc1' => optional($cuota->venc1)?->format('Y-m-d') ?? '',
            'venc2' => optional($cuota->venc2)?->format('Y-m-d') ?? '',
            'venc3' => optional($cuota->venc3)?->format('Y-m-d') ?? '',
            'sinConBeca' => (int) ($cuota->sinConBeca ?? 0),
            'orden' => (int) ($cuota->orden ?? 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filaVacia(int $orden): array
    {
        return [
            'id' => null,
            'idTerlec' => CuotasPlantillaCatalog::idTerlecActivo(),
            'nombre' => '',
            'idCuotasmeses' => (int) (CuotasPlantillaCatalog::mesesOrdenados()->value('id') ?? 1),
            'idCuotastipo' => (int) (CuotasPlantillaCatalog::tiposOrdenados()->value('id') ?? 1),
            'venc1' => '',
            'venc2' => '',
            'venc3' => '',
            'sinConBeca' => 0,
            'orden' => $orden,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadDesdeDraft(string $key): array
    {
        $d = $this->draft[$key];

        return [
            'idTerlec' => CuotasPlantillaCatalog::idTerlecActivo(),
            'nombre' => trim((string) ($d['nombre'] ?? '')),
            'idCuotasmeses' => (int) ($d['idCuotasmeses'] ?? 0),
            'idCuotastipo' => (int) ($d['idCuotastipo'] ?? 0),
            'venc1' => $d['venc1'] ?: null,
            'venc2' => ($d['venc2'] ?? '') !== '' ? $d['venc2'] : null,
            'venc3' => ($d['venc3'] ?? '') !== '' ? $d['venc3'] : null,
            'sinConBeca' => (int) ($d['sinConBeca'] ?? 0),
            'orden' => (int) ($d['orden'] ?? 0),
        ];
    }

    private function normalizarFechasVacias(string $key): void
    {
        foreach (['venc2', 'venc3'] as $campo) {
            if (($this->draft[$key][$campo] ?? '') === '') {
                $this->draft[$key][$campo] = '';
            }
        }
    }
}
