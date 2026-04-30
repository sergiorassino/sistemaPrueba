<div class="max-w-2xl space-y-4">

    <div class="flex items-center gap-3">
        <a href="{{ route('alumnos.comunicaciones.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-lg font-semibold text-gray-900">Nuevo Comunicado</h1>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm flex items-center justify-between gap-3">
        <span>{{ session('success') }}</span>
        @if($enviado)
        <a href="{{ route('alumnos.comunicaciones.hilo', $enviado) }}" class="underline font-medium">Ver comunicado</a>
        @endif
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-5">

        {{-- Vínculo familiar --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Yo soy el/la... <span class="text-red-500">*</span>
            </label>
            <select wire:model="vinculo"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Seleccionar vínculo...</option>
                @foreach($vinculos as $val => $label)
                <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('vinculo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Destinatario (rol) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Quiero comunicarme con... <span class="text-red-500">*</span>
            </label>
            <div class="flex flex-wrap gap-2">
                @foreach($rolesReceptoresPermitidos as $rol)
                @php
                    $labels = [
                        'preceptor' => 'El/La Preceptor/a',
                        'directivo' => 'Dirección / Secretaría',
                        'profesor'  => 'Un Profesor/a',
                    ];
                @endphp
                <button type="button" wire:click="$set('rolReceptor', '{{ $rol }}')"
                        @class([
                            'px-3 py-1.5 rounded-lg text-sm font-medium border transition',
                            'text-white border-transparent' => $rolReceptor === $rol,
                            'text-gray-600 border-gray-300 hover:border-gray-400' => $rolReceptor !== $rol,
                        ])
                        @style(['background:#40848D' => $rolReceptor === $rol])>
                    {{ $labels[$rol] ?? ucfirst($rol) }}
                </button>
                @endforeach
            </div>
            @error('rolReceptor') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Destinatario específico --}}
        @if(!empty($destinatariosDisponibles))
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Destinatario específico</label>
            <select wire:model="idDestinatario" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Seleccionar...</option>
                @foreach($destinatariosDisponibles as $d)
                <option value="{{ $d['id'] }}">{{ $d['label'] }}</option>
                @endforeach
            </select>
            @error('idDestinatario') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        @elseif($rolReceptor !== '')
        <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-700">
            No se encontraron destinatarios disponibles para ese rol. Consulte con la institución.
        </div>
        @endif

        {{-- Asunto --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Asunto <span class="text-red-500">*</span></label>
            <input type="text" wire:model="asunto" maxlength="{{ $maxAsunto }}"
                   placeholder="Motivo del comunicado..."
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            @error('asunto') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Contenido --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mensaje <span class="text-red-500">*</span></label>
            <textarea wire:model="contenido" rows="5" maxlength="{{ $maxContenido }}"
                      placeholder="Escriba su comunicado aquí..."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none"></textarea>
            <p class="text-xs text-gray-400 text-right mt-0.5">{{ mb_strlen($contenido) }} / {{ $maxContenido }}</p>
            @error('contenido') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button type="button" wire:click="enviar" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-white text-sm font-medium transition disabled:opacity-60"
                    style="background:#40848D">
                <span wire:loading wire:target="enviar">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </span>
                Enviar
            </button>
        </div>
    </div>
</div>
