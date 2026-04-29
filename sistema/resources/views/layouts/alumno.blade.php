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
            --se-sidebar-w: 23.04rem;
            --se-sidebar-w-collapsed: 5rem;
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
            .se-sidebar.is-collapsed { width: 0; }
        }
        .se-sidebar-sep { border-color: var(--se-sep); }
        .se-sidebar-iconbtn { color: var(--se-white-85); }
        .se-sidebar-iconbtn:hover { background: var(--se-white-10); color: #fff; }
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

<div id="se-shell"
     class="h-full"
     x-data="{
        sidebarOpen: false,
        sidebarCollapsed: false,
        init() {
            const pendingMenuCollapse = localStorage.getItem('sidebarCollapseNext') === '1';
            this.sidebarCollapsed = pendingMenuCollapse
                ? false
                : (localStorage.getItem('sidebarCollapsed') === '1');

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

<aside class="se-sidebar fixed inset-y-0 left-0 z-[1000] flex flex-col transform transition-transform duration-200 ease-in-out
              md:translate-x-0 md:transition-[width] md:duration-200 md:ease-in-out md:shadow-lg"
       :class="[
           (sidebarOpen || (!sidebarCollapsed)) ? 'translate-x-0' : '-translate-x-full md:-translate-x-full',
           sidebarCollapsed ? 'is-collapsed' : ''
       ]">

    <div class="h-12 px-2.5 border-b se-sidebar-sep flex items-center justify-between gap-2">
        @php $logoUrl = studentLogoUrl(); @endphp

        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="Logo"
                 class="h-8 w-auto object-contain flex-shrink-0"
                 x-show="!sidebarCollapsed" x-cloak>
        @endif

        @php
            $alumno = auth('alumno')->user();
            $sidebarSessionLine = studentCtx()->nivelNombre()
                . ' - ' . studentCtx()->terlecAno()
                . ' - ' . trim((string) ($alumno?->apellido ?? '') . ', ' . (string) ($alumno?->nombre ?? ''));
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
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="9" stroke-width="2"/>
                <path stroke-linecap="round" stroke-width="2" d="M8 10h8"/>
                <path stroke-linecap="round" stroke-width="2" d="M8 12.75h8"/>
                <path stroke-linecap="round" stroke-width="2" d="M8 15.5h8"/>
            </svg>
        </button>
    </div>

    <nav class="flex-1 px-2.5 py-3 overflow-y-auto space-y-0.5"
         @pointerdown.capture="$event.target.closest('a[href]') && localStorage.setItem('sidebarCollapseNext', '1')"
         @click.capture="$event.target.closest('a[href]') && (localStorage.setItem('sidebarCollapseNext', '1'), sidebarOpen = false)">

        <a href="{{ route('alumnos.calificaciones') }}"
           @class([
               'se-sidebar-link flex items-center gap-2 px-2.5 py-2 text-[13px] rounded-md font-semibold transition-colors',
               'is-active shadow-sm' => str_starts_with($route ?? '', 'alumnos.calificaciones'),
           ])
           title="Consulta de Calificaciones">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-cloak class="truncate">Consulta de Calificaciones</span>
        </a>

        <a href="{{ route('alumnos.push.index') }}"
           @class([
               'se-sidebar-link flex items-center gap-2 px-2.5 py-2 text-[13px] rounded-md font-semibold transition-colors',
               'is-active shadow-sm' => str_starts_with($route ?? '', 'alumnos.push'),
           ])
           title="Notificaciones">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span x-show="!sidebarCollapsed" x-cloak class="truncate">Notificaciones</span>
        </a>
    </nav>

    <div class="px-4 py-3 border-t se-sidebar-sep">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                 style="background: var(--se-primary);">
                <span class="text-white text-xs font-bold">
                    {{ strtoupper(substr((string) (auth('alumno')->user()?->apellido ?? 'U'), 0, 1)) }}
                </span>
            </div>
            <div class="flex-1 min-w-0" x-show="!sidebarCollapsed" x-cloak>
                <p class="text-white text-xs font-medium truncate">
                    {{ auth('alumno')->user()?->apellido ?? '' }}, {{ auth('alumno')->user()?->nombre ?? '' }}
                </p>
            </div>
            <form method="POST" action="{{ route('alumnos.logout') }}">
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

<div class="se-main flex flex-col min-h-screen transition-[padding] duration-200 ease-in-out"
     :class="[
        sidebarCollapsed ? 'is-collapsed' : '',
        sidebarOpen ? 'is-mobile-open' : ''
     ]">

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
        const LOGOUT_URL = @json(route('alumnos.logout'));
        const LOGIN_URL = @json(route('alumnos.login'));

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

