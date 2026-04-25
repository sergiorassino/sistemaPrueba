<div>
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md text-sm text-green-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0 w-full text-center sm:flex-1">
            <h2 class="text-xl font-semibold text-gray-800">{{ $id ? 'Editar plan de estudio' : 'Nuevo plan de estudio' }}</h2>
            <p class="text-xs text-gray-400 mt-0.5">Los campos marcados con * son obligatorios</p>
        </div>

        <div class="flex flex-wrap justify-center gap-2 sm:justify-end">
            <a href="{{ route('abm.planes') }}" class="btn-secondary btn-sm">Volver</a>
            <button wire:click="save" wire:loading.attr="disabled" class="btn-primary btn-sm">
                <span wire:loading.remove wire:target="save">Guardar</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>
    </div>

    <div class="card p-5 max-w-3xl">
        <div class="space-y-4">
            <div>
                <label class="form-label">Plan *</label>
                <input wire:model="plan" type="text" maxlength="80"
                       placeholder="Ej: Plan Común"
                       class="form-input @error('plan') border-red-400 @enderror">
                @error('plan') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Abreviatura</label>
                <input wire:model="abrev" type="text" maxlength="15"
                       placeholder="Ej: PC"
                       class="form-input font-mono @error('abrev') border-red-400 @enderror">
                @error('abrev') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        @if ($id)
            <div class="mt-4 text-xs text-gray-400">
                ID #{{ $id }}
            </div>
        @endif
    </div>
</div>

