<div class="se-page max-w-6xl">
    <section class="se-hero">
        <div class="se-hero-inner">
            <div class="min-w-0 space-y-2">
                <p class="se-eyebrow">Administración</p>
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Permisos de usuarios</h2>
                <p class="max-w-2xl text-sm text-white/80">
                    Editá los permisos (cadena 0/1) de cada usuario del nivel actual.
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

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <aside class="lg:col-span-4">
            <div class="se-card space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Usuarios</p>
                        <p class="text-sm font-semibold text-neutral-800">Nivel: {{ schoolCtx()->nivelNombre() }}</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Buscar</label>
                    <input type="text"
                           wire:model.live="q"
                           placeholder="Apellido, nombre o DNI…"
                           class="w-full rounded-xl border border-accent-200 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                </div>

                @error('profesorId')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror

                <div class="max-h-[520px] overflow-y-auto rounded-2xl border border-accent-200 bg-white">
                    <ul class="divide-y divide-accent-200">
                        @forelse ($usuarios as $u)
                            <li>
                                <button type="button"
                                        wire:click="seleccionarProfesor({{ $u->id }})"
                                        @class([
                                            'w-full text-left px-4 py-3 hover:bg-accent-50/60 transition-colors',
                                            'bg-[rgba(64,132,141,0.10)]' => (int) $profesorId === (int) $u->id,
                                        ])>
                                    <p class="text-sm font-semibold text-neutral-900">
                                        {{ trim($u->apellido . ', ' . $u->nombre) }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-neutral-600">DNI: {{ $u->dni }}</p>
                                </button>
                            </li>
                        @empty
                            <li class="px-4 py-6 text-sm text-neutral-600">
                                No hay usuarios para mostrar.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </aside>

        <main class="lg:col-span-8">
            <div class="se-card">
                @if (! $profesorSeleccionado)
                    <div class="rounded-2xl border border-dashed border-[#C1D7DA] bg-white/80 p-8 text-center text-sm text-neutral-600">
                        Seleccione un usuario para editar sus permisos.
                    </div>
                @else
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Usuario seleccionado</p>
                        <p class="mt-1 text-lg font-bold text-neutral-900">
                            {{ trim($profesorSeleccionado->apellido . ', ' . $profesorSeleccionado->nombre) }}
                        </p>
                        <p class="text-xs text-neutral-600">DNI: {{ $profesorSeleccionado->dni }}</p>
                        <p class="mt-2 text-xs text-neutral-500">Los cambios se guardan al marcar o desmarcar cada permiso.</p>
                    </div>

                    <div class="mt-6 space-y-6" wire:key="permisos-panel-{{ $profesorId }}">
                        @foreach ($porTema as $tema => $items)
                            <section class="rounded-2xl border border-accent-200 bg-white">
                                <div class="flex items-center justify-between border-b border-accent-200 bg-accent-50 px-4 py-3">
                                    <p class="text-sm font-bold uppercase tracking-wider text-neutral-900">{{ $tema }}</p>
                                    <p class="text-xs text-neutral-500">{{ $items->count() }} permiso(s)</p>
                                </div>
                                <div class="divide-y divide-accent-200">
                                    @foreach ($items as $perm)
                                        <div class="flex items-start gap-3 px-4 py-3 hover:bg-accent-50/40"
                                             wire:loading.class="opacity-60"
                                             wire:target="togglePermiso">
                                            <input type="checkbox"
                                                   wire:key="perm-{{ $profesorId }}-{{ (int) $perm->orden }}"
                                                   class="mt-1 rounded border-accent-300 text-primary-600 focus:ring-primary-500"
                                                   wire:click="togglePermiso({{ (int) $perm->orden }})"
                                                   wire:loading.attr="disabled"
                                                   wire:target="togglePermiso"
                                                   @checked(($permisos[(int) $perm->orden] ?? false) === true)>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-normal text-neutral-700">
                                                    Orden {{ (int) $perm->orden }}
                                                </p>
                                                <p class="mt-0.5 text-xs text-neutral-600">
                                                    {{ $perm->descripcion }}
                                                </p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>

                    <p class="mt-6 text-xs text-neutral-500">
                        Nota: el acceso a esta pantalla está protegido por el permiso de orden 0 (ADMINISTRACION / PERMISOS).
                    </p>
                @endif
            </div>
        </main>
    </div>
</div>

