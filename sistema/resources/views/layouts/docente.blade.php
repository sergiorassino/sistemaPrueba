{{--
    Menú de Docentes — portal reducido para profesores (pocas tareas).
    No confundir con el Menú de Secretaría (layouts/app.blade.php) ni con el grupo
    sidebar "DOCENTES" de secretaría. Ver docs/08-menus-de-navegacion.md
--}}
<!DOCTYPE html>
<html lang="es" class="h-full bg-[#F4F8F9]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? (isset($title) ? $title . ' — ' : '') }}{{ config('app.name') }}</title>
    @include('layouts.partials.favicon')
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
            --se-sidebar-w: 23.04rem;
            --se-sidebar-w-collapsed: 5rem;
        }
        /* Sin position:relative aquí: pisaría Tailwind `fixed` y el sidebar pasaría al flujo (contenido debajo). */
        .se-sidebar {
            background-color: var(--se-jet);
            color: #fff;
            font-family: "Roboto Condensed", "Arial Narrow", "Helvetica Neue", "Noto Sans", system-ui, sans-serif;
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
        .se-sidebar-link {
            font-family: "Roboto", system-ui, sans-serif;
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
            .se-sidebar.is-collapsed .se-sidebar-link {
                justify-content: center;
                gap: 0;
                padding-left: 0.35rem;
                padding-right: 0.35rem;
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
@php
    $route = request()->route()?->getName();
    $portalEsSecundario = str_contains(mb_strtolower((string) schoolCtx()->nivelNombre()), 'secundari');
    /** En desktop: rail colapsado salvo inicio y bandeja de comunicados; hover/focus expanden. */
    $isSidebarPeekMode = ! in_array($route ?? '', ['portalDocente.home', 'portalDocente.comunicaciones.index'], true);
    $docenteComBandejaActiva = str_starts_with($route ?? '', 'portalDocente.comunicaciones')
        && ! in_array($route ?? '', ['portalDocente.comunicaciones.nuevo', 'portalDocente.comunicaciones.revision'], true);
@endphp
<body class="h-full">

<div id="se-shell-docente"
     class="h-full"
     x-data="{
        sidebarOpen: false,
        peekMenuMode: @json($isSidebarPeekMode),
        sidebarCollapsed: false,
        _sidebarPeekTimer: null,
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
            this.applyPeekSidebarBootState(false);
            if (!this._sePeekResizeBound) {
                this._sePeekResizeBound = true;
                window.addEventListener('resize', () => this.applyPeekSidebarBootState(true));
            }
        },
     }">

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

<aside x-ref="seSidebar"
       @mouseenter="peekSidebarExpandNow()"
       @mouseleave="peekSidebarMaybeCollapseLater()"
       @focusin="peekSidebarExpandNow()"
       @focusout="peekSidebarFocusOut($event)"
       class="se-sidebar fixed inset-y-0 left-0 z-[1000] flex flex-col overflow-hidden transform transition-transform duration-200 ease-in-out
              md:translate-x-0 md:transition-[width] md:duration-200 md:ease-in-out md:shadow-lg"
       :class="[
           sidebarOpen ? 'translate-x-0' : '-translate-x-full',
           'md:translate-x-0',
           sidebarCollapsed ? 'is-collapsed' : ''
       ]">

    @php
        $logoUrl = schoolLogoUrl() ?: asset('img/3.png');
        $usuario = Auth::user();
        $sidebarSessionLine = schoolCtx()->nivelNombre()
            . ' · ' . schoolCtx()->terlecAno()
            . ' · ' . trim((string) ($usuario?->apellido ?? '') . ' ' . (string) ($usuario?->nombre ?? ''));
    @endphp

    <div class="border-b se-sidebar-sep relative z-[1] flex-shrink-0"
         :class="sidebarCollapsed ? 'flex flex-col items-center gap-2 py-3 px-1' : 'min-h-12 px-2.5 py-2 flex flex-col gap-1'">

        <a href="{{ route('portalDocente.home') }}"
           @click="sidebarOpen = false"
           class="flex min-w-0 items-center gap-2 rounded-lg text-left no-underline text-inherit transition-colors hover:bg-[var(--se-hover-bg)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--se-light-blue)]"
           :class="sidebarCollapsed ? 'flex-col justify-center' : 'flex-row flex-1'"
           title="Inicio del portal docente">
            <span class="rounded-lg bg-white px-2 py-1.5 shadow-sm flex-shrink-0">
                <img src="{{ $logoUrl }}" alt=""
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
                    {{ $usuario?->nombre ?? '' }} {{ $usuario?->apellido ?? '' }}
                </span>
            </p>
        </a>

        <p x-show="!sidebarCollapsed" x-cloak
           class="text-[10px] font-bold uppercase tracking-wider text-white/60 px-0.5">
            Menú de Docentes
        </p>
    </div>

    <nav class="flex-1 min-h-0 relative z-[1] px-2.5 py-3 overflow-y-auto space-y-0.5"
         :class="sidebarCollapsed ? '!px-1 !py-2' : ''"
         @click.capture="$event.target.closest('a[href]') && (sidebarOpen = false)">

        @if ($portalEsSecundario)
            <a href="{{ route('portalDocente.calificaciones') }}"
               @class([
                   'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                   'is-active shadow-sm' => str_starts_with($route ?? '', 'portalDocente.calificaciones'),
               ])
               title="Carga y consulta de calificaciones">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="truncate">Calificaciones</span>
            </a>
            <a href="{{ route('portalDocente.cuadernoSeguimiento') }}"
               @class([
                   'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                   'is-active shadow-sm' => str_starts_with($route ?? '', 'portalDocente.cuadernoSeguimiento'),
               ])
               title="Cuaderno de seguimiento áulico y situación disciplinaria">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="truncate">Cuaderno de Seguimiento Áulico</span>
            </a>
        @endif

        @if (tienePermiso(3))
            <p x-show="!sidebarCollapsed" x-cloak
               class="mt-3 mb-0.5 px-2.5 text-[10px] font-bold uppercase tracking-wider text-white/50">
                Comunicación institucional
            </p>

            <a href="{{ route('portalDocente.comunicaciones.index') }}"
               @class([
                   'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                   'is-active shadow-sm' => $docenteComBandejaActiva,
               ])
               title="Bandeja de comunicados con familias y personal">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="truncate">Bandeja de comunicados</span>
            </a>

            @if (tienePermiso(4))
                <a href="{{ route('portalDocente.comunicaciones.nuevo') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'portalDocente.comunicaciones.nuevo',
                   ])
                   title="Nuevo comunicado a familias o personal">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Nuevo comunicado</span>
                </a>
            @endif

            @if (tienePermiso(8))
                <a href="{{ route('portalDocente.comunicaciones.revision') }}"
                   @class([
                       'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
                       'is-active shadow-sm' => ($route ?? '') === 'portalDocente.comunicaciones.revision',
                   ])
                   title="Control Cuaderno de Comunicados">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span x-show="!sidebarCollapsed" x-cloak class="truncate">Control Cuaderno de Comunicados</span>
                </a>
            @endif
        @endif

    </nav>

    <div class="px-4 py-3 border-t se-sidebar-sep relative z-[1]"
         :class="sidebarCollapsed ? 'px-1.5 py-2.5' : ''">
        <div class="flex items-center gap-3"
             :class="sidebarCollapsed ? 'flex-col gap-2' : ''">
            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background: var(--se-primary);">
                <span class="text-white text-xs font-bold">
                    {{ strtoupper(substr((string) ($usuario?->apellido ?? 'U'), 0, 1)) }}
                </span>
            </div>
            <div class="flex-1 min-w-0" x-show="!sidebarCollapsed" x-cloak>
                <p class="text-white text-xs font-medium truncate">
                    {{ $usuario?->nombre ?? '' }} {{ $usuario?->apellido ?? '' }}
                </p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Cerrar sesión" class="text-white/85 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<div class="se-main flex flex-col min-h-screen transition-[padding] duration-200 ease-in-out"
     :class="[
        sidebarCollapsed ? 'is-collapsed' : '',
        sidebarOpen ? 'is-mobile-open' : ''
     ]">

    <header class="sticky top-0 z-20 md:hidden border-b border-[#C1D7DA] bg-white/95 backdrop-blur-sm supports-[backdrop-filter]:bg-white/85">
        <div class="flex items-center gap-3 h-14 px-4">
            <button type="button" @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <span class="font-semibold text-gray-800 text-sm">@yield('pageTitle', 'Portal docente')</span>
        </div>
    </header>

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
</body>
</html>
