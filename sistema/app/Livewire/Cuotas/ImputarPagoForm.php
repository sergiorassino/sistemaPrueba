<?php

namespace App\Livewire\Cuotas;

use App\Models\CuotaGenerada;
use App\Support\Cuotas\CuotasFormato;
use App\Support\Cuotas\GestionAranceles;
use App\Support\Cuotas\ImputacionPagoCalculo;
use App\Support\Cuotas\ImputacionPagoService;
use App\Support\PermisosCuotas;
use Carbon\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

/**
 * Imputación manual de pago sobre una cuota generada.
 */
class ImputarPagoForm extends Component
{
    public int $idLegajo;

    public int $idCuotaGenerada;

    public int $idCuotastipopago = 1;

    public string $saldoAPagar = '';

    public string $porcent = '';

    public string $interesImporte = '';

    public string $aPagar = '';

    public string $obs = '';

    public bool $avisoPago = false;

    public string $fechaPago = '';

    public function mount(int $idLegajo, int $idCuotaGenerada): void
    {
        abort_unless(PermisosCuotas::puedeAccederModulo(), 403);
        $this->idLegajo = $idLegajo;
        $this->idCuotaGenerada = $idCuotaGenerada;

        $registro = $this->registro();
        abort_unless($registro !== null, 404);

        $this->fechaPago = Carbon::today()->format('Y-m-d');
        $this->saldoAPagar = CuotasFormato::importeParaInput($registro->faltapa);
        $this->avisoPago = (int) ($registro->avisoPago ?? 0) === 1;

        $this->recalcular();
    }

    public function updatedSaldoAPagar(): void
    {
        $this->recalcular();
    }

    public function updatedPorcent(): void
    {
        $this->recalcular();
    }

    public function updatedFechaPago(): void
    {
        $this->recalcular();
    }

    public function recalcular(): void
    {
        $registro = $this->registro();
        if ($registro === null) {
            return;
        }

        $saldo = CuotasFormato::parseImporte($this->saldoAPagar);
        $faltapa = (float) ($registro->faltapa ?? 0);
        if ($saldo > $faltapa) {
            $saldo = $faltapa;
            $this->saldoAPagar = CuotasFormato::importeParaInput($saldo);
        }

        $fecha = $this->fechaPagoValida() ?? Carbon::today();
        $porcentRaw = trim($this->porcent);
        $porcentManual = $porcentRaw !== '' ? (float) str_replace(',', '.', $porcentRaw) : null;

        $calc = ImputacionPagoCalculo::calcular($registro, $saldo, $fecha, $porcentManual);

        if ($porcentManual === null) {
            $this->porcent = self::formatearPorcent($calc['porcent']);
        }

        $this->interesImporte = CuotasFormato::importeParaInput($calc['interes']);
        $this->aPagar = CuotasFormato::importeParaInput($calc['aPagar']);
    }

    public function guardar(): void
    {
        abort_unless(PermisosCuotas::puedeAccederModulo(), 403);

        $key = 'cuotas:imputar:'.(auth()->id() ?? 'guest');
        if (RateLimiter::tooManyAttempts($key, 15)) {
            session()->flash('error', 'Demasiados intentos. Espere un momento.');

            return;
        }
        RateLimiter::hit($key, 60);

        $registro = $this->registro();
        abort_unless($registro !== null, 404);

        $validated = $this->validate([
            'idCuotastipopago' => ['required', 'integer', 'min:1'],
            'saldoAPagar' => ['required', 'string'],
            'porcent' => ['nullable', 'numeric', 'min:0'],
            'fechaPago' => ['required', 'date'],
            'obs' => ['nullable', 'string', 'max:500'],
            'avisoPago' => ['boolean'],
        ]);

        $saldo = CuotasFormato::parseImporte($validated['saldoAPagar']);
        $faltapa = (float) ($registro->faltapa ?? 0);

        if ($saldo > $faltapa) {
            $this->addError('saldoAPagar', 'El importe no puede superar el saldo adeudado.');

            return;
        }

        $fecha = Carbon::parse($validated['fechaPago'])->startOfDay();
        $porcentManual = isset($validated['porcent']) ? (float) $validated['porcent'] : null;
        $calc = ImputacionPagoCalculo::calcular($registro, $saldo, $fecha, $porcentManual);

        if ($saldo <= 0 && ! $validated['avisoPago']) {
            $this->addError('saldoAPagar', 'Indique un importe a abonar o active aviso de pago.');

            return;
        }

        if ($saldo > 0 && (int) $validated['idCuotastipopago'] <= 0) {
            $this->addError('idCuotastipopago', 'Seleccione el medio de pago.');

            return;
        }

        ImputacionPagoService::registrar($registro, [
            'idCuotastipopago' => (int) $validated['idCuotastipopago'],
            'saldoAPagar' => $saldo,
            'interes' => $calc['interes'],
            'bonificacion' => $calc['bonificacion'],
            'aPagar' => $calc['aPagar'],
            'fechaPago' => $fecha->format('Y-m-d'),
            'obs' => trim((string) ($validated['obs'] ?? '')),
            'avisoPago' => (bool) $validated['avisoPago'],
        ]);

        session()->flash('success', 'Pago imputado correctamente.');

        $this->redirectRoute('cuotas.estudiante', ['idLegajo' => $this->idLegajo], navigate: true);
    }

    private function registro(): ?CuotaGenerada
    {
        return GestionAranceles::cuotaParaGestion($this->idCuotaGenerada, $this->idLegajo);
    }

    private static function formatearPorcent(float $valor): string
    {
        $s = rtrim(rtrim(number_format($valor, 4, '.', ''), '0'), '.');

        return $s === '' ? '0' : $s;
    }

    private function fechaPagoValida(): ?Carbon
    {
        $raw = trim($this->fechaPago);
        if ($raw === '') {
            return null;
        }

        try {
            return Carbon::parse($raw)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    public function render()
    {
        $registro = $this->registro();

        return view('livewire.cuotas.imputar-pago', [
            'registro' => $registro,
            'encabezado' => GestionAranceles::encabezadoEstudiante($this->idLegajo),
            'mediosPago' => GestionAranceles::mediosDePago(),
        ])->layout('layouts.app', ['pageTitle' => 'Imputar pago']);
    }
}
