<div>
    <div class="se-auth-card p-6 sm:p-8">
        <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-neutral-800">Iniciar sesión</h2>
        <p class="mt-1.5 text-sm text-neutral-600">Ingrese sus datos y seleccione nivel y año lectivo.</p>

        @if (session('error'))
            <div class="mt-5 p-3 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit="login" class="mt-6 space-y-4" autocomplete="new-password">
            {{-- DNI --}}
            <div>
                <label class="se-auth-label" for="dni">DNI (usuario)</label>
                <input wire:model.live.debounce.400ms="dni"
                       id="dni"
                       type="text"
                       inputmode="numeric"
                       autocomplete="off"
                       placeholder="Ej: 25038868"
                       class="se-auth-input py-2.5 px-3 @error('dni') !border-red-400 ring-2 ring-red-200/80 @enderror">
                @error('dni')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Contraseña --}}
            <div>
                <label class="se-auth-label" for="pwrd">Contraseña</label>
                <input wire:model="pwrd"
                       id="pwrd"
                       type="password"
                       autocomplete="new-password"
                       class="se-auth-input py-2.5 px-3 @error('pwrd') !border-red-400 ring-2 ring-red-200/80 @enderror">
                @error('pwrd')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nivel --}}
            <div>
                <label class="se-auth-label" for="idNivel">Nivel</label>
                <select wire:model="idNivel"
                        id="idNivel"
                        class="se-auth-select py-2.5 px-3 @error('idNivel') !border-red-400 ring-2 ring-red-200/80 @enderror">
                    <option value="">— Seleccione nivel —</option>
                    @foreach ($niveles as $n)
                        <option value="{{ $n->id }}">{{ $n->nivel }}</option>
                    @endforeach
                </select>
                @error('idNivel')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Año lectivo --}}
            <div>
                <label class="se-auth-label" for="idTerlec">Año lectivo</label>
                <select wire:model="idTerlec"
                        id="idTerlec"
                        class="se-auth-select py-2.5 px-3 @error('idTerlec') !border-red-400 ring-2 ring-red-200/80 @enderror">
                    <option value="">— Seleccione año —</option>
                    @foreach ($terlecs as $t)
                        <option value="{{ $t->id }}">{{ $t->ano }}</option>
                    @endforeach
                </select>
                @error('idTerlec')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-1">
                <button type="submit"
                        class="se-auth-btn"
                        wire:loading.attr="disabled"
                        wire:target="login">
                    <span wire:loading.remove wire:target="login">Ingresar al sistema</span>
                    <span wire:loading wire:target="login" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        Verificando…
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
