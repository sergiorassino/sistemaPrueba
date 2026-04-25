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
            <h2 class="text-xl font-semibold text-gray-800">Cursos modelo (CurPlan)</h2>
            <p class="text-sm text-gray-500 mt-0.5">Cursos base por plan para crear cursos de cada año lectivo</p>
        </div>
        <a href="{{ route('abm.curplan.create') }}" class="btn-primary btn-sm sm:self-start">
            + Nuevo curso modelo
        </a>
    </div>

    <div class="gf-wrap">
        <div class="gf min-w-[640px]">
            <div class="gf-head">
                <div class="gf-th w-36">Plan</div>
                <div class="gf-th flex-1 min-w-[16rem]">Curso modelo</div>
                <div class="gf-th-right w-48">Acciones</div>
            </div>

            @forelse ($curplanes as $c)
                <div class="gf-row gf-row-hover">
                    <div class="gf-td w-36">
                        <div class="font-medium text-gray-800">
                            {{ $c->plan?->abrev ?: ('#' . $c->idPlan) }}
                        </div>
                        <div class="text-xs text-gray-500 truncate">
                            {{ $c->plan?->plan }}
                        </div>
                    </div>
                    <div class="gf-td flex-1 min-w-[16rem] font-medium">{{ $c->curPlanCurso }}</div>
                    <div class="gf-td-actions w-48">
                        <a href="{{ route('abm.curplan.edit', ['id' => $c->id]) }}" class="btn-secondary btn-sm">Editar</a>
                        <button wire:click="confirmDelete({{ $c->id }})" class="btn-danger btn-sm">Eliminar</button>
                    </div>
                </div>
            @empty
                <div class="gf-empty">No hay cursos modelo registrados.</div>
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

