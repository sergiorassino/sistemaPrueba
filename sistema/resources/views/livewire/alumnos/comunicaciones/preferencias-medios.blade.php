<div class="max-w-xl space-y-4">

    <div class="flex items-center gap-3">
        <a href="{{ route('alumnos.comunicaciones.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Preferencias de Comunicación</h1>
            <p class="text-sm text-gray-500">Elegí cómo y quién prefiere recibir comunicados de la escuela</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-6">

        {{-- Vínculo de contacto --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">¿Quién es el/la responsable de recibir comunicados?</label>
            <p class="text-xs text-gray-500 mb-3">
                Esto determina cuál email y teléfono se usa cuando la escuela les escribe.
            </p>
            <select wire:model="vinculoContacto"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">Sin preferencia (usa el primer dato disponible)</option>
                @foreach($vinculos as $val => $label)
                <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Medios de contacto --}}
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">¿Por qué medios querés recibir comunicados?</label>
            <p class="text-xs text-gray-500 mb-3">
                Activá los que preferís. Siempre estará disponible la bandeja del portal.
            </p>

            <div class="space-y-3">
                {{-- Push --}}
                <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition select-none">
                    <div class="flex-shrink-0 pt-0.5">
                        <input type="checkbox" wire:model="push" class="rounded border-gray-300 w-4 h-4">
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800">🔔 Notificaciones push</p>
                        <p class="text-xs text-gray-500 mt-0.5">En el navegador o en la app instalada en tu dispositivo. Requiere activarlas previamente.</p>
                    </div>
                </label>

                {{-- Email --}}
                <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition select-none">
                    <div class="flex-shrink-0 pt-0.5">
                        <input type="checkbox" wire:model="email" class="rounded border-gray-300 w-4 h-4">
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800">✉ Correo electrónico</p>
                        <p class="text-xs text-gray-500 mt-0.5">Se envía al email registrado en el legajo, según el responsable seleccionado arriba.</p>
                    </div>
                </label>

                {{-- WhatsApp --}}
                <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition select-none">
                    <div class="flex-shrink-0 pt-0.5">
                        <input type="checkbox" wire:model="whatsapp" class="rounded border-gray-300 w-4 h-4">
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800">💬 WhatsApp</p>
                        <p class="text-xs text-gray-500 mt-0.5">Al número celular registrado en el legajo. El envío puede ser manual por parte de la institución.</p>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button" wire:click="guardar" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-white text-sm font-medium transition disabled:opacity-60"
                    style="background:#40848D">
                <span wire:loading wire:target="guardar">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </span>
                Guardar preferencias
            </button>
        </div>
    </div>
</div>
