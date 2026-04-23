<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? (isset($title) ? $title . ' — ' : '') }}{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full" x-data="{ sidebarOpen: false }">

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
<aside class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col bg-primary-900 transform transition-transform duration-200 ease-in-out
              md:translate-x-0"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">

    {{-- Logo / App name --}}
    <div class="flex items-center gap-3 h-16 px-4 bg-primary-950">
        <div class="w-8 h-8 bg-primary-400 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-primary-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
        </div>
        <span class="text-white font-semibold text-sm leading-tight">{{ config('app.name') }}</span>
    </div>

    {{-- Context info --}}
    <div class="px-4 py-3 bg-primary-800 border-b border-primary-700">
        <p class="text-primary-200 text-xs font-medium uppercase tracking-wide">Sesión activa</p>
        <p class="text-white text-sm font-semibold mt-0.5">{{ schoolCtx()->nivelNombre() }}</p>
        <p class="text-primary-300 text-xs">Año lectivo {{ schoolCtx()->terlecAno() }}</p>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-1">

        <p class="px-2 mb-2 text-primary-400 text-xs font-semibold uppercase tracking-widest">
            Configuración
        </p>

        @php $route = request()->route()?->getName(); @endphp

        <a href="{{ route('abm.terlec') }}"
           @class([
               'flex items-center gap-3 px-3 py-2 text-sm rounded-md font-medium transition-colors',
               'bg-primary-700 text-white' => str_starts_with($route ?? '', 'abm.terlec'),
               'text-primary-200 hover:bg-primary-700 hover:text-white' => !str_starts_with($route ?? '', 'abm.terlec'),
           ])>
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Términos Lectivos
        </a>

        <a href="{{ route('abm.niveles') }}"
           @class([
               'flex items-center gap-3 px-3 py-2 text-sm rounded-md font-medium transition-colors',
               'bg-primary-700 text-white' => str_starts_with($route ?? '', 'abm.niveles'),
               'text-primary-200 hover:bg-primary-700 hover:text-white' => !str_starts_with($route ?? '', 'abm.niveles'),
           ])>
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7h18M3 12h18M3 17h18"/>
            </svg>
            Niveles
        </a>

        <p class="px-2 mt-4 mb-2 text-primary-400 text-xs font-semibold uppercase tracking-widest">
            Estudiantes
        </p>

        <a href="{{ route('abm.legajos') }}"
           @class([
               'flex items-center gap-3 px-3 py-2 text-sm rounded-md font-medium transition-colors',
               'bg-primary-700 text-white' => str_starts_with($route ?? '', 'abm.legajos'),
               'text-primary-200 hover:bg-primary-700 hover:text-white' => !str_starts_with($route ?? '', 'abm.legajos'),
           ])>
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Legajos de Estudiantes
        </a>

    </nav>

    {{-- User footer --}}
    <div class="px-4 py-3 bg-primary-950 border-t border-primary-800">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-white text-xs font-bold">
                    {{ strtoupper(substr(Auth::user()->apellido ?? 'U', 0, 1)) }}
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-xs font-medium truncate">
                    {{ Auth::user()->nombre ?? '' }} {{ Auth::user()->apellido ?? '' }}
                </p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        title="Cerrar sesión"
                        class="text-primary-400 hover:text-white transition-colors">
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
<div class="md:pl-64 flex flex-col min-h-screen">

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
            <span class="ml-auto text-xs text-gray-500">{{ schoolCtx()->nivelNombre() }} · {{ schoolCtx()->terlecAno() }}</span>
        </div>
    </header>

    {{-- Desktop top bar --}}
    <header class="hidden md:flex items-center h-14 px-6 bg-white border-b border-gray-200">
        <h1 class="text-base font-semibold text-gray-800">
            @yield('pageTitle', '')
        </h1>
        <div class="ml-auto flex items-center gap-2">
            <span class="bg-primary-50 text-primary-700 px-2 py-0.5 rounded text-xs font-medium">
                {{ schoolCtx()->nivelNombre() }}
            </span>
            <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs font-medium">
                Año {{ schoolCtx()->terlecAno() }}
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
