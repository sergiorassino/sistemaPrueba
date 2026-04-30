<div class="se-page max-w-5xl">
    <section class="se-hero">
        <div class="se-hero-inner">
            <div class="min-w-0 space-y-2">
                <p class="se-eyebrow">Configuración</p>
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Canales de comunicación</h2>
                <p class="max-w-2xl text-sm text-white/80">
                    Quién puede iniciar y responder comunicados, y por qué medios.
                </p>
            </div>
        </div>
    </section>

    @if (session('success'))
        <div class="se-soft-card flex items-center gap-3 border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            <svg class="h-5 w-5 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="se-card overflow-hidden">
        <div class="w-full overflow-x-auto">
            <div class="flex justify-start">
                <table class="min-w-[720px] divide-y divide-accent-200 text-sm sm:min-w-full">
                    <thead class="bg-accent-50">
                        <tr>
                            <th class="table-header">De</th>
                            <th class="table-header">Para</th>
                            <th class="table-header text-center">Inicia</th>
                            <th class="table-header text-center">Responde</th>
                            <th class="table-header">Medios</th>
                            <th class="table-header text-center">Activo</th>
                            <th class="table-header w-40"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-accent-200 bg-white">
                        @foreach ($canales as $canal)
                            <tr @class([
                                'bg-amber-50/50' => $editandoId === $canal->id,
                                'hover:bg-accent-50/50' => $editandoId !== $canal->id,
                            ])>
                                <td class="table-cell font-semibold text-neutral-900">{{ $etiquetas[$canal->rol_emisor] ?? $canal->rol_emisor }}</td>
                                <td class="table-cell text-neutral-700">{{ $etiquetas[$canal->rol_receptor] ?? $canal->rol_receptor }}</td>

                                @if ($editandoId === $canal->id)
                                    <td class="table-cell text-center">
                                        <input type="checkbox" wire:model="editPuedeIniciar" class="rounded border-accent-300 text-primary-600 focus:ring-primary-500">
                                    </td>
                                    <td class="table-cell text-center">
                                        <input type="checkbox" wire:model="editPuedeResponder" class="rounded border-accent-300 text-primary-600 focus:ring-primary-500">
                                    </td>
                                    <td class="table-cell">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($mediosDisponibles as $medio)
                                                <label class="flex cursor-pointer select-none items-center gap-1.5 text-xs">
                                                    <input type="checkbox"
                                                           wire:click="toggleMedio('{{ $medio }}')"
                                                           @checked(in_array($medio, $editMedios))
                                                           class="rounded border-accent-300 text-primary-600 focus:ring-primary-500">
                                                    {{ ucfirst($medio) }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="table-cell text-center">
                                        <input type="checkbox" wire:model="editActivo" class="rounded border-accent-300 text-primary-600 focus:ring-primary-500">
                                    </td>
                                    <td class="table-cell">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <button type="button" wire:click="guardar" class="btn-primary btn-sm">Guardar</button>
                                            <button type="button" wire:click="cancelarEdicion" class="btn-secondary btn-sm">Cancelar</button>
                                        </div>
                                    </td>
                                @else
                                    <td class="table-cell text-center">
                                        @if ($canal->puede_iniciar)
                                            <svg class="mx-auto h-4 w-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @else
                                            <svg class="mx-auto h-4 w-4 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        @endif
                                    </td>
                                    <td class="table-cell text-center">
                                        @if ($canal->puede_responder)
                                            <svg class="mx-auto h-4 w-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @else
                                            <svg class="mx-auto h-4 w-4 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        @endif
                                    </td>
                                    <td class="table-cell">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($canal->medios_permitidos ?? [] as $medio)
                                                <span class="se-pill text-[10px]">{{ ucfirst($medio) }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="table-cell text-center">
                                        @if ($canal->activo)
                                            <span class="inline-block h-2 w-2 rounded-full bg-primary-500"></span>
                                        @else
                                            <span class="inline-block h-2 w-2 rounded-full bg-neutral-300"></span>
                                        @endif
                                    </td>
                                    <td class="table-cell">
                                        <button type="button" wire:click="iniciarEdicion({{ $canal->id }})"
                                                class="text-xs font-semibold text-primary-700 underline decoration-primary-300 underline-offset-2 hover:text-primary-900">
                                            Editar
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <p class="text-xs text-neutral-500">
        Los cambios se aplican al guardar. El caché de cada canal se invalida al guardar.
    </p>
</div>
