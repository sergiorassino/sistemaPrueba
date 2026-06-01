<?php

namespace App\Livewire\Cuotas;

use App\Models\CuotaGenerada;
use App\Support\Cuotas\CuotasFormato;
use App\Support\Cuotas\GestionAranceles;
use App\Support\PermisosCuotas;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

/**
 * Edición de un registro en cuotasgeneradas.
 */
class CuotaGeneradaForm extends Component
{
    public int $idLegajo;

    public int $idCuotaGenerada;

    public string $venc1 = '';

    public string $venc2 = '';

    public string $nueVenc = '';

    public string $importe = '';

    public string $bonificacion = '';

    public string $interes = '';

    public string $pagado = '';

    public string $faltapa = '';

    public string $obs = '';

    public function mount(int $idLegajo, int $idCuotaGenerada): void
    {
        abort_unless(PermisosCuotas::puedeAccederModulo(), 403);
        $this->idLegajo = $idLegajo;
        $this->idCuotaGenerada = $idCuotaGenerada;

        $registro = $this->registro();
        abort_unless($registro !== null, 404);

        $this->venc1 = optional($registro->venc1)?->format('Y-m-d') ?? '';
        $this->venc2 = optional($registro->venc2)?->format('Y-m-d') ?? '';
        $this->nueVenc = optional($registro->nueVenc)?->format('Y-m-d') ?? '';
        $this->importe = CuotasFormato::importeParaInput($registro->importe);
        $this->bonificacion = CuotasFormato::importeParaInput($registro->bonificacion);
        $this->interes = CuotasFormato::importeParaInput($registro->interes);
        $this->pagado = CuotasFormato::importeParaInput($registro->pagado);
        $this->recalcularFaltapa();
        $this->obs = trim((string) ($registro->obs ?? ''));
    }

    public function updatedImporte(): void
    {
        $this->recalcularFaltapa();
    }

    public function updatedPagado(): void
    {
        $this->recalcularFaltapa();
    }

    public function guardar(): void
    {
        abort_unless(PermisosCuotas::puedeAccederModulo(), 403);

        $key = 'cuotas:editar:'.(auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 20)) {
            session()->flash('error', 'Demasiados intentos. Espere un momento.');

            return;
        }
        RateLimiter::hit($key, 60);

        $this->validate([
            'venc1' => ['required', 'date'],
            'venc2' => ['required', 'date'],
            'nueVenc' => ['nullable', 'date'],
            'importe' => ['required', 'string'],
            'bonificacion' => ['required', 'string'],
            'interes' => ['required', 'string'],
            'pagado' => ['required', 'string'],
            'obs' => ['nullable', 'string', 'max:500'],
        ]);

        $importe = CuotasFormato::parseImporte($this->importe);
        $bonificacion = CuotasFormato::parseImporte($this->bonificacion);
        $interes = CuotasFormato::parseImporte($this->interes);
        $pagado = CuotasFormato::parseImporte($this->pagado);

        if ($importe < 0) {
            $this->addError('importe', 'El importe debe ser mayor o igual a cero.');

            return;
        }
        if ($bonificacion < 0) {
            $this->addError('bonificacion', 'La bonificación debe ser mayor o igual a cero.');

            return;
        }
        if ($interes < 0) {
            $this->addError('interes', 'El interés debe ser mayor o igual a cero.');

            return;
        }
        if ($pagado < 0) {
            $this->addError('pagado', 'El importe pagado debe ser mayor o igual a cero.');

            return;
        }

        $registro = $this->registro();
        abort_unless($registro !== null, 404);

        $faltapa = CuotasFormato::calcularFaltapa($importe, $pagado);

        $registro->fill([
            'venc1' => $this->venc1,
            'venc2' => $this->venc2,
            'venc3' => $this->venc2,
            'nueVenc' => $this->nueVenc !== '' ? $this->nueVenc : null,
            'importe' => $importe,
            'bonificacion' => $bonificacion,
            'interes' => $interes,
            'pagado' => $pagado,
            'faltapa' => $faltapa,
            'obs' => trim($this->obs),
        ]);
        $registro->save();

        session()->flash('success', 'Cuota actualizada correctamente.');

        $this->redirectRoute('cuotas.estudiante', ['idLegajo' => $this->idLegajo], navigate: true);
    }

    private function recalcularFaltapa(): void
    {
        $importe = CuotasFormato::parseImporte($this->importe);
        $pagado = CuotasFormato::parseImporte($this->pagado);
        $this->faltapa = CuotasFormato::importeParaInput(
            CuotasFormato::calcularFaltapa($importe, $pagado),
        );
    }

    private function registro(): ?CuotaGenerada
    {
        return GestionAranceles::cuotaParaGestion($this->idCuotaGenerada, $this->idLegajo);
    }

    public function render()
    {
        $registro = $this->registro();

        return view('livewire.cuotas.cuota-form', [
            'registro' => $registro,
            'encabezado' => GestionAranceles::encabezadoEstudiante($this->idLegajo),
            'becaEtiqueta' => $registro !== null ? GestionAranceles::etiquetaBeca($registro) : '',
        ])->layout('layouts.app', ['pageTitle' => 'Editar cuota']);
    }
}
