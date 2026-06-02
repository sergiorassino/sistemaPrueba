<div class="se-page max-w-[96rem] mx-auto">
    <section class="se-hero mb-6">
        <div class="se-hero-inner flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0 space-y-1">
                <p class="se-eyebrow">Gestión masiva</p>
                <h1 class="text-xl font-bold tracking-tight text-white sm:text-2xl uppercase">
                    Crear / Editar Cuotas — Año {{ $ano }}
                </h1>
            </div>
            <button type="button"
                    wire:click="addNew"
                    class="inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-primary-700 shadow-sm transition hover:bg-accent-100">
                + Nuevo
            </button>
        </div>
    </section>

    <div class="se-toolbar mb-4" x-data x-init="$nextTick(() => $refs.cuotasPlantillaBuscar?.focus())">
        <div class="relative flex-1 max-w-md">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input wire:model.live.debounce.300ms="search"
                   type="search"
                   x-ref="cuotasPlantillaBuscar"
                   placeholder="Búsqueda Rápida"
                   class="form-input pl-9"
                   autocomplete="off">
        </div>
    </div>

    <div class="se-card overflow-hidden p-2 sm:p-3">
        <div class="w-full overflow-x-auto">
            <div class="flex justify-start">
                <div class="gf min-w-[1140px] gf-cuotas-plantilla">
                    <div class="gf-head">
                        <div class="gf-th gf-th-accion w-12" title="Eliminar"></div>
                        <div class="gf-th w-24">Año</div>
                        <div class="gf-th flex-1 min-w-[12rem]">Nombre</div>
                        <div class="gf-th w-36">Id Cuotasmeses</div>
                        <div class="gf-th w-32">Id Cuotastipo</div>
                        <div class="gf-th w-32">Venc 1<br><span class="text-[9px] font-normal normal-case">dd/mm/aaaa</span></div>
                        <div class="gf-th w-32">Venc 2<br><span class="text-[9px] font-normal normal-case">dd/mm/aaaa</span></div>
                        <div class="gf-th w-32">Venc 3<br><span class="text-[9px] font-normal normal-case">dd/mm/aaaa</span></div>
                        <div class="gf-th w-40">Sin Con Beca</div>
                        <div class="gf-th w-16">Orden</div>
                    </div>

                    @forelse ($filas as $key => $row)
                        @php
                            $esNueva = is_string($key) && str_starts_with($key, 'new_');
                        @endphp
                        <div class="gf-row gf-row-hover {{ $esNueva ? 'bg-amber-50/50' : '' }}" wire:key="cuota-row-{{ $key }}">
                            <div class="gf-td gf-td-accion !py-1 w-12">
                                <button type="button"
                                        @if ($esNueva)
                                            wire:click="deleteRow('{{ $key }}')"
                                        @else
                                            x-on:click="window.seSwalConfirmar('¿Eliminar esta plantilla de cuota?', 'Confirmar eliminación', { confirmButtonText: 'Sí, eliminar' }).then((ok) => { if (ok) $wire.deleteRow('{{ $key }}'); })"
                                        @endif
                                        class="inline-flex h-7 w-7 items-center justify-center rounded border border-gray-300 bg-white text-red-600 hover:bg-red-50"
                                        title="Eliminar">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="gf-td w-24">
                                <select wire:model.defer="draft.{{ $key }}.idTerlec"
                                        disabled
                                        class="gf-inline-select font-mono text-neutral-700 opacity-80 cursor-not-allowed"
                                        title="Solo cuotas del ciclo lectivo activo">
                                    @foreach ($terlecs as $t)
                                        <option value="{{ $t->id }}">{{ $t->ano }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="gf-td flex-1 min-w-[12rem]">
                                <input type="text"
                                       wire:model.live.debounce.500ms="draft.{{ $key }}.nombre"
                                       maxlength="120"
                                       class="gf-inline w-full @error('draft.'.$key.'.nombre') ring-2 ring-red-400 @enderror">
                                @error('draft.'.$key.'.nombre')
                                    <div class="text-[10px] text-red-700 mt-0.5">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="gf-td w-36">
                                <select wire:model.live="draft.{{ $key }}.idCuotasmeses"
                                        class="gf-inline-select @error('draft.'.$key.'.idCuotasmeses') ring-2 ring-red-400 @enderror">
                                    @foreach ($meses as $m)
                                        <option value="{{ $m->id }}">{{ $m->mes }}</option>
                                    @endforeach
                                </select>
                                @error('draft.'.$key.'.idCuotasmeses')
                                    <div class="text-[10px] text-red-700 mt-0.5">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="gf-td w-32">
                                <select wire:model.live="draft.{{ $key }}.idCuotastipo"
                                        class="gf-inline-select @error('draft.'.$key.'.idCuotastipo') ring-2 ring-red-400 @enderror">
                                    @foreach ($tipos as $t)
                                        <option value="{{ $t->id }}">{{ $t->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('draft.'.$key.'.idCuotastipo')
                                    <div class="text-[10px] text-red-700 mt-0.5">{{ $message }}</div>
                                @enderror
                            </div>

                            @foreach (['venc1', 'venc2', 'venc3'] as $campoVenc)
                                <div class="gf-td w-32">
                                    <input type="date"
                                           wire:model.live="draft.{{ $key }}.{{ $campoVenc }}"
                                           class="gf-inline font-mono text-xs @error('draft.'.$key.'.'.$campoVenc) ring-2 ring-red-400 @enderror">
                                    @error('draft.'.$key.'.'.$campoVenc)
                                        <div class="text-[10px] text-red-700 mt-0.5">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach

                            <div class="gf-td w-40">
                                <select wire:model.live="draft.{{ $key }}.sinConBeca"
                                        class="gf-inline-select @error('draft.'.$key.'.sinConBeca') ring-2 ring-red-400 @enderror">
                                    @foreach ($opcionesBeca as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                @error('draft.'.$key.'.sinConBeca')
                                    <div class="text-[10px] text-red-700 mt-0.5">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="gf-td w-16">
                                <input type="text"
                                       inputmode="numeric"
                                       maxlength="4"
                                       wire:model.live.debounce.400ms="draft.{{ $key }}.orden"
                                       class="gf-inline font-mono w-full @error('draft.'.$key.'.orden') ring-2 ring-red-400 @enderror">
                                @error('draft.'.$key.'.orden')
                                    <div class="text-[10px] text-red-700 mt-0.5">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    @empty
                        <div class="gf-row">
                            <div class="gf-td col-span-full py-10 text-center text-sm text-neutral-500 w-full min-w-[1140px]">
                                @if (trim($search) !== '')
                                    No hay cuotas que coincidan con la búsqueda.
                                @else
                                    No hay plantillas de cuota para el año {{ $ano }}. Use «+ Nuevo» para agregar la primera.
                                @endif
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        (function () {
            function mensajeDeEvento(event, fallback) {
                return event?.mensaje ?? event?.detail?.mensaje ?? fallback;
            }

            $wire.on('se-swal-exito', (event) => {
                const mensaje = mensajeDeEvento(event, 'Operación realizada correctamente.');
                if (typeof window.seSwalExito === 'function') {
                    window.seSwalExito(mensaje);
                }
            });

            $wire.on('se-swal-error', (event) => {
                const mensaje = mensajeDeEvento(event, 'No se pudo completar la operación.');
                if (typeof window.seSwalError === 'function') {
                    window.seSwalError(mensaje);
                }
            });
        })();
    </script>
    @endscript
</div>
