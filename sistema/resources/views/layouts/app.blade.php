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
        .se-sidebar-groupbtn {
            color: rgba(255, 255, 255, 0.95);
            background: var(--se-white-05);
            border: 1px solid var(--se-sep);
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.22);
        }
        .se-sidebar-groupbtn:hover { background: var(--se-hover-bg); }
        .se-sidebar-groupbtn.is-open { background: var(--se-white-10); }
        /* Sidebar expandido: títulos de grupo a la izquierda (no centrados en el hueco flex). */
        .se-sidebar:not(.is-collapsed) .se-sidebar-groupbtn {
            justify-content: flex-start;
        }
        .se-sidebar:not(.is-collapsed) .se-sidebar-groupbtn .se-sidebar-group-label {
            flex: 1 1 0%;
            min-width: 0;
            text-align: left;
        }
        /* Opciones hijas: icono y texto desde 5 mm del borde interior del nav. */
        .se-sidebar-group-items > .se-sidebar-link {
            padding-left: 5mm;
            padding-right: 0.625rem;
        }
        /* Enlaces a módulos: un poco más anchos; grupos heredan Condensed del sidebar. */
        .se-sidebar-link {
            font-family: "Roboto", "Helvetica Neue", "Noto Sans", system-ui, -apple-system, "Segoe UI", sans-serif;
            font-stretch: normal;
            min-width: 0;
        }
        .se-sidebar-link span.truncate {
            min-width: 0;
            flex: 1 1 0%;
        }
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
    _sidebarNavScrollTop: 0,
    _sidebarPeekTimer: null,
    groups: {
        config: {{ (str_starts_with($route ?? '', 'abm.terlec') || str_starts_with($route ?? '', 'abm.niveles') || str_starts_with($route ?? '', 'abm.cursos') || str_starts_with($route ?? '', 'abm.planes') || str_starts_with($route ?? '', 'abm.curplan') || str_starts_with($route ?? '', 'abm.materias-anio') || str_starts_with($route ?? '', 'param.') || ($route ?? '') === 'push.suscribir') ? 'true' : 'false' }},
        planesCursos: {{ (str_starts_with($route ?? '', 'abm.planes') || str_starts_with($route ?? '', 'abm.curplan')) ? 'true' : 'false' }},
        cursosMateriasAno: {{ (str_starts_with($route ?? '', 'abm.cursos') || str_starts_with($route ?? '', 'abm.materias-anio')) ? 'true' : 'false' }},
        students: {{ (str_starts_with($route ?? '', 'abm.legajos') || str_starts_with($route ?? '', 'listados.')) ? 'true' : 'false' }},
        cuadernoComunicados: {{ ((str_starts_with($route ?? '', 'comunicaciones.') || ($route ?? '') === 'param.com-canales') && tienePermiso(3)) ? 'true' : 'false' }},
        calificacionesSec: {{ (str_starts_with($route ?? '', 'calificacionesSecundario.') || str_starts_with($route ?? '', 'boletinesSecundario.')) ? 'true' : 'false' }},
        disciplinario: {{ str_starts_with($route ?? '', 'seguimiento.disciplinario') ? 'true' : 'false' }},
        inasistenciasEstudiantes: {{ str_starts_with($route ?? '', 'seguimiento.inasistencias') || str_starts_with($route ?? '', 'seguimiento.partes-diarios') ? 'true' : 'false' }},
        docentes: {{ (str_starts_with($route ?? '', 'abm.profesores-por-materia') || str_starts_with($route ?? '', 'abm.legajos-profesor')) ? 'true' : 'false' }},
        examenes: {{ str_starts_with($route ?? '', 'examenes.') ? 'true' : 'false' }},
        horarios: {{ str_starts_with($route ?? '', 'horarios.') ? 'true' : 'false' }},
        comunicaciones: false,
    },
    isDesktopPeekLayout() {
        return window.matchMedia && window.matchMedia('(min-width: 768px)').matches;
    },
    saveSidebarNavScroll() {
        const nav = this.$refs.seSidebarNav;
        if (!nav) return;
        this._sidebarNavScrollTop = nav.scrollTop;
        try {
            sessionStorage.setItem('seSidebarNavScrollTop', String(this._sidebarNavScrollTop));
        } catch (e) {}
    },
    loadSidebarNavScroll() {
        try {
            const raw = sessionStorage.getItem('seSidebarNavScrollTop');
            if (raw === null || raw === '') return;
            const n = parseInt(raw, 10);
            if (!Number.isNaN(n) && n >= 0) this._sidebarNavScrollTop = n;
        } catch (e) {}
    },
    restoreSidebarNavScroll() {
        const nav = this.$refs.seSidebarNav;
        if (!nav) return;
        const top = this._sidebarNavScrollTop;
        let tries = 0;
        const apply = () => {
            nav.scrollTop = top;
            if (Math.abs(nav.scrollTop - top) > 2 && tries++ < 20) {
                requestAnimationFrame(apply);
            }
        };
        this.$nextTick(() => requestAnimationFrame(apply));
    },
    onSidebarNavScroll() {
        if (!this.sidebarCollapsed) this.saveSidebarNavScroll();
    },
    onSidebarNavLinkActivate(ev) {
        const link = ev.target.closest('a[href]');
        if (!link) return;
        const href = (link.getAttribute('href') || '').trim();
        if (!href || href === '#') return;
        this.saveSidebarNavScroll();
        if (ev.type === 'click') this.sidebarOpen = false;
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
            this.saveSidebarNavScroll();
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
    applyPeekSidebarBootState(respectInteraction = true) {
        if (!this.peekMenuMode || !this.isDesktopPeekLayout()) {
            this.sidebarCollapsed = false;
            return;
        }
        if (respectInteraction) {
            const el = this.$refs.seSidebar;
            if (el && (el.matches(':hover') || el.contains(document.activeElement))) return;
        }
        this.sidebarCollapsed = true;
    },
    init() {
        const raw = localStorage.getItem('sidebarGroups');
        if (raw) {
            try {
                const parsed = JSON.parse(raw);
                if (parsed && typeof parsed === 'object') this.groups = { ...this.groups, ...parsed };
            } catch (e) {}
        }
        this.loadSidebarNavScroll();
        // Desktop dashboard: sidebar ancho siempre; resto de rutas: rail hasta hover/focus.
        this.applyPeekSidebarBootState(false);
        this.$watch('sidebarCollapsed', (collapsed) => {
            if (!collapsed && this.peekMenuMode && this.isDesktopPeekLayout()) {
                this.restoreSidebarNavScroll();
            }
        });
        if (!this._sePeekResizeBound) {
            this._sePeekResizeBound = true;
            window.addEventListener('resize', () => this.applyPeekSidebarBootState(true));
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
       @mouseleave="saveSidebarNavScroll(); peekSidebarMaybeCollapseLater()"
       @focusin="peekSidebarExpandNow()"
       @focusout="peekSidebarFocusOut($event)"
       class="se-sidebar fixed inset-y-0 left-0 z-[1000] flex flex-col overflow-hidden transform transition-transform duration-200 ease-in-out
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
    <div class="border-b se-sidebar-sep relative z-10 overflow-visible flex-shrink-0"
         :class="sidebarCollapsed ? 'flex flex-col items-center gap-2 py-3 px-1' : 'min-h-12 px-2.5 py-2 flex flex-row items-center gap-2'">

        <a href="{{ route('dashboard') }}"
           @click="sidebarOpen = false"
           class="flex min-w-0 items-center gap-2 rounded-lg text-left no-underline text-inherit transition-colors hover:bg-[var(--se-hover-bg)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--se-light-blue)]"
           :class="sidebarCollapsed ? 'flex-col justify-center' : 'flex-1'"
           title="Ir al panel principal v1.0">
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
    <nav x-ref="seSidebarNav"
         class="flex-1 min-h-0 relative z-[1] px-2.5 py-3 overflow-y-auto space-y-0.5"
         :class="sidebarCollapsed ? '!px-1 !py-2' : ''"
         @scroll.passive="onSidebarNavScroll()"
         @mousedown.capture="onSidebarNavLinkActivate($event)"
         @click.capture="onSidebarNavLinkActivate($event)">

        {{-- Estudiantes --}}
            <button type="button"
                    class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors"
                    :class="(groups.students && !sidebarCollapsed) ? 'is-open' : ''"
                    @click="toggleGroup('students')"
                    title="Estudiantes v1.0">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="se-sidebar-group-label min-w-0 flex-1 truncate text-left">Estudiantes</span>
                <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                     :class="groups.students ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="mt-1 space-y-0.5 se-sidebar-group-items"
                 x-show="groups.students && !sidebarCollapsed"
                 x-collapse
                 x-cloak>
                <a href="{{ route('abm.legajos') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'abm.legajos'),
                   ])
                   title="Legajos de Estudiantes v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="truncate">Legajos de Estudiantes</span>
                </a>

                @php
                    if (! \Illuminate\Support\Facades\Route::has('listados.por-curso')) {
                        throw new \RuntimeException("Sidebar: falta la ruta 'listados.por-curso'.");
                    }
                @endphp
                <a href="{{ route('listados.por-curso') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => request()->routeIs('listados.por-curso', 'listados.por-curso.pdf', 'listados.exportar-excel'),
                   ])
                   title="Listados de Estudiantes por Curso v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="truncate">Listados de Estudiantes por Curso</span>
                </a>

                @php
                    if (! \Illuminate\Support\Facades\Route::has('listados.libro-matricula')) {
                        throw new \RuntimeException("Sidebar: falta la ruta 'listados.libro-matricula'.");
                    }
                @endphp
                <a href="{{ route('listados.libro-matricula') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => request()->routeIs('listados.libro-matricula', 'listados.libro-matricula.pdf'),
                   ])
                   title="Libro de Matrícula v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span class="truncate">Libro de Matrícula</span>
                </a>

            </div>

        {{-- Comunicación institucional --}}
        @if(tienePermiso(3))
            <div class="mt-4"></div>
            <button type="button"
                    class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors"
                    :class="(groups.cuadernoComunicados && !sidebarCollapsed) ? 'is-open' : ''"
                    @click="toggleGroup('cuadernoComunicados')"
                    title="Comunicación institucional v1.0">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="se-sidebar-group-label min-w-0 flex-1 truncate text-left">COMUNICACIÓN INSTITUCIONAL</span>
                <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                     :class="groups.cuadernoComunicados ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="mt-1 space-y-0.5 se-sidebar-group-items"
                 x-show="groups.cuadernoComunicados && !sidebarCollapsed"
                 x-collapse
                 x-cloak>
                <a href="{{ route('comunicaciones.index') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'comunicaciones.') && ! in_array(($route ?? ''), ['comunicaciones.nuevo', 'comunicaciones.revision'], true),
                   ])
                   title="Bandeja de comunicados v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span class="truncate">Bandeja de comunicados</span>
                </a>

                @if(tienePermiso(4))
                <a href="{{ route('comunicaciones.nuevo') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'comunicaciones.nuevo',
                   ])
                   title="Nuevo comunicado a familias v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="truncate">Nuevo comunicado</span>
                </a>
                @endif

                @if(tienePermiso(8))
                <a href="{{ route('comunicaciones.revision') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'comunicaciones.revision',
                   ])
                   title="Control Cuaderno de Comunicados v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span class="truncate">Control Cuaderno de Comunicados</span>
                </a>
                @endif

                @if(tienePermiso(5))
                <a href="{{ route('param.com-canales') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'param.com-canales',
                   ])
                   title="Configuración de canales v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="truncate">Configuración de Canales</span>
                </a>
                @endif
            </div>
        @endif

        {{-- Calificaciones Secundario --}}
            <div class="mt-4"></div>
            <button type="button"
                    class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors"
                    :class="(groups.calificacionesSec && !sidebarCollapsed) ? 'is-open' : ''"
                    @click="toggleGroup('calificacionesSec')"
                    title="Calificaciones (secundario) v1.0">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="se-sidebar-group-label min-w-0 flex-1 truncate text-left">CALIFICACIONES</span>
                <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                     :class="groups.calificacionesSec ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="mt-1 space-y-0.5 se-sidebar-group-items"
                 x-show="groups.calificacionesSec && !sidebarCollapsed"
                 x-collapse
                 x-cloak>
                @if (tienePermiso(9))
                <a href="{{ route('calificacionesSecundario.sincroGe') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'calificacionesSecundario.sincroGe',
                   ])
                   title="Descargar calificaciones desde CIDI (GE) v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span class="truncate">Descargar calificaciones desde CIDI</span>
                </a>
                <a href="{{ route('calificacionesSecundario.carga') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'calificacionesSecundario.carga',
                   ])
                   title="Carga de calificaciones (secundario) v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="truncate">Carga de calificaciones</span>
                </a>
                @endif
                <a href="{{ route('calificacionesSecundario.consulta') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'calificacionesSecundario.consulta'
                           || ($route ?? '') === 'calificacionesSecundario.consulta.pdf',
                   ])
                   title="Consulta de calificaciones (secundario) v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    <span class="truncate">Consulta de calificaciones</span>
                </a>
                <a href="{{ route('boletinesSecundario.index') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'boletinesSecundario.'),
                   ])
                   title="Boletines (secundario) · Informe de progreso escolar v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="truncate">Boletines (secundario)</span>
                </a>
                <a href="{{ route('calificacionesSecundario.planilla') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'calificacionesSecundario.planilla'
                           || ($route ?? '') === 'calificacionesSecundario.planilla.pdf',
                   ])
                   title="Planilla de calificaciones (secundario) v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="truncate">Planilla de calificaciones</span>
                </a>
                <a href="{{ route('calificacionesSecundario.planillaResumen') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'calificacionesSecundario.planillaResumen'
                           || ($route ?? '') === 'calificacionesSecundario.planillaResumen.pdf',
                   ])
                   title="Planilla resumen de calificaciones (secundario)">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span class="truncate">Planilla resumen</span>
                </a>
                @if (tienePermiso(10))
                <a href="{{ route('calificacionesSecundario.coloquios') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'calificacionesSecundario.coloquios',
                   ])
                   title="Carga de coloquios Dic / Feb (secundario)">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="truncate">Carga de coloquios</span>
                </a>
                @endif
                <a href="{{ route('calificacionesSecundario.actaVolanteColoquios') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'calificacionesSecundario.actaVolanteColoquios'
                           || ($route ?? '') === 'calificacionesSecundario.actaVolanteColoquios.pdf',
                   ])
                   title="Actas volantes de coloquio (secundario)">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="truncate">Actas volantes coloquio</span>
                </a>
            </div>

        {{-- Seguimiento disciplinario --}}
            <div class="mt-4"></div>
            <button type="button"
                    class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors"
                    :class="(groups.disciplinario && !sidebarCollapsed) ? 'is-open' : ''"
                    @click="toggleGroup('disciplinario')"
                    title="Seguimiento disciplinario v1.0">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="se-sidebar-group-label min-w-0 flex-1 truncate text-left">SEGUIMIENTO DISCIPLINARIO</span>
                <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                     :class="groups.disciplinario ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="mt-1 space-y-0.5 se-sidebar-group-items"
                 x-show="groups.disciplinario && !sidebarCollapsed"
                 x-collapse
                 x-cloak>
                    <a href="{{ route('seguimiento.disciplinario') }}"
                       @class([
                           'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                           'is-active shadow-sm' => str_starts_with($route ?? '', 'seguimiento.disciplinario'),
                       ])
                       title="Seguimiento Disciplinario v1.0">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="truncate">Seguimiento Disciplinario</span>
                    </a>
            </div>

        {{-- Asistencia estudiantes --}}
            <div class="mt-4"></div>
            <button type="button"
                    class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors"
                    :class="(groups.inasistenciasEstudiantes && !sidebarCollapsed) ? 'is-open' : ''"
                    @click="toggleGroup('inasistenciasEstudiantes')"
                    title="Asistencia estudiantes v1.0">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="se-sidebar-group-label min-w-0 flex-1 truncate text-left">ASISTENCIA ESTUDIANTES</span>
                <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                     :class="groups.inasistenciasEstudiantes ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="mt-1 space-y-0.5 se-sidebar-group-items"
                 x-show="groups.inasistenciasEstudiantes && !sidebarCollapsed"
                 x-collapse
                 x-cloak>
                <a href="{{ route('seguimiento.inasistencias') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'seguimiento.inasistencias'),
                   ])
                   title="Gestión de Inasistencias del Estudiante v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="truncate">Gestión de Inasistencias del Estudiante</span>
                </a>
                <a href="{{ route('seguimiento.partes-diarios') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'seguimiento.partes-diarios'),
                   ])
                   title="Parte diario del preceptor (PDF por curso(s) y fecha)">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="truncate">Parte diario del preceptor</span>
                </a>
            </div>

        {{-- Docentes --}}
        @if(tienePermiso(1))
            <div class="mt-4"></div>
            <button type="button"
                    class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors"
                    :class="(groups.docentes && !sidebarCollapsed) ? 'is-open' : ''"
                    @click="toggleGroup('docentes')"
                    title="Docentes v1.0">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 14l9-5v8a2 2 0 01-9 5v0a2 2 0 01-9-5V9l9 5"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="se-sidebar-group-label min-w-0 flex-1 truncate text-left">DOCENTES</span>
                <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                     :class="groups.docentes ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="mt-1 space-y-0.5 se-sidebar-group-items"
                 x-show="groups.docentes && !sidebarCollapsed"
                 x-collapse
                 x-cloak>
                <a href="{{ route('abm.legajos-profesor') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'abm.legajos-profesor'),
                   ])
                   title="Legajos del docente v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="truncate">Legajos del docente</span>
                </a>

                <a href="{{ route('abm.profesores-por-materia') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'abm.profesores-por-materia'),
                   ])
                   title="Asignación de profesores por materia y curso · ppc · v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 14l9-5v8a2 2 0 01-9 5v0a2 2 0 01-9-5V9l9 5"/>
                    </svg>
                    <span class="truncate">Asignación de Profesores por Materia y Curso</span>
                </a>
            </div>
        @endif

        @if (tienePermiso(12))
        {{-- Exámenes --}}
            <div class="mt-4"></div>
            <button type="button"
                    class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors"
                    :class="(groups.examenes && !sidebarCollapsed) ? 'is-open' : ''"
                    @click="toggleGroup('examenes')"
                    title="Exámenes">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="se-sidebar-group-label min-w-0 flex-1 truncate text-left">EXÁMENES</span>
                <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                     :class="groups.examenes ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="mt-1 space-y-0.5 se-sidebar-group-items"
                 x-show="groups.examenes && !sidebarCollapsed"
                 x-collapse
                 x-cloak>
                <a href="{{ route('examenes.materias-adeudadas.gestion.entrar') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => request()->routeIs('examenes.materias-adeudadas.gestion', 'examenes.materias-adeudadas.gestion.entrar'),
                   ])
                   title="Gestión de materias adeudadas (secundario)">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="truncate">Gestión de Materias Adeudadas</span>
                </a>
                <a href="{{ route('examenes.borrar-inscripciones') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => request()->routeIs('examenes.borrar-inscripciones'),
                   ])
                   title="Borrar todas las inscripciones a examen">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span class="truncate">Borrar TODAS las Inscripciones a Examen</span>
                </a>
                <a href="{{ route('examenes.materias-adeudadas.entrar') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => request()->routeIs('examenes.materias-adeudadas', 'examenes.materias-adeudadas.entrar', 'examenes.materias-adeudadas.pdf'),
                   ])
                   title="Listado de materias adeudadas">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="truncate">Listado de Materias Adeudadas</span>
                </a>
                <a href="{{ route('examenes.acta-volante-previos.entrar') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => request()->routeIs('examenes.acta-volante-previos', 'examenes.acta-volante-previos.entrar', 'examenes.acta-volante-previos.pdf'),
                   ])
                   title="Actas volante de examen (previas)">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span class="truncate">Actas Volante</span>
                </a>
                <a href="{{ route('examenes.permiso-examen.entrar') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => request()->routeIs('examenes.permiso-examen', 'examenes.permiso-examen.entrar', 'examenes.permiso-examen.pdf', 'examenes.permiso-examen.pdf.preparar'),
                   ])
                   title="Permisos de examen por alumno">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="truncate">Permisos de Examen</span>
                </a>
            </div>
        @endif

        {{-- Horarios --}}
            <div class="mt-4"></div>
            <button type="button"
                    class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors"
                    :class="(groups.horarios && !sidebarCollapsed) ? 'is-open' : ''"
                    @click="toggleGroup('horarios')"
                    title="Horarios de profesores">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="se-sidebar-group-label min-w-0 flex-1 truncate text-left">HORARIOS</span>
                <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                     :class="groups.horarios ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="mt-1 space-y-0.5 se-sidebar-group-items"
                 x-show="groups.horarios && !sidebarCollapsed"
                 x-collapse
                 x-cloak>
                @if (tienePermiso(13))
                    <a href="{{ route('horarios.config') }}"
                       @class([
                           'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                           'is-active shadow-sm' => ($route ?? '') === 'horarios.config',
                       ])
                       title="Turnos, días de clase y horario reloj">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="truncate">Configuración de horarios</span>
                    </a>
                @endif

                @if (tienePermiso(13))
                    <a href="{{ route('horarios.carga') }}"
                       @class([
                           'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                           'is-active shadow-sm' => ($route ?? '') === 'horarios.carga',
                       ])
                       title="Carga de horas cátedra por docente">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span class="truncate">Carga de horarios</span>
                    </a>
                @endif

                    <a href="{{ route('horarios.impresion') }}"
                       @class([
                           'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                           'is-active shadow-sm' => str_starts_with($route ?? '', 'horarios.impresion') || str_starts_with($route ?? '', 'horarios.pdf.'),
                       ])
                       title="Impresión PDF por curso o docente">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span class="truncate">Impresión de horarios</span>
                    </a>
            </div>

        {{-- Configuración --}}
        @if (tienePermiso(14))
            <div class="mt-4"></div>
            <button type="button"
                    class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors"
                    :class="(groups.config && !sidebarCollapsed) ? 'is-open' : ''"
                    @click="toggleGroup('config')"
                    title="Configuración v1.0">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 16v-2m8-6h-2M6 12H4m14.364 6.364l-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0L16.95 7.05M7.05 16.95l-1.414 1.414"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="se-sidebar-group-label min-w-0 flex-1 truncate text-left">Configuración</span>
                <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                     :class="groups.config ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="mt-1 space-y-0.5 se-sidebar-group-items"
                 x-show="groups.config && !sidebarCollapsed"
                 x-collapse
                 x-cloak>
                <a href="{{ route('abm.terlec') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'abm.terlec'),
                   ])
                   title="Términos Lectivos v1.0">
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
                   title="Niveles v1.0">
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
                   title="Campos activos (Legajo del estudiante) v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span class="truncate">Campos activos (Legajo del estudiante)</span>
                </a>

                <a href="{{ route('param.solapas-legajo') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'param.solapas-legajo'),
                   ])
                   title="Solapas del Legajo v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 10h18M3 6h18M3 14h10M3 18h10"/>
                    </svg>
                    <span class="truncate">Solapas del Legajo</span>
                </a>

                <a href="{{ route('param.campos-legajo-profesor') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'param.campos-legajo-profesor'),
                   ])
                   title="Campos activos (Legajo del docente) v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span class="truncate">Campos activos (Legajo del docente)</span>
                </a>

                <a href="{{ route('param.solapas-legajo-profesor') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'param.solapas-legajo-profesor'),
                   ])
                   title="Solapas del Legajo del docente v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 10h18M3 6h18M3 14h10M3 18h10"/>
                    </svg>
                    <span class="truncate">Solapas del Legajo del docente</span>
                </a>

                <a href="{{ route('param.parametros-sistema') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => str_starts_with($route ?? '', 'param.parametros-sistema'),
                   ])
                   title="Parámetros del sistema v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6V4m0 16v-2m8-6h-2M6 12H4m14.364 6.364l-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0L16.95 7.05M7.05 16.95l-1.414 1.414"/>
                    </svg>
                    <span class="truncate">Parámetros del sistema</span>
                </a>

                <a href="{{ route('push.suscribir') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'push.suscribir',
                   ])
                   title="Notificaciones push en este dispositivo">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="truncate">Notificaciones Push</span>
                </a>

                @if(tienePermiso(0))
                <a href="{{ route('admin.permisos') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'admin.permisos',
                   ])
                   title="Administración de permisos de usuarios v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.6 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 8.49 1.65 1.65 0 004.27 6.67l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 008.91 4.2 1.65 1.65 0 009.92 2.7V2a2 2 0 014 0v.09c0 .69.4 1.31 1.02 1.6.62.29 1.34.19 1.82-.28l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06c-.47.47-.57 1.19-.28 1.82.29.62.91 1.02 1.6 1.02H21a2 2 0 010 4h-.09c-.69 0-1.31.4-1.6 1.02z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15a3 3 0 100-6 3 3 0 000 6z"/>
                    </svg>
                    <span class="truncate">Permisos de usuarios</span>
                </a>
                @endif

                @if(tienePermiso(5))
                <a href="{{ route('param.com-canales') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'param.com-canales',
                   ])
                   title="Configuración de canales escuela–familia v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/>
                    </svg>
                    <span class="truncate">Configuración de Canales</span>
                </a>
                @endif

                {{-- Planes + Cursos modelo --}}
                <button type="button"
                        class="se-sidebar-groupbtn w-full flex items-center gap-2 px-2.5 py-2 text-[12px] font-bold uppercase tracking-widest rounded-md transition-colors mt-2"
                        :class="(groups.planesCursos && !sidebarCollapsed) ? 'is-open' : ''"
                        @click="toggleGroup('planesCursos')"
                        title="Gestión de planes y cursos modelo v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6V4m0 16v-2m8-6h-2M6 12H4m14.364 6.364l-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0L16.95 7.05M7.05 16.95l-1.414 1.414"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak class="se-sidebar-group-label min-w-0 flex-1 truncate text-left">GESTIÓN DE PLANES Y CURSOS MODELO</span>
                    <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                         :class="groups.planesCursos ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div class="space-y-0.5 se-sidebar-group-items"
                     x-show="groups.planesCursos && !sidebarCollapsed"
                     x-collapse
                     x-cloak>
                    <a href="{{ route('abm.planes') }}"
                       @class([
                           'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                           'is-active shadow-sm' => str_starts_with($route ?? '', 'abm.planes'),
                       ])
                       title="Gestión de Planes de Estudio v1.0">
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
                       title="Gestión de Cursos y Materias del Plan v1.0">
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
                        title="Gestión de cursos y materias del año v1.0">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6V4m0 16v-2m8-6h-2M6 12H4m14.364 6.364l-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0L16.95 7.05M7.05 16.95l-1.414 1.414"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak class="se-sidebar-group-label min-w-0 flex-1 truncate text-left">GESTION DE CURSOS Y MATERIAS DEL AÑO</span>
                    <svg x-show="!sidebarCollapsed" x-cloak class="w-4 h-4 transition-transform"
                         :class="groups.cursosMateriasAno ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div class="space-y-0.5 se-sidebar-group-items"
                     x-show="groups.cursosMateriasAno && !sidebarCollapsed"
                     x-collapse
                     x-cloak>
                    <a href="{{ route('abm.cursos') }}"
                       @class([
                           'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                           'is-active shadow-sm' => str_starts_with($route ?? '', 'abm.cursos'),
                       ])
                       title="Gestión de Cursos / Grados / Salas v1.0">
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
                       title="Gestión de asignaturas del año v1.0">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        <span class="truncate">Gestión de asignaturas del año</span>
                    </a>
                </div>

            </div>

        @endif

        {{-- Manual del sistema (todos los usuarios de gestión) --}}
        <div class="mt-4 pt-3 border-t se-sidebar-sep">
            <a href="{{ route('manual.sistema.pdf') }}"
               target="_blank"
               rel="noopener noreferrer"
               class="se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors"
               title="Abrir manual del sistema (PDF) en una pestaña nueva">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <span class="truncate" x-show="!sidebarCollapsed" x-cloak>Manual del sistema</span>
            </a>
        </div>

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
        @if (isset($slot) && ! $slot->isEmpty())
            {{ $slot }}
        @elseif (View::hasSection('content'))
            @yield('content')
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
