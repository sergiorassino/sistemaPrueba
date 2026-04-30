<div class="max-w-4xl space-y-4">

    <div>
        <h1 class="text-lg font-semibold text-gray-900">Canales de Comunicación</h1>
        <p class="text-sm text-gray-500">Configura quién puede iniciar y responder comunicados, y por qué medios.</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">De</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Para</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Inicia</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Responde</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Medios</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Activo</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($canales as $canal)
                    <tr @class(['bg-yellow-50' => $editandoId === $canal->id, 'hover:bg-gray-50' => $editandoId !== $canal->id])>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $etiquetas[$canal->rol_emisor] ?? $canal->rol_emisor }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $etiquetas[$canal->rol_receptor] ?? $canal->rol_receptor }}</td>

                        @if($editandoId === $canal->id)
                        {{-- Edición inline --}}
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" wire:model="editPuedeIniciar" class="rounded border-gray-300">
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" wire:model="editPuedeResponder" class="rounded border-gray-300">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-2">
                                @foreach($mediosDisponibles as $medio)
                                <label class="flex items-center gap-1 text-xs cursor-pointer select-none">
                                    <input type="checkbox"
                                           wire:click="toggleMedio('{{ $medio }}')"
                                           @checked(in_array($medio, $editMedios))
                                           class="rounded border-gray-300">
                                    {{ ucfirst($medio) }}
                                </label>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" wire:model="editActivo" class="rounded border-gray-300">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button wire:click="guardar"
                                        class="px-3 py-1 rounded-md text-white text-xs font-medium"
                                        style="background:#40848D">Guardar</button>
                                <button wire:click="cancelarEdicion"
                                        class="px-3 py-1 rounded-md text-gray-600 text-xs font-medium border border-gray-300 hover:bg-gray-50">
                                    Cancelar
                                </button>
                            </div>
                        </td>
                        @else
                        <td class="px-4 py-3 text-center">
                            @if($canal->puede_iniciar)
                            <svg class="w-4 h-4 text-emerald-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            @else
                            <svg class="w-4 h-4 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($canal->puede_responder)
                            <svg class="w-4 h-4 text-emerald-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            @else
                            <svg class="w-4 h-4 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($canal->medios_permitidos ?? [] as $medio)
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">{{ ucfirst($medio) }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($canal->activo)
                            <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                            @else
                            <span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <button wire:click="iniciarEdicion({{ $canal->id }})"
                                    class="text-xs text-gray-500 hover:text-gray-700 underline transition">
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

    <p class="text-xs text-gray-400">
        Los cambios se aplican de inmediato. El caché de cada canal se invalida al guardar.
    </p>
</div>
