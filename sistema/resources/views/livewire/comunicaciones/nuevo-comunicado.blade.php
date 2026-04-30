<div class="se-page">
    <section class="se-hero">
        <div class="se-hero-inner">
            <div class="min-w-0 space-y-3">
                <p class="se-eyebrow">Comunicaciones</p>
                <div>
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Nuevo comunicado</h2>
                    <p class="mt-2 max-w-2xl text-sm text-white/80">
                        {{ schoolCtx()->nivelNombre() }} · Ciclo lectivo {{ schoolCtx()->terlecAno() }}
                    </p>
                </div>
            </div>

            <a href="{{ route('comunicaciones.index') }}"
               class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver a la bandeja
            </a>
        </div>
    </section>

    @if (session('success'))
        <div class="se-soft-card flex flex-col gap-3 border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            @if ($enviado)
                <a href="{{ route('comunicaciones.hilo', $enviado) }}" class="font-semibold text-primary-700 underline decoration-primary-300 underline-offset-2 hover:text-primary-800">
                    Ver comunicado
                </a>
            @endif
        </div>
    @endif

    <div class="se-card overflow-hidden">
        <div class="border-b border-accent-200 bg-white px-5 py-4">
            <p class="se-section-title">Destinatarios y mensaje</p>
            <p class="mt-1 text-sm text-neutral-600">El envío respeta canales y preferencias de cada familia.</p>
        </div>

        <div class="space-y-6 border-t border-accent-100 bg-accent-50/30 p-5 sm:p-6">
            <div>
                <span class="form-label">Destinatarios</span>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach (['alumno' => 'Un alumno', 'varios_alumnos' => 'Varios alumnos', 'curso' => 'Un curso', 'colegio' => 'Todo el colegio'] as $val => $label)
                        <button type="button"
                                wire:click="$set('tipoDestino', '{{ $val }}')"
                                @class([
                                    'inline-flex cursor-pointer items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2',
                                    'border-primary-500 bg-primary-600 text-white' => $tipoDestino === $val,
                                    'border-accent-200 bg-white text-neutral-700 hover:bg-accent-50' => $tipoDestino !== $val,
                                ])>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                @error('tipoDestino') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            @if (in_array($tipoDestino, ['alumno', 'varios_alumnos'], true))
                <div>
                    <label for="buscar-alumno-com" class="form-label">
                        Buscar alumno @if ($tipoDestino === 'varios_alumnos') (podés agregar varios) @endif
                    </label>
                    <div class="relative mt-1.5">
                        <input id="buscar-alumno-com"
                               type="text"
                               wire:model.live.debounce.300ms="alumnoSearch"
                               placeholder="Apellido, nombre o DNI…"
                               class="form-input" />
                        @if (! empty($alumnoResults))
                            <div class="absolute z-20 mt-2 max-h-48 w-full overflow-y-auto rounded-2xl border border-accent-200 bg-white shadow-lg">
                                @foreach ($alumnoResults as $al)
                                    <button type="button"
                                            wire:click="selectAlumno({{ $al['id'] }}, @js($al['label']))"
                                            class="block w-full border-b border-accent-100 px-3 py-2.5 text-left text-sm transition last:border-b-0 hover:bg-accent-50">
                                        <span class="font-semibold text-neutral-900">{{ $al['label'] }}</span>
                                        @if ($al['dni'])
                                            <span class="ml-1 text-xs text-neutral-400">DNI {{ $al['dni'] }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if (! empty($alumnosSeleccionados))
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($alumnosSeleccionados as $al)
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-primary-400 bg-primary-600 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                                    {{ $al['label'] }}
                                    <button type="button"
                                            wire:click="removeAlumno({{ $al['id'] }})"
                                            class="rounded-full text-white/85 transition hover:bg-white/20 hover:text-white"
                                            title="Quitar">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            @if ($tipoDestino === 'curso')
                <div>
                    <label for="curso-com" class="form-label">Curso</label>
                    <select id="curso-com" wire:model="cursoId" class="form-select">
                        <option value="">Seleccionar curso…</option>
                        @foreach ($cursos as $c)
                            <option value="{{ $c['id'] }}">{{ $c['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label for="asunto-com" class="form-label">Asunto</label>
                <input id="asunto-com"
                       type="text"
                       wire:model="asunto"
                       maxlength="{{ $maxAsunto }}"
                       placeholder="Asunto del comunicado"
                       class="form-input" />
                @error('asunto') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="contenido-com" class="form-label">Mensaje</label>
                <textarea id="contenido-com"
                          wire:model="contenido"
                          rows="5"
                          maxlength="{{ $maxContenido }}"
                          placeholder="Escriba el comunicado aquí…"
                          class="form-input resize-none leading-relaxed"></textarea>
                <p class="mt-1 text-right text-xs text-neutral-500 tabular-nums">
                    {{ mb_strlen($contenido) }} / {{ $maxContenido }}
                </p>
                @error('contenido') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="rounded-2xl border border-accent-200 bg-white p-4 shadow-sm">
                <label class="flex cursor-pointer select-none items-start gap-3">
                    <input type="checkbox"
                           wire:model="familiaPuedeResponder"
                           class="mt-0.5 rounded border-accent-300 text-primary-600 focus:ring-primary-500" />
                    <span class="text-sm text-neutral-800">
                        <span class="font-semibold text-neutral-900">Permitir que la familia responda</span>
                        <span class="mt-1 block text-xs leading-relaxed text-neutral-500">
                            Si lo desactivás, el comunicado queda <strong class="font-medium text-neutral-700">solo informativo</strong>: podrán leerlo en el cuaderno pero no enviar respuestas.
                        </span>
                    </span>
                </label>
            </div>

            <div class="flex justify-end border-t border-accent-200 pt-2">
                <button type="button"
                        wire:click="enviar"
                        wire:loading.attr="disabled"
                        class="btn-primary disabled:opacity-60">
                    <span wire:loading wire:target="enviar" class="mr-2 inline-flex">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </span>
                    Enviar comunicado
                </button>
            </div>
        </div>
    </div>
</div>
