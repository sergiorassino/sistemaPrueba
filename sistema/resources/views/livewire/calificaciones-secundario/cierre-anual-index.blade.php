{{-- Cierre anual (secundario): cierre masivo + listado de matrículas del ciclo activo. --}}
<div class="se-cierre-anual-fill">
    <div class="se-cierre-anual-grid se-cierre-anual-grid--listado gap-4">
    <section class="se-hero min-w-0">
        <div class="se-hero-inner flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-2">
                <p class="se-eyebrow">Calificaciones · Secundario</p>
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Cierre anual</h2>
                <p class="text-sm text-white/80">
                    {{ schoolCtx()->nivelNombre() }} · Ciclo lectivo {{ schoolCtx()->terlecAno() }}
                    · Condiciones de matrícula: regulares y salidos (1–4)
                </p>
            </div>
            <a href="{{ route('dashboard') }}"
               class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver al panel
            </a>
        </div>
    </section>

    @error('cierre')
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
            {{ $message }}
        </div>
    @enderror

    <div class="se-card max-h-[min(42vh,26rem)] min-w-0 overflow-y-auto p-0">
        <div class="border-b border-accent-200 bg-accent-50 px-5 py-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-neutral-500">Cierre masivo del ciclo lectivo</p>
            @php($anoCierre = schoolCtx()->terlecAno() ?? '—')
            <p class="mt-1 max-w-3xl text-sm text-neutral-600">
                Procesa todas las calificaciones del secundario vinculadas a matrícula en el ciclo lectivo activo.
                <span class="mt-2 flex flex-col gap-1 font-bold text-neutral-800 lg:flex-row lg:flex-wrap lg:items-baseline lg:gap-x-2">
                    <span>Verifique que está cerrando un año lectivo que ya tiene las calificaciones completas.</span>
                    <span class="whitespace-nowrap tabular-nums">Año lectivo que está por cerrar: {{ $anoCierre }}</span>
                </span>
            </p>
            <div class="mt-4 flex flex-wrap gap-3">
                @if (! $confirmarDic)
                    <button type="button"
                            wire:click="solicitarCierreDic"
                            class="btn-secondary border-primary-200 text-primary-800 hover:border-primary-300">
                        Pasar materias APROBADAS al Matriz (Dic)
                    </button>
                @else
                    <div class="w-full rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                        <p class="font-semibold">Confirmar cierre de diciembre</p>
                        <p class="mt-3 flex flex-col gap-1 rounded-lg border border-amber-300 bg-amber-100/90 px-3 py-2.5 text-sm font-bold uppercase leading-snug tracking-wide text-amber-950 lg:flex-row lg:flex-wrap lg:items-baseline lg:gap-x-2">
                            <span>Verifique que está cerrando un año lectivo que ya tiene las calificaciones completas.</span>
                            <span class="whitespace-nowrap tabular-nums normal-case">Año lectivo que está por cerrar: {{ $anoCierre }}</span>
                        </p>
                        <p class="mt-2">Se pasarán al matriz las materias aprobadas (promedio anual ≥ 7 o coloquio de diciembre ≥ 7).</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" wire:click="ejecutarCierreDic" wire:loading.attr="disabled" class="btn-primary">
                                <span wire:loading.remove wire:target="ejecutarCierreDic">Confirmar</span>
                                <span wire:loading wire:target="ejecutarCierreDic">Procesando…</span>
                            </button>
                            <button type="button" wire:click="cancelarCierreDic" class="btn-secondary">Cancelar</button>
                        </div>
                    </div>
                @endif

                @if (! $confirmarFeb)
                    <button type="button"
                            wire:click="solicitarCierreFeb"
                            class="btn-secondary border-primary-200 text-primary-800 hover:border-primary-300">
                        Pasar APROBADAS al Matriz y REPROBADAS como Previas (Feb)
                    </button>
                @else
                    <div class="w-full rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                        <p class="font-semibold">Confirmar cierre de febrero</p>
                        <p class="mt-3 flex flex-col gap-1 rounded-lg border border-amber-300 bg-amber-100/90 px-3 py-2.5 text-sm font-bold uppercase leading-snug tracking-wide text-amber-950 lg:flex-row lg:flex-wrap lg:items-baseline lg:gap-x-2">
                            <span>Verifique que está cerrando un año lectivo que ya tiene las calificaciones completas.</span>
                            <span class="whitespace-nowrap tabular-nums normal-case">Año lectivo que está por cerrar: {{ $anoCierre }}</span>
                        </p>
                        <p class="mt-2">
                            Aprobadas (promedio ≥ 7 o coloquio dic/feb ≥ 7) pasan al matriz; el resto queda como previa (<code class="text-xs">apro = 1</code>, <code class="text-xs">condAdeuda = PR</code>).
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" wire:click="ejecutarCierreFeb" wire:loading.attr="disabled" class="btn-primary">
                                <span wire:loading.remove wire:target="ejecutarCierreFeb">Confirmar</span>
                                <span wire:loading wire:target="ejecutarCierreFeb">Procesando…</span>
                            </button>
                            <button type="button" wire:click="cancelarCierreFeb" class="btn-secondary">Cancelar</button>
                        </div>
                    </div>
                @endif
            </div>

            @if (! empty($informeCierre))
                <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50/80 px-4 py-4" role="status" wire:key="informe-cierre-{{ $informeCierre['operacion'] }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0 space-y-1">
                            <p class="text-sm font-semibold text-emerald-950">{{ $informeCierre['titulo'] }}</p>
                            <p class="text-xs text-emerald-900/80">
                                {{ $informeCierre['nivel'] }} · Ciclo lectivo {{ $informeCierre['ano_lectivo'] }}
                                · Alcance: todas las calificaciones con matrícula del ciclo activo
                            </p>
                        </div>
                        <button type="button"
                                wire:click="cerrarInformeCierre"
                                class="btn-secondary btn-sm shrink-0 border-emerald-200 text-emerald-900 hover:bg-white">
                            Cerrar informe
                        </button>
                    </div>
                    <dl class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl border border-emerald-200/80 bg-white px-3 py-2.5">
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-neutral-500">Registros procesados</dt>
                            <dd class="mt-0.5 text-xl font-bold tabular-nums text-neutral-900">{{ $informeCierre['procesados'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-emerald-200/80 bg-white px-3 py-2.5">
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-neutral-500">Registros actualizados</dt>
                            <dd class="mt-0.5 text-xl font-bold tabular-nums text-emerald-800">{{ $informeCierre['actualizados'] }}</dd>
                        </div>
                        <div class="rounded-xl border border-emerald-200/80 bg-white px-3 py-2.5">
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-neutral-500">Pasados al matriz</dt>
                            <dd class="mt-0.5 text-xl font-bold tabular-nums text-neutral-900">{{ $informeCierre['aprobados'] }}</dd>
                        </div>
                        @if (($informeCierre['operacion'] ?? '') === 'feb')
                            <div class="rounded-xl border border-emerald-200/80 bg-white px-3 py-2.5">
                                <dt class="text-[10px] font-semibold uppercase tracking-wide text-neutral-500">Marcados como previa</dt>
                                <dd class="mt-0.5 text-xl font-bold tabular-nums text-amber-800">{{ $informeCierre['previas'] }}</dd>
                            </div>
                        @endif
                        <div class="rounded-xl border border-emerald-200/80 bg-white px-3 py-2.5 {{ ($informeCierre['operacion'] ?? '') === 'feb' ? 'sm:col-span-2 lg:col-span-1' : '' }}">
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-neutral-500">Sin cambio</dt>
                            <dd class="mt-0.5 text-xl font-bold tabular-nums text-neutral-600">{{ $informeCierre['omitidos'] }}</dd>
                            <p class="mt-1 text-[10px] leading-snug text-neutral-500">No aprobados (Dic) o ya cerrados / sin actualización</p>
                        </div>
                    </dl>
                </div>
            @endif
        </div>
    </div>

    <div class="se-toolbar flex-col !items-stretch gap-4 lg:flex-row lg:items-end">
        <div class="min-w-0 flex-1">
            <label for="se-cierre-anual-buscar" class="form-label">Buscar alumno</label>
            <input id="se-cierre-anual-buscar"
                   type="search"
                   wire:model.live.debounce.350ms="buscar"
                   class="form-input mt-1.5 w-full max-w-md"
                   placeholder="Apellido, nombre o DNI (mín. 2 caracteres)"
                   autocomplete="off">
        </div>
        <p class="se-pill tabular-nums shrink-0">{{ count($alumnos) }} estudiante{{ count($alumnos) === 1 ? '' : 's' }}</p>
    </div>

    <div class="se-card flex min-h-0 min-w-0 flex-col p-0">
        <div class="se-cierre-anual-grilla">
            <div class="se-cierre-anual-head-wrap"
                 data-se-cierre-head>
                <table class="se-cierre-anual-tabla w-full table-fixed">
                    <colgroup>
                        <col style="width:38%">
                        <col style="width:12%">
                        <col style="width:22%">
                        <col style="width:18%">
                        <col style="width:10rem">
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col" class="!px-4 text-left">Apellido y nombre</th>
                            <th scope="col" class="!px-4 text-left">DNI</th>
                            <th scope="col" class="!px-4 text-left">Curso</th>
                            <th scope="col" class="!px-4 text-left">Condición</th>
                            <th scope="col" class="!px-3 text-right">Acción</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="se-cierre-anual-body-wrap"
                 tabindex="0"
                 data-se-cierre-body>
                <table class="se-cierre-anual-tabla w-full table-fixed divide-y divide-accent-100">
                    <colgroup>
                        <col style="width:38%">
                        <col style="width:12%">
                        <col style="width:22%">
                        <col style="width:18%">
                        <col style="width:10rem">
                    </colgroup>
                    <tbody class="bg-white">
                        @forelse ($alumnos as $a)
                            <tr class="hover:bg-accent-50/60" wire:key="cierre-anual-{{ $a['idLegajos'] }}">
                                <td class="!px-4 !py-2.5 font-medium text-neutral-800">
                                    {{ $a['apellido'] }}, {{ $a['nombre'] }}
                                </td>
                                <td class="!px-4 !py-2.5 whitespace-nowrap tabular-nums text-neutral-700">{{ $a['dni'] !== '' ? $a['dni'] : '—' }}</td>
                                <td class="!px-4 !py-2.5 text-neutral-700">{{ $a['curso'] !== '' ? $a['curso'] : '—' }}</td>
                                <td class="!px-4 !py-2.5 text-neutral-600">{{ $a['condicion'] !== '' ? $a['condicion'] : '—' }}</td>
                                <td class="!px-3 !py-2.5 text-right">
                                    <a href="{{ route('calificacionesSecundario.cierreAnual.historial', ['idLegajos' => $a['idLegajos']]) }}"
                                       wire:navigate
                                       class="btn-secondary btn-sm">
                                        Ver historial
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="!px-5 !py-10 text-center text-sm text-neutral-500">
                                    No hay matrículas para el ciclo lectivo actual con las condiciones indicadas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</div>
