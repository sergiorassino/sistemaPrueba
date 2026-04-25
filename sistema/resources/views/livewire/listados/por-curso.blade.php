<x-form-shell maxWidth="max-w-5xl">
    <div class="card p-6">
        <h1 class="mb-4 w-full text-center text-lg font-semibold text-gray-800">Listado de alumnos por curso</h1>

        @if ($cursos->isEmpty())
            <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
                No hay cursos cargados para este nivel y año lectivo.
            </p>
        @else
            <div class="space-y-6">
                <div>
                    <span class="block text-sm font-medium text-gray-700 mb-2">Condición de matrícula en el listado</span>
                    <div class="flex flex-wrap gap-4 mb-4 text-sm text-gray-800">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="filtro-condicion" value="regulares" class="text-[#40848D] focus:ring-[#40848D]"
                                   wire:model.live="filtroCondicion">
                            <span class="font-medium">REGULARES</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="filtro-condicion" value="salidos" class="text-[#40848D] focus:ring-[#40848D]"
                                   wire:model.live="filtroCondicion">
                            <span class="font-medium">SALIDOS</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="filtro-condicion" value="todos" class="text-[#40848D] focus:ring-[#40848D]"
                                   wire:model.live="filtroCondicion">
                            <span class="font-medium">TODAS LAS CONDICIONES</span>
                        </label>
                    </div>

                    <span class="block text-sm font-medium text-gray-700 mb-2">Cursos a incluir en el PDF</span>
                    <div>
                        <p class="text-xs text-gray-600 mb-2">
                            Cursos disponibles a la izquierda; los incluidos en el PDF a la derecha.
                            <strong>Doble clic</strong> en la lista izquierda pasa a la derecha lo seleccionado (un curso o varios si ya los marcó).
                            Use <kbd class="px-1 py-0.5 rounded bg-gray-200 text-gray-800 font-mono text-[10px]">Ctrl</kbd> o
                            <kbd class="px-1 py-0.5 rounded bg-gray-200 text-gray-800 font-mono text-[10px]">Mayús</kbd> + clic para marcar varios y pasarlos en lote con el botón ›.
                        </p>

                        <div class="flex flex-col sm:flex-row gap-3 items-stretch">
                            <div class="flex-1 min-w-0 flex flex-col">
                                <label for="lista-cursos-izq" class="text-xs font-medium text-gray-600 mb-1">Disponibles</label>
                                <select id="lista-cursos-izq" multiple size="10"
                                        wire:model.live="seleccionListaIzq"
                                        wire:dblclick="pasarSeleccionADerecha"
                                        class="form-select w-full min-h-[220px] text-sm font-mono py-1">
                                    @foreach ($cursosIzquierda as $c)
                                        <option value="{{ $c->Id }}">{{ $c->nombreParaListado() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex sm:flex-col flex-row flex-wrap justify-center gap-2 sm:justify-center sm:pt-6 shrink-0">
                                <button type="button" wire:click="pasarTodosADerecha" title="Pasar todos a la derecha"
                                        class="px-2.5 py-1.5 text-sm rounded border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 min-w-[2.5rem]">
                                    »
                                </button>
                                <button type="button" wire:click="pasarSeleccionADerecha" title="Pasar selección a la derecha"
                                        class="px-2.5 py-1.5 text-sm rounded border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 min-w-[2.5rem]">
                                    ›
                                </button>
                                <button type="button" wire:click="pasarSeleccionAIzquierda" title="Pasar selección a la izquierda"
                                        class="px-2.5 py-1.5 text-sm rounded border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 min-w-[2.5rem]">
                                    ‹
                                </button>
                                <button type="button" wire:click="pasarTodosAIzquierda" title="Quitar todos de la derecha"
                                        class="px-2.5 py-1.5 text-sm rounded border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 min-w-[2.5rem]">
                                    «
                                </button>
                            </div>

                            <div class="flex-1 min-w-0 flex flex-col">
                                <label for="lista-cursos-der" class="text-xs font-medium text-gray-600 mb-1">Incluidos en el PDF</label>
                                <select id="lista-cursos-der" multiple size="10"
                                        wire:model.live="seleccionListaDer"
                                        class="form-select w-full min-h-[220px] text-sm font-mono py-1">
                                    @foreach ($cursosDerecha as $c)
                                        <option value="{{ $c->Id }}">{{ $c->nombreParaListado() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <span class="text-sm font-medium text-gray-700">Campos del listado (tablas legajos y matrícula)</span>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="seleccionarSoloDefecto" class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">
                                Solo apellido, nombre y DNI
                            </button>
                            <button type="button" wire:click="seleccionarTodos" class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">
                                Marcar todos
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">
                        Elija las columnas que desea ver en el PDF. El orden de las columnas en el archivo sigue el orden en que aparecen los grupos abajo.
                    </p>

                    <div class="max-h-80 overflow-y-auto border border-gray-200 rounded-lg p-4 bg-gray-50/80 space-y-4">
                        @foreach ($camposPorGrupo as $grupo => $items)
                            <fieldset class="min-w-0">
                                <legend class="text-xs font-semibold text-[#40848D] uppercase tracking-wide mb-2">{{ $grupo }}</legend>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-3 gap-y-1.5">
                                    @foreach ($items as $item)
                                        <label class="flex items-start gap-2 text-sm text-gray-700 cursor-pointer">
                                            <input type="checkbox" class="mt-0.5 rounded border-gray-300 text-[#40848D] focus:ring-[#40848D]"
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

                <div class="flex flex-wrap items-center gap-3">
                    <a class="btn-primary inline-flex items-center justify-center @if(!$this->puedeGenerarPdf()) pointer-events-none opacity-50 @endif"
                       target="_blank"
                       rel="noopener noreferrer"
                       href="{{ $this->pdfUrl }}">
                        Abrir PDF en pestaña nueva
                    </a>
                    @if (!$this->puedeGenerarPdf())
                        <span class="text-sm text-gray-500">Mueva al menos un curso al panel derecho.</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-form-shell>
