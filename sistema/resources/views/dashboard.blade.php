@extends('layouts.app')

@section('pageTitle', 'Panel principal')

@section('content')
@php
    $dashboardLinks = [];

    if (tienePermiso(1)) {
        $dashboardLinks[] = [
            'title' => 'Términos lectivos',
            'hint' => 'Ciclos y calendario escolar',
            'href' => route('abm.terlec'),
            'icon' => 'calendar',
        ];
        $dashboardLinks[] = [
            'title' => 'Niveles',
            'hint' => 'Niveles educativos',
            'href' => route('abm.niveles'),
            'icon' => 'levels',
        ];
        $dashboardLinks[] = [
            'title' => 'Campos listado alumnos',
            'hint' => 'Visibilidad en PDF por curso',
            'href' => route('param.campos-listado-alumnos'),
            'icon' => 'table',
        ];
        $dashboardLinks[] = [
            'title' => 'Parámetros del sistema',
            'hint' => 'Configuración general',
            'href' => route('param.parametros-sistema'),
            'icon' => 'cog',
        ];
        $dashboardLinks[] = [
            'title' => 'Gestión de planes de estudio',
            'hint' => 'Planes modelo',
            'href' => route('abm.planes'),
            'icon' => 'layers',
        ];
        $dashboardLinks[] = [
            'title' => 'Cursos y materias del plan',
            'hint' => 'Estructura curricular modelo',
            'href' => route('abm.curplan'),
            'icon' => 'book-plan',
        ];
        $dashboardLinks[] = [
            'title' => 'Cursos / grados / salas',
            'hint' => 'Del año lectivo activo',
            'href' => route('abm.cursos'),
            'icon' => 'building',
        ];
        $dashboardLinks[] = [
            'title' => 'Asignaturas del año',
            'hint' => 'Materias por curso',
            'href' => route('abm.materias-anio'),
            'icon' => 'rows',
        ];
    }

    if (tienePermiso(53)) {
        $dashboardLinks[] = [
            'title' => 'Canales de comunicación',
            'hint' => 'Escuela–familia',
            'href' => route('param.com-canales'),
            'icon' => 'channels',
        ];
    }

    if (tienePermiso(2)) {
        $dashboardLinks[] = [
            'title' => 'Legajos de estudiantes',
            'hint' => 'Datos y matrícula',
            'href' => route('abm.legajos'),
            'icon' => 'users',
        ];
        $dashboardLinks[] = [
            'title' => 'Listado por curso',
            'hint' => 'PDF de alumnos',
            'href' => route('listados.por-curso'),
            'icon' => 'doc',
        ];
        $dashboardLinks[] = [
            'title' => 'Notificación push',
            'hint' => 'Envío a familias',
            'href' => route('push.enviar'),
            'icon' => 'bell',
        ];
        $dashboardLinks[] = [
            'title' => 'Carga de calificaciones',
            'hint' => 'Secundario',
            'href' => route('calificaciones.carga'),
            'icon' => 'clipboard',
        ];
        $dashboardLinks[] = [
            'title' => 'Seguimiento disciplinario',
            'hint' => 'Sanciones y antecedentes',
            'href' => route('seguimiento.disciplinario'),
            'icon' => 'shield',
        ];
    }

    if (tienePermiso(51)) {
        $dashboardLinks[] = [
            'title' => 'Bandeja de comunicados',
            'hint' => 'Con familias',
            'href' => route('comunicaciones.index'),
            'icon' => 'mail',
        ];
    }

    if (tienePermiso(52)) {
        $dashboardLinks[] = [
            'title' => 'Nuevo comunicado',
            'hint' => 'Redactar envío a familias',
            'href' => route('comunicaciones.nuevo'),
            'icon' => 'plus-mail',
        ];
    }

    $accesosDisponibles = count($dashboardLinks);
    $nombreUsuario = trim((Auth::user()->nombre ?? '') . ' ' . (Auth::user()->apellido ?? ''));
    $heroLogo = schoolLogoUrl() ?: asset('img/3.png');
@endphp

