{{-- Módulo: Carga de calificaciones (UI). Guardado vía `saveCell`: TEA con `wire:change`; el resto de inputs numéricos con delegación `focusout` en `tbody` (validación de notas permitidas en el navegador, ver `app.js`). --}}
<x-form-shell maxWidth="max-w-[98rem]">
    <div class="card p-3 space-y-3">
        <div class="flex w-full flex-col items-stretch gap-2 sm:flex-row sm:items-center sm:gap-3">
            <h1 class="w-full flex-1 text-center text-[15px] font-semibold text-gray-800 sm:min-w-0">Carga de calificaciones</h1>
            <a href="{{ route('dashboard') }}"
               class="mx-auto shrink-0 px-2 py-1.5 rounded border border-gray-300 bg-white text-gray-800 hover:bg-gray-50 text-xs sm:mx-0 sm:ml-auto">
                Volver
            </a>
        </div>

        {{-- Paso 1/2: selección de curso y materia. `wire:model.live` dispara los `updated*()` del componente. --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Curso</label>
                <select wire:model.live="cursoId" class="form-select w-full">
                    <option value="">— Seleccione —</option>
                    @foreach ($cursos as $c)
                        <option value="{{ $c->Id }}">{{ $c->nombreParaListado() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Materia</label>
                <select wire:model.live="materiaId" class="form-select w-full" @disabled(!$cursoId)>
                    <option value="">— Seleccione —</option>
                    @foreach ($materias as $m)
                        <option value="{{ $m->id }}">{{ trim((string) ($m->materia ?? '')) !== '' ? $m->materia : ('ID ' . $m->id) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if ($cursoId && $materiaId)
            {{-- Contexto elegido + recordatorio de guardado automático al salir de cada celda. --}}
            <div class="text-xs text-gray-600">
                <span class="font-medium">Curso:</span> {{ $cursoLabel ?? '—' }}
                <span class="mx-2">·</span>
                <span class="font-medium">Materia:</span> {{ $materiaLabel ?? '—' }}
                <span class="mx-2">·</span>
                <span class="italic">(Los datos se guardan automáticamente al salir de cada celda)</span>
            </div>

            {{-- Grilla compacta: `colgroup` fija anchos (evita que inputs “empujen” el layout en pantallas chicas). --}}
            <div class="border border-gray-300 rounded-lg">
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
                    <thead class="sticky top-0 bg-white text-gray-900">
                        <tr class="text-[10px] leading-tight">
                            <th class="border border-gray-300 px-1 py-1 text-center w-[36px]">Ord</th>
                            <th class="border border-gray-300 px-1 py-1 text-left w-[120px]">Estudiante</th>
                            @for ($e = 1; $e <= 8; $e++)
                                <th colspan="3" class="border border-gray-300 px-1 py-1.5 text-center">Eval. {{ $e }}</th>
                                @if ($e < 8)
                                    <th class="border border-gray-300 p-0" aria-hidden="true"></th>
                                @endif
                            @endfor
                            <th class="border border-gray-300 p-0" aria-hidden="true"></th>
                            <th colspan="2" class="border border-gray-300 px-1 py-1.5 text-center">JIS 1</th>
                            <th class="border border-gray-300 p-0" aria-hidden="true"></th>
                            <th colspan="2" class="border border-gray-300 px-1 py-1.5 text-center">JIS 2</th>
                            <th class="border border-gray-300 p-0" aria-hidden="true"></th>
                            {{-- Dic/Feb/Pr.Final/TEA: rowspan=2 para “fusionar” con la fila de subencabezado vacía. --}}
                            <th rowspan="2" class="border border-gray-300 px-1 py-2 text-center align-middle">Dic</th>
                            <th rowspan="2" class="border border-gray-300 px-1 py-2 text-center align-middle">Feb</th>
                            <th rowspan="2" class="border border-gray-300 px-1 py-2 text-center align-middle font-bold">Pr.Final</th>
                            <th rowspan="2" class="border border-gray-300 px-1 py-2 text-center align-middle">TEA</th>
                        </tr>
                        <tr class="text-[9px] bg-white leading-tight">
                            <th class="border border-gray-300 px-1 py-1"></th>
                            <th class="border border-gray-300 px-1 py-1"></th>
                            @for ($e = 1; $e <= 8; $e++)
                                <th class="border border-gray-300 px-0 py-1 text-center">N</th>
                                <th class="border border-gray-300 px-0 py-1 text-center">R1</th>
                                <th class="border border-gray-300 px-0 py-1 text-center">R2</th>
                                @if ($e < 8)
                                    <th class="border border-gray-300 p-0 bg-white" aria-hidden="true"></th>
                                @endif
                            @endfor
                            <th class="border border-gray-300 p-0 bg-white" aria-hidden="true"></th>
                            <th class="border border-gray-300 px-0 py-1 text-center">N</th>
                            <th class="border border-gray-300 px-0 py-1 text-center">R</th>
                            <th class="border border-gray-300 p-0 bg-white" aria-hidden="true"></th>
                            <th class="border border-gray-300 px-0 py-1 text-center">N</th>
                            <th class="border border-gray-300 px-0 py-1 text-center">R</th>
                            <th class="border border-gray-300 p-0 bg-white" aria-hidden="true"></th>
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
                            <tr class="text-[11px] hover:bg-gray-50" wire:key="row-{{ (int) $materiaId }}-{{ (int) $row['id'] }}">
                                <td class="border border-gray-300 px-1 py-0.5 text-center text-gray-700 bg-gray-50/60">
                                    {{ $row['ord'] ?? '' }}
                                </td>
                                <td class="border border-gray-300 px-1.5 py-0.5 text-gray-800 bg-gray-50/60 truncate" title="{{ $row['alumno'] ?? '—' }}">
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
                                    <td class="border border-gray-300 px-0.5 py-0.5">
                                        <input
                                            id="se-calif-{{ (int) $row['id'] }}-{{ $field }}"
                                            class="w-full text-center text-[12px] border border-gray-300 rounded px-0 py-0.5 focus:ring-[#40848D] focus:border-[#40848D]"
                                            maxlength="2"
                                            value="{{ $row[$field] ?? '' }}"
                                            wire:key="cell-{{ (int) $materiaId }}-{{ (int) $row['id'] }}-{{ $field }}"
                                        />
                                    </td>
                                    @if ($isEvalBlockEnd && $idx < 23)
                                        <td class="border border-gray-300 p-0 bg-white" aria-hidden="true"></td>
                                    @elseif ($isEvalBlockEnd && $idx === 23)
                                        <td class="border border-gray-300 p-0 bg-white" aria-hidden="true"></td>
                                    @elseif ($isJisBlockEnd && $idx === 25)
                                        <td class="border border-gray-300 p-0 bg-white" aria-hidden="true"></td>
                                    @elseif ($isJisBlockEnd && $idx === 27)
                                        <td class="border border-gray-300 p-0 bg-white" aria-hidden="true"></td>
                                    @endif
                                @endforeach

                                <td class="border border-gray-300 px-0.5 py-0.5">
                                    <input
                                        id="se-calif-{{ (int) $row['id'] }}-dic"
                                        class="w-full text-center text-[12px] border border-gray-300 rounded px-0 py-0.5 focus:ring-[#40848D] focus:border-[#40848D]"
                                        maxlength="2"
                                        value="{{ $row['dic'] ?? '' }}"
                                        wire:key="cell-{{ (int) $materiaId }}-{{ (int) $row['id'] }}-dic"
                                    />
                                </td>
                                <td class="border border-gray-300 px-0.5 py-0.5">
                                    <input
                                        id="se-calif-{{ (int) $row['id'] }}-feb"
                                        class="w-full text-center text-[12px] border border-gray-300 rounded px-0 py-0.5 focus:ring-[#40848D] focus:border-[#40848D]"
                                        maxlength="2"
                                        value="{{ $row['feb'] ?? '' }}"
                                        wire:key="cell-{{ (int) $materiaId }}-{{ (int) $row['id'] }}-feb"
                                    />
                                </td>
                                <td class="border border-gray-300 px-0.5 py-0.5 bg-gray-50/80">
                                    <input
                                        id="se-calif-{{ (int) $row['id'] }}-calif"
                                        type="text"
                                        readonly
                                        tabindex="0"
                                        aria-readonly="true"
                                        class="w-full cursor-default text-center text-[12px] font-bold text-gray-900 border border-gray-200 rounded px-0 py-0.5 bg-transparent focus:outline-none focus:ring-0"
                                        maxlength="5"
                                        value="{{ $row['calif'] ?? '' }}"
                                        wire:key="cell-{{ (int) $materiaId }}-{{ (int) $row['id'] }}-calif"
                                    />
                                </td>
                                <td class="border border-gray-300 px-0.5 py-0.5 text-center">
                                    <input
                                        type="checkbox"
                                        class="h-3.5 w-3.5 rounded border-gray-300 text-[#40848D] focus:ring-[#40848D]"
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
                                <td colspan="43" class="border border-gray-300 px-4 py-6 text-center text-sm text-gray-600">
                                    No hay alumnos con calificaciones registradas para esta materia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-600">
                Seleccione un curso y luego una materia para comenzar.
            </p>
        @endif
    </div>
</x-form-shell>

