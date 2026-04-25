<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? (isset($title) ? $title . ' — ' : '') }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            --se-jet: #333333;
            --se-primary: #40848D;
            --se-hover: rgba(64, 132, 141, 0.2);
            --se-sep: rgba(255, 255, 255, 0.10);
            --se-white-85: rgba(255, 255, 255, 0.85);
            --se-white-05: rgba(255, 255, 255, 0.05);
            --se-white-10: rgba(255, 255, 255, 0.10);
            --se-sidebar-w: 23.04rem; /* +20% sobre 19.2rem (total +44% vs 16rem) */
            --se-sidebar-w-collapsed: 5rem; /* md:w-20 */
        }
        .se-sidebar {
            background: var(--se-jet);
            color: #fff;
            font-family: "Roboto Condensed", "Arial Narrow", "Helvetica Neue", "Noto Sans", system-ui, -apple-system, "Segoe UI", sans-serif;
            font-stretch: condensed;
            width: var(--se-sidebar-w);
            overflow-x: hidden;
        }
        @media (min-width: 768px) {
            /* En desktop, al contraer: no dejar barra con íconos, ocultar por completo */
            .se-sidebar.is-collapsed { width: 0; }
        }
        .se-sidebar-sep { border-color: var(--se-sep); }
        .se-sidebar-iconbtn { color: var(--se-white-85); }
        .se-sidebar-iconbtn:hover { background: var(--se-white-10); color: #fff; }
        .se-sidebar-groupbtn { color: var(--se-white-85); background: var(--se-white-05); border: 1px solid var(--se-sep); }
        .se-sidebar-groupbtn:hover { background: var(--se-white-10); }
        .se-sidebar-groupbtn.is-open { background: var(--se-white-10); }
        .se-sidebar-link { color: var(--se-white-85); }
        .se-sidebar-link:hover { background: var(--se-hover); color: #fff; }
        .se-sidebar-link.is-active { background: var(--se-primary); color: #fff; }
        .se-main {
            width: 100%;
            min-width: 0;
            transition: transform 200ms ease-in-out, width 200ms ease-in-out;
            transform: translateX(0);
        }
        @media (min-width: 768px) {
            .se-main {
                transform: translateX(var(--se-sidebar-w));
                width: calc(100% - var(--se-sidebar-w));
            }
            .se-main.is-collapsed {
                transform: translateX(0);
                width: 100%;
            }
        }
        @media (max-width: 767px) {
            .se-main.is-mobile-open {
                transform: translateX(var(--se-sidebar-w));
                width: calc(100% - var(--se-sidebar-w));
            }
        }
    </style>
</head>
@php $route = request()->route()?->getName(); @endphp
<body class="h-full">

{{-- Livewire puede usar el <body> como raíz; el estado del layout va en un wrapper para evitar choques con Alpine. --}}
<div id="se-shell"
     class="h-full"
     @se-sidebar-post-nav-collapse="applyPostNavCollapse()"
     x-data="{
    sidebarOpen: false,
    sidebarCollapsed: false,
    groups: {
        config: {{ (str_starts_with($route ?? '', 'abm.terlec') || str_starts_with($route ?? '', 'abm.niveles') || str_starts_with($route ?? '', 'abm.cursos') || str_starts_with($route ?? '', 'abm.planes') || str_starts_with($route ?? '', 'abm.curplan') || str_starts_with($route ?? '', 'abm.materias-anio') || str_starts_with($route ?? '', 'param.')) ? 'true' : 'false' }},
        planesCursos: {{ (str_starts_with($route ?? '', 'abm.planes') || str_starts_with($route ?? '', 'abm.curplan')) ? 'true' : 'false' }},
        cursosMateriasAno: {{ (str_starts_with($route ?? '', 'abm.cursos') || str_starts_with($route ?? '', 'abm.materias-anio')) ? 'true' : 'false' }},
        students: {{ (str_starts_with($route ?? '', 'abm.legajos') || str_starts_with($route ?? '', 'listados.')) ? 'true' : 'false' }},
        calificacionesSec: {{ (str_starts_with($route ?? '', 'calificaciones.')) ? 'true' : 'false' }},
    },
    init() {
        const pendingMenuCollapse = localStorage.getItem('sidebarCollapseNext') === '1';
        // Llegamos desde un link del menú: mostrar sidebar expandido un instante y colapsar después
        // (no borrar `sidebarCollapseNext` acá: Alpine/Livewire puede llamar init() más de una vez).
        this.sidebarCollapsed = pendingMenuCollapse
            ? false
            : (localStorage.getItem('sidebarCollapsed') === '1');

        const raw = localStorage.getItem('sidebarGroups');
        if (raw) {
            try {
                const parsed = JSON.parse(raw);
                if (parsed && typeof parsed === 'object') this.groups = { ...this.groups, ...parsed };
            } catch (e) {}
        }

        if (pendingMenuCollapse) {
            this.sidebarOpen = false;
            const collapseAfterPaint = () => {
                if (localStorage.getItem('sidebarCollapseNext') !== '1') return;
                this.applyPostNavCollapse();
            };

            requestAnimationFrame(() => requestAnimationFrame(collapseAfterPaint));
            window.setTimeout(collapseAfterPaint, 350);
        }
    },
    applyPostNavCollapse() {
        if (window.matchMedia && window.matchMedia('(min-width: 768px)').matches) {
            this.sidebarCollapsed = true;
            localStorage.setItem('sidebarCollapsed', '1');
        }
        localStorage.removeItem('sidebarCollapseNext');
    },
    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed ? '1' : '0');
    },
    toggleGroup(key) {
        this.groups[key] = !this.groups[key];
        localStorage.setItem('sidebarGroups', JSON.stringify(this.groups));
    },
}">

