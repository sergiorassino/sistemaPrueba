<div>
    <div class="card p-4 sm:p-5">
        <h2 class="text-base sm:text-lg font-semibold text-gray-800 mb-2.5 sm:mb-3">Iniciar sesión</h2>

        @if (session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <form wire:submit="login" class="space-y-3">
            {{-- DNI --}}
            <div>
                <label class="form-label text-xs" for="dni">DNI (usuario)</label>
                <input wire:model.live.debounce.400ms="dni"
                       id="dni"
                       type="text"
                       inputmode="numeric"
                       autocomplete="username"
                       placeholder="Ej: 25038868"
                       class="form-input text-sm py-2 @error('dni') border-red-400 @enderror">
                @error('dni')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Contraseña --}}
            <div>
                <label class="form-label text-xs" for="pwrd">Contraseña</label>
                <input wire:model="pwrd"
                       id="pwrd"
                       type="password"
                       autocomplete="current-password"
                       class="form-input text-sm py-2 @error('pwrd') border-red-400 @enderror">
                @error('pwrd')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nivel --}}
            <div>
                <label class="form-label text-xs" for="idNivel">Nivel</label>
                <select wire:model="idNivel"
                        id="idNivel"
                        class="form-select text-sm py-2 @error('idNivel') border-red-400 @enderror">
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
                <label class="form-label text-xs" for="idTerlec">Año lectivo</label>
                <select wire:model="idTerlec"
                        id="idTerlec"
                        class="form-select text-sm py-2 @error('idTerlec') border-red-400 @enderror">
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
                        class="btn-primary w-full py-2 text-sm"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-75"
                        wire:target="login">
                    <span wire:loading.remove wire:target="login">Ingresar al sistema</span>
                    <span wire:loading wire:target="login" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
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
