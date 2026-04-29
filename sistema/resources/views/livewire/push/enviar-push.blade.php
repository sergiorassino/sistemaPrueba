<div class="max-w-4xl space-y-4">
    @if (session()->has('success'))
        <div class="p-3 rounded-md border border-green-200 bg-green-50 text-green-800 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-900">Enviar notificación push</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Se envía a los dispositivos suscriptos del/los alumno(s).
                </p>
            </div>
            <button type="button"
                    wire:click="previewNow"
                    class="px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 border border-gray-200 text-sm font-semibold">
                Actualizar vista previa
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-1 space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-800">Destino</label>
                    <select wire:model.live="tipoDestino"
                            wire:change="previewNow"
                            class="mt-1 w-full rounded-md border-gray-300">
                        <option value="alumno">Un alumno</option>
                        <option value="curso">Un curso</option>
                        <option value="colegio">Todo el colegio (contexto)</option>
                    </select>
                    @error('tipoDestino') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>

                @if ($tipoDestino === 'alumno')
                    <div>
                        <label class="block text-sm font-semibold text-gray-800">Alumno</label>
                        @if ($alumnoId && $alumnoLabel)
                            <div class="mt-1 flex items-center justify-between gap-2 p-2 rounded-md border border-gray-200 bg-gray-50">
                                <div class="text-sm text-gray-900 truncate">{{ $alumnoLabel }}</div>
                                <button type="button"
                                        wire:click="clearAlumno"
                                        class="text-sm text-gray-700 hover:text-gray-900">
                                    Quitar
                                </button>
                            </div>
                        @else
                            <input type="text"
                                   wire:model.live.debounce.250ms="alumnoSearch"
                                   placeholder="Buscar por apellido, nombre o DNI…"
                                   class="mt-1 w-full rounded-md border-gray-300" />
                            @error('alumnoId') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror

                            @if (!empty($alumnoResults))
                                <div class="mt-1 border border-gray-200 rounded-md bg-white divide-y max-h-64 overflow-auto">
                                    @foreach ($alumnoResults as $r)
                                        <button type="button"
                                                class="w-full text-left p-2 hover:bg-gray-50"
                                                wire:click="selectAlumno({{ $r['id'] }}, @js($r['label']))">
                                            <div class="text-sm font-semibold text-gray-900">{{ $r['label'] }}</div>
                                            @if (!empty($r['dni']))
                                                <div class="text-xs text-gray-600">DNI: {{ $r['dni'] }}</div>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                @endif

                @if ($tipoDestino === 'curso')
                    <div>
                        <label class="block text-sm font-semibold text-gray-800">Curso</label>
                        <select wire:model.live="cursoId"
                                wire:change="previewNow"
                                class="mt-1 w-full rounded-md border-gray-300">
                            <option value="">Seleccionar…</option>
                            @foreach ($cursos as $c)
                                <option value="{{ $c['id'] }}">{{ $c['label'] }}</option>
                            @endforeach
                        </select>
                        @error('cursoId') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="grid grid-cols-3 gap-2 pt-2">
                    <div class="p-3 rounded-md border border-gray-200 bg-gray-50">
                        <div class="text-xs text-gray-500">Destinatarios</div>
                        <div class="text-lg font-bold text-gray-900">{{ $preview['destinatarios'] ?? 0 }}</div>
                    </div>
                    <div class="p-3 rounded-md border border-gray-200 bg-gray-50">
                        <div class="text-xs text-gray-500">Suscriptos</div>
                        <div class="text-lg font-bold text-gray-900">{{ $preview['suscriptos'] ?? 0 }}</div>
                    </div>
                    <div class="p-3 rounded-md border border-gray-200 bg-gray-50">
                        <div class="text-xs text-gray-500">No suscriptos</div>
                        <div class="text-lg font-bold text-gray-900">{{ $preview['no_suscriptos'] ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 space-y-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-800">Título</label>
                    <input type="text" wire:model.live="title" class="mt-1 w-full rounded-md border-gray-300" maxlength="80">
                    @error('title') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-800">
                        Mensaje (máx. {{ \App\Push\WebPushService::MAX_MENSAJE_CARACTERES }} caracteres)
                    </label>
                    <textarea wire:model.live="body" rows="6" class="mt-1 w-full rounded-md border-gray-300"></textarea>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ mb_strlen($body ?? '') }} / {{ \App\Push\WebPushService::MAX_MENSAJE_CARACTERES }}
                    </div>
                    @error('body') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-800">URL al hacer clic (opcional)</label>
                    <input type="text" wire:model.live="url" class="mt-1 w-full rounded-md border-gray-300" placeholder="https://...">
                    @error('url') <p class="text-xs text-red-700 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <button type="button"
                            wire:click="send"
                            class="px-4 py-2 rounded-md bg-teal-700 text-white hover:bg-teal-800 font-semibold">
                        Enviar
                    </button>
                    <span wire:loading wire:target="send" class="text-sm text-gray-600">Enviando…</span>
                </div>

                @if (is_array($lastSend))
                    <div class="p-3 rounded-md border border-gray-200 bg-gray-50 text-sm text-gray-900">
                        Resultado: <span class="font-semibold">{{ $lastSend['sent'] ?? 0 }}</span> enviados,
                        <span class="font-semibold">{{ $lastSend['failed'] ?? 0 }}</span> fallidos.

                        @if (!empty($lastSend['failed_user_keys']))
                            <div class="mt-2">
                                <div class="text-xs text-gray-600 font-semibold">Fallidos por alumno</div>
                                <ul class="list-disc ml-5 mt-1 text-xs text-gray-700">
                                    @foreach ($lastSend['failed_user_keys'] as $uk => $motivo)
                                        <li><span class="font-semibold">{{ $uk }}</span>: {{ $motivo }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

