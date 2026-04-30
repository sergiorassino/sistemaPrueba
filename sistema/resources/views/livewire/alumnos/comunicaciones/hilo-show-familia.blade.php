<div class="max-w-2xl space-y-4">

    {{-- Encabezado --}}
    <div class="flex items-start gap-3">
        <a href="{{ route('alumnos.comunicaciones.index') }}" class="text-gray-400 hover:text-gray-600 transition mt-1 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div class="min-w-0">
            <h1 class="text-lg font-semibold text-gray-900 break-words">{{ $hilo->asunto }}</h1>
            <p class="text-xs text-gray-400 mt-0.5">
                {{ \Carbon\Carbon::parse($hilo->created_at)->format('d/m/Y H:i') }}
                @if($hilo->estado === 'cerrado') · <span class="text-gray-500">Comunicado cerrado</span> @endif
            </p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    @if($hilo->esComunicadoInformativoEscuela())
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <p class="font-semibold">Comunicado solo informativo</p>
        <p class="mt-1 text-amber-800/90">La escuela envió este mensaje sin opción de respuesta. Podés leer el contenido y el historial; no es posible enviar respuestas en este cuaderno.</p>
    </div>
    @endif

    {{-- Timeline --}}
    <div class="space-y-6">
        @forelse($mensajesPorDia as $fecha => $mensajes)
        <div>
            <div class="flex items-center gap-3 mb-3">
                <div class="h-px flex-1 bg-gray-200"></div>
                <span class="text-xs text-gray-400 font-medium px-2">
                    {{ \Carbon\Carbon::parse($fecha)->locale('es')->isoFormat('dddd D [de] MMMM') }}
                </span>
                <div class="h-px flex-1 bg-gray-200"></div>
            </div>

            <div class="space-y-3">
                @foreach($mensajes as $msg)
                @php $esFamilia = $msg->tipo_remitente === 'familia'; @endphp
                <div @class(['flex', 'justify-end' => $esFamilia, 'justify-start' => !$esFamilia])>
                    <div @class([
                        'max-w-[85%] rounded-2xl px-4 py-3 shadow-sm',
                        'rounded-tr-sm text-white' => $esFamilia,
                        'rounded-tl-sm bg-white border border-gray-200' => !$esFamilia,
                    ]) @style(['background:#40848D' => $esFamilia])>

                        <div @class([
                            'text-xs font-semibold mb-1',
                            'text-white/80' => $esFamilia,
                            'text-gray-500' => !$esFamilia,
                        ])>
                            @if($esFamilia)
                                {{ $msg->nombre_remitente_snapshot ?? 'Familiar' }}
                                @if($msg->vinculo_familiar)
                                <span class="font-normal text-white/60"> ({{ $msg->vinculoLabel() }})</span>
                                @endif
                            @else
                                Escuela
                                @if($msg->nombre_remitente_snapshot)
                                — <span class="font-normal">{{ $msg->nombre_remitente_snapshot }}</span>
                                @endif
                            @endif
                        </div>

                        <p @class([
                            'text-sm whitespace-pre-wrap leading-relaxed',
                            'text-white' => $esFamilia,
                            'text-gray-800' => !$esFamilia,
                        ])>{{ $msg->contenido }}</p>

                        <p @class([
                            'text-[10px] mt-1.5',
                            'text-white/60' => $esFamilia,
                            'text-gray-400' => !$esFamilia,
                        ])>
                            {{ $msg->hora ? substr($msg->hora, 0, 5) : '' }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500 text-sm">
            No hay mensajes en este comunicado.
        </div>
        @endforelse
    </div>

    {{-- Formulario de respuesta --}}
    @if($puedeResponder && $hilo->estado === 'abierto')
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        @if(!$mostrarFormRespuesta)
        <button type="button" wire:click="$set('mostrarFormRespuesta', true)"
                class="w-full text-left text-sm text-gray-400 px-3 py-2 rounded-lg border border-gray-200 bg-gray-50 hover:border-gray-300 transition">
            Escribir respuesta...
        </button>
        @else
        <div class="space-y-3">
            {{-- Vínculo --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Yo soy el/la...</label>
                <select wire:model="vinculo" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="">Seleccionar...</option>
                    @foreach(['madre' => 'Madre', 'padre' => 'Padre', 'tutor' => 'Tutor/a', 'resp_admin' => 'Resp. Administrativo/a', 'otro' => 'Otro responsable'] as $val => $lbl)
                    <option value="{{ $val }}">{{ $lbl }}</option>
                    @endforeach
                </select>
                @error('vinculo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <textarea wire:model="respuesta" rows="4"
                      placeholder="Su respuesta..."
                      maxlength="{{ $maxContenido }}"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none"></textarea>
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
