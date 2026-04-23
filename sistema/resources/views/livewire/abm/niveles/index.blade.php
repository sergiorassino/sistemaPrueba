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

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Niveles Educativos</h2>
            <p class="text-sm text-gray-500 mt-0.5">Inicial, Primario, Secundario, etc.</p>
        </div>
        <button wire:click="openCreate" class="btn-primary btn-sm sm:self-start">
            + Nuevo nivel
        </button>
    </div>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="table-header">Nivel</th>
                    <th class="table-header">Abreviatura</th>
                    <th class="table-header text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($niveles as $n)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="table-cell font-medium">{{ $n->nivel }}</td>
                        <td class="table-cell">
                            <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs font-mono">
                                {{ $n->abrev }}
                            </span>
                        </td>
                        <td class="table-cell text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="openEdit({{ $n->id }})"
                                        class="btn-secondary btn-sm">Editar</button>
                                <button wire:click="confirmDelete({{ $n->id }})"
                                        class="btn-danger btn-sm">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="table-cell text-center text-gray-400 py-8">
                            No hay niveles registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md" @click.stop>
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-800">
                        {{ $editId ? 'Editar nivel' : 'Nuevo nivel' }}
                    </h3>
                    <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="form-label" for="nivel-nombre">Nombre del nivel</label>
                        <input wire:model="nivel"
                               id="nivel-nombre"
                               type="text"
                               maxlength="50"
                               placeholder="Ej: Nivel Secundario"
                               class="form-input @error('nivel') border-red-400 @enderror">
                        @error('nivel') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label" for="nivel-abrev">Abreviatura (máx. 5 caracteres)</label>
                        <input wire:model="abrev"
                               id="nivel-abrev"
                               type="text"
                               maxlength="5"
                               placeholder="Ej: Secu"
                               class="form-input @error('abrev') border-red-400 @enderror">
                        @error('abrev') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                    <button wire:click="$set('showModal', false)" class="btn-secondary">Cancelar</button>
                    <button wire:click="save"
                            wire:loading.attr="disabled"
                            class="btn-primary">
                        <span wire:loading.remove wire:target="save">Guardar</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Confirm / Info Modal --}}
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
                        <button wire:click="delete"
                                wire:loading.attr="disabled"
                                class="btn-danger">
                            <span wire:loading.remove wire:target="delete">Eliminar</span>
                            <span wire:loading wire:target="delete">Eliminando…</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
