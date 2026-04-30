<div class="se-page">
    <section class="se-hero">
        <div class="se-hero-inner">
            <div class="min-w-0 space-y-3">
                <p class="se-eyebrow">Comunicaciones</p>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Bandeja de comunicados</h2>
                    <p class="mt-2 max-w-2xl text-sm text-white/80">
                        {{ schoolCtx()->nivelNombre() }} · Ciclo lectivo {{ schoolCtx()->terlecAno() }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <span class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white/85">
                    <span class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-white/50">En esta vista</span>
                    <span class="text-xl font-bold tabular-nums">{{ $hilos->count() }}</span>
                </span>
                @if (tienePermiso(52))
                    <a href="{{ route('comunicaciones.nuevo') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-primary-700 shadow-sm transition hover:bg-accent-100 focus:outline-none focus:ring-2 focus:ring-white/60">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nuevo comunicado
                    </a>
                @endif
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

    <div class="se-toolbar">
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-neutral-500">Filtrar</p>
        <div class="flex flex-wrap gap-2">
            @foreach (['todos' => 'Todos', 'no_leidos' => 'No leídos', 'respondidos' => 'Respondidos'] as $val => $label)
                <button type="button"
                        wire:click="$set('filtro', '{{ $val }}')"
                        @class([
                            'inline-flex cursor-pointer items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
                            'border-primary-500 bg-primary-600 text-white' => $filtro === $val,
                            'border-accent-200 bg-white text-neutral-700 hover:bg-accent-50' => $filtro !== $val,
                        ])>
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($hilos as $hilo)
            @php
                $noLeidos = (int) $hilo->no_leidos;
                $respondidos = (int) $hilo->respondidos;
                $estado = $respondidos > 0 ? 'respondido' : ($noLeidos > 0 ? 'no_leido' : 'leido');
            @endphp
            <a href="{{ route('comunicaciones.hilo', $hilo->id) }}"
               @class([
                   'se-card block p-4 transition hover:shadow-md sm:p-5',
                   'border-l-4 border-l-primary-600 bg-primary-50/35' => $estado === 'no_leido',
                   'border-l-4 border-l-primary-400 bg-accent-50/80' => $estado === 'respondido',
               ])>
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($estado === 'no_leido')
                                <span class="inline-flex shrink-0 items-center rounded-full border border-primary-300 bg-primary-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-primary-800">
                                    {{ $noLeidos }} no leído{{ $noLeidos > 1 ? 's' : '' }}
                                </span>
                            @elseif ($estado === 'respondido')
                                <span class="se-pill border-primary-200 bg-primary-50 text-primary-800">
                                    Respondido
                                </span>
                            @endif
                            @if ($hilo->creado_por_tipo === 'profesor' && ! ($hilo->familia_puede_responder ?? true))
                                <span class="inline-flex shrink-0 items-center rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-900">
                                    Solo informativo
                                </span>
                            @endif
                            <span class="text-sm font-semibold text-neutral-900">{{ $hilo->asunto }}</span>
                        </div>
                        <div class="mt-1.5 flex flex-wrap gap-x-3 gap-y-0.5 text-xs text-neutral-500">
                            <span>{{ \App\Models\ComHilo::make(['scope' => $hilo->scope])->scopeLabel() }}</span>
                            @if ($hilo->estado === 'cerrado')
                                <span>· Cerrado</span>
                            @endif
                        </div>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-xs text-neutral-400">
                            {{ $hilo->ultimo_mensaje_at ? \Carbon\Carbon::parse($hilo->ultimo_mensaje_at)->diffForHumans() : '' }}
                        </p>
                    </div>
                </div>
            </a>
        @empty
            <div class="se-card p-10">
                <div class="flex flex-col items-center justify-center gap-3 text-center sm:flex-row sm:text-left">
                    <div class="se-icon-badge h-14 w-14">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-neutral-800">Bandeja vacía</p>
                        <p class="mt-1 text-sm text-neutral-600">No hay comunicados con este filtro.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
