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

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="w-full min-w-0 text-center sm:flex-1">
            <h2 class="text-xl font-semibold text-gray-800">Gestión de planes de estudio</h2>
            <p class="text-sm text-gray-500 mt-0.5">Planes disponibles para el nivel actual</p>
        </div>
        <div class="flex justify-center sm:shrink-0 sm:justify-end">
            <a href="{{ route('abm.planes.create') }}" class="btn-primary btn-sm">
                + Nuevo plan
            </a>
        </div>
    </div>

    <div class="gf-wrap">
        <div class="gf min-w-[640px]">
            <div class="gf-head">
                <div class="gf-th w-24">ID</div>
                <div class="gf-th flex-1 min-w-[18rem]">Plan</div>
                <div class="gf-th w-40">Abrev</div>
                <div class="gf-th-right w-48">Acciones</div>
            </div>

            @forelse ($planes as $p)
                <div class="gf-row gf-row-hover">
                    <div class="gf-td w-24 font-mono text-gray-600">{{ $p->id }}</div>
                    <div class="gf-td flex-1 min-w-[18rem] font-medium text-gray-800">{{ $p->plan }}</div>
                    <div class="gf-td w-40 font-mono text-xs text-gray-600">{{ $p->abrev }}</div>
                    <div class="gf-td-actions w-48">
                        <a href="{{ route('abm.planes.edit', ['id' => $p->id]) }}" class="btn-secondary btn-sm">Editar</a>
                        <button wire:click="confirmDelete({{ $p->id }})" class="btn-danger btn-sm">Eliminar</button>
                    </div>
                </div>
            @empty
                <div class="gf-empty">No hay planes registrados.</div>
            @endforelse
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

