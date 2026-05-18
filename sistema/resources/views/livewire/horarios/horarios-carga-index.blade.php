@php
    use App\Support\HorariosProfesores;
@endphp
<div class="mx-auto w-full max-w-5xl space-y-6">
    <section class="se-hero">
        <div class="se-hero-inner">
            <div class="min-w-0 space-y-2">
                <p class="se-eyebrow">Horarios</p>
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Carga de horarios</h2>
                <p class="max-w-2xl text-sm text-white/80">
                    {{ schoolCtx()->nivelNombre() }} · Ciclo {{ schoolCtx()->terlecAno() }}
                </p>
            </div>
            <a href="{{ route('dashboard') }}"
               class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-white/20">
                Volver al panel
            </a>
        </div>
    </section>

    <div class="se-card px-5 py-5 space-y-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="hor-prof" class="se-section-title">Docente</label>
                <select id="hor-prof" wire:model.live="profesorId" class="form-select mt-2 w-full">
                    <option value="">— Seleccione —</option>
                    @foreach ($profesores as $p)
                        <option value="{{ $p->id }}">{{ $p->label }}</option>
                    @endforeach
                </select>
            </div>
            @if ($profesorId)
                <div>
                    <label for="hor-mat" class="se-section-title">Curso y materia (asignación PPC)</label>
                    <select id="hor-mat" wire:model.live="materiaId" class="form-select mt-2 w-full">
                        <option value="">— Seleccione —</option>
                        @foreach ($asignaciones as $a)
                            <option value="{{ $a->idMateria }}">{{ $a->cursoLabel }} — {{ $a->materia }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        @if ($asignacionActual)
            <p class="text-sm text-neutral-600">
                Cargando horas para <strong>{{ $asignacionActual->materia }}</strong>
                en <strong>{{ $asignacionActual->cursoLabel }}</strong>
                (turno {{ HorariosProfesores::nombreTurnoClase($asignacionActual->idTurnoClase) }}).
            </p>
        @endif
    </div>

    {{--
    Panel depuración SQL — desactivado junto con HorariosCargaIndex (no ejecutar textoDepuracionSql* sin mostrar).

    @if ($this->mostrarPanelSqlDepuracion)
        <div class="se-card overflow-hidden border-dashed border-primary-400/40 bg-accent-50/60">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-accent-200 px-4 py-2.5">
                <h3 class="text-[11px] font-semibold uppercase tracking-[0.12em] text-neutral-600">
                    Consultas SQL · carga de horarios (depuración)
                </h3>
                <button type="button" wire:click="$set('mostrarPanelSqlDepuracion', false)"
                        class="rounded-lg border border-accent-300 bg-white px-2.5 py-1 text-[11px] font-semibold text-neutral-600 hover:bg-accent-50">
                    Ocultar
                </button>
            </div>
            <pre class="max-h-[28rem] overflow-auto whitespace-pre-wrap break-words px-4 py-3 font-mono text-[10px] leading-relaxed text-neutral-800 sm:text-[11px]">{{ $consultaSqlDepuracion ?? '' }}</pre>
        </div>
    @else
        <div class="flex justify-center">
            <button type="button" wire:click="$set('mostrarPanelSqlDepuracion', true)"
                    class="text-sm font-semibold text-primary-700 hover:underline">
                Mostrar consultas SQL (depuración)
            </button>
        </div>
    @endif
    --}}

    @if ($avisoConflicto)
        <div class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-950" role="alert">
            {{ $avisoConflicto }}
        </div>
    @endif

    @if ($this->puedeEditarGrilla())
        <div class="se-card overflow-hidden px-2 py-4 sm:px-4">
            <p class="se-section-title mb-3 px-2">Grilla semanal (marque las horas cátedra)</p>
            <div class="w-full overflow-x-auto">
                <table class="min-w-full border-collapse text-center text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-accent-50">
                            <th class="border border-accent-200 px-2 py-2 font-semibold text-neutral-600">Hora</th>
                            @foreach ($dias as $diaCodigo)
                                <th class="border border-accent-200 px-2 py-2 font-semibold text-neutral-800">
                                    {{ HorariosProfesores::etiquetaDiaLegacy($diaCodigo) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($horas as $h)
                            <tr>
                                <td class="border border-accent-200 bg-accent-50/50 px-2 py-2 font-semibold tabular-nums">{{ $h }}º</td>
                                @foreach ($dias as $diaCodigo)
                                    @php $key = HorariosProfesores::celdaKeyLegacy($diaCodigo, $h); @endphp
                                    <td class="border border-accent-200 p-1" wire:key="hor-celda-{{ $profesorId }}-{{ $materiaId }}-{{ $diaCodigo }}-{{ $h }}">
                                        {{-- wire:key incluye el estado para que Livewire recree el input y el checked se refleje (evita bug de morphdom con @checked + wire:click.prevent). --}}
                                        <input type="checkbox"
                                               class="h-5 w-5 rounded border-accent-300 text-primary-600 focus:ring-primary-500"
                                               wire:key="hor-chk-{{ $profesorId }}-{{ $materiaId }}-{{ $diaCodigo }}-{{ $h }}-{{ ! empty($celdasMarcadas[$key]) ? '1' : '0' }}"
                                               @checked(! empty($celdasMarcadas[$key]))
                                               wire:click.prevent="alternarCelda('{{ $diaCodigo }}', {{ $h }})"
                                               aria-label="{{ HorariosProfesores::etiquetaDiaLegacy($diaCodigo) }} hora {{ $h }}">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-3 px-2 text-xs text-neutral-500">
                Si el curso ya tiene otra materia en ese horario, el sistema avisará y no permitirá la superposición.
                Dos docentes de la misma materia pueden compartir la misma celda (cátedra compartida).
            </p>
        </div>
    @elseif ($profesorId && ! $materiaId)
        <p class="text-sm text-neutral-600">Seleccione curso y materia para ver la grilla.</p>
    @endif

    <div class="flex flex-wrap gap-3">
        <a href="{{ route('horarios.config') }}" class="text-sm font-semibold text-primary-700 hover:underline">Configuración</a>
        <a href="{{ route('horarios.impresion') }}" class="text-sm font-semibold text-primary-700 hover:underline">Impresión →</a>
    </div>
</div>
