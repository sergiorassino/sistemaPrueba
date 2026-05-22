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
        .se-sidebar-link { color: var(--se-white-85); font-family: "Roboto", system-ui, sans-serif; font-stretch: normal; }
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
    </style>
</head>
@php
    $route = request()->route()?->getName();
@endphp
<body class="h-full">

<div id="se-shell-docente" class="h-full" x-data="{ sidebarOpen: false, sidebarCollapsed: false }">

<div x-show="sidebarOpen"
     x-transition:enter="transition-opacity ease-out duration-200"
     x-transition:leave="transition-opacity ease-in duration-150"
     class="fixed inset-0 z-30 bg-gray-900/50 md:hidden"
     @click="sidebarOpen = false"
     style="display:none"></div>

<aside class="se-sidebar fixed inset-y-0 left-0 z-[1000] flex flex-col transform transition-transform duration-200 ease-in-out
              md:translate-x-0 md:shadow-lg"
       :class="[
           sidebarOpen ? 'translate-x-0' : '-translate-x-full',
           'md:translate-x-0',
           sidebarCollapsed ? 'is-collapsed' : ''
       ]">

    @php
        $logoUrl = schoolLogoUrl() ?: asset('img/3.png');
        $usuario = Auth::user();
        $linea = schoolCtx()->nivelNombre()
            . ' · ' . schoolCtx()->terlecAno()
            . ' · ' . trim((string) ($usuario?->apellido ?? '') . ' ' . (string) ($usuario?->nombre ?? ''));
    @endphp

    <div class="border-b se-sidebar-sep relative z-[1] flex-shrink-0 px-2.5 py-2">
        <div class="flex items-center gap-2 min-w-0">
            <span class="rounded-lg bg-white px-2 py-1.5 shadow-sm flex-shrink-0">
                <img src="{{ $logoUrl }}" alt="" class="h-9 w-auto max-w-[9.5rem] object-contain">
            </span>
            <p class="text-white text-[11px] font-semibold truncate min-w-0" title="{{ $linea }}">{{ $linea }}</p>
        </div>
        <p class="mt-1 text-[10px] font-bold uppercase tracking-wider text-white/60 px-0.5">Menú de Docentes</p>
    </div>

    <nav class="flex-1 relative z-[1] px-2.5 py-3 overflow-y-auto space-y-0.5"
         @click.capture="$event.target.closest('a[href]') && (sidebarOpen = false)">

        <a href="{{ route('portalDocente.home') }}"
           @class([
               'se-sidebar-link flex items-center gap-2 px-2.5 py-1.5 text-[13px] rounded-md font-medium transition-colors',
               'is-active shadow-sm' => ($route ?? '') === 'portalDocente.home',
           ])
           title="Inicio del portal docente">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/>
            </svg>
            <span class="truncate">Inicio</span>
        </a>

        {{-- Nuevos ítems del Menú de Docentes: agregar aquí (ver docs/08-menus-de-navegacion.md) --}}

    </nav>

    <div class="px-4 py-3 border-t se-sidebar-sep relative z-[1]">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background: var(--se-primary);">
                <span class="text-white text-xs font-bold">
                    {{ strtoupper(substr((string) ($usuario?->apellido ?? 'U'), 0, 1)) }}
                </span>
            </div>
            <div class="flex-1 min-w-0">
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

<div class="se-main flex flex-col min-h-screen"
     :class="[sidebarCollapsed ? 'is-collapsed' : '', sidebarOpen ? '' : '']">

    <header class="sticky top-0 z-20 md:hidden border-b border-[#C1D7DA] bg-white/95 backdrop-blur-sm">
        <div class="flex items-center gap-3 h-14 px-4">
            <button type="button" @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-700">
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
