<div>
    {{-- Flash --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md text-sm text-green-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Legajos de Estudiantes</h2>
            <p class="text-sm text-gray-500 mt-0.5">Nivel: {{ schoolCtx()->nivelNombre() }} · Año: {{ schoolCtx()->terlecAno() }}</p>
        </div>
        <button wire:click="openCreate" class="btn-primary btn-sm sm:self-start">
            + Nuevo legajo
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-4">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
            <input wire:model.live.debounce.400ms="search"
                   type="search"
                   placeholder="Buscar por apellido, nombre o DNI…"
                   class="form-input pl-9">
        </div>
        <select wire:model.live="filtroNivel" class="form-input sm:w-48">
            <option value="">Todos los niveles</option>
            @foreach ($niveles as $niv)
                <option value="{{ $niv->id }}">{{ $niv->nivel }}</option>
            @endforeach
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer whitespace-nowrap">
            <input wire:model.live="soloMatricula" type="checkbox" class="rounded border-gray-300 text-primary-600">
            Solo matriculados año activo
        </label>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-header">Apellido y Nombre</th>
                        <th class="table-header hidden sm:table-cell">DNI</th>
                        <th class="table-header hidden md:table-cell">Nac.</th>
                        <th class="table-header hidden lg:table-cell">Legajo</th>
                        <th class="table-header text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($legajos as $l)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="table-cell">
                                <div class="font-medium text-gray-900">{{ $l->apellido }}, {{ $l->nombre }}</div>
                                <div class="text-xs text-gray-500 sm:hidden">DNI: {{ $l->dni }}</div>
                            </td>
                            <td class="table-cell hidden sm:table-cell text-gray-600">{{ $l->dni }}</td>
                            <td class="table-cell hidden md:table-cell text-gray-500 text-xs">
                                {{ $l->fechnaci ? $l->fechnaci->format('d/m/Y') : '—' }}
                            </td>
                            <td class="table-cell hidden lg:table-cell text-gray-500">{{ $l->legajo ?: '—' }}</td>
                            <td class="table-cell text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button wire:click="openEdit({{ $l->id }})"
                                            class="btn-secondary btn-sm">Editar</button>
                                    <button wire:click="confirmDelete({{ $l->id }})"
                                            class="btn-danger btn-sm">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="table-cell text-center text-gray-400 py-10">
                                @if ($search)
                                    No se encontraron legajos para "{{ $search }}".
                                @else
                                    No hay legajos registrados para este nivel.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($legajos->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                {{ $legajos->links() }}
            </div>
        @endif

        <div class="px-4 py-2 border-t border-gray-100 text-xs text-gray-400">
            {{ $legajos->total() }} legajos encontrados
        </div>
    </div>

    {{-- ═══════════════════ FORM MODAL ═══════════════════ --}}
    @if ($showModal)
    <div class="fixed inset-0 z-50 flex items-start justify-center bg-gray-900/60 overflow-y-auto py-4 px-2"
         x-data>
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-3xl my-auto" @click.stop>

            {{-- Modal header --}}
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white rounded-t-lg z-10">
                <h3 class="text-base font-semibold text-gray-800">
                    {{ $editId ? 'Editar legajo' : 'Nuevo legajo' }}
                </h3>
                <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="border-b border-gray-200 overflow-x-auto">
                <nav class="flex whitespace-nowrap px-4 gap-0">
                    @foreach ([
                        'alumno'    => 'Alumno',
                        'domicilio' => 'Domicilio',
                        'madre'     => 'Madre',
                        'padre'     => 'Padre',
                        'tutor'     => 'Tutor',
                        'escolar'   => 'Escolaridad',
                    ] as $tab => $label)
                        <button wire:click="setTab('{{ $tab }}')"
                                @class([
                                    'px-4 py-3 text-sm font-medium border-b-2 transition-colors',
                                    'border-primary-600 text-primary-700' => $activeTab === $tab,
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' => $activeTab !== $tab,
                                ])>
                            {{ $label }}
                        </button>
                    @endforeach
                </nav>
            </div>

            {{-- Tab contents --}}
            <div class="px-6 py-5 space-y-4 max-h-[60vh] overflow-y-auto">

                {{-- ── TAB ALUMNO ── --}}
                @if ($activeTab === 'alumno')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Apellido *</label>
                            <input wire:model="apellido" type="text" maxlength="50" class="form-input @error('apellido') border-red-400 @enderror">
                            @error('apellido') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Nombre *</label>
                            <input wire:model="nombre" type="text" maxlength="50" class="form-input @error('nombre') border-red-400 @enderror">
                            @error('nombre') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">DNI *</label>
                            <input wire:model="dni" type="text" inputmode="numeric" maxlength="11" class="form-input @error('dni') border-red-400 @enderror">
                            @error('dni') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">CUIL</label>
                            <input wire:model="cuil" type="text" maxlength="13" placeholder="Ej: 20-12345678-9" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Fecha de nacimiento</label>
                            <input wire:model="fechnaci" type="date" class="form-input @error('fechnaci') border-red-400 @enderror">
                            @error('fechnaci') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Sexo</label>
                            <select wire:model="sexo" class="form-select">
                                <option value="">— Seleccione —</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="X">No binario</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Nacionalidad</label>
                            <input wire:model="nacion" type="text" maxlength="20" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Tipo de alumno</label>
                            <select wire:model="tipoalumno" class="form-select">
                                <option value="0">Regular</option>
                                <option value="1">Oyente</option>
                                <option value="2">Libre</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Nivel *</label>
                            <select wire:model="idnivel" class="form-select @error('idnivel') border-red-400 @enderror">
                                <option value="">— Seleccione —</option>
                                @foreach ($niveles as $n)
                                    <option value="{{ $n->id }}">{{ $n->nivel }}</option>
                                @endforeach
                            </select>
                            @error('idnivel') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Familia</label>
                            <select wire:model="idFamilias" class="form-select @error('idFamilias') border-red-400 @enderror">
                                @foreach ($familias as $f)
                                    <option value="{{ $f->id }}">{{ $f->apellido }}{{ $f->responsable ? ' – ' . $f->responsable : '' }}</option>
                                @endforeach
                            </select>
                            @error('idFamilias') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">N° Legajo</label>
                            <input wire:model="legajo" type="text" maxlength="10" class="form-input">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="form-label">Libro</label>
                                <input wire:model="libro" type="text" maxlength="10" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Folio</label>
                                <input wire:model="folio" type="text" maxlength="10" class="form-input">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── TAB DOMICILIO ── --}}
                @if ($activeTab === 'domicilio')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="form-label">Calle y número</label>
                            <input wire:model="callenum" type="text" maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Barrio</label>
                            <input wire:model="barrio" type="text" maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Localidad</label>
                            <input wire:model="localidad" type="text" maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Código postal</label>
                            <input wire:model="codpos" type="text" maxlength="10" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Teléfono</label>
                            <input wire:model="telefono" type="text" maxlength="60" class="form-input">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Email</label>
                            <input wire:model="email" type="email" maxlength="100" class="form-input @error('email') border-red-400 @enderror">
                            @error('email') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <p class="sm:col-span-2 text-xs font-medium text-gray-500 pt-1">Lugar de nacimiento</p>
                        <div>
                            <label class="form-label">Ciudad</label>
                            <input wire:model="ln_ciudad" type="text" maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Departamento</label>
                            <input wire:model="ln_depto" type="text" maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Provincia</label>
                            <input wire:model="ln_provincia" type="text" maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">País</label>
                            <input wire:model="ln_pais" type="text" maxlength="50" class="form-input">
                        </div>
                    </div>
                @endif

                {{-- ── TAB MADRE ── --}}
                @if ($activeTab === 'madre')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="form-label">Nombre completo</label>
                            <input wire:model="nombremad" type="text" maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">DNI</label>
                            <input wire:model="dnimad" type="text" maxlength="10" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Fecha de nacimiento</label>
                            <input wire:model="fechnacmad" type="date" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Nacionalidad</label>
                            <input wire:model="nacionmad" type="text" maxlength="20" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Estado civil</label>
                            <input wire:model="estacivimad" type="text" maxlength="20" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">¿Vive con el alumno?</label>
                            <select wire:model="vivemad" class="form-select">
                                <option value="">—</option>
                                <option value="si">Sí</option>
                                <option value="no">No</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Ocupación</label>
                            <input wire:model="ocupacmad" type="text" maxlength="30" class="form-input">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Domicilio</label>
                            <input wire:model="domimad" type="text" maxlength="100" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Teléfono</label>
                            <input wire:model="telemad" type="text" maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Celular</label>
                            <input wire:model="telecelmad" type="text" maxlength="50" class="form-input">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Email</label>
                            <input wire:model="emailmad" type="email" maxlength="50" class="form-input @error('emailmad') border-red-400 @enderror">
                            @error('emailmad') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endif

                {{-- ── TAB PADRE ── --}}
                @if ($activeTab === 'padre')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="form-label">Nombre completo</label>
                            <input wire:model="nombrepad" type="text" maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">DNI</label>
                            <input wire:model="dnipad" type="text" maxlength="10" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Fecha de nacimiento</label>
                            <input wire:model="fechnacpad" type="date" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Nacionalidad</label>
                            <input wire:model="nacionpad" type="text" maxlength="20" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Estado civil</label>
                            <input wire:model="estacivipad" type="text" maxlength="20" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">¿Vive con el alumno?</label>
                            <select wire:model="vivepad" class="form-select">
                                <option value="">—</option>
                                <option value="si">Sí</option>
                                <option value="no">No</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Ocupación</label>
                            <input wire:model="ocupacpad" type="text" maxlength="30" class="form-input">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Domicilio</label>
                            <input wire:model="domipad" type="text" maxlength="100" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Teléfono</label>
                            <input wire:model="telepad" type="text" maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Celular</label>
                            <input wire:model="telecelpad" type="text" maxlength="50" class="form-input">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Email</label>
                            <input wire:model="emailpad" type="email" maxlength="50" class="form-input @error('emailpad') border-red-400 @enderror">
                            @error('emailpad') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endif

                {{-- ── TAB TUTOR ── --}}
                @if ($activeTab === 'tutor')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <p class="sm:col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tutor / Referente</p>
                        <div class="sm:col-span-2">
                            <label class="form-label">Nombre</label>
                            <input wire:model="nombretut" type="text" maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">DNI</label>
                            <input wire:model="dnitut" type="text" inputmode="numeric" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Teléfono</label>
                            <input wire:model="teletut" type="text" maxlength="20" class="form-input">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Email</label>
                            <input wire:model="emailtut" type="email" maxlength="50" class="form-input @error('emailtut') border-red-400 @enderror">
                            @error('emailtut') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        <p class="sm:col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wide pt-2">Responsable administrativo</p>
                        <div>
                            <label class="form-label">Nombre</label>
                            <input wire:model="respAdmiNom" type="text" maxlength="100" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">DNI</label>
                            <input wire:model="respAdmiDni" type="text" inputmode="numeric" class="form-input">
                        </div>
                    </div>
                @endif

                {{-- ── TAB ESCOLARIDAD ── --}}
                @if ($activeTab === 'escolar')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Escuela de origen</label>
                            <input wire:model="escori" type="text" maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Destino</label>
                            <input wire:model="destino" type="text" maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Parroquia</label>
                            <input wire:model="parroquia" type="text" maxlength="50" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Estado civil de los padres</label>
                            <input wire:model="ec_padres" type="text" maxlength="30" class="form-input">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Vive con</label>
                            <input wire:model="vivecon" type="text" maxlength="200" class="form-input">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Hermanos</label>
                            <textarea wire:model="hermanos" rows="2" class="form-input resize-none"></textarea>
                        </div>
                        <div>
                            <label class="form-label">¿Necesidades especiales?</label>
                            <select wire:model="needes" class="form-select">
                                <option value="">No</option>
                                <option value="si">Sí</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Certif. discapacidad</label>
                            <input wire:model="certDisc" type="text" maxlength="100" class="form-input">
                        </div>
                        @if ($needes === 'si')
                            <div class="sm:col-span-2">
                                <label class="form-label">Detalle necesidades especiales</label>
                                <textarea wire:model="needes_detalle" rows="2" class="form-input resize-none"></textarea>
                            </div>
                        @endif
                        <div class="sm:col-span-2">
                            <label class="form-label">Identificación (CUIL u otro)</label>
                            <input wire:model="identif" type="text" maxlength="100" class="form-input">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Personas autorizadas a retirar</label>
                            <textarea wire:model="retira" rows="2" class="form-input resize-none"></textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Contacto de emergencia</label>
                            <textarea wire:model="emeravis" rows="2" class="form-input resize-none"></textarea>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Observaciones</label>
                            <textarea wire:model="obs" rows="3" class="form-input resize-none"></textarea>
                        </div>
                    </div>
                @endif

            </div>{{-- end tab contents --}}

            {{-- Modal footer --}}
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between rounded-b-lg">
                <div class="text-xs text-gray-400">
                    Los campos marcados con * son obligatorios
                </div>
                <div class="flex gap-3">
                    <button wire:click="$set('showModal', false)" class="btn-secondary">Cancelar</button>
                    <button wire:click="save"
                            wire:loading.attr="disabled"
                            class="btn-primary">
                        <span wire:loading.remove wire:target="save">Guardar legajo</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
    @endif

    {{-- ═══════════════════ CONFIRM / INFO MODAL ═══════════════════ --}}
    @if ($showConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-sm" @click.stop>
                <div class="px-6 py-5">
                    <div class="flex items-start gap-3">
                        @if ($deleteId)
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                            </div>
                        @else
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 mb-1">
                                {{ $deleteId ? 'Confirmar eliminación' : 'No se puede eliminar' }}
                            </h3>
                            <p class="text-sm text-gray-600">{{ $deleteInfo }}</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 pb-5 flex justify-end gap-3">
                    <button wire:click="$set('showConfirm', false)" class="btn-secondary">
                        {{ $deleteId ? 'Cancelar' : 'Cerrar' }}
                    </button>
                    @if ($deleteId)
                        <button wire:click="delete"
                                wire:loading.attr="disabled"
                                class="btn-danger">
                            <span wire:loading.remove wire:target="delete">Eliminar</span>
                            <span wire:loading wire:target="delete">Eliminando…</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>
