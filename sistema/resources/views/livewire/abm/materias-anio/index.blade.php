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

    <div class="flex flex-col gap-3 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Gestión de Asignaturas del Año</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Materias del año lectivo actual ({{ schoolCtx()->terlecAno() }}) para el nivel actual
                </p>
            </div>
            @if (! $creating)
                <button type="button" wire:click="startCreate" class="btn-primary btn-sm sm:self-start">
                    + Nueva materia
                </button>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            {{-- Ancho explícito: mitad del viewport en PC (con tope), + self-start para que flex no lo estire --}}
            <div class="w-full self-start shrink-0 sm:w-[min(50vw,36rem)]">
                <div class="text-xs font-semibold text-gray-600 mb-1">Curso</div>
                {{-- No usar .gf-inline-select acá: en app.css fuerza w-full y anula el ancho del contenedor --}}
                <select wire:model.live="cursoId"
                        class="block w-full rounded-md py-2 pl-3 pr-8 text-sm text-gray-800 bg-white border-2 border-[var(--se-primary)]/60 shadow-sm
                               focus:border-[var(--se-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--se-primary)]/20">
                    <option value="">— Seleccione curso —</option>
                    @foreach ($cursos as $c)
                        @php
                            $label = trim((string) ($c->cursec ?? ''));
                            $extra = collect([$c->c ?? null, $c->s ?? null, $c->turno ?? null])->filter(fn($v) => $v !== null && trim((string) $v) !== '')->implode(' ');
                            $display = $label !== '' ? $label : ('Curso ' . $c->Id);
                        @endphp
                        <option value="{{ $c->Id }}">
                            {{ $c->Id }} — {{ $display }}{{ $extra !== '' ? ' · ' . $extra : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="w-full overflow-x-auto">
        <div class="flex justify-start">
            <div class="gf min-w-[1320px]">
                <div class="gf-head">
                    <div class="gf-th w-20">ID</div>
                    <div class="gf-th w-20">Ord</div>
                    <div class="gf-th w-24">idNivel</div>
                    <div class="gf-th w-28">idCursos</div>
                    <div class="gf-th w-24">idTerlec</div>
                    <div class="gf-th w-28">idCurPlan</div>
                    <div class="gf-th w-28">idMatPlan</div>
                    <div class="gf-th flex-1 min-w-[20rem]">Materia</div>
                    <div class="gf-th w-28">Abrev</div>
                    <div class="gf-th-right w-52">Acciones</div>
                </div>

                @if ($creating)
                    <div class="gf-row gf-row-hover bg-amber-50/40">
                        <div class="gf-td w-20 font-mono text-gray-500">—</div>

                        <div class="gf-td w-20">
                            <input type="text" inputmode="numeric" maxlength="3" wire:model.defer="create.ord"
                                   class="gf-inline font-mono text-gray-700 @error('create.ord') ring-2 ring-red-400 @enderror">
                            @error('create.ord') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="gf-td w-24">
                            <div class="font-mono text-gray-700">{{ $create['idNivel'] ?? '—' }}</div>
                            @error('create.idNivel') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="gf-td w-28">
                            <select wire:model.defer="create.idCursos"
                                    class="gf-inline-select font-mono text-gray-700 @error('create.idCursos') ring-2 ring-red-400 @enderror">
                                <option value="">—</option>
                                @foreach ($cursos as $c)
                                    <option value="{{ $c->Id }}">
                                        {{ $c->Id }}
                                    </option>
                                @endforeach
                            </select>
                            @error('create.idCursos') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="gf-td w-24">
                            <div class="font-mono text-gray-700">{{ $create['idTerlec'] ?? '—' }}</div>
                            @error('create.idTerlec') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="gf-td w-28">
                            <select wire:model.live="create.idCurPlan"
                                    class="gf-inline-select font-mono text-gray-700 @error('create.idCurPlan') ring-2 ring-red-400 @enderror">
                                <option value="">—</option>
                                @foreach ($curplanes as $cp)
                                    <option value="{{ $cp->id }}">{{ $cp->id }}</option>
                                @endforeach
                            </select>
                            @error('create.idCurPlan') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="gf-td w-28">
                            @php $cpId = (int) ($create['idCurPlan'] ?? 0); @endphp
                            <select wire:model.defer="create.idMatPlan"
                                    class="gf-inline-select font-mono text-gray-700 @error('create.idMatPlan') ring-2 ring-red-400 @enderror">
                                <option value="">—</option>
                                @foreach (($matplanesByCurplan[$cpId] ?? collect()) as $mp)
                                    <option value="{{ $mp->id }}">{{ $mp->id }}</option>
                                @endforeach
                            </select>
                            @error('create.idMatPlan') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="gf-td flex-1 min-w-[20rem]">
                            <input type="text" maxlength="70" wire:model.defer="create.materia"
                                   class="gf-inline text-gray-700 @error('create.materia') ring-2 ring-red-400 @enderror">
                            @error('create.materia') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="gf-td w-28">
                            <input type="text" maxlength="5" wire:model.defer="create.abrev"
                                   class="gf-inline font-mono text-gray-700 @error('create.abrev') ring-2 ring-red-400 @enderror">
                            @error('create.abrev') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="gf-td-actions w-52 whitespace-nowrap">
                            <button type="button" wire:click="saveCreate" wire:loading.attr="disabled" class="btn-primary btn-sm">
                                <span wire:loading.remove wire:target="saveCreate">Guardar</span>
                                <span wire:loading wire:target="saveCreate">…</span>
                            </button>
                            <button type="button" wire:click="cancelCreate" class="btn-secondary btn-sm">Cancelar</button>
                        </div>
                    </div>
                @endif

                @forelse ($materias as $m)
                    <div class="gf-row gf-row-hover">
                        <div class="gf-td w-20 font-mono text-gray-600">{{ $m->id }}</div>

                        <div class="gf-td w-20">
                            @if ($editingId === $m->id)
                                <input type="text" inputmode="numeric" maxlength="3"
                                       wire:model.defer="draft.{{ $m->id }}.ord"
                                       class="gf-inline font-mono text-gray-700 @error('draft.'.$m->id.'.ord') ring-2 ring-red-400 @enderror">
                                @error('draft.'.$m->id.'.ord') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                            @else
                                <div class="font-mono text-gray-700">{{ $m->ord }}</div>
                            @endif
                        </div>

                        <div class="gf-td w-24">
                            <div class="font-mono text-gray-700">{{ $m->idNivel }}</div>
                        </div>

                        <div class="gf-td w-28">
                            @if ($editingId === $m->id)
                                <select wire:model.defer="draft.{{ $m->id }}.idCursos"
                                        class="gf-inline-select font-mono text-gray-700 @error('draft.'.$m->id.'.idCursos') ring-2 ring-red-400 @enderror">
                                    @foreach ($cursos as $c)
                                        <option value="{{ $c->Id }}">{{ $c->Id }}</option>
                                    @endforeach
                                </select>
                                @error('draft.'.$m->id.'.idCursos') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                            @else
                                <div class="font-mono text-gray-700">{{ $m->idCursos }}</div>
                            @endif
                        </div>

                        <div class="gf-td w-24">
                            <div class="font-mono text-gray-700">{{ $m->idTerlec }}</div>
                        </div>

                        <div class="gf-td w-28">
                            @if ($editingId === $m->id)
                                <select wire:model.live="draft.{{ $m->id }}.idCurPlan"
                                        class="gf-inline-select font-mono text-gray-700 @error('draft.'.$m->id.'.idCurPlan') ring-2 ring-red-400 @enderror">
                                    @foreach ($curplanes as $cp)
                                        <option value="{{ $cp->id }}">{{ $cp->id }}</option>
                                    @endforeach
                                </select>
                                @error('draft.'.$m->id.'.idCurPlan') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                            @else
                                <div class="font-mono text-gray-700">{{ $m->idCurPlan }}</div>
                            @endif
                        </div>

                        <div class="gf-td w-28">
                            @if ($editingId === $m->id)
                                @php $cpId = (int) ($draft[$m->id]['idCurPlan'] ?? 0); @endphp
                                <select wire:model.defer="draft.{{ $m->id }}.idMatPlan"
                                        class="gf-inline-select font-mono text-gray-700 @error('draft.'.$m->id.'.idMatPlan') ring-2 ring-red-400 @enderror">
                                    @foreach (($matplanesByCurplan[$cpId] ?? collect()) as $mp)
                                        <option value="{{ $mp->id }}">{{ $mp->id }}</option>
                                    @endforeach
                                </select>
                                @error('draft.'.$m->id.'.idMatPlan') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                            @else
                                <div class="font-mono text-gray-700">{{ $m->idMatPlan }}</div>
                            @endif
                        </div>

                        <div class="gf-td flex-1 min-w-[20rem]">
                            @if ($editingId === $m->id)
                                <input type="text" maxlength="70"
                                       wire:model.defer="draft.{{ $m->id }}.materia"
                                       class="gf-inline text-gray-700 @error('draft.'.$m->id.'.materia') ring-2 ring-red-400 @enderror">
                                @error('draft.'.$m->id.'.materia') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                            @else
                                <div class="text-gray-800">{{ $m->materia }}</div>
                            @endif
                        </div>

                        <div class="gf-td w-28">
                            @if ($editingId === $m->id)
                                <input type="text" maxlength="5"
                                       wire:model.defer="draft.{{ $m->id }}.abrev"
                                       class="gf-inline font-mono text-gray-700 @error('draft.'.$m->id.'.abrev') ring-2 ring-red-400 @enderror">
                                @error('draft.'.$m->id.'.abrev') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                            @else
                                <div class="font-mono text-gray-700">{{ $m->abrev ?: '—' }}</div>
                            @endif
                        </div>

                        <div class="gf-td-actions w-52 whitespace-nowrap">
                            @if ($editingId === $m->id)
                                <button type="button" wire:click="saveRow({{ $m->id }})" wire:loading.attr="disabled" class="btn-primary btn-sm">
                                    <span wire:loading.remove wire:target="saveRow({{ $m->id }})">Guardar</span>
                                    <span wire:loading wire:target="saveRow({{ $m->id }})">…</span>
                                </button>
                                <button type="button" wire:click="cancelEdit" class="btn-secondary btn-sm">Cancelar</button>
                            @else
                                <button type="button" wire:click="startEdit({{ $m->id }})" class="btn-secondary btn-sm">Editar</button>
                                <button type="button" wire:click="confirmDelete({{ $m->id }})" class="btn-danger btn-sm">Eliminar</button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="gf-empty">No hay materias registradas para el año lectivo actual.</div>
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

