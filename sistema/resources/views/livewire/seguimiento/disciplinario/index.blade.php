<x-form-shell maxWidth="max-w-6xl">
    <div class="card p-6 space-y-5">
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
                 class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md text-sm text-green-700 flex items-center gap-2">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="text-center">
            <h1 class="text-lg font-semibold text-gray-800">Seguimiento disciplinario</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Año lectivo: {{ schoolCtx()->terlecAno() }} · Nivel: {{ schoolCtx()->nivelNombre() }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Curso</label>
                <select wire:model.live="idCurso" class="form-select">
                    <option value="">— Seleccione —</option>
                    @foreach ($cursos as $c)
                        <option value="{{ $c->Id }}">{{ $c->nombreParaListado() }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Alumno</label>
                <select wire:model.live="idMatricula" class="form-select" @disabled(!$idCurso)>
                    <option value="">— Seleccione —</option>
                    @foreach ($alumnos as $a)
                        <option value="{{ $a->id }}">{{ trim(($a->apellido ?? '').', '.($a->nombre ?? '')) }}{{ $a->dni ? ' · DNI '.$a->dni : '' }}</option>
                    @endforeach
                </select>
                @if ($idCurso && $alumnos->isEmpty())
                    <p class="text-xs text-amber-700 mt-1">No hay matrículas para ese curso en el año actual.</p>
                @endif
            </div>
        </div>

        @if ($matricula)
            <div class="border-t border-gray-100 pt-4 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-gray-800 truncate">
                            {{ $matricula->legajo?->apellido }}, {{ $matricula->legajo?->nombre }}
                        </div>
                        <div class="text-xs text-gray-500 truncate">
                            Curso: {{ $matricula->curso?->nombreParaListado() ?? '—' }} · Matrícula #{{ $matricula->id }}
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <a class="btn-secondary btn-sm"
                           href="{{ route('seguimiento.disciplinario.antecedentes', ['idMatricula' => $matricula->id]) }}">
                            Antecedentes disciplinarios
                        </a>
                        <a class="btn-primary btn-sm"
                           href="{{ route('seguimiento.disciplinario.create', ['matricula' => $matricula->id]) }}">
                            + Nueva sanción
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="table-header w-28">Fecha</th>
                                <th class="table-header w-56">Tipo</th>
                                <th class="table-header w-28">Cantidad</th>
                                <th class="table-header">Motivo</th>
                                <th class="table-header w-48">Solicitada por</th>
                                <th class="table-header w-40 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @forelse ($sanciones as $s)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="table-cell font-mono">{{ $s->fecha?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="table-cell">{{ $s->tipo?->tipo ?? ('#'.$s->idTipoSancion) }}</td>
                                    <td class="table-cell font-mono">{{ $s->cantidad ?? '—' }}</td>
                                    <td class="table-cell">
                                        <div class="line-clamp-2">{{ $s->motivo ?? '—' }}</div>
                                    </td>
                                    <td class="table-cell">
                                        {{ $s->solipor ?: ($s->profesor?->nombre_completo ?? '—') }}
                                    </td>
                                    <td class="table-cell text-right whitespace-nowrap">
                                        <a class="btn-secondary btn-sm"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           title="Imprimir comunicado"
                                           href="{{ route('seguimiento.disciplinario.print', ['id' => $s->id]) }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M6 9V2h12v7M6 18h12v4H6v-4z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M6 14H5a3 3 0 01-3-3V9a3 3 0 013-3h14a3 3 0 013 3v2a3 3 0 01-3 3h-1"/>
                                            </svg>
                                        </a>
                                        <a class="btn-secondary btn-sm"
                                           href="{{ route('seguimiento.disciplinario.edit', ['id' => $s->id]) }}">
                                            Editar
                                        </a>
                                        <button type="button"
                                                wire:click="confirmDelete({{ $s->id }})"
                                                class="btn-danger btn-sm">
                                            Borrar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="table-cell text-center text-gray-400 py-10">
                                        Sin sanciones registradas para esta matrícula en el año actual.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-500 text-center">
                Seleccione un curso y un alumno para ver sus sanciones del año actual.
            </p>
        @endif
    </div>

    @if ($showDeleteConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50">
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
                            <h3 class="text-base font-semibold text-gray-800 mb-1">Confirmar borrado</h3>
                            <p class="text-sm text-gray-600">{{ $deleteInfo }}</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 pb-5 flex justify-end gap-3">
                    <button wire:click="$set('showDeleteConfirm', false)" class="btn-secondary">Cancelar</button>
                    <button wire:click="delete" wire:loading.attr="disabled" class="btn-danger">
                        <span wire:loading.remove wire:target="delete">Borrar</span>
                        <span wire:loading wire:target="delete">Borrando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-form-shell>

