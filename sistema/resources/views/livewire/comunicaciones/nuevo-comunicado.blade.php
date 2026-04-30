<div class="max-w-3xl space-y-4">

    <div class="flex items-center gap-3">
        <a href="{{ route('comunicaciones.index') }}" class="text-gray-400 hover:text-gray-600 transition">
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
        <a href="{{ route('comunicaciones.hilo', $enviado) }}" class="underline font-medium">Ver comunicado</a>
        @endif
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-5">

        {{-- Tipo de destino --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Destinatarios</label>
            <div class="flex flex-wrap gap-2">
                @foreach(['alumno' => 'Un alumno', 'varios_alumnos' => 'Varios alumnos', 'curso' => 'Un curso', 'colegio' => 'Todo el colegio'] as $val => $label)
                <button type="button" wire:click="$set('tipoDestino', '{{ $val }}')"
                        @class([
                            'px-3 py-1.5 rounded-lg text-sm font-medium border transition',
                            'text-white border-transparent' => $tipoDestino === $val,
                            'text-gray-600 border-gray-300 hover:border-gray-400' => $tipoDestino !== $val,
                        ])
                        @style(['background:#40848D' => $tipoDestino === $val])>
                    {{ $label }}
                </button>
                @endforeach
            </div>
            @error('tipoDestino') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Búsqueda de alumnos --}}
        @if(in_array($tipoDestino, ['alumno', 'varios_alumnos']))
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Buscar alumno {{ $tipoDestino === 'varios_alumnos' ? '(podés agregar varios)' : '' }}
            </label>
            <div class="relative">
                <input type="text" wire:model.live.debounce.300ms="alumnoSearch"
                       placeholder="Apellido, nombre o DNI..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                       style="focus:ring-color:#40848D">
                @if(!empty($alumnoResults))
                <div class="absolute z-10 top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                    @foreach($alumnoResults as $al)
                    <button type="button"
                            wire:click="selectAlumno({{ $al['id'] }}, '{{ addslashes($al['label']) }}')"
                            class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 border-b border-gray-100 last:border-0">
                        <span class="font-medium">{{ $al['label'] }}</span>
                        @if($al['dni']) <span class="text-gray-400 text-xs ml-1">DNI {{ $al['dni'] }}</span> @endif
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            @if(!empty($alumnosSeleccionados))
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach($alumnosSeleccionados as $al)
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium text-white"
                      style="background:#40848D">
                    {{ $al['label'] }}
                    <button type="button" wire:click="removeAlumno({{ $al['id'] }})"
                            class="ml-0.5 text-white/80 hover:text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </span>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        {{-- Selector de curso --}}
        @if($tipoDestino === 'curso')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Curso</label>
            <select wire:model="cursoId" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Seleccionar curso...</option>
                @foreach($cursos as $c)
                <option value="{{ $c['id'] }}">{{ $c['label'] }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Asunto --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Asunto</label>
            <input type="text" wire:model="asunto" maxlength="{{ $maxAsunto }}"
                   placeholder="Asunto del comunicado"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            @error('asunto') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Contenido --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mensaje</label>
            <textarea wire:model="contenido" rows="5" maxlength="{{ $maxContenido }}"
                      placeholder="Escriba el comunicado aquí..."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm resize-none"></textarea>
            <p class="text-xs text-gray-400 text-right mt-0.5">
                {{ mb_strlen($contenido) }} / {{ $maxContenido }}
            </p>
            @error('contenido') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 space-y-2">
            <label class="flex items-start gap-3 cursor-pointer select-none">
                <input type="checkbox" wire:model="familiaPuedeResponder"
                       class="mt-0.5 rounded border-gray-300 text-teal-700 focus:ring-teal-600">
                <span class="text-sm text-gray-800">
                    <span class="font-medium">Permitir que la familia responda</span>
                    <span class="block text-xs text-gray-500 mt-0.5">
                        Si lo desactivás, el comunicado queda <strong class="font-medium">solo informativo</strong>: la familia podrá leerlo en el cuaderno pero no enviar respuestas.
                    </span>
                </span>
            </label>
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
                Enviar comunicado
            </button>
        </div>
    </div>
</div>
