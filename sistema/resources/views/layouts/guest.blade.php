<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Acceso</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full">
    <div class="min-h-screen flex flex-col justify-center py-2 sm:py-4 px-4 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md flex flex-col items-center gap-1.5 sm:gap-2">
            <img src="{{ asset('img/3.png') }}"
                 alt="{{ config('app.name') }}"
                 class="w-auto h-auto max-h-[150px] sm:max-h-[170px] md:max-h-[190px] object-contain drop-shadow-sm">

            <div class="w-full">
                {{ $slot }}
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