<div class="max-w-6xl mx-auto space-y-8">

    {{-- Hero operativo --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#40848D] via-[#366f76] to-[#333333] text-white shadow-lg shadow-neutral-900/15">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_95%_75%_at_100%_0%,rgba(255,255,255,0.12),transparent_55%)]"
             aria-hidden="true"></div>
        <div class="relative flex flex-col gap-6 p-6 sm:p-8 md:flex-row md:items-center md:justify-between md:gap-8">
            <div class="min-w-0 flex-1 space-y-3">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/60">Panel de inicio</p>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight leading-tight">
                    Hola, {{ $nombreUsuario !== '' ? $nombreUsuario : 'usuario' }}
                </h1>
                <p class="text-sm sm:text-base text-white/85 max-w-xl">
                    <span class="font-medium text-white">{{ schoolCtx()->nivelNombre() }}</span>
                    <span class="text-white/45"> · </span>
                    Ciclo lectivo
                    <span class="font-semibold text-[#C1D7DA]">{{ schoolCtx()->terlecAno() }}</span>
                </p>
            </div>
            <div class="flex shrink-0 justify-start md:justify-end">
                <div class="rounded-2xl bg-white p-4 shadow-md">
                    <img src="{{ $heroLogo }}" alt="" class="h-16 sm:h-20 w-auto max-w-[200px] object-contain">
                </div>
            </div>
        </div>
    </section>

    {{-- Resumen --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border bg-white p-5 shadow-sm border-[#C1D7DA]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Accesos disponibles</p>
            <p class="mt-2 text-3xl font-bold tabular-nums text-[#40848D]">{{ $accesosDisponibles }}</p>
            <p class="mt-1 text-xs text-neutral-600">Según su perfil en este contexto.</p>
        </div>
        <div class="rounded-2xl border bg-white p-5 shadow-sm border-[#C1D7DA]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Ciclo lectivo activo</p>
            <p class="mt-2 text-xl font-bold text-neutral-800">{{ schoolCtx()->terlecAno() }}</p>
            <p class="mt-1 text-xs text-neutral-600">{{ schoolCtx()->nivelNombre() }}</p>
        </div>
        <div class="rounded-2xl border bg-white p-5 shadow-sm border-[#C1D7DA]">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-neutral-500">Sesión</p>
            <p class="mt-2 text-xl font-bold text-neutral-800">Activa</p>
            <p class="mt-1 text-xs text-neutral-600">Use el menú lateral para navegar.</p>
        </div>
    </div>

    {{-- Accesos rápidos --}}
    @if ($accesosDisponibles === 0)
        <div class="rounded-2xl border border-dashed border-[#C1D7DA] bg-white/80 p-8 text-center text-sm text-neutral-600">
            No hay accesos configurados para su usuario en este nivel. Consulte al administrador.
        </div>
    @else
        <div>
            <h2 class="mb-4 text-lg font-bold text-neutral-800 tracking-tight">Accesos principales</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($dashboardLinks as $link)
                    <a href="{{ $link['href'] }}" class="se-dash-access group">
                        <div class="se-dash-access-icon group-hover:bg-[rgba(64,132,141,0.15)]">
                            @switch($link['icon'])
                                @case('calendar')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @break
                                @case('levels')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/></svg>
                                    @break
                                @case('table')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                    @break
                                @case('cog')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 16v-2m8-6h-2M6 12H4m14.364 6.364l-1.414-1.414M7.05 7.05 5.636 5.636m12.728 0L16.95 7.05M7.05 16.95l-1.414 1.414"/></svg>
                                    @break
                                @case('layers')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                                    @break
                                @case('book-plan')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @break
                                @case('building')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    @break
                                @case('rows')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16"/></svg>
                                    @break
                                @case('channels')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/></svg>
                                    @break
                                @case('users')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @break
                                @case('doc')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @break
                                @case('bell')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    @break
                                @case('clipboard')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    @break
                                @case('shield')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    @break
                                @case('mail')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    @break
                                @case('plus-mail')
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    @break
                                @default
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            @endswitch
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-neutral-800 group-hover:text-[#40848D] transition-colors">{{ $link['title'] }}</p>
                            <p class="mt-0.5 text-xs text-neutral-600">{{ $link['hint'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
