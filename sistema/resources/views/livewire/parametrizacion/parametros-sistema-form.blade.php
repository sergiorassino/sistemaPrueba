<x-form-shell maxWidth="max-w-4xl">
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
                <h2 class="text-xl font-semibold text-gray-800">Parámetros del sistema</h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Editando nivel: <span class="font-semibold">{{ $nivelNombre !== '' ? $nivelNombre : '—' }}</span>
                </p>
            </div>

            <div class="flex flex-wrap justify-center gap-2 sm:justify-end">
                <a href="{{ route('dashboard') }}" class="btn-secondary btn-sm">Volver</a>
                <button wire:click="save" wire:loading.attr="disabled" class="btn-primary btn-sm">
                    <span wire:loading.remove wire:target="save">Guardar</span>
                    <span wire:loading wire:target="save">Guardando…</span>
                </button>
            </div>
        </div>

        <div class="card p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="form-label">Institución</label>
                    <input wire:model="insti" type="text" maxlength="120" class="form-input @error('insti') border-red-400 @enderror">
                    @error('insti') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">CUE</label>
                    <input wire:model="cue" type="text" maxlength="30" class="form-input font-mono @error('cue') border-red-400 @enderror">
                    @error('cue') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">EE</label>
                    <input wire:model="ee" type="text" maxlength="30" class="form-input font-mono @error('ee') border-red-400 @enderror">
                    @error('ee') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">CUIT</label>
                    <input wire:model="cuit" type="text" maxlength="20" class="form-input font-mono @error('cuit') border-red-400 @enderror">
                    @error('cuit') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Categoría</label>
                    <input wire:model="categoria" type="text" maxlength="80" class="form-input @error('categoria') border-red-400 @enderror">
                    @error('categoria') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">Dirección</label>
                    <input wire:model="direccion" type="text" maxlength="150" class="form-input @error('direccion') border-red-400 @enderror">
                    @error('direccion') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Localidad</label>
                    <input wire:model="localidad" type="text" maxlength="80" class="form-input @error('localidad') border-red-400 @enderror">
                    @error('localidad') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Departamento</label>
                    <input wire:model="departamento" type="text" maxlength="80" class="form-input @error('departamento') border-red-400 @enderror">
                    @error('departamento') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Provincia</label>
                    <input wire:model="provincia" type="text" maxlength="80" class="form-input @error('provincia') border-red-400 @enderror">
                    @error('provincia') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Teléfono</label>
                    <input wire:model="telefono" type="text" maxlength="50" class="form-input @error('telefono') border-red-400 @enderror">
                    @error('telefono') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Mail</label>
                    <input wire:model="mail" type="email" maxlength="120" class="form-input @error('mail') border-red-400 @enderror">
                    @error('mail') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Rep. legal</label>
                    <input wire:model="replegal" type="text" maxlength="120" class="form-input @error('replegal') border-red-400 @enderror">
                    @error('replegal') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 border-t pt-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">Logo (JPG/JPEG/PNG por nivel)</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                    <div class="space-y-3">
                        <div>
                            <label class="form-label">Subir logo</label>
                            <input wire:model="logo" type="file" accept="image/jpeg,image/png" class="form-input @error('logo') border-red-400 @enderror">
                            @error('logo') <p class="form-error">{{ $message }}</p> @enderror
                            <p class="text-xs text-gray-500 mt-1">Formato: JPG/JPEG/PNG. Máx: 2MB.</p>
                        </div>

                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="removeLogo"
                                   class="rounded border-gray-300 text-[#40848D] focus:ring-[#40848D]">
                            <span class="text-xs text-gray-600">Quitar logo actual</span>
                        </label>
                    </div>

                    <div class="space-y-2">
                        <p class="text-xs text-gray-600">Vista previa</p>

                        <div class="border border-gray-200 rounded-lg bg-white p-3 flex items-center justify-center min-h-[120px]">
                            @if ($logo)
                                <img src="{{ $logo->temporaryUrl() }}" alt="Logo (preview)"
                                     class="max-h-28 object-contain">
                            @elseif ($currentLogoUrl)
                                <img src="{{ $currentLogoUrl }}" alt="Logo (actual)"
                                     class="max-h-28 object-contain">
                            @else
                                <span class="text-xs text-gray-400">Sin logo</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-form-shell>

