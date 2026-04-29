@extends('layouts.alumno')

@section('pageTitle', 'Notificación')

@section('content')
    <div class="max-w-3xl space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-900 truncate">{{ $msg['titulo'] }}</h1>
            <a href="{{ route('alumnos.push.mis') }}" class="text-sm text-teal-700 hover:text-teal-800">
                Volver
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 space-y-3">
            @if (!empty($msg['created_at']))
                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($msg['created_at'])->format('d/m/Y H:i') }}</p>
            @endif

            <div class="text-gray-800 whitespace-pre-line">{{ $msg['cuerpo'] }}</div>

            @if (!empty($msg['url']))
                <a class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-teal-700 text-white hover:bg-teal-800 transition"
                   href="{{ $msg['url'] }}">
                    Abrir
                </a>
            @endif
        </div>
    </div>
@endsection

