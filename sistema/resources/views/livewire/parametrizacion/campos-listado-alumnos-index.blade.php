<div class="max-w-4xl">
    <div class="card p-6">
        <h1 class="text-lg font-semibold text-gray-800 mb-1">Campos de legajos en listados</h1>
        <p class="text-sm text-gray-600 mb-4">
            Desmarque los campos que no deben ofrecerse al armar listados (PDF por curso). Use el botón para incorporar columnas nuevas si se agregaron a la tabla <code class="text-xs bg-gray-100 px-1 rounded">legajos</code> en la base de datos.
        </p>

        @if (session('status'))
            <p class="text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg px-4 py-2 mb-4">{{ session('status') }}</p>
        @endif

        <div class="flex flex-wrap gap-2 mb-4">
            <button type="button" wire:click="sincronizarDesdeLegajos" wire:loading.attr="disabled"
                    class="btn-primary text-sm inline-flex items-center gap-2">
                <span wire:loading.remove wire:target="sincronizarDesdeLegajos">Actualizar columnas desde esquema</span>
                <span wire:loading wire:target="sincronizarDesdeLegajos">Comparando…</span>
            </button>
        </div>

        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-700">
                    <tr>
                        <th class="px-3 py-2 font-medium">Orden</th>
                        <th class="px-3 py-2 font-medium">Columna (legajos)</th>
                        <th class="px-3 py-2 font-medium">Visible en listados</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($campos as $c)
                        {{-- La clave incluye visible_listado para que Livewire reemplace la fila y el checked no quede desincronizado con wire:click.prevent --}}
                        <tr wire:key="campo-listado-{{ $c->id }}-{{ $c->visible_listado ? 1 : 0 }}">
                            <td class="px-3 py-2 text-gray-600 font-mono">{{ $c->orden }}</td>
                            <td class="px-3 py-2 font-mono text-gray-900">{{ $c->columna }}</td>
                            <td class="px-3 py-2">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="rounded border-gray-300 text-[#40848D] focus:ring-[#40848D]"
                                           wire:click.prevent="toggleVisible({{ $c->id }})"
                                           @checked((bool) $c->visible_listado)>
                                    <span class="text-gray-600 text-xs">Visible en listados</span>
                                </label>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-6 text-center text-gray-500">No hay registros. Ejecute migraciones o pulse «Actualizar columnas desde esquema».</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
