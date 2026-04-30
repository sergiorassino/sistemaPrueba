<div class="max-w-3xl space-y-4">

    {{-- Encabezado --}}
    <div class="flex items-start gap-3">
        <a href="{{ route('comunicaciones.index') }}" class="text-gray-400 hover:text-gray-600 transition mt-1 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="min-w-0">
            <h1 class="text-lg font-semibold text-gray-900">{{ $hilo->asunto }}</h1>
            <p class="text-xs text-gray-400 mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1">
                <span>{{ $hilo->scopeLabel() }} ·
                Iniciado {{ \Carbon\Carbon::parse($hilo->created_at)->format('d/m/Y H:i') }}</span>
                @if($hilo->estado === 'cerrado')<span class="text-gray-500">Cerrado</span>@endif
                @if($hilo->esComunicadoInformativoEscuela())
                <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide bg-amber-100 text-amber-800 border border-amber-200">
                    Solo informativo · sin respuesta familia
                </span>
                @endif
            </p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Timeline de mensajes --}}
    <div class="space-y-6">
        @forelse($mensajesPorDia as $fecha => $mensajes)
        <div>
            {{-- Separador de fecha --}}
            <div class="flex items-center gap-3 mb-3">
                <div class="h-px flex-1 bg-gray-200"></div>
                <span class="text-xs text-gray-400 font-medium px-2">
                    {{ \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('dddd D [de] MMMM') }}
                </span>
                <div class="h-px flex-1 bg-gray-200"></div>
            </div>

            <div class="space-y-3">
                @foreach($mensajes as $msg)
                @php $esMio = $msg->tipo_remitente === 'profesor'; @endphp
                <div @class(['flex', 'justify-end' => $esMio, 'justify-start' => !$esMio])>
                    <div @class([
                        'max-w-[80%] rounded-2xl px-4 py-3 shadow-sm',
                        'rounded-tr-sm text-white' => $esMio,
                        'rounded-tl-sm bg-white border border-gray-200' => !$esMio,
                    ]) @style(['background:#40848D' => $esMio])>

                        {{-- Remitente --}}
                        <div @class([
                            'text-xs font-semibold mb-1',
                            'text-white/80' => $esMio,
                            'text-gray-500' => !$esMio,
                        ])>
                            {{ $msg->nombre_remitente_snapshot ?? ($esMio ? 'Personal escolar' : 'Familia') }}
                            @if($msg->vinculo_familiar)
                            <span @class(['ml-1 font-normal', 'text-white/60' => $esMio, 'text-gray-400' => !$esMio])>
                                ({{ $msg->vinculoLabel() }})
                            </span>
                            @endif
                        </div>

                        {{-- Contenido --}}
                        <p @class(['text-sm whitespace-pre-wrap leading-relaxed', 'text-white' => $esMio, 'text-gray-800' => !$esMio])>
                            {{ $msg->contenido }}
                        </p>

                        {{-- Hora y envíos --}}
                        <div @class([
                            'flex items-center justify-between gap-3 mt-2',
                            'text-white/60' => $esMio,
                            'text-gray-400' => !$esMio,
                        ])>
                            <span class="text-[10px]">
                                {{ $msg->hora ? substr($msg->hora, 0, 5) : '' }}
                            </span>
                            @if($msg->destinatarios->count())
                            <div class="flex items-center gap-1">
                                @foreach($msg->destinatarios->take(1)->first()?->envios ?? [] as $envio)
                                <span title="{{ $envio->medio }}: {{ $envio->estadoLabel() }}{{ $envio->motivo ? ' — '.$envio->motivo : '' }}"
                                      @class([
                                          'text-[10px]',
                                          'text-white/80' => $esMio && $envio->estado === 'enviado',
                                          'text-yellow-300' => $esMio && $envio->estado === 'pendiente',
                                          'text-red-300' => $esMio && $envio->estado === 'fallido',
                                          'text-gray-300' => $esMio && $envio->estado === 'no_aplicable',
                                          'text-gray-400' => !$esMio,
                                      ])>
                                    {{ $envio->iconoMedio() }}
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500 text-sm">
            No hay mensajes en este hilo.
        </div>
        @endforelse
    </div>

    {{-- Formulario de respuesta --}}
    @if($puedeResponder && $hilo->estado === 'abierto')
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        @if(!$mostrarFormRespuesta)
        <button type="button" wire:click="$set('mostrarFormRespuesta', true)"
                class="w-full text-left text-sm text-gray-400 px-3 py-2 rounded-lg border border-gray-200 hover:border-gray-300 transition bg-gray-50">
            Escribir respuesta...
        </button>
        @else
        <div class="space-y-3">
            <textarea wire:model="respuesta" rows="4"
                      placeholder="Escriba su respuesta..."
                      autofocus
                      maxlength="{{ $maxContenido }}"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2"
                      style="focus-ring-color:#40848D"></textarea>
            @error('respuesta') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            <p class="text-xs text-gray-400 text-right">{{ mb_strlen($respuesta) }} / {{ $maxContenido }}</p>
            <div class="flex items-center justify-between gap-3">
                <button type="button" wire:click="$set('mostrarFormRespuesta', false)"
                        class="text-sm text-gray-500 hover:text-gray-700 transition">Cancelar</button>
                <button type="button" wire:click="responder" wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white text-sm font-medium transition disabled:opacity-60"
                        style="background:#40848D">
                    Enviar respuesta
                </button>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>
