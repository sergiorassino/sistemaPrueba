@php
    use App\Support\Cuotas\CuotasFormato;
@endphp

<div class="se-page max-w-4xl mx-auto">
    <section class="se-hero mb-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="se-eyebrow">Imputar pago</p>
                @if ($encabezado)
                    <h1 class="text-xl font-bold text-white">{{ $encabezado['apellido'] }} {{ $encabezado['nombre'] }}</h1>
                @endif
            </div>
            <a href="{{ route('cuotas.estudiante', ['idLegajo' => $idLegajo]) }}" wire:navigate
               class="rounded-xl border border-white/25 bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/20">Volver</a>
        </div>
    </section>

    <form wire:submit="guardar" class="space-y-6">
        <div class="se-card p-6 space-y-4">
            <h2 class="text-sm font-bold uppercase tracking-wide text-neutral-800">Medio de pago</h2>
            <div class="flex flex-wrap gap-4">
                @foreach ($mediosPago as $medio)
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm">
                        <input type="radio" wire:model="idCuotastipopago" value="{{ $medio->id }}"
                               class="text-primary-600 focus:ring-primary-500">
                        <span>{{ $medio->tipoPago }}</span>
                    </label>
                @endforeach
            </div>
            @error('idCuotastipopago') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="se-card p-6 space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label" for="saldoAPagar">Saldo a pagar</label>
                    <input id="saldoAPagar" type="text" wire:model.blur="saldoAPagar"
                           class="form-input tabular-nums font-semibold">
                    @error('saldoAPagar') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label" for="porcent">Porcent (Int/Bon)</label>
                    <input id="porcent" type="text" wire:model.blur="porcent" wire:change="recalcular"
                           class="form-input tabular-nums">
                    @error('porcent') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Intereses (importe)</label>
                    <input type="text" readonly value="{{ $interesImporte }}"
                           class="form-input tabular-nums bg-accent-50">
                </div>
                <div>
                    <label class="form-label">A pagar</label>
                    <input type="text" readonly value="{{ $aPagar }}"
                           class="form-input tabular-nums bg-primary-50 font-bold text-primary-900">
                </div>
            </div>

            <div>
                <label class="form-label" for="obs">Observaciones</label>
                <textarea id="obs" wire:model="obs" rows="2" class="form-input"></textarea>
            </div>

            <div class="flex flex-wrap items-center gap-6">
                <label class="inline-flex cursor-pointer items-center gap-3 text-sm font-medium text-neutral-800">
                    <span>Aviso de pago</span>
                    <input type="checkbox" wire:model="avisoPago"
                           class="h-5 w-10 rounded-full border-accent-300 text-primary-600 focus:ring-primary-500">
                </label>
                <p class="text-xs text-neutral-500 max-w-md">Marque si la familia presentó cupón pagado y aún no impactó en SIRO.</p>
            </div>

            <div class="max-w-xs">
                <label class="form-label" for="fechaPago">Fecha pago</label>
                <input id="fechaPago" type="date" wire:model.blur="fechaPago" class="form-input">
                <p class="mt-1 text-[10px] text-neutral-500">Formato DD/MM/AAAA en pantalla al guardar.</p>
                @error('fechaPago') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        @if ($registro)
            <div class="se-card p-6">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wide text-neutral-800">Datos de la cuota</h2>
                <dl class="grid gap-2 text-sm sm:grid-cols-2">
                    <div><dt class="text-neutral-500">Cuota</dt><dd class="font-semibold uppercase">{{ $registro->cuota?->nombre }}</dd></div>
                    <div><dt class="text-neutral-500">Curso</dt><dd class="uppercase">{{ $registro->curso?->nombreParaListado() }}</dd></div>
                    <div><dt class="text-neutral-500">Venc 1</dt><dd>{{ CuotasFormato::formatearFecha($registro->venc1) }}</dd></div>
                    <div><dt class="text-neutral-500">Venc 2</dt><dd>{{ CuotasFormato::formatearFecha($registro->venc2) }}</dd></div>
                    <div><dt class="text-neutral-500">Venc 3</dt><dd>{{ CuotasFormato::formatearFecha($registro->venc3) }}</dd></div>
                    <div><dt class="text-neutral-500">Importe</dt><dd class="tabular-nums">{{ CuotasFormato::formatearImporte($registro->importe) }}</dd></div>
                    <div><dt class="text-neutral-500">Pagado</dt><dd class="tabular-nums">{{ CuotasFormato::formatearImporte($registro->pagado) }}</dd></div>
                    <div><dt class="text-neutral-500">Faltaba</dt><dd class="tabular-nums font-bold">{{ CuotasFormato::formatearImporte($registro->faltapa) }}</dd></div>
                </dl>
            </div>
        @endif

        <div class="flex justify-end gap-2">
            <a href="{{ route('cuotas.estudiante', ['idLegajo' => $idLegajo]) }}" wire:navigate
               class="rounded-xl border border-accent-200 bg-white px-4 py-2 text-sm font-semibold text-primary-700">Cancelar</a>
            <button type="submit" class="rounded-xl bg-primary-600 px-5 py-2 text-sm font-semibold text-white hover:bg-primary-700">
                Registrar pago
            </button>
        </div>
    </form>
</div>
