{{-- Módulo calificacionesSecundario: consulta institucional del boletín (PDF compartido con autogestión). --}}
{{-- Preferir config(): evita depender del helper en entornos con autoload distinto al local. Monte Cristo: TENANT_SLUG=montecristo --}}
<div class="mx-auto w-full max-w-5xl space-y-6">
    <section class="se-hero">
        <div class="se-hero-inner">
            <div class="min-w-0 space-y-2">
                <p class="se-eyebrow">Calificaciones · Secundario</p>
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Consulta de calificaciones</h2>
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
        <div class="min-w-0 flex-1">
            <label for="se-consulta-calif-curso" class="form-label">Curso</label>
            <select id="se-consulta-calif-curso" wire:model.live="cursoId" class="form-select mt-1.5 w-full max-w-xl">
                <option value="">— Seleccione —</option>
                @foreach ($cursos as $c)
                    <option value="{{ $c->Id }}">{{ $c->nombreParaListado() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($cursoId)
        <div class="se-card overflow-hidden p-0">
            <div class="border-b border-accent-200 bg-accent-50 px-5 py-3">
                <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Estudiantes del curso</p>
                <p class="text-sm text-neutral-600">Abre el boletín en PDF (mismo formato que en autogestión del estudiante).</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-accent-200 text-sm">
                    <thead class="bg-white">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-neutral-500">Apellido y nombre</th>
                            @if (config('tenant.slug') === 'montecristo')
                                <th scope="col" class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-neutral-500">D.N.I.</th>
                                <th scope="col" class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-neutral-500">Fecha de nacimiento</th>
                            @endif
                            <th scope="col" class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-neutral-500">Boletín</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-accent-100 bg-white">
                        @forelse ($matriculas as $mat)
                            <tr class="hover:bg-accent-50/60">
                                <td class="px-5 py-3 font-medium text-neutral-800">
                                    {{ trim((string) ($mat->legajo?->nombre_completo ?? '')) === '' ? '—' : $mat->legajo->nombre_completo }}
                                </td>
                                @if (config('tenant.slug') === 'montecristo')
                                    <td class="px-5 py-3 text-neutral-600">{{ trim((string) ($mat->legajo?->dni ?? '')) !== '' ? $mat->legajo->dni : '—' }}</td>
                                    <td class="px-5 py-3 text-neutral-600">
                                        {{ $mat->legajo?->fechnaci ? $mat->legajo->fechnaci->format('d/m/Y') : '—' }}
                                    </td>
                                @endif
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('calificacionesSecundario.consulta.pdf', ['matricula' => $mat->id]) }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="inline-flex max-w-full items-center justify-center gap-1.5 rounded-xl border border-accent-200 bg-white px-3 py-2 text-xs font-semibold text-primary-700 shadow-sm transition hover:border-primary-300 hover:bg-accent-50">
                                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        @if (config('tenant.slug') === 'montecristo')
                                            <span class="text-left leading-snug whitespace-normal lg:whitespace-nowrap">CONSULTA DE CALIFICACIONES</span>
                                        @else
                                            Boletín
                                        @endif
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ config('tenant.slug') === 'montecristo' ? 4 : 2 }}" class="px-5 py-10 text-center text-sm text-neutral-500">
                                    No hay matrículas en este curso para el ciclo lectivo actual.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
