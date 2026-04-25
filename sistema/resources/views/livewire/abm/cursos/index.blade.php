<div>
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
             class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md text-sm text-green-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
             class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md text-sm text-red-700 flex items-start gap-2">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="w-full min-w-0 text-center sm:flex-1">
            <h2 class="text-xl font-semibold text-gray-800">Gestión de Cursos / Grados / Salas</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Cursos del año lectivo actual ({{ schoolCtx()->terlecAno() }}) para el nivel actual
            </p>
        </div>
        <div class="flex justify-center sm:shrink-0 sm:justify-end">
            <button type="button" wire:click="createQuick" class="btn-primary btn-sm">
                + Nuevo curso
            </button>
        </div>
    </div>

    <div class="w-full overflow-x-auto">
        <div class="flex justify-start">
            <div class="gf min-w-[1180px]">
            <div class="gf-head">
                <div class="gf-th w-24">ID</div>
                <div class="gf-th w-20">Ord</div>
                <div class="gf-th w-44">Curso modelo (CurPlan)</div>
                <div class="gf-th w-28">Año (Terlec)</div>
                <div class="gf-th w-36">Nivel</div>
                <div class="gf-th w-48">Sección (cursec)</div>
                <div class="gf-th w-16">C</div>
                <div class="gf-th w-16">S</div>
                <div class="gf-th w-36">Turno</div>
                <div class="gf-th-right w-52">Acciones</div>
            </div>

            @forelse ($cursos as $c)
                <div class="gf-row gf-row-hover">
                    <div class="gf-td w-24 font-mono text-gray-600">{{ $c->Id }}</div>
                    {{-- orden --}}
                    <div class="gf-td w-20">
                        @if ($editingId === $c->Id)
                            <input type="text" inputmode="numeric" maxlength="3"
                                   wire:model.defer="draft.{{ $c->Id }}.orden"
                                   class="gf-inline font-mono text-gray-700 @error('draft.'.$c->Id.'.orden') ring-2 ring-red-400 @enderror">
                            @error('draft.'.$c->Id.'.orden') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        @else
                            <div class="font-mono text-gray-600">{{ $c->orden ?? '—' }}</div>
                        @endif
                    </div>

                    {{-- idCurPlan (mostrar valor relacionado) --}}
                    <div class="gf-td w-44">
                        @if ($editingId === $c->Id)
                            <select wire:model.defer="draft.{{ $c->Id }}.idCurPlan"
                                    class="gf-inline-select text-gray-800 @error('draft.'.$c->Id.'.idCurPlan') ring-2 ring-red-400 @enderror">
                                @foreach ($curplanes as $cp)
                                    <option value="{{ $cp->id }}">
                                        {{ $cp->plan?->abrev ? ($cp->plan->abrev . ' · ') : '' }}{{ $cp->curPlanCurso }}
                                    </option>
                                @endforeach
                            </select>
                            @error('draft.'.$c->Id.'.idCurPlan') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                            <div class="text-[10px] text-gray-400 mt-1">Al cambiarlo se re-crean las materias del año.</div>
                        @else
                            <div class="font-medium text-gray-800 truncate">{{ $c->curplan?->curPlanCurso ?? ('#'.$c->idCurPlan) }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ $c->curplan?->plan?->abrev ?: $c->curplan?->plan?->plan }}</div>
                        @endif
                    </div>

                    {{-- idTerlec (mostrar año) --}}
                    <div class="gf-td w-28">
                        @if ($editingId === $c->Id)
                            <select wire:model.defer="draft.{{ $c->Id }}.idTerlec"
                                    class="gf-inline-select font-mono text-gray-700 @error('draft.'.$c->Id.'.idTerlec') ring-2 ring-red-400 @enderror">
                                @foreach ($terlecs as $t)
                                    <option value="{{ $t->id }}">{{ $t->ano }}</option>
                                @endforeach
                            </select>
                            @error('draft.'.$c->Id.'.idTerlec') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        @else
                            <div class="font-mono text-gray-700">{{ $c->terlec?->ano ?? $c->idTerlec }}</div>
                        @endif
                    </div>

                    {{-- idNivel (mostrar nombre) --}}
                    <div class="gf-td w-36">
                        @if ($editingId === $c->Id)
                            <select wire:model.defer="draft.{{ $c->Id }}.idNivel"
                                    class="gf-inline-select text-gray-800 @error('draft.'.$c->Id.'.idNivel') ring-2 ring-red-400 @enderror">
                                @foreach ($niveles as $n)
                                    <option value="{{ $n->id }}">
                                        {{ $n->abrev ? ($n->abrev . ' · ') : '' }}{{ $n->nivel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('draft.'.$c->Id.'.idNivel') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        @else
                            <div class="text-gray-800 truncate">{{ $c->nivel?->nivel ?? ('#'.$c->idNivel) }}</div>
                        @endif
                    </div>

                    {{-- cursec --}}
                    <div class="gf-td w-48">
                        @if ($editingId === $c->Id)
                            <input type="text" maxlength="30"
                                   wire:model.defer="draft.{{ $c->Id }}.cursec"
                                   class="gf-inline font-mono text-gray-700 @error('draft.'.$c->Id.'.cursec') ring-2 ring-red-400 @enderror">
                            @error('draft.'.$c->Id.'.cursec') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        @else
                            <div class="font-mono text-gray-700">{{ $c->cursec ?? '—' }}</div>
                        @endif
                    </div>

                    {{-- c --}}
                    <div class="gf-td w-16">
                        @if ($editingId === $c->Id)
                            <input type="text" maxlength="1"
                                   wire:model.defer="draft.{{ $c->Id }}.c"
                                   class="gf-inline font-mono text-gray-700 @error('draft.'.$c->Id.'.c') ring-2 ring-red-400 @enderror">
                            @error('draft.'.$c->Id.'.c') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        @else
                            <div class="font-mono text-gray-700">{{ $c->c ?? '—' }}</div>
                        @endif
                    </div>

                    {{-- s --}}
                    <div class="gf-td w-16">
                        @if ($editingId === $c->Id)
                            <input type="text" maxlength="1"
                                   wire:model.defer="draft.{{ $c->Id }}.s"
                                   class="gf-inline font-mono text-gray-700 @error('draft.'.$c->Id.'.s') ring-2 ring-red-400 @enderror">
                            @error('draft.'.$c->Id.'.s') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        @else
                            <div class="font-mono text-gray-700">{{ $c->s ?? '—' }}</div>
                        @endif
                    </div>

                    {{-- turno --}}
                    <div class="gf-td w-36">
                        @if ($editingId === $c->Id)
                            <input type="text" maxlength="20"
                                   wire:model.defer="draft.{{ $c->Id }}.turno"
                                   class="gf-inline text-gray-700 @error('draft.'.$c->Id.'.turno') ring-2 ring-red-400 @enderror">
                            @error('draft.'.$c->Id.'.turno') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        @else
                            <div class="text-gray-700">{{ $c->turno ?? '—' }}</div>
                        @endif
                    </div>

                    <div class="gf-td-actions w-52 whitespace-nowrap">
                        @if ($editingId === $c->Id)
                            <button type="button" wire:click="saveRow({{ $c->Id }})" wire:loading.attr="disabled" class="btn-primary btn-sm">
                                <span wire:loading.remove wire:target="saveRow({{ $c->Id }})">Guardar</span>
                                <span wire:loading wire:target="saveRow({{ $c->Id }})">…</span>
                            </button>
                            <button type="button" wire:click="cancelEdit" class="btn-secondary btn-sm">Cancelar</button>
                        @else
                            <button type="button" wire:click="startEdit({{ $c->Id }})" class="btn-secondary btn-sm">Editar</button>
                            <button type="button" wire:click="confirmDelete({{ $c->Id }})" class="btn-danger btn-sm">Eliminar</button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="gf-empty">No hay cursos registrados para el año lectivo actual.</div>
            @endforelse
            </div>
        </div>
    </div>

    @if ($showConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-sm" @click.stop>
                <div class="px-6 py-5">
                    <div class="flex items-start gap-3">
                        @if ($deleteId)
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                            </div>
                        @else
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 mb-1">
                                {{ $deleteId ? 'Confirmar eliminación' : 'No se puede eliminar' }}
                            </h3>
                            <p class="text-sm text-gray-600">{{ $deleteInfo }}</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 pb-5 flex justify-end gap-3">
                    <button wire:click="$set('showConfirm', false)" class="btn-secondary">
                        {{ $deleteId ? 'Cancelar' : 'Cerrar' }}
                    </button>
                    @if ($deleteId)
                        <button wire:click="delete" wire:loading.attr="disabled" class="btn-danger">
                            <span wire:loading.remove wire:target="delete">Eliminar</span>
                            <span wire:loading wire:target="delete">Eliminando…</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

