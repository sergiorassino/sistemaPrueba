<div>
    {{-- Flash messages --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
             class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md text-sm text-green-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="w-full min-w-0 text-center sm:flex-1">
            <h2 class="text-xl font-semibold text-gray-800">Términos Lectivos</h2>
            <p class="text-sm text-gray-500 mt-0.5">Gestión de años lectivos del sistema</p>
        </div>
        <div class="flex justify-center sm:shrink-0 sm:justify-end">
            <button wire:click="openCreate" class="btn-primary btn-sm">
                + Nuevo año lectivo
            </button>
        </div>
    </div>

    {{-- ── Listado (.gf-wrap > .gf) ── --}}
    <div class="gf-wrap">
        <div class="gf min-w-[420px]">

            <div class="gf-head">
                <div class="gf-th w-32">Año</div>
                <div class="gf-th w-40">Orden (prioridad)</div>
                <div class="gf-th-right flex-1">Acciones</div>
            </div>

            @forelse ($terlecs as $t)
                <div class="gf-row gf-row-hover">
                    <div class="gf-td w-32 font-medium">{{ $t->ano }}</div>
                    <div class="gf-td-muted w-40">{{ $t->orden }}</div>
                    <div class="gf-td-actions flex-1">
                        <button wire:click="openEdit({{ $t->id }})" class="btn-secondary btn-sm">Editar</button>
                        <button wire:click="confirmDelete({{ $t->id }})" class="btn-danger btn-sm">Eliminar</button>
                    </div>
                </div>
            @empty
                <div class="gf-empty">No hay términos lectivos registrados.</div>
            @endforelse

        </div>
    </div>

    {{-- ── Modal Crear / Editar ── --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50"
             x-data x-init="$el.querySelector('input')?.focus()">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md" @click.stop>
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800">
                        {{ $editId ? 'Editar término lectivo' : 'Nuevo término lectivo' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- ── Formulario (.gf) ── --}}
                <div class="px-6 py-4">
                    <div class="gf w-full">

                        <div class="gf-row @error('ano') gf-cell-err @enderror">
                            <div class="gf-label gf-label-req w-40">Año lectivo</div>
                            <div class="gf-cell @error('ano') gf-cell-err @enderror">
                                <input wire:model="ano" id="terlec-ano" type="number"
                                       min="2000" max="2100" placeholder="Ej: 2026"
                                       class="gf-input @error('ano') gf-input-err @enderror">
                            </div>
                        </div>
                        @error('ano')
                            <div class="gf-error-row">
                                <div class="gf-error-spacer w-40"></div>
                                <div class="gf-error-msg">{{ $message }}</div>
                            </div>
                        @enderror

                        <div class="gf-row @error('orden') gf-cell-err @enderror">
                            <div class="gf-label gf-label-req w-40">
                                Orden <span class="font-normal text-gray-400">(1&nbsp;=&nbsp;reciente)</span>
                            </div>
                            <div class="gf-cell @error('orden') gf-cell-err @enderror">
                                <input wire:model="orden" id="terlec-orden" type="number"
                                       min="1" placeholder="Ej: 1"
                                       class="gf-input @error('orden') gf-input-err @enderror">
                            </div>
                        </div>
                        @error('orden')
                            <div class="gf-error-row">
                                <div class="gf-error-spacer w-40"></div>
                                <div class="gf-error-msg">{{ $message }}</div>
                            </div>
                        @enderror

                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                    <button wire:click="$set('showModal', false)" class="btn-secondary">Cancelar</button>
                    <button wire:click="save" wire:loading.attr="disabled" class="btn-primary">
                        <span wire:loading.remove wire:target="save">Guardar</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Modal Confirmar / Info ── --}}
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
