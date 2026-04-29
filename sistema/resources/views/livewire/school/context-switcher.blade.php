<div class="relative" x-data="{ open: @entangle('open') }">
    <button type="button"
            class="se-sidebar-iconbtn inline-flex items-center justify-center w-9 h-9 rounded-md transition-colors flex-shrink-0"
            title="Cambiar ciclo lectivo"
            @click="open = !open">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3M5 11h14M7 21h10a2 2 0 002-2v-8a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>
    </button>

    <div x-show="open"
         x-cloak
         @click.outside="open = false"
         class="absolute right-0 top-11 z-[9999] w-64 rounded-lg border border-gray-200 bg-white text-gray-900 shadow-lg p-3">
        <p class="text-xs font-semibold text-gray-700 mb-2">Cambiar ciclo lectivo</p>

        <form wire:submit="changeTerlec" class="space-y-2">
            <div>
                <label class="block text-[11px] font-medium text-gray-600 mb-1" for="ctx_idTerlec">Año lectivo</label>
                <select id="ctx_idTerlec"
                        wire:model="idTerlec"
                        class="form-select text-sm py-2 bg-white text-gray-900">
                    <option value="">— Seleccione año —</option>
                    @foreach ($terlecs as $t)
                        <option value="{{ $t->id }}">{{ $t->ano }}</option>
                    @endforeach
                </select>
                @error('idTerlec')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-2 pt-1">
                <button type="button"
                        class="btn-secondary px-3 py-1.5 text-xs"
                        @click="open = false">
                    Cancelar
                </button>
                <button type="submit"
                        class="btn-primary px-3 py-1.5 text-xs"
                        wire:loading.attr="disabled"
                        wire:target="changeTerlec">
                    Cambiar
                </button>
            </div>
        </form>
    </div>
</div>

