<div class="se-page max-w-4xl">
    <section class="se-hero">
        <div class="se-hero-inner">
            <div class="min-w-0 space-y-2">
                <p class="se-eyebrow">Configuración</p>
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Campos en listados</h2>
                <p class="max-w-2xl text-sm text-white/80">
                    Visibilidad de columnas de <span class="font-mono text-white/90">legajos</span> al armar listados PDF por curso.
                </p>
            </div>
        </div>
    </section>

    @if (session('status'))
        <div class="se-soft-card border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="se-card overflow-hidden">
        <div class="border-b border-accent-200 bg-white px-5 py-4">
            <p class="se-section-title">Columnas</p>
            <p class="mt-1 text-sm text-neutral-600">
                Desmarcá lo que no deba ofrecerse. Si agregaste columnas en la base, sincronizá desde el esquema.
            </p>
            <div class="mt-4">
                <button type="button" wire:click="sincronizarDesdeLegajos" wire:loading.attr="disabled" class="btn-primary btn-sm">
                    <span wire:loading.remove wire:target="sincronizarDesdeLegajos">Actualizar columnas desde esquema</span>
                    <span wire:loading wire:target="sincronizarDesdeLegajos">Comparando…</span>
                </button>
            </div>
        </div>

        <div class="w-full overflow-x-auto">
            <div class="flex justify-start">
                <table class="min-w-full border-collapse text-sm">
                    <thead class="bg-accent-50">
                        <tr>
                            <th class="table-header">Orden</th>
                            <th class="table-header">Columna (legajos)</th>
                            <th class="table-header">Visible en listados</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-accent-200 bg-white">
                        @forelse ($campos as $c)
                            <tr wire:key="campo-listado-{{ $c->id }}-{{ $c->visible_listado ? 1 : 0 }}">
                                <td class="table-cell font-mono text-neutral-600">{{ $c->orden }}</td>
                                <td class="table-cell font-mono text-neutral-900">{{ $c->columna }}</td>
                                <td class="table-cell">
                                    <label class="inline-flex cursor-pointer items-center gap-2">
                                        <input type="checkbox"
                                               class="rounded border-accent-300 text-primary-600 focus:ring-primary-500"
                                               wire:click.prevent="toggleVisible({{ $c->id }})"
                                               @checked((bool) $c->visible_listado)>
                                        <span class="text-xs text-neutral-600">Visible</span>
                                    </label>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="table-cell py-10 text-center text-neutral-500">
                                    No hay registros. Ejecutá migraciones o «Actualizar columnas desde esquema».
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
