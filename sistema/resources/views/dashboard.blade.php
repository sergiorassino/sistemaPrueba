@extends('layouts.app')

@section('pageTitle', 'Panel principal')

@section('content')
<div class="max-w-5xl mx-auto">
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Bienvenido,
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
    </div>
</div>
@endsection
