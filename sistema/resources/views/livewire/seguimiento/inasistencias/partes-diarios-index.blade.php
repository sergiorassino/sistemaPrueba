<div class="se-page max-w-6xl">
    @php
        use App\Support\HorariosProfesores;
    @endphp
    <section class="se-hero">
        <div class="se-hero-inner">
            <div class="min-w-0 space-y-2">
                <p class="se-eyebrow">Inasistencias estudiantes</p>
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Parte diario del preceptor</h2>
                <p class="text-sm text-white/80">
                    {{ schoolCtx()->nivelNombre() }} · Año lectivo {{ schoolCtx()->terlecAno() }}
                </p>
            </div>
        </div>
    </section>

    <div class="se-card overflow-hidden">
        <div class="border-b border-accent-200 bg-white px-5 py-4">
            <p class="text-sm text-neutral-700">
                Genere el impreso en PDF por uno, varios o todos los cursos de la misma fecha. El día de la semana y el
                horario mostrado se toman de esa fecha. Los espacios curriculares y docentes provienen del horario cargado
                para el ciclo lectivo actual.
            </p>
        </div>

        <div class="bg-white px-5 py-4 space-y-6">
            <div>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="se-section-title">Cursos a incluir</p>
                        <p class="mt-1 text-sm text-neutral-600">
                            Marcá uno o más cursos. Cada curso genera su propia hoja en el mismo PDF.
                        </p>
                    </div>
                    <span class="se-pill tabular-nums">
                        {{ $cantidadSeleccionados }} de {{ $cursos->count() }} seleccionados
                    </span>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button"
                            wire:click="seleccionarTodosCursos"
                            class="inline-flex items-center rounded-lg border border-accent-200 bg-white px-3 py-1.5 text-sm font-semibold text-neutral-700 shadow-sm transition hover:bg-accent-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        Todos
                    </button>
                    <button type="button"
                            wire:click="quitarTodosCursos"
                            class="inline-flex items-center rounded-lg border border-accent-200 bg-white px-3 py-1.5 text-sm font-semibold text-neutral-700 shadow-sm transition hover:bg-accent-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                        Ninguno
                    </button>
                </div>

                @if ($cursos->isEmpty())
                    <p class="mt-4 text-sm text-neutral-600">No hay cursos en este nivel y ciclo lectivo.</p>
                @else
                    <div class="mt-4 max-h-72 overflow-y-auto rounded-xl border border-accent-200 bg-accent-50/30 p-3">
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($cursos as $c)
                                <label class="inline-flex cursor-pointer items-center gap-2.5 rounded-lg border border-transparent px-2 py-1.5 text-sm text-neutral-800 transition hover:border-accent-200 hover:bg-white">
                                    <input type="checkbox"
                                           class="rounded border-accent-300 text-primary-600 focus:ring-primary-500"
                                           wire:model.live="cursosSeleccionados"
                                           value="{{ $c->Id }}">
                                    <span class="font-medium">{{ $c->nombreParaListado() }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid min-w-0 grid-cols-1 gap-4 md:grid-cols-2">
                @if ($mostrarSelectorTurno)
                    <div>
                        <label for="se-partes-turno" class="form-label">Turno</label>
                        <select id="se-partes-turno"
                                wire:model.live="turnoElegido"
                                class="form-select mt-1.5">
                            <option value="">— Primer turno (predeterminado) —</option>
                            @foreach ($turnosCurso as $tid)
                                <option value="{{ $tid }}">{{ HorariosProfesores::nombreTurnoClase($tid) }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-neutral-500">
                            Solo aplica al único curso seleccionado.
                        </p>
                    </div>
                @endif
                <div class="{{ $mostrarSelectorTurno ? '' : 'md:col-span-2' }}">
                    <label for="se-partes-fecha" class="form-label">Fecha en el impreso</label>
                    <input id="se-partes-fecha"
                           type="date"
                           wire:model.live="fecha"
                           class="form-input mt-1.5 w-full max-w-xs rounded-xl border border-accent-200 px-3 py-2 text-sm text-neutral-800">
                    @if ($etiquetaDiaFecha)
                        <p class="mt-1.5 text-xs text-neutral-500">
                            Horario del día: <span class="font-medium text-neutral-700">{{ $etiquetaDiaFecha }}</span>
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-2 border-t border-accent-100 pt-4">
                @if ($puedeGenerarPdf && $pdfUrl)
                    <a class="btn-primary inline-flex items-center gap-2"
                       target="_blank"
                       rel="noopener noreferrer"
                       href="{{ $pdfUrl }}">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        @if ($cantidadSeleccionados > 1)
                            Imprimir {{ $cantidadSeleccionados }} partes (PDF)
                        @else
                            Descargar / ver PDF
                        @endif
                    </a>
                @else
                    <button type="button" class="btn-primary opacity-50 pointer-events-none" disabled>
                        Descargar / ver PDF
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
