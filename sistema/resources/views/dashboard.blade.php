@extends('layouts.app')

@section('pageTitle', 'Panel principal')

@section('content')
<div class="max-w-5xl mx-auto">
    <h2 class="mb-6 w-full text-center text-xl font-semibold text-gray-800">Bienvenido,
        {{ Auth::user()->nombre ?? '' }} {{ Auth::user()->apellido ?? '' }}</h2>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('abm.terlec') }}"
           class="card p-5 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                    <svg class="w-6 h-6 text-primary-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Términos Lectivos</p>
                    <p class="text-xs text-gray-500 mt-0.5">ABM años lectivos</p>
                </div>
            </div>
        </a>

        @if (tienePermiso(1))
        <a href="{{ route('param.campos-listado-alumnos') }}"
           class="card p-5 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                    <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Campos listado (legajos)</p>
                    <p class="text-xs text-gray-500 mt-0.5">Visibilidad para PDF por curso</p>
                </div>
            </div>
        </a>
        @endif

        <a href="{{ route('abm.niveles') }}"
           class="card p-5 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                    <svg class="w-6 h-6 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Niveles</p>
                    <p class="text-xs text-gray-500 mt-0.5">ABM niveles educativos</p>
                </div>
            </div>
        </a>

        <a href="{{ route('abm.legajos') }}"
           class="card p-5 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                    <svg class="w-6 h-6 text-purple-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Legajos de Estudiantes</p>
                    <p class="text-xs text-gray-500 mt-0.5">ABM estudiantes</p>
                </div>
            </div>
        </a>

        @if (tienePermiso(2))
        <a href="{{ route('listados.por-curso') }}"
           class="card p-5 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center group-hover:opacity-90 transition-colors"
                     style="background: #C1D7DA;">
                    <svg class="w-6 h-6" style="color: #40848D;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Listado por curso</p>
                    <p class="text-xs text-gray-500 mt-0.5">PDF de alumnos matriculados</p>
                </div>
            </div>
        </a>
        @endif

        @if (tienePermiso(51))
        <a href="{{ route('comunicaciones.index') }}"
           class="card p-5 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center group-hover:opacity-90 transition-colors"
                     style="background: #C1D7DA;">
                    <svg class="w-6 h-6" style="color: #40848D;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Comunicaciones con familias</p>
                    <p class="text-xs text-gray-500 mt-0.5">Bandeja de comunicados y conversaciones</p>
                </div>
            </div>
        </a>
        @endif

        @if (tienePermiso(53))
        <a href="{{ route('param.com-canales') }}"
           class="card p-5 hover:shadow-md transition-shadow group">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center group-hover:bg-slate-200 transition-colors">
                    <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3-3-3z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Canales de comunicación</p>
                    <p class="text-xs text-gray-500 mt-0.5">Quién puede escribir a quién y por qué medios</p>
                </div>
            </div>
        </a>
        @endif
    </div>
</div>
@endsection
