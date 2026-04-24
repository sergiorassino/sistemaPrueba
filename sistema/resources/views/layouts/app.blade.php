<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        }
        .se-sidebar { background: var(--se-jet); color: #fff; }
        .se-sidebar-sep { border-color: var(--se-sep); }
        .se-sidebar-iconbtn { color: var(--se-white-85); }
        .se-sidebar-iconbtn:hover { background: var(--se-white-10); color: #fff; }
        .se-sidebar-groupbtn { color: var(--se-white-85); background: var(--se-white-05); border: 1px solid var(--se-sep); }
        .se-sidebar-groupbtn:hover { background: var(--se-white-10); }
        .se-sidebar-groupbtn.is-open { background: var(--se-white-10); }
        .se-sidebar-link { color: var(--se-white-85); }
        .se-sidebar-link:hover { background: var(--se-hover); color: #fff; }
        .se-sidebar-link.is-active { background: var(--se-primary); color: #fff; }
    </style>
</head>
@php $route = request()->route()?->getName(); @endphp
<body class="h-full" x-data="{
    sidebarOpen: false,
    sidebarCollapsed: false,
    groups: {
        config: {{ (str_starts_with($route ?? '', 'abm.terlec') || str_starts_with($route ?? '', 'abm.niveles')) ? 'true' : 'false' }},
        students: {{ (str_starts_with($route ?? '', 'abm.legajos') || str_starts_with($route ?? '', 'listados.')) ? 'true' : 'false' }},
    },
    init() {
        this.sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === '1';

        const raw = localStorage.getItem('sidebarGroups');
        if (raw) {
            try {
                const parsed = JSON.parse(raw);
                if (parsed && typeof parsed === 'object') this.groups = { ...this.groups, ...parsed };
            } catch (e) {}
        }
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
              w-64 md:translate-x-0 md:transition-[width] md:duration-200 md:ease-in-out md:shadow-lg"
       :class="[
           sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
           sidebarCollapsed ? 'md:w-20' : 'md:w-64'
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
            <svg x-show="!sidebarCollapsed" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <svg x-show="sidebarCollapsed" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-2.5 py-3 overflow-y-auto space-y-0.5">

        {{-- Configuración --}}
        @if(tienePermiso(1))
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
            </div>
        @endif

        {{-- Estudiantes --}}
        @if(tienePermiso(2))
            <div class="mt-4"></div>
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
<div class="flex flex-col min-h-screen transition-[padding] duration-200 ease-in-out md:pl-64"
     :class="sidebarCollapsed ? 'md:pl-20' : 'md:pl-64'">

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

@livewireScripts
</body>
</html>