{{-- Mobile sidebar backdrop --}}
<div x-show="sidebarOpen"
     x-transition:enter="transition-opacity ease-linear duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-30 bg-gray-900/50 md:hidden"
     @click="sidebarOpen = false"
     style="display:none"></div>

{{-- Sidebar --}}
<aside class="se-sidebar fixed inset-y-0 left-0 z-40 flex flex-col transform transition-transform duration-200 ease-in-out
              md:translate-x-0 md:transition-[width] md:duration-200 md:ease-in-out md:shadow-lg"
       :class="[
           (sidebarOpen || (!sidebarCollapsed)) ? 'translate-x-0' : '-translate-x-full md:-translate-x-full',
           sidebarCollapsed ? 'is-collapsed' : ''
       ]">

    {{-- Header compacto: Nivel - Año - Usuario + contraer/expandir --}}
    <div class="h-12 px-2.5 border-b se-sidebar-sep flex items-center justify-between gap-2">
        @php
            $sidebarSessionLine = schoolCtx()->nivelNombre()
                . ' - ' . schoolCtx()->terlecAno()
                . ' - ' . trim((Auth::user()->nombre ?? '') . ' ' . (Auth::user()->apellido ?? ''));
        @endphp

        <p class="text-white text-[12px] font-semibold truncate min-w-0 flex-1 leading-tight"
           x-show="!sidebarCollapsed" x-cloak
           title="{{ $sidebarSessionLine }}">
            {{ $sidebarSessionLine }}
        </p>

        <button type="button"
                class="se-sidebar-iconbtn hidden md:inline-flex items-center justify-center w-9 h-9 rounded-md transition-colors flex-shrink-0"
                :title="sidebarCollapsed ? 'Expandir menú' : 'Contraer menú'"
                @click="toggleSidebar()">
            {{-- Ícono "menú hamburguesa" redondo --}}
            <svg x-show="!sidebarCollapsed" x-cloak class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                <path stroke-linecap="round" stroke-width="2" d="M8 10h8"/>
                <path stroke-linecap="round" stroke-width="2" d="M8 12.75h8"/>
                <path stroke-linecap="round" stroke-width="2" d="M8 15.5h8"/>
            </svg>
            <svg x-show="sidebarCollapsed" x-cloak class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                <path stroke-linecap="round" stroke-width="2" d="M8 10h8"/>
                <path stroke-linecap="round" stroke-width="2" d="M8 12.75h8"/>
                <path stroke-linecap="round" stroke-width="2" d="M8 15.5h8"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-2.5 py-3 overflow-y-auto space-y-0.5"
         @pointerdown.capture="$event.target.closest('a[href]') && localStorage.setItem('sidebarCollapseNext', '1')"
         @click.capture="$event.target.closest('a[href]') && (localStorage.setItem('sidebarCollapseNext', '1'), sidebarOpen = false)">

        {{-- Estudiantes --}}
        @if(tienePermiso(2))
            <button type="button"
                    class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors"
                    :class="(groups.students && !sidebarCollapsed) ? 'is-open' : ''"
                    @click="toggleGroup('students')"
                    title="Estudiantes">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="truncate flex-1 text-left">Estudiantes</span>
                <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                     :class="groups.students ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="mt-1 space-y-0.5 pl-1"
                 x-show="groups.students && !sidebarCollapsed"
                 x-collapse
                 x-cloak>
                <a href="{{ route('abm.legajos') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'abm.legajos'),
                   ])
                   title="Legajos de Estudiantes">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="truncate">Legajos de Estudiantes</span>
                </a>

                <a href="{{ route('listados.por-curso') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'listados.'),
                   ])
                   title="Listado por curso (PDF)">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="truncate">Listado por curso</span>
                </a>
            </div>
        @endif

        {{-- Calificaciones Secundario --}}
        @if(tienePermiso(2))
            <div class="mt-4"></div>
            <button type="button"
                    class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors"
                    :class="(groups.calificacionesSec && !sidebarCollapsed) ? 'is-open' : ''"
                    @click="toggleGroup('calificacionesSec')"
                    title="Calificaciones Secundario">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="truncate flex-1 text-left">Calificaciones Secundario</span>
                <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                     :class="groups.calificacionesSec ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="mt-1 space-y-0.5 pl-1"
                 x-show="groups.calificacionesSec && !sidebarCollapsed"
                 x-collapse
                 x-cloak>
                <a href="{{ route('calificaciones.carga') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'calificaciones.'),
                   ])
                   title="Carga de calificaciones">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="truncate">Carga de calificaciones</span>
                </a>
            </div>
        @endif

        {{-- Configuración --}}
        @if(tienePermiso(1))
            <div class="mt-4"></div>
            <button type="button"
                    class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors"
                    :class="(groups.config && !sidebarCollapsed) ? 'is-open' : ''"
                    @click="toggleGroup('config')"
                    title="Configuración">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 16v-2m8-6h-2M6 12H4m14.364 6.364l-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0L16.95 7.05M7.05 16.95l-1.414 1.414"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="truncate flex-1 text-left">Configuración</span>
                <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                     :class="groups.config ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="mt-1 space-y-0.5 pl-1"
                 x-show="groups.config && !sidebarCollapsed"
                 x-collapse
                 x-cloak>
                <a href="{{ route('abm.terlec') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'abm.terlec'),
                   ])
                   title="Términos Lectivos">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="truncate">Términos Lectivos</span>
                </a>

                <a href="{{ route('abm.niveles') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'abm.niveles'),
                   ])
                   title="Niveles">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 7h18M3 12h18M3 17h18"/>
                    </svg>
                    <span class="truncate">Niveles</span>
                </a>

                <a href="{{ route('param.campos-listado-alumnos') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'param.campos-listado-alumnos'),
                   ])
                   title="Campos Disponibles Listado Alumnos">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span class="truncate">Campos Disponibles Listado Alumnos</span>
                </a>

                {{-- Planes + Cursos modelo --}}
                <button type="button"
                        class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors mt-2"
                        :class="(groups.planesCursos && !sidebarCollapsed) ? 'is-open' : ''"
                        @click="toggleGroup('planesCursos')"
                        title="Gestión de planes y cursos modelo">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6V4m0 16v-2m8-6h-2M6 12H4m14.364 6.364l-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0L16.95 7.05M7.05 16.95l-1.414 1.414"/>
                    </svg>
                    <span class="truncate flex-1 text-left">GESTIÓN DE PLANES Y CURSOS MODELO</span>
                    <svg class="w-4 h-4 transition-transform"
                         :class="groups.planesCursos ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div class="space-y-0.5 pl-1"
                     x-show="groups.planesCursos"
                     x-collapse
                     x-cloak>
                    <a href="{{ route('abm.planes') }}"
                       @class([
                           'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                           'is-active shadow-sm' => str_starts_with($route ?? '', 'abm.planes'),
                       ])
                       title="Gestión de Planes de Estudio">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        <span class="truncate">Gestión de Planes de Estudio</span>
                    </a>

                    <a href="{{ route('abm.curplan') }}"
                       @class([
                           'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                           'is-active shadow-sm' => str_starts_with($route ?? '', 'abm.curplan'),
                       ])
                       title="Gestión de Cursos y Materias del Plan">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="truncate">Gestión de Cursos y Materias del Plan</span>
                    </a>
                </div>

                {{-- Cursos + Materias del año --}}
                <button type="button"
                        class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors mt-2"
                        :class="(groups.cursosMateriasAno && !sidebarCollapsed) ? 'is-open' : ''"
                        @click="toggleGroup('cursosMateriasAno')"
                        title="Gestión de cursos y materias del año">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6V4m0 16v-2m8-6h-2M6 12H4m14.364 6.364l-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0L16.95 7.05M7.05 16.95l-1.414 1.414"/>
                    </svg>
                    <span class="truncate flex-1 text-left">GESTION DE CURSOS Y MATERIAS DEL AÑO</span>
                    <svg class="w-4 h-4 transition-transform"
                         :class="groups.cursosMateriasAno ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div class="space-y-0.5 pl-1"
                     x-show="groups.cursosMateriasAno"
                     x-collapse
                     x-cloak>
                    <a href="{{ route('abm.cursos') }}"
                       @class([
                           'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                           'is-active shadow-sm' => str_starts_with($route ?? '', 'abm.cursos'),
                       ])
                       title="Gestión de Cursos / Grados / Salas">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="truncate">Gestión de Cursos / Grados / Salas</span>
                    </a>

                    <a href="{{ route('abm.materias-anio') }}"
                       @class([
                           'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                           'is-active shadow-sm' => str_starts_with($route ?? '', 'abm.materias-anio'),
                       ])
                       title="Gestión de asignaturas del año">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        <span class="truncate">Gestión de asignaturas del año</span>
                    </a>
                </div>

            </div>

        @endif

    </nav>

    {{-- User footer --}}
    <div class="px-4 py-3 border-t se-sidebar-sep">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                 style="background: var(--se-primary);">
                <span class="text-white text-xs font-bold">
                    {{ strtoupper(substr(Auth::user()->apellido ?? 'U', 0, 1)) }}
                </span>
            </div>
            <div class="flex-1 min-w-0" x-show="!sidebarCollapsed" x-cloak>
                <p class="text-white text-xs font-medium truncate">
                    {{ Auth::user()->nombre ?? '' }} {{ Auth::user()->apellido ?? '' }}
                </p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        title="Cerrar sesión"
                        class="text-white/85 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Main content area --}}
