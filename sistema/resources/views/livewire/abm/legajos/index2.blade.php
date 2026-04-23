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

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Legajos de Estudiantes</h2>
            <p class="text-sm text-gray-500 mt-0.5">Listado completo de legajos · Año de sesión: {{ schoolCtx()->terlecAno() }}</p>
        </div>
        <a href="{{ route('abm.legajos.create') }}" class="btn-primary btn-sm sm:self-start">
            + Nuevo legajo
        </a>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-4">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input wire:model.live.debounce.400ms="search"
                   type="search"
                   placeholder="Buscar por apellido, nombre o DNI…"
                   class="form-input pl-9">
        </div>
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer whitespace-nowrap">
            <input wire:model.live="soloMatricula" type="checkbox" class="rounded border-gray-300 text-primary-600">
            Solo matriculados año activo
        </label>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-header w-[min(28%,18rem)]">Estudiante</th>
                        <th class="table-header w-32">DNI</th>
                        <th class="table-header">Matriculaciones en la escuela</th>
                        <th class="table-header text-right w-36 shrink-0">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse ($legajos as $l)
                        <tr id="legajo-{{ $l->id }}"
                            x-data="{ focus: {{ (int) $focusId === (int) $l->id ? 'true' : 'false' }} }"
                            x-init="if (focus) { $nextTick(() => { const el = document.getElementById('legajo-{{ $l->id }}'); el?.scrollIntoView({ block: 'center' }); el?.classList.add('ring-2','ring-primary-400','bg-primary-50/30'); el?.querySelector('a[data-focus-target]')?.focus(); }); }"
                            class="align-top hover:bg-gray-50 transition-colors">
                            <td class="table-cell">
                                <div class="font-medium text-gray-900">{{ $l->apellido }}, {{ $l->nombre }}</div>
                            </td>
                            <td class="table-cell font-mono text-gray-700">
                                {{ $l->dni }}
                            </td>
                            <td class="table-cell py-2">
                                @if ($l->matriculas->isEmpty())
                                    <span class="text-xs text-gray-400 italic">Sin matrículas</span>
                                @else
                                    <div class="gf text-[10px] w-max max-w-full">
                                        <div class="gf-head">
                                            <div class="gf-th w-14 px-1">Año</div>
                                            <div class="gf-th w-44">Curso</div>
                                            <div class="gf-th w-24 px-1">Cond.</div>
                                        </div>
                                        @foreach ($l->matriculas as $mat)
                                            <div @class([
                                                'gf-row',
                                                'bg-amber-50/80' => (int) ($mat->idTerlec ?? 0) === (int) schoolCtx()->idTerlec,
                                            ])>
                                                <div class="gf-td w-14 px-1 font-mono font-semibold text-gray-800">
                                                    {{ $mat->terlec?->ano ?? '—' }}
                                                </div>
                                                <div class="gf-td w-44 truncate" title="{{ $mat->curso?->cursec }}">
                                                    {{ $mat->curso?->cursec ? trim($mat->curso->cursec) : '—' }}
                                                </div>
                                                <div class="gf-td w-24 px-1 truncate" title="{{ $mat->condicion?->condicion }}">
                                                    {{ \Illuminate\Support\Str::limit($mat->condicion?->condicion ?? '—', 12) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="table-cell text-right whitespace-nowrap">
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-1.5">
                                    <a data-focus-target href="{{ route('abm.legajos.edit', ['id' => $l->id]) }}"
                                       class="btn-secondary btn-sm">Editar</a>
                                    <button wire:click="confirmDelete({{ $l->id }})"
                                            class="btn-danger btn-sm">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="table-cell text-center text-gray-400 py-10">
                                @if ($search)
                                    No se encontraron legajos para "{{ $search }}".
                                @else
                                    No hay legajos registrados.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($legajos->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $legajos->links() }}
            </div>
        @endif

        <div class="px-4 py-2 border-t border-gray-100 text-xs text-gray-400">
            {{ $legajos->total() }} legajos encontrados
        </div>
    </div>

    {{-- ═══════════════════ CONFIRM / INFO MODAL ═══════════════════ --}}
    @if ($showConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60">
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

