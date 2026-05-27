{{-- Datos adicionales del analítico (analiticodatos) por legajo. --}}
<div class="se-page max-w-3xl">
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
            {{ session('success') }}
        </div>
    @endif

    <section class="se-hero">
        <div class="se-hero-inner !gap-3 !p-4 sm:!p-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1 space-y-1">
                <p class="se-eyebrow">Matríz y analíticos</p>
                <h2 class="text-xl font-bold tracking-tight sm:text-2xl">Datos adicionales</h2>
                @if (! empty($alumno))
                    <p class="text-sm text-white/90">
                        <span class="font-semibold">{{ $alumno['apellido'] }}, {{ $alumno['nombre'] }}</span>
                        @if (($alumno['dni'] ?? '') !== '')
                            · DNI {{ $alumno['dni'] }}
                        @endif
                        @if (($alumno['curso'] ?? '') !== '')
                            · {{ $alumno['curso'] }}
                        @endif
                        <span class="text-white/70"> · {{ schoolCtx()->nivelNombre() }}</span>
                    </p>
                @endif
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
                <button type="button"
                        wire:click="guardar"
                        wire:loading.attr="disabled"
                        wire:target="guardar"
                        class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-primary-700 shadow-sm transition hover:bg-accent-100 disabled:opacity-60">
                    <span wire:loading.remove wire:target="guardar">Guardar</span>
                    <span wire:loading wire:target="guardar">Guardando…</span>
                </button>
                <x-nav-contexto-estudiante
                    destino="matrizAnaliticos.libroMatriz.editar"
                    :alcance="\App\Support\Navegacion\ContextoEstudianteSesion::MATRIZ_ANALITICOS"
                    :id-legajos="$idLegajos"
                    class="inline">
                    <span class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Volver a matriz
                    </span>
                </x-nav-contexto-estudiante>
            </div>
        </div>
    </section>

    @error('guardar')
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
            {{ $message }}
        </div>
    @enderror

    <form wire:submit="guardar" class="se-card overflow-hidden p-6 sm:p-7">
        <p class="se-section-title mb-5">Certificado analítico · datos complementarios</p>

        <div class="space-y-5">
            <div>
                <label for="analCohorte" class="form-label">Cohorte</label>
                <input id="analCohorte"
                       type="text"
                       wire:model="analCohorte"
                       maxlength="30"
                       class="form-input mt-1.5 @error('analCohorte') border-red-400 @enderror">
                @error('analCohorte') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="analObservaciones" class="form-label">Observaciones</label>
                <textarea id="analObservaciones"
                          wire:model="analObservaciones"
                          rows="4"
                          class="form-input mt-1.5 leading-relaxed @error('analObservaciones') border-red-400 @enderror"></textarea>
                @error('analObservaciones') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="analParaCompletar" class="form-label">Leyenda (para completar)</label>
                <textarea id="analParaCompletar"
                          wire:model="analParaCompletar"
                          rows="4"
                          class="form-input mt-1.5 leading-relaxed @error('analParaCompletar') border-red-400 @enderror"></textarea>
                @error('analParaCompletar') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="analValidez" class="form-label">Validez</label>
                <input id="analValidez"
                       type="text"
                       wire:model="analValidez"
                       maxlength="50"
                       class="form-input mt-1.5 @error('analValidez') border-red-400 @enderror">
                @error('analValidez') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="serie" class="form-label">Serie</label>
                <input id="serie"
                       type="text"
                       wire:model="serie"
                       maxlength="6"
                       class="form-input mt-1.5 @error('serie') border-red-400 @enderror">
                @error('serie') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="analLibroFolio" class="form-label">Libro folio</label>
                <input id="analLibroFolio"
                       type="text"
                       wire:model="analLibroFolio"
                       maxlength="50"
                       class="form-input mt-1.5 @error('analLibroFolio') border-red-400 @enderror">
                @error('analLibroFolio') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="analFechaEmision" class="form-label">Fecha emisión</label>
                <input id="analFechaEmision"
                       type="date"
                       wire:model="analFechaEmision"
                       class="form-input mt-1.5 @error('analFechaEmision') border-red-400 @enderror">
                @error('analFechaEmision') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="analParaPre" class="form-label">Para presentar a</label>
                <input id="analParaPre"
                       type="text"
                       wire:model="analParaPre"
                       maxlength="200"
                       class="form-input mt-1.5 @error('analParaPre') border-red-400 @enderror">
                @error('analParaPre') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        @if ($idAnaliticoDato)
            <p class="mt-6 text-xs text-neutral-400">Registro analítico #{{ $idAnaliticoDato }}</p>
        @else
            <p class="mt-6 text-xs text-neutral-400">Sin registro previo: al guardar se creará uno nuevo para este legajo.</p>
        @endif

        <div class="mt-6 flex flex-wrap justify-end gap-2 border-t border-accent-200 pt-5">
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="guardar"
                    class="btn-primary">
                <span wire:loading.remove wire:target="guardar">Guardar</span>
                <span wire:loading wire:target="guardar">Guardando…</span>
            </button>
        </div>
    </form>
</div>