<div class="se-main flex flex-col min-h-screen transition-[padding] duration-200 ease-in-out"
     :class="[
        sidebarCollapsed ? 'is-collapsed' : '',
        sidebarOpen ? 'is-mobile-open' : ''
     ]">

    {{-- Botón flotante (desktop) cuando el sidebar está contraído --}}
    <button type="button"
            x-show="sidebarCollapsed"
            x-cloak
            @click="toggleSidebar()"
            class="hidden md:inline-flex fixed z-50 top-4 left-3 items-center justify-center w-10 h-10 rounded-full bg-white shadow-md border border-gray-200 text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition"
            title="Expandir menú"
            aria-label="Expandir menú">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    {{-- Top bar (mobile) --}}
    <header class="sticky top-0 z-20 bg-white border-b border-gray-200 md:hidden">
        <div class="flex items-center gap-3 h-14 px-4">
            <button @click="sidebarOpen = true"
                    class="text-gray-500 hover:text-gray-700 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <span class="font-semibold text-gray-800 text-sm">
                @yield('pageTitle', config('app.name'))
            </span>
        </div>
    </header>

    {{-- Page content --}}
    <main class="flex-1 p-4 md:p-6">
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot ?? '' }}
        @endif
    </main>
</div>

</div>

@livewireScripts
<script>
    (() => {
        const IDLE_TIMEOUT_MS = 15 * 60 * 1000;
        const LOGOUT_URL = @json(route('logout'));
        const LOGIN_URL = @json(route('login'));

        let timer = null;
        let hasTriggered = false;

        const getCsrfToken = () =>
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const logoutAndRedirect = async () => {
            if (hasTriggered) return;
            hasTriggered = true;

            try {
                await fetch(LOGOUT_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });
            } catch (e) {
                // Si falla el request, igual redirigimos al login.
            } finally {
                window.location.assign(LOGIN_URL);
            }
        };

        const resetTimer = () => {
            if (hasTriggered) return;
            if (timer) window.clearTimeout(timer);
            timer = window.setTimeout(logoutAndRedirect, IDLE_TIMEOUT_MS);
        };

        const activityEvents = [
            'mousemove',
            'mousedown',
            'keydown',
            'scroll',
            'touchstart',
            'pointerdown',
        ];

        activityEvents.forEach((evt) => {
            window.addEventListener(evt, resetTimer, { passive: true });
        });

        window.addEventListener('focus', resetTimer);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) resetTimer();
        });

        resetTimer();
    })();
</script>
</body>
</html>
