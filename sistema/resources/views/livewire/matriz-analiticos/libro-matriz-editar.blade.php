{{-- Edición en grilla de calificaciones del matriz (secundario) — estilo planilla compacta. --}}
<div class="se-cierre-anual-fill se-matriz-edit-fill">
    <div class="se-cierre-anual-grid se-cierre-anual-grid--matriz-edit gap-2 min-h-0 flex-1">
        <section class="se-hero se-matriz-edit-hero min-w-0 shrink-0">
            <div class="se-hero-inner flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0 flex-1 space-y-0.5">
                    <p class="se-eyebrow !text-[10px]">Matríz y analíticos</p>
                    <h2 class="font-bold tracking-tight">Calificaciones en matriz</h2>
                    @if (! empty($alumno))
                        <p class="text-xs text-white/90 leading-snug truncate" title="{{ $alumno['apellido'] }}, {{ $alumno['nombre'] }}">
                            <span class="font-semibold">{{ $alumno['apellido'] }}, {{ $alumno['nombre'] }}</span>
                            @if (($alumno['dni'] ?? '') !== '')
                                · {{ $alumno['dni'] }}
                            @endif
                            @if (($alumno['curso'] ?? '') !== '')
                                · {{ $alumno['curso'] }}
                            @endif
                        </p>
                    @endif
                </div>
                <div class="flex shrink-0 flex-wrap gap-1.5">
                    <a href="{{ \App\Support\MatrizAnaliticos\LibroMatrizAnalitico::urlDatosAdicionales($idLegajos, $buscarRetorno) }}"
                       wire:navigate
                       class="inline-flex items-center justify-center rounded-lg border border-white/25 bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white transition hover:bg-white/20">
                        Datos adicionales
                    </a>
                    <button type="button"
                            wire:click="solicitarVolver"
                            class="inline-flex items-center justify-center gap-1 rounded-lg border border-white/25 bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-white transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Volver
                    </button>
                </div>
            </div>
        </section>

        <div class="se-matriz-edit-body">
            @if (session('success'))
                <div class="shrink-0 rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1 text-xs text-emerald-900" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @error('guardar')
                <div class="shrink-0 rounded-lg border border-red-200 bg-red-50 px-2 py-1 text-xs text-red-900" role="alert">
                    {{ $message }}
                </div>
            @enderror

            <form wire:submit="guardar"
                  @class([
                      'se-matriz-edit-panel min-h-0 flex-1',
                      'se-matriz-edit-panel--solo-grilla' => count($lineas) === 0,
                  ])>
                @if (count($lineas) > 0)
                    <div class="se-matriz-edit-bar se-matriz-edit-bar--top">
                        <p class="se-matriz-edit-hint min-w-0 flex-1">
                            Calif · Mes · Año · Cond. · Escuapro editables.
                            <span class="text-neutral-500">Apro: 0=Cursando, 1=Adeudada, 2=Aprobada.</span>
                        </p>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="guardar"
                                class="btn-matriz-save shrink-0">
                            <span wire:loading.remove wire:target="guardar">Guardar todo</span>
                            <span wire:loading wire:target="guardar">Guardando…</span>
                        </button>
                    </div>
                @endif

                {{-- Cabecera fuera del scroll vertical (mismo patrón que cierre anual; evita bleed con sticky). --}}
                <div class="se-cierre-anual-grilla se-matriz-excel-grilla se-matriz-excel-grilla--unified">
                    @if (count($lineas) > 0)
                        <div class="se-cierre-anual-head-wrap se-matriz-excel-head-wrap"
                             data-se-cierre-head>
                            <table class="se-matriz-excel-tabla min-w-[52rem] w-full">
                                <colgroup>
                                    <col style="width:7%">
                                    <col style="width:14%">
                                    <col style="width:21.5%">
                                    <col style="width:7%">
                                    <col style="width:5.5%">
                                    <col style="width:6.5%">
                                    <col style="width:8.5%">
                                    <col style="width:17.5%">
                                    <col style="width:12%">
                                </colgroup>
                                <thead class="se-matriz-excel-thead">
                                    <tr>
                                        <th scope="col" class="text-left">Año</th>
                                        <th scope="col" class="text-left">Curso</th>
                                        <th scope="col" class="text-left">Asignatura</th>
                                        <th scope="col" class="text-center">Calif</th>
                                        <th scope="col" class="text-center">Mes</th>
                                        <th scope="col" class="text-center">Año</th>
                                        <th scope="col" class="text-left">Cond</th>
                                        <th scope="col" class="text-left">Escuapro</th>
                                        <th scope="col" class="text-center">Apro</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    @endif
                    <div class="se-cierre-anual-body-wrap se-matriz-excel-scroll" tabindex="0" data-se-cierre-body>
                        <table class="se-matriz-excel-tabla min-w-[52rem] w-full">
                            <colgroup>
                                <col style="width:7%">
                                <col style="width:14%">
                                <col style="width:21.5%">
                                <col style="width:7%">
                                <col style="width:5.5%">
                                <col style="width:6.5%">
                                <col style="width:8.5%">
                                <col style="width:17.5%">
                                <col style="width:12%">
                            </colgroup>
                            <tbody class="bg-white">
                                    @forelse ($lineas as $i => $lin)
                                        <tr wire:key="matriz-lin-{{ $lin['id'] }}">
                                            <td><span class="se-matriz-excel-read tabular-nums">{{ $lin['ano_lectivo'] }}</span></td>
                                            <td><span class="se-matriz-excel-read" title="{{ $lin['curso'] }}">{{ $lin['curso'] !== '' ? $lin['curso'] : '—' }}</span></td>
                                            <td><span class="se-matriz-excel-read font-medium" title="{{ $lin['materia'] }}">{{ $lin['materia'] }}</span></td>
                                            <td>
                                                <input type="text"
                                                       wire:model="lineas.{{ $i }}.calif"
                                                       maxlength="10"
                                                       @class([
                                                           'se-matriz-excel-input text-center',
                                                           'se-matriz-excel-input--err' => $errors->has('lineas.'.$i.'.calif'),
                                                       ])
                                                       aria-label="Calificación {{ $lin['materia'] }}">
                                            </td>
                                            <td>
                                                <input type="text"
                                                       wire:model="lineas.{{ $i }}.mes"
                                                       maxlength="2"
                                                       inputmode="numeric"
                                                       @class([
                                                           'se-matriz-excel-input text-center',
                                                           'se-matriz-excel-input--err' => $errors->has('lineas.'.$i.'.mes'),
                                                       ])
                                                       aria-label="Mes {{ $lin['materia'] }}">
                                            </td>
                                            <td>
                                                <input type="text"
                                                       wire:model="lineas.{{ $i }}.ano"
                                                       maxlength="4"
                                                       inputmode="numeric"
                                                       @class([
                                                           'se-matriz-excel-input text-center',
                                                           'se-matriz-excel-input--err' => $errors->has('lineas.'.$i.'.ano'),
                                                       ])
                                                       aria-label="Año matrícula {{ $lin['materia'] }}">
                                            </td>
                                            <td>
                                                <input type="text"
                                                       wire:model="lineas.{{ $i }}.cond"
                                                       maxlength="20"
                                                       @class([
                                                           'se-matriz-excel-input',
                                                           'se-matriz-excel-input--err' => $errors->has('lineas.'.$i.'.cond'),
                                                       ])
                                                       aria-label="Condición {{ $lin['materia'] }}">
                                            </td>
                                            <td>
                                                <input type="text"
                                                       wire:model="lineas.{{ $i }}.escuapro"
                                                       maxlength="100"
                                                       @class([
                                                           'se-matriz-excel-input',
                                                           'se-matriz-excel-input--err' => $errors->has('lineas.'.$i.'.escuapro'),
                                                       ])
                                                       aria-label="Escuela de aprobación {{ $lin['materia'] }}">
                                            </td>
                                            <td class="text-center">
                                                <span class="se-matriz-excel-apro" title="apro = {{ $lin['apro'] }}">{{ $lin['apro_etiqueta'] }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="!h-auto !max-h-none px-3 py-6 text-center text-xs text-neutral-500">
                                                No hay calificaciones de secundario registradas para este alumno.
                                            </td>
                                        </tr>
                                    @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($modalSalirAbierto)
        @teleport('body')
        <div class="fixed inset-0 z-[90] flex items-center justify-center overflow-y-auto px-4 py-3 sm:px-6 sm:py-4"
             role="dialog"
             aria-modal="true"
             aria-labelledby="matriz-salir-titulo"
             wire:key="matriz-modal-salir">
            <div class="absolute inset-0 bg-neutral-900/55 backdrop-blur-sm"
                 wire:click="cerrarModalSalir"
                 aria-hidden="true"></div>

            <div class="relative z-10 my-auto w-full max-w-md rounded-2xl border border-accent-200 bg-white p-6 shadow-xl ring-1 ring-black/5"
                 @click.stop>
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-50 text-amber-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 id="matriz-salir-titulo" class="mt-4 text-center text-base font-bold text-neutral-900">
                    Cambios sin guardar
                </h3>
                <p class="mt-2 text-center text-sm text-neutral-600">
                    Hay modificaciones en la grilla que aún no se guardaron.
                    ¿Desea guardar antes de volver al listado?
                </p>
                <div class="mt-6 flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:justify-center">
                    <button type="button"
                            wire:click="guardarYSalir"
                            wire:loading.attr="disabled"
                            wire:target="guardarYSalir"
                            class="btn-primary w-full sm:w-auto">
                        <span wire:loading.remove wire:target="guardarYSalir">Guardar y salir</span>
                        <span wire:loading wire:target="guardarYSalir">Guardando…</span>
                    </button>
                    <button type="button"
                            wire:click="salirSinGuardar"
                            class="btn-secondary w-full sm:w-auto">
                        Salir sin guardar
                    </button>
                    <button type="button"
                            wire:click="cerrarModalSalir"
                            class="btn-secondary w-full sm:w-auto">
                        Seguir editando
                    </button>
                </div>
            </div>
        </div>
        @endteleport
    @endif
</div>
