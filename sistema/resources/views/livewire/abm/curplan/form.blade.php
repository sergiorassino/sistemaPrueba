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

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
        <div class="min-w-0">
            <h2 class="text-xl font-semibold text-gray-800">{{ $id ? 'Editar curso modelo' : 'Nuevo curso modelo' }}</h2>
            <p class="text-xs text-gray-400 mt-0.5">Los campos marcados con * son obligatorios</p>
        </div>

        <div class="flex flex-wrap gap-2 sm:justify-end">
            <a href="{{ route('abm.curplan') }}" class="btn-secondary btn-sm">Volver</a>
            <button wire:click="save" wire:loading.attr="disabled" class="btn-primary btn-sm">
                <span wire:loading.remove wire:target="save">Guardar</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- CurPlan --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="text-sm font-semibold text-gray-800">Curso modelo</div>
                    <div class="text-xs text-gray-500">Datos base del CurPlan</div>
                </div>
                @if ($id)
                    <span class="text-xs text-gray-500">ID #{{ $id }}</span>
                @endif
            </div>

            <div class="space-y-4">
                <div>
                    <label class="form-label">Plan *</label>
                    <select wire:model="idPlan" class="form-select @error('idPlan') border-red-400 @enderror">
                        <option value="">— Seleccione —</option>
                        @foreach ($planes as $p)
                            <option value="{{ $p->id }}">
                                {{ $p->abrev ? ($p->abrev . ' · ') : '' }}{{ $p->plan }}
                            </option>
                        @endforeach
                    </select>
                    @error('idPlan') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Curso modelo *</label>
                    <input wire:model="curPlanCurso" type="text" maxlength="30"
                           placeholder="Ej: PRIMERO (PRIM)"
                           class="form-input @error('curPlanCurso') border-red-400 @enderror">
                    @error('curPlanCurso') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- MatPlan --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
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
                    <div class="max-h-[260px] overflow-auto">
                        <table class="min-w-full text-sm table-fixed">
                            <colgroup>
                                <col style="width: 4.5rem;">
                                <col>
                                <col style="width: 5.5rem;">
                                <col style="width: 6.5rem;">
                                <col style="width: 6.5rem;">
                                <col style="width: 6.5rem;">
                                <col style="width: 8.5rem;">
                            </colgroup>
                            <thead class="bg-gray-50 text-gray-700">
                            <tr>
                                <th class="px-3 py-2 text-left">Ord</th>
                                <th class="px-3 py-2 text-left">Materia</th>
                                <th class="px-3 py-2 text-left">Abrev</th>
                                <th class="px-3 py-2 text-left">CodGE</th>
                                <th class="px-3 py-2 text-left">CodGE2</th>
                                <th class="px-3 py-2 text-left">CodGE3</th>
                                <th class="px-3 py-2 text-right">Acciones</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                            @forelse ($materias as $m)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-gray-700 font-mono">{{ $m->ord }}</td>
                                    <td class="px-3 py-2 text-gray-800">
                                        <div class="font-medium">{{ $m->matPlanMateria }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-gray-700 font-mono text-xs">{{ $m->abrev }}</td>
                                    <td class="px-3 py-2 text-gray-700 font-mono text-xs">{{ $m->codGE }}</td>
                                    <td class="px-3 py-2 text-gray-700 font-mono text-xs">{{ $m->codGE2 }}</td>
                                    <td class="px-3 py-2 text-gray-700 font-mono text-xs">{{ $m->codGE3 }}</td>
                                    <td class="px-3 py-2 text-right whitespace-nowrap">
                                        <button type="button" wire:click="openMateriaEdit({{ $m->id }})" class="btn-secondary btn-sm">Editar</button>
                                        <button type="button" wire:click="confirmDeleteMateria({{ $m->id }})" class="btn-danger btn-sm">Eliminar</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-6 text-center text-gray-500">
                                        No hay materias asociadas.
                                    </td>
                                </tr>
                            @endforelse

                            {{-- Fila de carga/edición inline (misma grilla que el listado) --}}
                            <tr class="bg-gray-50" wire:key="matplan-inline-row-{{ $id }}-{{ $matEditId ?? 'new' }}">
                                <td class="px-2 py-2 align-top">
                                    <input wire:model.defer="ord" type="text" inputmode="numeric" maxlength="3"
                                           class="form-input !py-1 !text-xs w-full font-mono @error('ord') border-red-400 @enderror"
                                           placeholder="0">
                                    @error('ord') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                </td>
                                <td class="px-2 py-2 align-top">
                                    <input wire:model.defer="matPlanMateria" type="text" maxlength="70"
                                           class="form-input !py-1 !text-xs w-full @error('matPlanMateria') border-red-400 @enderror"
                                           placeholder="Nueva materia…">
                                    @error('matPlanMateria') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                </td>
                                <td class="px-2 py-2 align-top">
                                    <input wire:model.defer="abrev" type="text" maxlength="5"
                                           class="form-input !py-1 !text-xs w-full font-mono @error('abrev') border-red-400 @enderror"
                                           placeholder="Abrev">
                                    @error('abrev') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                </td>
                                <td class="px-2 py-2 align-top">
                                    <input wire:model.defer="codGE" type="text" maxlength="15"
                                           class="form-input !py-1 !text-xs w-full font-mono @error('codGE') border-red-400 @enderror"
                                           placeholder="CodGE">
                                    @error('codGE') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                </td>
                                <td class="px-2 py-2 align-top">
                                    <input wire:model.defer="codGE2" type="text" maxlength="15"
                                           class="form-input !py-1 !text-xs w-full font-mono @error('codGE2') border-red-400 @enderror"
                                           placeholder="CodGE2">
                                    @error('codGE2') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                </td>
                                <td class="px-2 py-2 align-top">
                                    <input wire:model.defer="codGE3" type="text" maxlength="15"
                                           class="form-input !py-1 !text-xs w-full font-mono @error('codGE3') border-red-400 @enderror"
                                           placeholder="CodGE3">
                                    @error('codGE3') <div class="text-[10px] text-red-700 mt-1">{{ $message }}</div> @enderror
                                </td>
                                <td class="px-2 py-2 align-top text-right whitespace-nowrap">
                                    <button type="button" wire:click="saveMateria" wire:loading.attr="disabled"
                                            class="btn-primary btn-sm">
                                        <span wire:loading.remove wire:target="saveMateria">{{ $matEditId ? 'Guardar' : 'Agregar' }}</span>
                                        <span wire:loading wire:target="saveMateria">…</span>
                                    </button>
                                    @if ($matEditId)
                                        <button type="button" wire:click="openMateriaCreate" class="btn-secondary btn-sm">Limpiar</button>
                                    @endif
                                </td>
                            </tr>
                            </tbody>
                        </table>
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

