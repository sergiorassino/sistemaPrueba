<div>
    {{-- Flash --}}
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
            <h2 class="text-xl font-semibold text-gray-800">{{ $id ? 'Editar curso modelo' : 'Nuevo curso modelo' }}</h2>
            <p class="text-xs text-gray-400 mt-0.5">Los campos marcados con * son obligatorios</p>
        </div>

        <div class="flex flex-wrap justify-center gap-2 sm:justify-end">
            <a href="{{ route('abm.curplan') }}" class="btn-secondary btn-sm">Volver</a>
            <button wire:click="save" wire:loading.attr="disabled" class="btn-primary btn-sm">
                <span wire:loading.remove wire:target="save">Guardar</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>
    </div>

    <div class="card p-5">
        <div class="mb-4 flex flex-col items-center gap-2 text-center sm:flex-row sm:items-center sm:justify-between">
            <div class="w-full min-w-0 text-center sm:flex-1">
                <div class="text-sm font-semibold text-gray-800">Gestión de Cursos y Materias del Plan</div>
                <div class="text-xs text-gray-500">CurPlan + MatPlan (materias del curso modelo)</div>
            </div>
            @if ($id)
                <span class="shrink-0 text-xs text-gray-500">ID #{{ $id }}</span>
            @endif
        </div>

        {{-- Datos del curso modelo --}}
        <div class="gf w-full">
            <div class="gf-row @error('idPlan') gf-cell-err @enderror">
                <div class="gf-label gf-label-req w-40">Plan</div>
                <div class="gf-cell @error('idPlan') gf-cell-err @enderror">
                    <select wire:model="idPlan" class="gf-select @error('idPlan') gf-select-err @enderror">
                        <option value="">— Seleccione —</option>
                        @foreach ($planes as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->abrev ? ($p->abrev . ' · ') : '' }}{{ $p->plan }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @error('idPlan')
                <div class="gf-error-row">
                    <div class="gf-error-spacer w-40"></div>
                    <div class="gf-error-msg">{{ $message }}</div>
                </div>
            @enderror

            <div class="gf-row @error('curPlanCurso') gf-cell-err @enderror">
                <div class="gf-label gf-label-req w-40">Curso modelo</div>
                <div class="gf-cell @error('curPlanCurso') gf-cell-err @enderror">
                    <input wire:model="curPlanCurso" type="text" maxlength="30"
                           placeholder="Ej: PRIMERO (PRIM)"
                           class="gf-input @error('curPlanCurso') gf-input-err @enderror">
                </div>
            </div>
            @error('curPlanCurso')
                <div class="gf-error-row">
                    <div class="gf-error-spacer w-40"></div>
                    <div class="gf-error-msg">{{ $message }}</div>
                </div>
            @enderror
        </div>

        {{-- Materias --}}
        <div class="mt-6">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <div class="text-sm font-semibold text-gray-800">Materias asociadas</div>
                    <div class="text-xs text-gray-500">Tabla <span class="font-mono">matplan</span></div>
                </div>
                <button type="button" wire:click="openMateriaCreate" class="btn-secondary btn-sm" @disabled(! $id)>
                    + Agregar
                </button>
            </div>

            @if (! $id)
                <div class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    Primero guardá el curso modelo para poder cargar materias.
                </div>
            @else
                <div class="border border-gray-200 rounded-md overflow-hidden">
                    <div class="max-h-[340px] overflow-auto">
                        <div class="gf min-w-[900px] w-full">
                            <div class="gf-head">
                                <div class="gf-th w-20">Ord</div>
                                <div class="gf-th flex-1 min-w-[18rem]">Materia</div>
                                <div class="gf-th w-24">Abrev</div>
                                <div class="gf-th w-28">CodGE</div>
                                <div class="gf-th w-28">CodGE2</div>
                                <div class="gf-th w-28">CodGE3</div>
                                <div class="gf-th-right w-52">Acciones</div>
                            </div>

                            @forelse ($materias as $m)
                                <div class="gf-row gf-row-hover">
                                    <div class="gf-td w-20 font-mono text-gray-700">
                                        @if ($matEditingId === $m->id)
                                            <input wire:model.defer="matDraft.ord" type="text" inputmode="numeric" maxlength="3"
                                                   class="gf-inline font-mono text-gray-700 @error('matDraft.ord') ring-2 ring-red-400 @enderror">
                                            @error('matDraft.ord') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                        @else
                                            {{ $m->ord }}
                                        @endif
                                    </div>
                                    <div class="gf-td flex-1 min-w-[18rem] text-gray-800">
                                        @if ($matEditingId === $m->id)
                                            <input wire:model.defer="matDraft.matPlanMateria" type="text" maxlength="70"
                                                   class="gf-inline text-gray-800 @error('matDraft.matPlanMateria') ring-2 ring-red-400 @enderror">
                                            @error('matDraft.matPlanMateria') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                        @else
                                            <div class="font-medium">{{ $m->matPlanMateria }}</div>
                                        @endif
                                    </div>
                                    <div class="gf-td w-24 font-mono text-xs text-gray-700">
                                        @if ($matEditingId === $m->id)
                                            <input wire:model.defer="matDraft.abrev" type="text" maxlength="5"
                                                   class="gf-inline font-mono text-gray-700 @error('matDraft.abrev') ring-2 ring-red-400 @enderror">
                                            @error('matDraft.abrev') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                        @else
                                            {{ $m->abrev }}
                                        @endif
                                    </div>
                                    <div class="gf-td w-28 font-mono text-xs text-gray-700">
                                        @if ($matEditingId === $m->id)
                                            <input wire:model.defer="matDraft.codGE" type="text" maxlength="15"
                                                   class="gf-inline font-mono text-gray-700 @error('matDraft.codGE') ring-2 ring-red-400 @enderror">
                                            @error('matDraft.codGE') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                        @else
                                            {{ $m->codGE }}
                                        @endif
                                    </div>
                                    <div class="gf-td w-28 font-mono text-xs text-gray-700">
                                        @if ($matEditingId === $m->id)
                                            <input wire:model.defer="matDraft.codGE2" type="text" maxlength="15"
                                                   class="gf-inline font-mono text-gray-700 @error('matDraft.codGE2') ring-2 ring-red-400 @enderror">
                                            @error('matDraft.codGE2') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                        @else
                                            {{ $m->codGE2 }}
                                        @endif
                                    </div>
                                    <div class="gf-td w-28 font-mono text-xs text-gray-700">
                                        @if ($matEditingId === $m->id)
                                            <input wire:model.defer="matDraft.codGE3" type="text" maxlength="15"
                                                   class="gf-inline font-mono text-gray-700 @error('matDraft.codGE3') ring-2 ring-red-400 @enderror">
                                            @error('matDraft.codGE3') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                        @else
                                            {{ $m->codGE3 }}
                                        @endif
                                    </div>

                                    <div class="gf-td-actions w-52 whitespace-nowrap">
                                        @if ($matEditingId === $m->id)
                                            <button type="button" wire:click="saveMateriaRow" wire:loading.attr="disabled" class="btn-primary btn-sm">
                                                <span wire:loading.remove wire:target="saveMateriaRow">Guardar</span>
                                                <span wire:loading wire:target="saveMateriaRow">…</span>
                                            </button>
                                            <button type="button" wire:click="cancelMateriaEdit" class="btn-secondary btn-sm">Cancelar</button>
                                        @else
                                            <button type="button" wire:click="openMateriaEdit({{ $m->id }})" class="btn-secondary btn-sm">Editar</button>
                                            <button type="button" wire:click="confirmDeleteMateria({{ $m->id }})" class="btn-danger btn-sm">Eliminar</button>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="gf-empty">No hay materias asociadas.</div>
                            @endforelse

                            {{-- Fila de creación --}}
                            @if ($matEditingId === 0)
                                <div class="gf-row bg-gray-50" wire:key="matplan-create-row-{{ $id }}">
                                    <div class="gf-td w-20 font-mono text-gray-700">
                                        <input wire:model.defer="matDraft.ord" type="text" inputmode="numeric" maxlength="3"
                                               class="gf-inline font-mono text-gray-700 @error('matDraft.ord') ring-2 ring-red-400 @enderror"
                                               placeholder="0">
                                        @error('matDraft.ord') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="gf-td flex-1 min-w-[18rem] text-gray-800">
                                        <input wire:model.defer="matDraft.matPlanMateria" type="text" maxlength="70"
                                               class="gf-inline text-gray-800 @error('matDraft.matPlanMateria') ring-2 ring-red-400 @enderror"
                                               placeholder="Nueva materia…">
                                        @error('matDraft.matPlanMateria') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="gf-td w-24 font-mono text-xs text-gray-700">
                                        <input wire:model.defer="matDraft.abrev" type="text" maxlength="5"
                                               class="gf-inline font-mono text-gray-700 @error('matDraft.abrev') ring-2 ring-red-400 @enderror"
                                               placeholder="Abrev">
                                        @error('matDraft.abrev') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="gf-td w-28 font-mono text-xs text-gray-700">
                                        <input wire:model.defer="matDraft.codGE" type="text" maxlength="15"
                                               class="gf-inline font-mono text-gray-700 @error('matDraft.codGE') ring-2 ring-red-400 @enderror"
                                               placeholder="CodGE">
                                        @error('matDraft.codGE') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="gf-td w-28 font-mono text-xs text-gray-700">
                                        <input wire:model.defer="matDraft.codGE2" type="text" maxlength="15"
                                               class="gf-inline font-mono text-gray-700 @error('matDraft.codGE2') ring-2 ring-red-400 @enderror"
                                               placeholder="CodGE2">
                                        @error('matDraft.codGE2') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="gf-td w-28 font-mono text-xs text-gray-700">
                                        <input wire:model.defer="matDraft.codGE3" type="text" maxlength="15"
                                               class="gf-inline font-mono text-gray-700 @error('matDraft.codGE3') ring-2 ring-red-400 @enderror"
                                               placeholder="CodGE3">
                                        @error('matDraft.codGE3') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="gf-td-actions w-52 whitespace-nowrap">
                                        <button type="button" wire:click="saveMateriaRow" wire:loading.attr="disabled" class="btn-primary btn-sm">
                                            <span wire:loading.remove wire:target="saveMateriaRow">Agregar</span>
                                            <span wire:loading wire:target="saveMateriaRow">…</span>
                                        </button>
                                        <button type="button" wire:click="cancelMateriaEdit" class="btn-secondary btn-sm">Cancelar</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Confirm delete materia --}}
    @if ($showMatConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-sm" @click.stop>
                <div class="px-6 py-5">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 mb-1">Confirmar eliminación</h3>
                            <p class="text-sm text-gray-600">{{ $matDeleteInfo }}</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 pb-5 flex justify-end gap-3">
                    <button wire:click="$set('showMatConfirm', false)" class="btn-secondary">Cancelar</button>
                    <button wire:click="deleteMateria" wire:loading.attr="disabled" class="btn-danger">
                        <span wire:loading.remove wire:target="deleteMateria">Eliminar</span>
                        <span wire:loading wire:target="deleteMateria">Eliminando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

