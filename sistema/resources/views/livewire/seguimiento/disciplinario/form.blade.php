<div>
    {{-- Flash --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
             class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md text-sm text-green-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0 w-full text-center sm:flex-1">
            <h2 class="text-xl font-semibold text-gray-800">{{ $this->id ? 'Editar sanción' : 'Nueva sanción' }}</h2>
            <p class="text-xs text-gray-400 mt-0.5">Los campos marcados con * son obligatorios</p>
        </div>

        <div class="flex flex-wrap justify-center gap-2 sm:justify-end">
            @if ($m)
                <a href="{{ route('seguimiento.disciplinario', ['curso' => $m->idCursos, 'matricula' => $m->id]) }}"
                   class="btn-secondary btn-sm">Cancelar</a>
            @else
                <a href="{{ route('seguimiento.disciplinario') }}" class="btn-secondary btn-sm">Cancelar</a>
            @endif

            <button wire:click="save" wire:loading.attr="disabled" class="btn-primary btn-sm">
                <span wire:loading.remove wire:target="save">Guardar</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>
    </div>

    <x-form-shell maxWidth="max-w-4xl">
        <div class="card p-6 space-y-4">
            @if ($m)
                <div class="text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                    <div class="font-semibold">{{ $m->legajo?->apellido }}, {{ $m->legajo?->nombre }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">
                        Curso: {{ $m->curso?->nombreParaListado() ?? '—' }} · Año: {{ schoolCtx()->terlecAno() }}
                    </div>
                </div>
            @endif

            <input type="hidden" wire:model="idMatricula">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Tipo de registro *</label>
                    <select wire:model.live="idTipoSancion" class="form-select @error('idTipoSancion') border-red-400 @enderror">
                        <option value="">— Seleccione —</option>
                        @foreach ($tipos as $t)
                            <option value="{{ $t->id }}">{{ $t->tipo }}</option>
                        @endforeach
                    </select>
                    @error('idTipoSancion') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Fecha *</label>
                    <input wire:model="fecha" type="date" class="form-input @error('fecha') border-red-400 @enderror">
                    @error('fecha') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div x-data="{ tipo: @entangle('idTipoSancion').live }">
                    <label class="form-label">Cantidad</label>
                    <input x-show="true"
                           wire:model="cantidad"
                           type="text"
                           inputmode="numeric"
                           maxlength="2"
                           class="form-input @error('cantidad') border-red-400 @enderror">
                    @error('cantidad') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Motivo</label>
                    <textarea wire:model="motivo" rows="4" class="form-input resize-y @error('motivo') border-red-400 @enderror"></textarea>
                    @error('motivo') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Solicitada por</label>
                    <input wire:model="solipor" type="text" maxlength="150" class="form-input @error('solipor') border-red-400 @enderror">
                    @error('solipor') <p class="form-error">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-500 mt-1">Si se deja vacío, se toma el profesor del contexto actual.</p>
                </div>
            </div>
        </div>
    </x-form-shell>
</div>

