{{-- Módulo: Carga de calificaciones (UI). Guardado vía `saveCell`: TEA con `wire:change`; el resto de inputs numéricos con delegación `focusout` en `tbody` (validación de notas permitidas en el navegador, ver `app.js`). --}}
<div class="mx-auto w-full max-w-[98rem] space-y-6">
    <section class="se-hero">
        <div class="se-hero-inner">
            <div class="min-w-0 space-y-2">
                <p class="se-eyebrow">Calificaciones</p>
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Carga de calificaciones</h2>
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

    {{-- Paso 1/2: selección de curso y materia. `wire:model.live` dispara los `updated*()` del componente. --}}
    <div class="se-toolbar flex-col !items-stretch gap-4 lg:flex-row lg:items-end">
        <div class="grid min-w-0 flex-1 grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="se-calif-curso" class="form-label">Curso</label>
                <select id="se-calif-curso" wire:model.live="cursoId" class="form-select w-full mt-1.5">
                    <option value="">— Seleccione —</option>
                    @foreach ($cursos as $c)
                        <option value="{{ $c->Id }}">{{ $c->nombreParaListado() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="se-calif-materia" class="form-label">Materia</label>
                <select id="se-calif-materia" wire:model.live="materiaId" class="form-select mt-1.5 w-full" @disabled(! $cursoId)>
                    <option value="">— Seleccione —</option>
                    @foreach ($materias as $m)
                        <option value="{{ $m->id }}">{{ trim((string) ($m->materia ?? '')) !== '' ? $m->materia : ('ID ' . $m->id) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if ($cursoId && $materiaId)
        <div class="se-card px-5 py-3">
            <p class="text-sm text-neutral-600">
                <span class="font-semibold text-neutral-800">{{ $cursoLabel ?? '—' }}</span>
                <span class="mx-1.5 text-neutral-400">·</span>
                <span class="font-semibold text-neutral-800">{{ $materiaLabel ?? '—' }}</span>
                <span class="mt-1 block text-xs text-neutral-500 sm:mt-0 sm:inline sm:before:mx-2 sm:before:content-['·']">
                    Los datos se guardan al salir de cada celda.
                </span>
            </p>
        </div>

        {{-- Grilla tipo planilla: scroll horizontal desde la izquierda (sidebar). --}}
        <div class="se-card overflow-hidden p-2 sm:p-3">
            <div class="w-full overflow-x-auto">
                <div class="flex justify-start">
                    <div class="min-w-max rounded-xl border border-accent-200 bg-white shadow-sm">
                <table class="w-full border-collapse table-fixed text-[10px] leading-none">
                    <colgroup>
                        <col style="width:36px">
                        <col style="width:120px">
                        @for ($e = 1; $e <= 8; $e++)
                            <col style="width:18px">
                            <col style="width:18px">
                            <col style="width:18px">
                            @if ($e < 8)
                                {{-- Separación visual entre bloques (columna vacía muy angosta). --}}
                                <col style="width:3px">
                            @endif
                        @endfor
                        <col style="width:3px">
                        <col style="width:18px">
                        <col style="width:18px">
                        <col style="width:3px">
                        <col style="width:18px">
                        <col style="width:18px">
                        <col style="width:3px">
                        <col style="width:20px">
                        <col style="width:20px">
                        {{-- Pr.Final: ancho extra (promedio, solo lectura). --}}
                        <col style="width:26px">
                        <col style="width:18px">
                    </colgroup>
                    {{-- Encabezado en 2 filas: títulos de bloque + subcolumnas (N/R1/R2, etc.). --}}
                    <thead class="sticky top-0 z-[1] bg-accent-50 text-neutral-900 shadow-sm shadow-neutral-900/5">
                        <tr class="text-[10px] leading-tight">
                            <th class="border border-accent-200 px-1 py-1 text-center w-[36px]">Ord</th>
                            <th class="border border-accent-200 px-1 py-1 text-left w-[120px]">Estudiante</th>
                            @for ($e = 1; $e <= 8; $e++)
                                <th colspan="3" class="border border-accent-200 px-1 py-1.5 text-center">Eval. {{ $e }}</th>
                                @if ($e < 8)
                                    <th class="border border-accent-200 p-0" aria-hidden="true"></th>
                                @endif
                            @endfor
                            <th class="border border-accent-200 p-0" aria-hidden="true"></th>
                            <th colspan="2" class="border border-accent-200 px-1 py-1.5 text-center">JIS 1</th>
                            <th class="border border-accent-200 p-0" aria-hidden="true"></th>
                            <th colspan="2" class="border border-accent-200 px-1 py-1.5 text-center">JIS 2</th>
                            <th class="border border-accent-200 p-0" aria-hidden="true"></th>
                            {{-- Dic/Feb/Pr.Final/TEA: rowspan=2 para “fusionar” con la fila de subencabezado vacía. --}}
                            <th rowspan="2" class="border border-accent-200 px-1 py-2 text-center align-middle">Dic</th>
                            <th rowspan="2" class="border border-accent-200 px-1 py-2 text-center align-middle">Feb</th>
                            <th rowspan="2" class="border border-accent-200 px-1 py-2 text-center align-middle font-bold">Pr.Final</th>
                            <th rowspan="2" class="border border-accent-200 px-1 py-2 text-center align-middle">TEA</th>
                        </tr>
                        <tr class="text-[9px] leading-tight bg-accent-50/90">
                            <th class="border border-accent-200 px-1 py-1"></th>
                            <th class="border border-accent-200 px-1 py-1"></th>
                            @for ($e = 1; $e <= 8; $e++)
                                <th class="border border-accent-200 px-0 py-1 text-center">N</th>
                                <th class="border border-accent-200 px-0 py-1 text-center">R1</th>
                                <th class="border border-accent-200 px-0 py-1 text-center">R2</th>
                                @if ($e < 8)
                                    <th class="border border-accent-200 p-0 bg-white" aria-hidden="true"></th>
                                @endif
                            @endfor
                            <th class="border border-accent-200 p-0 bg-white" aria-hidden="true"></th>
                            <th class="border border-accent-200 px-0 py-1 text-center">N</th>
                            <th class="border border-accent-200 px-0 py-1 text-center">R</th>
                            <th class="border border-accent-200 p-0 bg-white" aria-hidden="true"></th>
                            <th class="border border-accent-200 px-0 py-1 text-center">N</th>
                            <th class="border border-accent-200 px-0 py-1 text-center">R</th>
                            <th class="border border-accent-200 p-0 bg-white" aria-hidden="true"></th>
                        </tr>
                    </thead>
                    <tbody
                        class="bg-white"
                        data-se-calif-tbody
                        data-se-calif-activa="{{ $notasPermitidasActiva ? '1' : '0' }}"
                        data-se-calif-allowed='@json($notasPermitidasLista ?? [])'
                    >
                        @forelse ($rows as $row)
                            {{-- `wire:key` incluye `materiaId` para forzar recreación de inputs al cambiar de materia (evita valores “pegados” del DOM). --}}
                            <tr class="text-[11px] transition-colors hover:bg-accent-50/60" wire:key="row-{{ (int) $materiaId }}-{{ (int) $row['id'] }}">
                                <td class="border border-accent-200 px-1 py-0.5 text-center text-neutral-700 bg-accent-50/80">
                                    {{ $row['ord'] ?? '' }}
                                </td>
                                <td class="border border-accent-200 px-1.5 py-0.5 text-neutral-800 bg-accent-50/80 truncate" title="{{ $row['alumno'] ?? '—' }}">
                                    {{ $row['alumno'] ?? '—' }}
                                </td>

                                @php
                                    // Orden físico de columnas `ic**` tal como se renderizan en la tabla (debe coincidir con los separadores).
                                    $map = [
                                        // Eval 1..8 (N,R1,R2) => ic01..ic24
                                        'ic01','ic02','ic03','ic04','ic05','ic06','ic07','ic08','ic09','ic10','ic11','ic12',
                                        'ic13','ic14','ic15','ic16','ic17','ic18','ic19','ic20','ic21','ic22','ic23','ic24',
                                        // JIS 1/2 => ic25..ic28
                                        'ic25','ic26','ic27','ic28',
                                    ];
                                @endphp

                                @foreach ($map as $i => $field)
                                    @php
                                        $idx = (int) $i; // 0-based
                                        // Fin de bloque Eval: cada 3 campos (N/R1/R2). Además, luego de E8 hay separación antes de JIS1.
                                        $isEvalBlockEnd = $idx <= 23 && (($idx + 1) % 3 === 0); // ic01..ic24 (E1..E8) cada 3
                                        // Fin de bloque JIS: separación después de R de JIS1 y JIS2.
                                        $isJisBlockEnd = $idx === 25 || $idx === 27; // ic26 fin JIS1, ic28 fin JIS2
                                    @endphp
                                    <td class="border border-accent-200 px-0.5 py-0.5">
                                        <input
                                            id="se-calif-{{ (int) $row['id'] }}-{{ $field }}"
                                            class="w-full text-center text-[12px] border border-accent-200 rounded px-0 py-0.5 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                            maxlength="2"
                                            value="{{ $row[$field] ?? '' }}"
                                            wire:key="cell-{{ (int) $materiaId }}-{{ (int) $row['id'] }}-{{ $field }}"
                                        />
                                    </td>
                                    @if ($isEvalBlockEnd && $idx < 23)
                                        <td class="border border-accent-200 p-0 bg-white" aria-hidden="true"></td>
                                    @elseif ($isEvalBlockEnd && $idx === 23)
                                        <td class="border border-accent-200 p-0 bg-white" aria-hidden="true"></td>
                                    @elseif ($isJisBlockEnd && $idx === 25)
                                        <td class="border border-accent-200 p-0 bg-white" aria-hidden="true"></td>
                                    @elseif ($isJisBlockEnd && $idx === 27)
                                        <td class="border border-accent-200 p-0 bg-white" aria-hidden="true"></td>
                                    @endif
                                @endforeach

                                <td class="border border-accent-200 px-0.5 py-0.5">
                                    <input
                                        id="se-calif-{{ (int) $row['id'] }}-dic"
                                        class="w-full text-center text-[12px] border border-accent-200 rounded px-0 py-0.5 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        maxlength="2"
                                        value="{{ $row['dic'] ?? '' }}"
                                        wire:key="cell-{{ (int) $materiaId }}-{{ (int) $row['id'] }}-dic"
                                    />
                                </td>
                                <td class="border border-accent-200 px-0.5 py-0.5">
                                    <input
                                        id="se-calif-{{ (int) $row['id'] }}-feb"
                                        class="w-full text-center text-[12px] border border-accent-200 rounded px-0 py-0.5 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                        maxlength="2"
                                        value="{{ $row['feb'] ?? '' }}"
                                        wire:key="cell-{{ (int) $materiaId }}-{{ (int) $row['id'] }}-feb"
                                    />
                                </td>
                                <td class="border border-accent-200 px-0.5 py-0.5 bg-accent-50/90">
                                    <input
                                        id="se-calif-{{ (int) $row['id'] }}-calif"
                                        type="text"
                                        readonly
                                        tabindex="0"
                                        aria-readonly="true"
                                        class="w-full cursor-default text-center text-[12px] font-bold text-neutral-900 border border-accent-200 rounded px-0 py-0.5 bg-transparent focus:outline-none focus:ring-0"
                                        maxlength="5"
                                        value="{{ $row['calif'] ?? '' }}"
                                        wire:key="cell-{{ (int) $materiaId }}-{{ (int) $row['id'] }}-calif"
                                    />
                                </td>
                                <td class="border border-accent-200 px-0.5 py-0.5 text-center">
                                    <input
                                        type="checkbox"
                                        class="h-3.5 w-3.5 rounded border-accent-300 text-primary-600 focus:ring-primary-500"
                                        @checked((bool) ($row['tea'] ?? false))
                                        wire:key="cell-{{ (int) $materiaId }}-{{ (int) $row['id'] }}-tea"
                                        {{-- Checkbox: guardado en `change` (no aplica blur). --}}
                                        wire:change="saveCell({{ $row['id'] }}, 'tea', $event.target.checked)"
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                {{-- Debe coincidir con la cantidad total de columnas de la tabla (incluye separadores). --}}
                                <td colspan="43" class="border border-accent-200 px-4 py-8 text-center text-sm text-neutral-600">
                                    No hay alumnos con calificaciones registradas para esta materia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="se-card px-5 py-8">
            <p class="text-center text-sm text-neutral-600 sm:text-left">
                Seleccioná un curso y después una materia para cargar la planilla.
            </p>
        </div>
    @endif
</div>

