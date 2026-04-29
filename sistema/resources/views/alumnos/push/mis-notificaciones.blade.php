@extends('layouts.alumno')

@section('pageTitle', 'Mis notificaciones')

@section('content')
    <div class="max-w-3xl space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-900">Mis notificaciones</h1>
            <a href="{{ route('alumnos.push.index') }}" class="text-sm text-teal-700 hover:text-teal-800">
                Volver a notificaciones
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 divide-y">
            @forelse ($mensajes as $m)
                <a class="block p-4 hover:bg-gray-50"
                   href="{{ route('alumnos.push.ver', ['id' => $m['id']]) }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 truncate">{{ $m['titulo'] }}</p>
                            <p class="text-sm text-gray-600 mt-1 line-clamp-2 whitespace-pre-line">{{ $m['cuerpo'] }}</p>
                        </div>
                        @if (!empty($m['created_at']))
                            <span class="text-xs text-gray-500 flex-shrink-0">{{ \Carbon\Carbon::parse($m['created_at'])->format('d/m/Y H:i') }}</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="p-4 text-sm text-gray-600">
                    Todavía no tenés notificaciones registradas.
                </div>
            @endforelse
        </div>
    </div>
@endsection

