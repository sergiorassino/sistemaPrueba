<div class="se-page">
    <section class="se-hero">
        <div class="se-hero-inner">
            <div class="min-w-0 space-y-3">
                <p class="se-eyebrow">Listados</p>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Alumnos por curso</h2>
                    <p class="mt-2 max-w-2xl text-sm text-white/80">
                        {{ schoolCtx()->nivelNombre() }} · Ciclo lectivo {{ schoolCtx()->terlecAno() }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <span class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white/85">
                    <span class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-white/50">Cursos en el colegio</span>
                    <span class="text-xl font-bold tabular-nums">{{ $cursos->count() }}</span>
                </span>
                @if ($cursos->isNotEmpty())
                    <span class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white/85">
                        <span class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-white/50">Seleccionados para PDF</span>
                        <span class="text-xl font-bold tabular-nums">{{ count($cursosElegidos) }}</span>
                    </span>
                @endif
            </div>
        </div>
    </section>

    @if ($cursos->isEmpty())
        <div class="se-card p-8">
            <div class="flex flex-col items-center justify-center gap-4 text-center sm:flex-row sm:text-left">
                <div class="se-icon-badge h-14 w-14">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="max-w-md">
                    <p class="text-sm font-semibold text-neutral-800">Sin cursos en este contexto</p>
                    <p class="mt-1 text-sm text-neutral-600">No hay cursos cargados para el nivel y año lectivo activos.</p>
                </div>
            </div>
        </div>
    @else
        <div class="se-toolbar">
            <div class="min-w-0 flex-1 space-y-2">
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-neutral-500">Condición de matrícula en el PDF</p>
                <div class="flex flex-wrap gap-2">
                    <label @class([
                        'inline-flex cursor-pointer items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold shadow-sm transition-colors focus-within:ring-2 focus-within:ring-primary-500 focus-within:ring-offset-2',
                        'border-primary-500 bg-primary-600 text-white' => $filtroCondicion === 'regulares',
                        'border-accent-200 bg-white text-neutral-700 hover:bg-accent-50' => $filtroCondicion !== 'regulares',
                    ])>
                        <input type="radio" name="filtro-condicion" value="regulares" class="sr-only" wire:model.live="filtroCondicion">
                        Regulares
                    </label>
                    <label @class([
                        'inline-flex cursor-pointer items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold shadow-sm transition-colors focus-within:ring-2 focus-within:ring-primary-500 focus-within:ring-offset-2',
                        'border-primary-500 bg-primary-600 text-white' => $filtroCondicion === 'salidos',
                        'border-accent-200 bg-white text-neutral-700 hover:bg-accent-50' => $filtroCondicion !== 'salidos',
                    ])>
                        <input type="radio" name="filtro-condicion" value="salidos" class="sr-only" wire:model.live="filtroCondicion">
                        Salidos
                    </label>
                    <label @class([
                        'inline-flex cursor-pointer items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold shadow-sm transition-colors focus-within:ring-2 focus-within:ring-primary-500 focus-within:ring-offset-2',
                        'border-primary-500 bg-primary-600 text-white' => $filtroCondicion === 'todos',
                        'border-accent-200 bg-white text-neutral-700 hover:bg-accent-50' => $filtroCondicion !== 'todos',
                    ])>
                        <input type="radio" name="filtro-condicion" value="todos" class="sr-only" wire:model.live="filtroCondicion">
                        Todas
                    </label>
                </div>
            </div>
        </div>

        <div class="se-card overflow-hidden">
            <div class="border-b border-accent-200 bg-white px-5 py-4">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="se-section-title">Cursos en el PDF</p>
                        <p class="mt-1 text-sm text-neutral-600">
                            Disponibles a la izquierda; incluidos a la derecha. Doble clic en la lista izquierda pasa la selección.
                            <span class="text-neutral-500">Ctrl o Mayús + clic para varios; luego › o «».</span>
                        </p>
                    </div>
                    <span class="se-pill tabular-nums">{{ $cursosIzquierda->count() }} libres · {{ $cursosDerecha->count() }} en PDF</span>
                </div>
            </div>

            <div class="space-y-4 bg-accent-50/40 p-5 sm:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-stretch">
                    <div class="min-w-0 flex-1 flex flex-col">
                        <label for="lista-cursos-izq" class="form-label">Disponibles</label>
                        <select id="lista-cursos-izq" multiple size="10"
                                wire:model.live="seleccionListaIzq"
                                wire:dblclick="pasarSeleccionADerecha"
                                class="form-select min-h-[220px] font-mono text-sm">
                            @foreach ($cursosIzquierda as $c)
                                <option value="{{ $c->Id }}">{{ $c->nombreParaListado() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-row flex-wrap items-center justify-center gap-2 lg:flex-col lg:justify-center lg:pt-7 shrink-0">
                        <button type="button" wire:click="pasarTodosADerecha" title="Pasar todos a la derecha"
                                class="btn-secondary btn-sm min-w-[2.75rem] px-3 font-mono">»</button>
                        <button type="button" wire:click="pasarSeleccionADerecha" title="Pasar selección a la derecha"
                                class="btn-secondary btn-sm min-w-[2.75rem] px-3 font-mono">›</button>
                        <button type="button" wire:click="pasarSeleccionAIzquierda" title="Pasar selección a la izquierda"
                                class="btn-secondary btn-sm min-w-[2.75rem] px-3 font-mono">‹</button>
                        <button type="button" wire:click="pasarTodosAIzquierda" title="Quitar todos de la derecha"
                                class="btn-secondary btn-sm min-w-[2.75rem] px-3 font-mono">«</button>
                    </div>

                    <div class="min-w-0 flex-1 flex flex-col">
                        <label for="lista-cursos-der" class="form-label">Incluidos en el PDF</label>
                        <select id="lista-cursos-der" multiple size="10"
                                wire:model.live="seleccionListaDer"
                                class="form-select min-h-[220px] font-mono text-sm">
                            @foreach ($cursosDerecha as $c)
                                <option value="{{ $c->Id }}">{{ $c->nombreParaListado() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="se-card overflow-hidden">
            <div class="border-b border-accent-200 bg-white px-5 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="se-section-title">Columnas del PDF</p>
                        <p class="mt-1 text-sm text-neutral-600">Legajos y matrícula. El orden de columnas sigue los grupos abajo.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="seleccionarSoloDefecto" class="btn-secondary btn-sm whitespace-nowrap">
                            Solo apellido, nombre y DNI
                        </button>
                        <button type="button" wire:click="seleccionarTodos" class="btn-secondary btn-sm whitespace-nowrap">
                            Marcar todos
                        </button>
                    </div>
                </div>
            </div>

            <div class="max-h-[22rem] overflow-y-auto border-t border-accent-100 bg-white p-5 sm:p-6">
                <div class="space-y-6">
                    @foreach ($camposPorGrupo as $grupo => $items)
                        <fieldset class="min-w-0 rounded-2xl border border-accent-200 bg-accent-50/50 p-4">
                            <legend class="mb-3 w-full border-b border-accent-200 pb-2 text-[11px] font-bold uppercase tracking-[0.12em] text-primary-700">{{ $grupo }}</legend>
                            <div class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($items as $item)
                                    <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-transparent px-2 py-1.5 text-sm text-neutral-800 transition-colors hover:bg-white/80 hover:border-accent-200/80">
                                        <input type="checkbox"
                                               class="mt-0.5 rounded border-accent-300 text-primary-600 focus:ring-primary-500"
                                               wire:model.live="camposSeleccionados"
                                               value="{{ $item['key'] }}">
                                        <span>{{ $item['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-accent-200 bg-accent-50/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <a class="btn-primary @if(!$this->puedeGenerarPdf()) pointer-events-none opacity-50 @endif"
                       target="_blank"
                       rel="noopener noreferrer"
                       href="{{ $this->pdfUrl }}">
                        Abrir PDF en pestaña nueva
                    </a>
                    @if (!$this->puedeGenerarPdf())
                        <span class="text-sm text-neutral-500">Incluya al menos un curso a la derecha.</span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
