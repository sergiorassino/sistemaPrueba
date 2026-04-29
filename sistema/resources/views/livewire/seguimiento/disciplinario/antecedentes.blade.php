<x-form-shell maxWidth="max-w-6xl">
    <div class="card p-6 space-y-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 w-full text-center sm:flex-1">
                <h2 class="text-xl font-semibold text-gray-800">Antecedentes disciplinarios</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $base->legajo?->apellido }}, {{ $base->legajo?->nombre }}
                    · Nivel: {{ schoolCtx()->nivelNombre() }}
                </p>
            </div>

            <div class="flex flex-wrap justify-center gap-2 sm:justify-end">
                <a class="btn-secondary btn-sm"
                   target="_blank"
                   rel="noopener noreferrer"
                   href="{{ route('seguimiento.disciplinario.antecedentes.pdf', ['idMatricula' => $base->id]) }}">
                    Imprimir PDF
                </a>
                <a href="{{ route('seguimiento.disciplinario', ['curso' => $base->idCursos, 'matricula' => $base->id]) }}"
                   class="btn-secondary btn-sm">Volver</a>
            </div>
        </div>

        @if ($porAno->isEmpty())
            <p class="text-sm text-gray-500 text-center py-10">Sin antecedentes disciplinarios.</p>
        @else
            <div class="space-y-5">
                @foreach ($porAno as $ano => $items)
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-800">
                            Año lectivo: {{ $ano > 0 ? $ano : '—' }}
                            <span class="text-xs font-normal text-gray-500">({{ $items->count() }} evento/s)</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="table-header w-28">Fecha</th>
                                        <th class="table-header w-40">Curso</th>
                                        <th class="table-header w-56">Tipo</th>
                                        <th class="table-header w-24">Cant.</th>
                                        <th class="table-header">Motivo</th>
                                        <th class="table-header w-40">Solicitada por</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @foreach ($items as $s)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="table-cell font-mono">{{ $s->fecha?->format('d/m/Y') ?? '—' }}</td>
                                            <td class="table-cell">{{ $s->matricula?->curso?->nombreParaListado() ?? '—' }}</td>
                                            <td class="table-cell">{{ $s->tipo?->tipo ?? ('#'.$s->idTipoSancion) }}</td>
                                            <td class="table-cell font-mono">{{ $s->cantidad ?? '—' }}</td>
                                            <td class="table-cell">{{ $s->motivo ?? '—' }}</td>
                                            <td class="table-cell">{{ $s->solipor ?: ($s->profesor?->nombre_completo ?? '—') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-form-shell>

