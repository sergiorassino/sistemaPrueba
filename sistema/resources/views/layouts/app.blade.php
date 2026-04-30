<!DOCTYPE html>
<html lang="es" class="h-full bg-[#F4F8F9]">
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
            --se-light-blue: #C1D7DA;
            --se-hover-bg: rgba(193, 215, 218, 0.18);
            --se-sep: rgba(193, 215, 218, 0.22);
            --se-white-85: rgba(255, 255, 255, 0.85);
            --se-white-05: rgba(255, 255, 255, 0.05);
            --se-white-10: rgba(255, 255, 255, 0.10);
            --se-sidebar-w: 23.04rem;
            --se-sidebar-w-collapsed: 5rem;
        }
        /* Sin position:relative aquí: pisaría Tailwind `fixed` y el sidebar pasaría al flujo (contenido debajo). */
        .se-sidebar {
            background-color: var(--se-jet);
            color: #fff;
            font-family: "Roboto Condensed", "Arial Narrow", "Helvetica Neue", "Noto Sans", system-ui, -apple-system, "Segoe UI", sans-serif;
            font-stretch: condensed;
            width: var(--se-sidebar-w);
            overflow-x: hidden;
        }
        .se-sidebar::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(ellipse 142% 86% at 0% 0%, rgba(64, 132, 141, 0.60), transparent 65%),
                radial-gradient(ellipse 78% 52% at 100% 6%, rgba(64, 132, 141, 0.14), transparent 58%);
        }
        @media (min-width: 768px) {
            .se-sidebar.is-collapsed { width: var(--se-sidebar-w-collapsed); }
        }
        .se-sidebar-sep { border-color: var(--se-sep); }
        .se-sidebar-iconbtn { color: var(--se-white-85); }
        .se-sidebar-iconbtn:hover { background: var(--se-hover-bg); color: #fff; }
        .se-sidebar-groupbtn { color: var(--se-white-85); background: var(--se-white-05); border: 1px solid var(--se-sep); }
        .se-sidebar-groupbtn:hover { background: var(--se-hover-bg); }
        .se-sidebar-groupbtn.is-open { background: var(--se-white-10); }
        .se-sidebar-link { color: var(--se-white-85); }
        .se-sidebar-link:hover { background: var(--se-hover-bg); color: #fff; }
        .se-sidebar-link.is-active {
            background: var(--se-primary);
            color: #fff;
            box-shadow: inset 3px 0 0 var(--se-light-blue);
        }
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
                transform: translateX(var(--se-sidebar-w-collapsed));
                width: calc(100% - var(--se-sidebar-w-collapsed));
            }
        }
        @media (min-width: 768px) {
            .se-sidebar.is-collapsed .se-sidebar-groupbtn {
                justify-content: center;
                gap: 0;
                padding-left: 0.35rem;
                padding-right: 0.35rem;
            }
        }
    </style>
</head>
@php
    $route = request()->route()?->getName();
    /** En desktop el menú usa rail colapsado salvo dashboard; hover/focus lo expanden. */
    $isSidebarPeekMode = (($route ?? '') !== 'dashboard');
@endphp
<body class="h-full">

