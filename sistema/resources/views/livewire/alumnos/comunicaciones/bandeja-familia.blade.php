<div class="max-w-2xl space-y-4">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Comunicaciones</h1>
            <p class="text-sm text-gray-500">Cuaderno de comunicados con la escuela</p>
        </div>
        <a href="{{ route('alumnos.comunicaciones.nuevo') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-md text-white text-sm font-medium transition"
           style="background:#40848D">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo comunicado
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-4 py-3 flex flex-wrap gap-2">
        @foreach(['todos' => 'Todos', 'no_leidos' => 'No leídos', 'respondidos' => 'Respondidos'] as $val => $label)
        <button type="button" wire:click="$set('filtro', '{{ $val }}')"
                @class([
                    'px-3 py-1 rounded-full text-xs font-medium border transition',
                    'text-white border-transparent' => $filtro === $val,
                    'text-gray-600 border-gray-300 hover:border-gray-400' => $filtro !== $val,
                ])
                @style(['background:#40848D' => $filtro === $val])>
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Hilos --}}
    <div class="space-y-2">
        @forelse($hilos as $hilo)
        @php
            $noLeidos    = (int) $hilo->no_leidos;
            $respondidos = (int) $hilo->respondidos;
            $estado      = $respondidos > 0 ? 'respondido' : ($noLeidos > 0 ? 'no_leido' : 'leido');
        @endphp
        <a href="{{ route('alumnos.comunicaciones.hilo', $hilo->id) }}"
           @class([
               'block rounded-xl border p-4 transition',
               'border-l-4 border-l-rose-500 bg-rose-50 border-rose-200'         => $estado === 'no_leido',
               'border-l-4 border-l-emerald-500 bg-emerald-50 border-emerald-200' => $estado === 'respondido',
               'bg-white border-gray-200 hover:bg-gray-50'                        => $estado === 'leido',
           ])>
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($estado === 'no_leido')
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">
                            NUEVO
                        </span>
                        @elseif($estado === 'respondido')
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                            RESPONDIDO
                        </span>
                        @endif
                        @if($hilo->creado_por_tipo === 'profesor' && !($hilo->familia_puede_responder ?? true))
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            Solo informativo
                        </span>
                        @endif
                        <span class="font-semibold text-gray-900 text-sm truncate">{{ $hilo->asunto }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $hilo->creado_por_tipo === 'familia' ? 'Iniciado por usted' : 'Recibido de la escuela' }}
                    </p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-xs text-gray-400">
                        {{ $hilo->ultimo_mensaje_at ? \Carbon\Carbon::parse($hilo->ultimo_mensaje_at)->diffForHumans() : '' }}
                    </p>
                </div>
            </div>
        </a>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500 text-sm">
            No hay comunicados todavía.
        </div>
        @endforelse
    </div>
</div>
