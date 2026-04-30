<div class="se-page max-w-4xl">
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="se-soft-card flex items-center gap-3 border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            <svg class="h-5 w-5 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <section class="se-hero">
        <div class="se-hero-inner">
            <div class="min-w-0 space-y-2">
                <p class="se-eyebrow">Configuración</p>
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Parámetros del sistema</h2>
                <p class="text-sm text-white/80">
                    Nivel: <span class="font-semibold">{{ $nivelNombre !== '' ? $nivelNombre : '—' }}</span>
                    · {{ schoolCtx()->terlecAno() }}
                </p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">
                    Volver
                </a>
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-primary-700 shadow-sm transition hover:bg-accent-100 disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">Guardar</span>
                    <span wire:loading wire:target="save">Guardando…</span>
                </button>
            </div>
        </div>
    </section>

    <div class="se-card overflow-hidden p-6 sm:p-7">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="form-label">Institución</label>
                <input wire:model="insti" type="text" maxlength="120" class="form-input mt-1.5 @error('insti') border-red-400 @enderror">
                @error('insti') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">CUE</label>
                <input wire:model="cue" type="text" maxlength="30" class="form-input mt-1.5 font-mono @error('cue') border-red-400 @enderror">
                @error('cue') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">EE</label>
                <input wire:model="ee" type="text" maxlength="30" class="form-input mt-1.5 font-mono @error('ee') border-red-400 @enderror">
                @error('ee') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">CUIT</label>
                <input wire:model="cuit" type="text" maxlength="20" class="form-input mt-1.5 font-mono @error('cuit') border-red-400 @enderror">
                @error('cuit') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Categoría</label>
                <input wire:model="categoria" type="text" maxlength="80" class="form-input mt-1.5 @error('categoria') border-red-400 @enderror">
                @error('categoria') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="form-label">Dirección</label>
                <input wire:model="direccion" type="text" maxlength="150" class="form-input mt-1.5 @error('direccion') border-red-400 @enderror">
                @error('direccion') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Localidad</label>
                <input wire:model="localidad" type="text" maxlength="80" class="form-input mt-1.5 @error('localidad') border-red-400 @enderror">
                @error('localidad') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Departamento</label>
                <input wire:model="departamento" type="text" maxlength="80" class="form-input mt-1.5 @error('departamento') border-red-400 @enderror">
                @error('departamento') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Provincia</label>
                <input wire:model="provincia" type="text" maxlength="80" class="form-input mt-1.5 @error('provincia') border-red-400 @enderror">
                @error('provincia') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Teléfono</label>
                <input wire:model="telefono" type="text" maxlength="50" class="form-input mt-1.5 @error('telefono') border-red-400 @enderror">
                @error('telefono') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Mail</label>
                <input wire:model="mail" type="email" maxlength="120" class="form-input mt-1.5 @error('mail') border-red-400 @enderror">
                @error('mail') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Rep. legal</label>
                <input wire:model="replegal" type="text" maxlength="120" class="form-input mt-1.5 @error('replegal') border-red-400 @enderror">
                @error('replegal') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-8 border-t border-accent-200 pt-6">
            <p class="se-section-title mb-4">Logo (JPG/JPEG/PNG por nivel)</p>

            <div class="grid grid-cols-1 items-start gap-6 md:grid-cols-2">
                <div class="space-y-3">
                    <div>
                        <label class="form-label">Subir logo</label>
                        <input wire:model="logo" type="file" accept="image/jpeg,image/png"
                               class="form-input mt-1.5 @error('logo') border-red-400 @enderror">
                        @error('logo') <p class="form-error">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-neutral-500">JPG/JPEG/PNG · máx. 2&nbsp;MB.</p>
                    </div>

                    <label class="inline-flex cursor-pointer items-center gap-2">
                        <input type="checkbox" wire:model="removeLogo"
                               class="rounded border-accent-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-xs text-neutral-600">Quitar logo actual</span>
                    </label>
                </div>

                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-neutral-500">Vista previa</p>
                    <div class="flex min-h-[120px] items-center justify-center rounded-2xl border border-accent-200 bg-white p-4">
                        @if ($logo)
                            <img src="{{ $logo->temporaryUrl() }}" alt="Logo (preview)" class="max-h-28 object-contain">
                        @elseif ($currentLogoUrl)
                            <img src="{{ $currentLogoUrl }}" alt="Logo (actual)" class="max-h-28 object-contain">
                        @else
                            <span class="text-xs text-neutral-400">Sin logo</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