{{-- Livewire puede usar el <body> como raíz; el estado del layout va en un wrapper para evitar choques con Alpine. --}}
<div id="se-shell"
     class="h-full"
     x-data="{
    sidebarOpen: false,
    peekMenuMode: @json($isSidebarPeekMode),
    sidebarCollapsed: false,
    _sidebarPeekTimer: null,
    groups: {
        config: {{ (str_starts_with($route ?? '', 'abm.terlec') || str_starts_with($route ?? '', 'abm.niveles') || str_starts_with($route ?? '', 'abm.cursos') || str_starts_with($route ?? '', 'abm.planes') || str_starts_with($route ?? '', 'abm.curplan') || str_starts_with($route ?? '', 'abm.materias-anio') || str_starts_with($route ?? '', 'param.')) ? 'true' : 'false' }},
        planesCursos: {{ (str_starts_with($route ?? '', 'abm.planes') || str_starts_with($route ?? '', 'abm.curplan')) ? 'true' : 'false' }},
        cursosMateriasAno: {{ (str_starts_with($route ?? '', 'abm.cursos') || str_starts_with($route ?? '', 'abm.materias-anio')) ? 'true' : 'false' }},
        students: {{ (str_starts_with($route ?? '', 'abm.legajos') || str_starts_with($route ?? '', 'listados.') || str_starts_with($route ?? '', 'push.') || (str_starts_with($route ?? '', 'comunicaciones.') && tienePermiso(51) && tienePermiso(2))) ? 'true' : 'false' }},
        calificacionesSec: {{ (str_starts_with($route ?? '', 'calificaciones.')) ? 'true' : 'false' }},
        disciplinario: {{ (str_starts_with($route ?? '', 'seguimiento.disciplinario')) ? 'true' : 'false' }},
        comunicaciones: {{ (tienePermiso(51) && !tienePermiso(2) && (str_starts_with($route ?? '', 'comunicaciones.') || ($route ?? '') === 'param.com-canales')) ? 'true' : 'false' }},
    },
    isDesktopPeekLayout() {
        return window.matchMedia && window.matchMedia('(min-width: 768px)').matches;
    },
    peekSidebarExpandNow() {
        if (!this.peekMenuMode || !this.isDesktopPeekLayout()) return;
        clearTimeout(this._sidebarPeekTimer);
        this.sidebarCollapsed = false;
    },
    peekSidebarMaybeCollapseLater() {
        if (!this.peekMenuMode || !this.isDesktopPeekLayout()) return;
        clearTimeout(this._sidebarPeekTimer);
        this._sidebarPeekTimer = window.setTimeout(() => {
            const el = this.$refs.seSidebar;
            if (!el) return;
            if (el.matches(':hover')) return;
            if (el.contains(document.activeElement)) return;
            this.sidebarCollapsed = true;
        }, 200);
    },
    peekSidebarFocusOut(ev) {
        if (!this.peekMenuMode || !this.isDesktopPeekLayout()) return;
        const sidebar = this.$refs.seSidebar;
        const rt = ev.relatedTarget;
        if (sidebar && rt && sidebar.contains(rt)) return;
        this.peekSidebarMaybeCollapseLater();
    },
    init() {
        const raw = localStorage.getItem('sidebarGroups');
        if (raw) {
            try {
                const parsed = JSON.parse(raw);
                if (parsed && typeof parsed === 'object') this.groups = { ...this.groups, ...parsed };
            } catch (e) {}
        }
        // Desktop dashboard: sidebar ancho siempre; resto de rutas: rail hasta hover/focus.
        if (this.isDesktopPeekLayout() && this.peekMenuMode) {
            this.sidebarCollapsed = true;
        } else {
            this.sidebarCollapsed = false;
        }
        if (!this._sePeekResizeBound) {
            this._sePeekResizeBound = true;
            window.addEventListener('resize', () => {
                if (!this.peekMenuMode) {
                    this.sidebarCollapsed = false;
                    return;
                }
                if (this.isDesktopPeekLayout()) {
                    const el = this.$refs.seSidebar;
                    if (el && (el.matches(':hover') || el.contains(document.activeElement))) return;
                    this.sidebarCollapsed = true;
                } else {
                    this.sidebarCollapsed = false;
                }
            });
        }
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
<aside x-ref="seSidebar"
       @mouseenter="peekSidebarExpandNow()"
       @mouseleave="peekSidebarMaybeCollapseLater()"
       @focusin="peekSidebarExpandNow()"
       @focusout="peekSidebarFocusOut($event)"
       class="se-sidebar fixed inset-y-0 left-0 z-[1000] flex flex-col transform transition-transform duration-200 ease-in-out
              md:translate-x-0 md:transition-[width] md:duration-200 md:ease-in-out md:shadow-lg"
       :class="[
           sidebarOpen ? 'translate-x-0' : '-translate-x-full',
           'md:translate-x-0',
           sidebarCollapsed ? 'is-collapsed' : ''
       ]">

    {{-- Header: logo y contexto; en desktop fuera del dashboard el menú se expande con hover sobre el lateral --}}
    @php
        $sidebarLogoUrl = schoolLogoUrl() ?: asset('img/3.png');
        $sidebarSessionLine = schoolCtx()->nivelNombre()
            . ' · ' . schoolCtx()->terlecAno()
            . ' · ' . trim((Auth::user()->nombre ?? '') . ' ' . (Auth::user()->apellido ?? ''));
    @endphp
    <div class="border-b se-sidebar-sep relative z-[1] flex-shrink-0"
         :class="sidebarCollapsed ? 'flex flex-col items-center gap-2 py-3 px-1' : 'min-h-12 px-2.5 py-2 flex flex-row items-center gap-2'">

        <a href="{{ route('dashboard') }}"
           @click="sidebarOpen = false"
           class="flex min-w-0 items-center gap-2 rounded-lg text-left no-underline text-inherit transition-colors hover:bg-[var(--se-hover-bg)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--se-light-blue)]"
           :class="sidebarCollapsed ? 'flex-col justify-center' : 'flex-1'"
           title="Ir al panel principal">
            <span class="rounded-lg bg-white px-2 py-1.5 shadow-sm flex-shrink-0">
                <img src="{{ $sidebarLogoUrl }}" alt=""
                     class="object-contain flex-shrink-0 block"
                     :class="sidebarCollapsed ? 'h-8 w-8' : 'h-9 w-auto max-w-[9.5rem]'">
            </span>

            <p class="text-white text-[11px] font-semibold truncate min-w-0 leading-snug"
               x-show="!sidebarCollapsed" x-cloak
               title="{{ $sidebarSessionLine }}">
                <span class="text-white/90">{{ schoolCtx()->nivelNombre() }}</span>
                <span class="text-white/50"> · </span>
                <span class="text-white/90">{{ schoolCtx()->terlecAno() }}</span>
                <span class="block text-[10px] font-medium text-white/70 truncate mt-0.5">
                    {{ Auth::user()->nombre ?? '' }} {{ Auth::user()->apellido ?? '' }}
                </span>
            </p>
        </a>

        <div x-show="!sidebarCollapsed" x-cloak class="min-w-0 flex-shrink-0">
            <livewire:school.context-switcher />
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 relative z-[1] px-2.5 py-3 overflow-y-auto space-y-0.5"
         :class="sidebarCollapsed ? '!px-1 !py-2' : ''"
         @click.capture="$event.target.closest('a[href]') && (sidebarOpen = false)">

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

                <a href="{{ route('push.enviar') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'push.'),
                   ])
                   title="Enviar notificación push">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="truncate">Enviar notificación push</span>
                </a>

                @if(tienePermiso(51))
                    <p x-show="!sidebarCollapsed" x-cloak class="mt-2 mb-0.5 px-2.5 text-[10px] font-bold uppercase tracking-wider text-white/50">
                        Comunicaciones con familias
                    </p>
                    <a href="{{ route('comunicaciones.index') }}"
                       @class([
                           'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                           'is-active shadow-sm' => str_starts_with($route ?? '', 'comunicaciones.') && ($route ?? '') !== 'comunicaciones.nuevo',
                       ])
                       title="Bandeja de comunicados">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="truncate">Bandeja de comunicados</span>
                    </a>
                    @if(tienePermiso(52))
                    <a href="{{ route('comunicaciones.nuevo') }}"
                       @class([
                           'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                           'is-active shadow-sm' => ($route ?? '') === 'comunicaciones.nuevo',
                       ])
                       title="Nuevo comunicado a familias">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="truncate">Nuevo comunicado</span>
                    </a>
                    @endif
                @endif
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

        {{-- Comunicaciones (solo si no está ya en el menú Estudiantes) --}}
        @if(tienePermiso(51) && !tienePermiso(2))
            <div class="mt-4"></div>
            <button type="button"
                    class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors"
                    :class="(groups.comunicaciones && !sidebarCollapsed) ? 'is-open' : ''"
                    @click="toggleGroup('comunicaciones')"
                    title="Comunicaciones">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="truncate flex-1 text-left">COMUNICACIONES</span>
                <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                     :class="groups.comunicaciones ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="mt-1 space-y-0.5 pl-1"
                 x-show="groups.comunicaciones && !sidebarCollapsed"
                 x-collapse
                 x-cloak>
                <a href="{{ route('comunicaciones.index') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'comunicaciones.') && ($route ?? '') !== 'comunicaciones.nuevo',
                   ])
                   title="Bandeja de comunicados">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="truncate">Bandeja</span>
                </a>
                @if(tienePermiso(52))
                <a href="{{ route('comunicaciones.nuevo') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'comunicaciones.nuevo',
                   ])
                   title="Nuevo comunicado">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="truncate">Nuevo comunicado</span>
                </a>
                @endif
                @if(tienePermiso(53))
                <a href="{{ route('param.com-canales') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'param.com-canales',
                   ])
                   title="Config. de canales">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="truncate">Config. canales</span>
                </a>
                @endif
            </div>
        @endif

        {{-- Seguimiento disciplinario --}}
        @if(tienePermiso(2))
            <div class="mt-4"></div>
            <button type="button"
                    class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors"
                    :class="(groups.disciplinario && !sidebarCollapsed) ? 'is-open' : ''"
                    @click="toggleGroup('disciplinario')"
                    title="Seguimiento disciplinario">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="truncate flex-1 text-left">SEGUIMIENTO DISCIPLINARIO</span>
                <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                     :class="groups.disciplinario ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="mt-1 space-y-0.5 pl-1"
                 x-show="groups.disciplinario && !sidebarCollapsed"
                 x-collapse
                 x-cloak>
                <a href="{{ route('seguimiento.disciplinario') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'seguimiento.disciplinario'),
                   ])
                   title="Seguimiento Disciplinario">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="truncate">Seguimiento Disciplinario</span>
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

                <a href="{{ route('param.parametros-sistema') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'param.parametros-sistema'),
                   ])
                   title="Parámetros del sistema">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6V4m0 16v-2m8-6h-2M6 12H4m14.364 6.364l-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0L16.95 7.05M7.05 16.95l-1.414 1.414"/>
                    </svg>
                    <span class="truncate">Parámetros del sistema</span>
                </a>

                @if(tienePermiso(53))
                <a href="{{ route('param.com-canales') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'param.com-canales',
                   ])
                   title="Canales de comunicación escuela–familia">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/>
                    </svg>
                    <span class="truncate">Canales de comunicación</span>
                </a>
                @endif

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
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate flex-1 text-left">GESTIÓN DE PLANES Y CURSOS MODELO</span>
                    <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                         :class="groups.planesCursos ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div class="space-y-0.5 pl-1"
                     x-show="groups.planesCursos && !sidebarCollapsed"
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
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate flex-1 text-left">GESTION DE CURSOS Y MATERIAS DEL AÑO</span>
                    <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                         :class="groups.cursosMateriasAno ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div class="space-y-0.5 pl-1"
                     x-show="groups.cursosMateriasAno && !sidebarCollapsed"
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
    <div class="px-4 py-3 border-t se-sidebar-sep relative z-[1]"
         :class="sidebarCollapsed ? 'px-1.5 py-2.5' : ''">
        <div class="flex items-center gap-3"
             :class="sidebarCollapsed ? 'flex-col gap-2' : ''">
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

    {{-- Barra estrecha visible al colapsar: el toggle vive en el sidebar --}}
    {{-- Top bar (mobile): translúcida y borde marca --}}
    <header class="sticky top-0 z-20 md:hidden border-b border-[#C1D7DA] bg-white/95 backdrop-blur-sm supports-[backdrop-filter]:bg-white/85">
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

    {{-- Contenido principal: padding generoso en desktop --}}
    <main class="flex-1 p-4 md:p-8">
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
