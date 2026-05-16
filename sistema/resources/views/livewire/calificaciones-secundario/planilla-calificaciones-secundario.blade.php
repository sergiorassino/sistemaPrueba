{{-- Planilla de calificaciones por curso y materia (PDF). --}}
<div class="mx-auto w-full max-w-4xl space-y-6">
    <section class="se-hero">
        <div class="se-hero-inner">
            <div class="min-w-0 space-y-2">
                <p class="se-eyebrow">Calificaciones · Secundario</p>
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Planilla de calificaciones</h2>
                <p class="max-w-2xl text-sm text-white/80">
                    {{ schoolCtx()->nivelNombre() }} · Ciclo lectivo {{ schoolCtx()->terlecAno() }}
                </p>
            </div>
            <a href="{{ route('dashboard') }}"
               class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver al panel
            </a>
        </div>
    </section>

    <div class="se-toolbar flex-col !items-stretch gap-4 lg:flex-row lg:items-end">
        <div class="grid min-w-0 flex-1 grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="se-planilla-curso" class="form-label">Curso</label>
                <select id="se-planilla-curso" wire:model.live="cursoId" class="form-select w-full mt-1.5">
                    <option value="">— Seleccione —</option>
                    @foreach ($cursos as $c)
                        <option value="{{ $c->Id }}">{{ $c->nombreParaListado() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="se-planilla-materia" class="form-label">Materia</label>
                <select id="se-planilla-materia" wire:model.live="materiaId" class="form-select mt-1.5 w-full" @disabled(! $cursoId)>
                    <option value="">— Seleccione —</option>
                    @foreach ($materias as $m)
                        <option value="{{ $m->id }}">{{ trim((string) ($m->materia ?? '')) !== '' ? $m->materia : ('ID ' . $m->id) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if ($cursoId && $materiaId && $pdfUrl)
        <div class="se-card space-y-4 px-5 py-5">
            <p class="text-sm text-neutral-700">
                <span class="font-semibold text-neutral-900">{{ $cursoLabel ?? '—' }}</span>
                <span class="mx-1.5 text-neutral-400">·</span>
                <span class="font-semibold text-neutral-900">{{ $materiaLabel ?? '—' }}</span>
            </p>
            <p class="text-sm text-neutral-600">
                Todos los estudiantes del curso entran en <strong>una sola hoja</strong> A4; el alto de fila se ajusta automáticamente. Los bloques en gris indican módulos desaprobados (ninguna nota del bloque alcanza 7).
                El docente se obtiene de la asignación en profesores por curso (<code class="text-xs">ppc</code>).
            </p>
            <a href="{{ $pdfUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir planilla (PDF)
            </a>
        </div>
    @else
        <div class="se-card px-5 py-8">
            <p class="text-center text-sm text-neutral-600 sm:text-left">
                Seleccioná un curso y una materia para generar la planilla en PDF.
            </p>
        </div>
    @endif
</div>
