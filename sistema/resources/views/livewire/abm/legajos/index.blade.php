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
            <p class="text-sm text-gray-500 mt-0.5">Listado completo de legajos · Año de sesión: {{ schoolCtx()->terlecAno() }}</p>
        </div>
        <a href="{{ route('abm.legajos.create') }}" class="btn-primary btn-sm sm:self-start">
            + Nuevo legajo
        </a>
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
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer whitespace-nowrap">
            <input wire:model.live="soloMatricula" type="checkbox" class="rounded border-gray-300 text-primary-600">
            Solo matriculados año activo
        </label>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse border border-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-header w-[min(28%,18rem)]">Estudiante</th>
                        <th class="table-header w-32">DNI</th>
                        <th class="table-header">Matriculaciones en la escuela</th>
                        <th class="table-header text-right w-36 shrink-0">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse ($legajos as $l)
                        <tr id="legajo-{{ $l->id }}"
                            x-data="{ focus: {{ (int) $focusId === (int) $l->id ? 'true' : 'false' }} }"
                            x-init="if (focus) { $nextTick(() => { const el = document.getElementById('legajo-{{ $l->id }}'); el?.scrollIntoView({ block: 'center' }); el?.classList.add('ring-2','ring-primary-400','bg-primary-50/30'); el?.querySelector('a[data-focus-target]')?.focus(); }); }"
                            class="align-top hover:bg-gray-50 transition-colors">
                            <td class="table-cell">
                                <div class="font-medium text-gray-900">{{ $l->apellido }}, {{ $l->nombre }}</div>
                            </td>
                            <td class="table-cell font-mono text-gray-700">
                                {{ $l->dni }}
                            </td>
                            <td class="table-cell py-2">
                                @if ($l->matriculas->isEmpty())
                                    <span class="text-xs text-gray-400 italic">Sin matrículas</span>
                                @else
                                    <div class="gf text-[10px] w-max max-w-full">
                                        <div class="gf-head">
                                            <div class="gf-th w-14 px-1">Año</div>
                                            <div class="gf-th w-44">Curso</div>
                                            <div class="gf-th w-24 px-1">Cond.</div>
                                        </div>
                                        @foreach ($l->matriculas as $mat)
                                            <div @class([
                                                'gf-row',
                                                'bg-amber-50/80' => (int) ($mat->idTerlec ?? 0) === (int) schoolCtx()->idTerlec,
                                            ])>
                                                <div class="gf-td w-14 px-1 font-mono font-semibold text-gray-800">
                                                    {{ $mat->terlec?->ano ?? '—' }}
                                                </div>
                                                <div class="gf-td w-44 truncate" title="{{ $mat->curso?->cursec }}">
                                                    {{ $mat->curso?->cursec ? trim($mat->curso->cursec) : '—' }}
                                                </div>
                                                <div class="gf-td w-24 px-1 truncate" title="{{ $mat->condicion?->condicion }}">
                                                    {{ \Illuminate\Support\Str::limit($mat->condicion?->condicion ?? '—', 12) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="table-cell text-right whitespace-nowrap">
                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-1.5">
                                    <a data-focus-target href="{{ route('abm.legajos.edit', ['id' => $l->id]) }}"
                                       class="btn-secondary btn-sm">Editar</a>
                                    <button wire:click="confirmDelete({{ $l->id }})"
                                            class="btn-danger btn-sm">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="table-cell text-center text-gray-400 py-10">
                                @if ($search)
                                    No se encontraron legajos para "{{ $search }}".
                                @else
                                    No hay legajos registrados.
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

    {{-- ═══════════════════ FORM (EMBEBIDO, NO MODAL) ═══════════════════ --}}
    @if ($showModal)
        <div class="card overflow-hidden mb-4" x-data>
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white">
                <div class="min-w-0">
                    <h3 class="text-base font-semibold text-gray-800">
                        {{ $editId ? 'Editar legajo' : 'Nuevo legajo' }}
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">Los campos marcados con * son obligatorios</p>
                </div>

                <div class="flex flex-wrap gap-2 sm:justify-end">
                    @if ($editId)
                        <button wire:click="openMatriculas" class="btn-secondary btn-sm">
                            Gestionar matrículas
                        </button>
                    @endif

                    <button wire:click="$set('showModal', false)" class="btn-secondary btn-sm">
                        Cancelar
                    </button>

                    <button wire:click="save"
                            wire:loading.attr="disabled"
                            class="btn-primary btn-sm">
                        <span wire:loading.remove wire:target="save">Guardar legajo</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
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

            {{-- Footer --}}
            <div class="px-6 py-3 border-t border-gray-100 bg-gray-50">
                <div class="text-[11px] text-gray-400">
                    Tip: podés desplazarte por las pestañas para completar el legajo.
                </div>
            </div>
    @endif

    {{-- ═══════════════════ MATRICULAS MODAL ═══════════════════ --}}
    @if ($showMatriculasModal)
        <div class="fixed inset-0 z-50 flex items-start justify-center bg-gray-900/60 overflow-y-auto py-4 px-2" x-data>
            <div class="bg-white rounded-lg shadow-2xl w-full max-w-4xl my-auto" @click.stop>
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white rounded-t-lg z-10">
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">Matrículas del estudiante</h3>
                    </div>
                    <button wire:click="closeMatriculas" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-4 flex items-center justify-between gap-3">
                    <div class="text-sm text-gray-600">
                        {{ $matriculasAlumno->count() }} registro(s)
                    </div>
                    <button wire:click="openNuevaMatricula" class="btn-primary btn-sm">+ NUEVA MATRÍCULA</button>
                </div>

                <div class="px-6 pb-6">
                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                        <table class="min-w-full border-collapse">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="table-header w-24">Año</th>
                                    <th class="table-header">Curso y sección</th>
                                    <th class="table-header w-40">Condición</th>
                                    <th class="table-header w-32">N°</th>
                                    <th class="table-header w-36">F. matrícula</th>
                                    <th class="table-header w-36">F. baja</th>
                                    <th class="table-header text-right w-36">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @forelse ($matriculasAlumno as $m)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="table-cell font-mono">{{ $m->terlec?->ano ?? '—' }}</td>
                                        <td class="table-cell">{{ $m->curso?->cursec ? trim($m->curso->cursec) : '—' }}</td>
                                        <td class="table-cell">{{ $m->condicion?->condicion ?? '—' }}</td>
                                        <td class="table-cell font-mono">{{ $m->nroMatricula ?? '—' }}</td>
                                        <td class="table-cell font-mono">{{ $m->fechaMatricula?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="table-cell font-mono">{{ $m->fechaBaja?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="table-cell text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <button wire:click="openEditMatricula({{ $m->id }})" class="btn-secondary btn-sm">Editar</button>
                                                <button wire:click="confirmDeleteMatricula({{ $m->id }})" class="btn-danger btn-sm">Borrar</button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="table-cell text-center text-gray-400 py-10">
                                            Sin matrículas cargadas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Matricula form (create/edit) --}}
                @if ($showMatriculaForm)
                    <div class="border-t border-gray-100 bg-gray-50 px-6 py-5">
                        <h4 class="text-sm font-semibold text-gray-800 mb-3">{{ $matriculaEditId ? 'Editar matrícula' : 'Nueva matrícula' }}</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="lg:col-span-2">
                                <label class="form-label">Curso y sección *</label>
                                <select wire:model="m_idCursos" class="form-select @error('m_idCursos') border-red-400 @enderror">
                                    <option value="">— Seleccione —</option>
                                    @foreach ($cursos as $c)
                                        <option value="{{ $c->Id }}">{{ trim($c->cursec) }}</option>
                                    @endforeach
                                </select>
                                @error('m_idCursos') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="form-label">Condición *</label>
                                <select wire:model="m_idCondiciones" class="form-select @error('m_idCondiciones') border-red-400 @enderror">
                                    <option value="">— Seleccione —</option>
                                    @foreach ($condiciones as $cnd)
                                        <option value="{{ $cnd->id }}">{{ $cnd->condicion }}</option>
                                    @endforeach
                                </select>
                                @error('m_idCondiciones') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="form-label">Año lectivo</label>
                                <input wire:model="m_idTerlec" type="text" class="form-input bg-gray-100" readonly>
                            </div>

                            <div>
                                <label class="form-label">Nivel</label>
                                <input wire:model="m_idNivel" type="text" class="form-input bg-gray-100" readonly>
                            </div>

                            <div>
                                <label class="form-label">Número de matrícula</label>
                                <input wire:model="m_nroMatricula" type="text" maxlength="20" class="form-input">
                            </div>

                            <div>
                                <label class="form-label">Fecha de matrícula</label>
                                <input wire:model="m_fechaMatricula" type="date" class="form-input @error('m_fechaMatricula') border-red-400 @enderror">
                                @error('m_fechaMatricula') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="form-label">Fecha de baja</label>
                                <input wire:model="m_fechaBaja" type="date" class="form-input @error('m_fechaBaja') border-red-400 @enderror">
                                @error('m_fechaBaja') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end gap-3">
                            <button wire:click="$set('showMatriculaForm', false)" class="btn-secondary">Cancelar</button>
                            <button wire:click="saveMatricula" wire:loading.attr="disabled" class="btn-primary">
                                <span wire:loading.remove wire:target="saveMatricula">Guardar matrícula</span>
                                <span wire:loading wire:target="saveMatricula">Guardando…</span>
                            </button>
                        </div>
                    </div>
                @endif
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

    {{-- ═══════════════════ CONFIRM DELETE MATRICULA ═══════════════════ --}}
    @if ($showMatriculaConfirm)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-gray-900/60">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-sm" @click.stop>
                <div class="px-6 py-5">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 mb-1">Confirmar eliminación</h3>
                            <p class="text-sm text-gray-600">{{ $matriculaDeleteInfo }}</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 pb-5 flex justify-end gap-3">
                    <button wire:click="$set('showMatriculaConfirm', false)" class="btn-secondary">Cancelar</button>
                    <button wire:click="deleteMatricula" wire:loading.attr="disabled" class="btn-danger">
                        <span wire:loading.remove wire:target="deleteMatricula">Eliminar</span>
                        <span wire:loading wire:target="deleteMatricula">Eliminando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
