<div class="mx-auto w-full max-w-4xl space-y-6">
    <section class="se-hero">
        <div class="se-hero-inner">
            <div class="min-w-0 space-y-2">
                <p class="se-eyebrow">Horarios</p>
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Impresión de horarios</h2>
                <p class="max-w-2xl text-sm text-white/80">
                    PDF A4 apaisado: una hoja por turno activo del establecimiento.
                </p>
            </div>
            <a href="{{ route('dashboard') }}"
               class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-white/20">
                Volver al panel
            </a>
        </div>
    </section>

    <div class="se-card px-5 py-5 space-y-4">
        <p class="se-section-title">Tipo de listado</p>
        <div class="flex flex-wrap gap-4">
            <label class="inline-flex items-center gap-2 text-sm font-medium">
                <input type="radio" wire:model.live="modo" value="curso" class="text-primary-600 focus:ring-primary-500">
                Por curso
            </label>
            <label class="inline-flex items-center gap-2 text-sm font-medium">
                <input type="radio" wire:model.live="modo" value="profesor" class="text-primary-600 focus:ring-primary-500">
                Por docente
            </label>
        </div>

        @if ($modo === 'curso')
            <div>
                <label for="imp-curso" class="se-section-title">Curso</label>
                <select id="imp-curso" wire:model.live="cursoId" class="form-select mt-2 w-full max-w-md">
                    <option value="">— Seleccione —</option>
                    @foreach ($cursos as $c)
                        <option value="{{ $c->Id }}">{{ $c->nombreParaListado() }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div>
                <label for="imp-prof" class="se-section-title">Docente</label>
                <select id="imp-prof" wire:model.live="profesorId" class="form-select mt-2 w-full max-w-md">
                    <option value="">— Seleccione —</option>
                    @foreach ($profesores as $p)
                        <option value="{{ $p->id }}">{{ $p->label }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    @if ($pdfUrl)
        <div class="se-card px-5 py-5">
            <a href="{{ $pdfUrl }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                Abrir PDF del horario
            </a>
        </div>
    @endif

    {{--
    Panel depuración SQL — desactivado junto con HorariosImpresionIndex.

    @if ($modo === 'curso' && ($consultaSqlImpresionCurso ?? '') !== '')
        @if ($this->mostrarPanelSqlImpresionCurso)
            <div class="se-card overflow-hidden border border-neutral-200">
                <div class="flex items-center justify-between gap-3 border-b border-neutral-100 bg-neutral-50 px-4 py-3">
                    <p class="text-sm font-semibold text-neutral-800">Depuración SQL (impresión por curso)</p>
                    <button type="button" wire:click="$set('mostrarPanelSqlImpresionCurso', false)"
                            class="shrink-0 text-xs font-medium text-neutral-600 hover:text-neutral-900">
                        Ocultar
                    </button>
                </div>
                <pre class="max-h-[28rem] overflow-auto whitespace-pre-wrap break-words px-4 py-3 font-mono text-[10px] leading-relaxed text-neutral-800 sm:text-[11px]">{{ $consultaSqlImpresionCurso }}</pre>
            </div>
        @else
            <button type="button" wire:click="$set('mostrarPanelSqlImpresionCurso', true)"
                    class="text-sm font-medium text-primary-700 hover:underline">
                Mostrar depuración SQL (impresión por curso)
            </button>
        @endif
    @endif
    --}}

    <div class="flex flex-wrap gap-3 text-sm">
        <a href="{{ route('horarios.carga') }}" class="font-semibold text-primary-700 hover:underline">Carga</a>
        <a href="{{ route('horarios.config') }}" class="font-semibold text-primary-700 hover:underline">Configuración</a>
    </div>
</div>
