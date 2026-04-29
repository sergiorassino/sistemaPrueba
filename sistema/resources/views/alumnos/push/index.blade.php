@extends('layouts.alumno')

@section('pageTitle', 'Notificaciones')

@section('content')
    <meta name="pwa-base" content="{{ url('/notificaciones-push') }}/">
    <meta name="pwa-scope" content="{{ url('/notificaciones-push') }}/">
    <meta name="pwa-sw-url" content="{{ url('/notificaciones-push/sw.js') }}">
    <meta name="vapid-public-key" content="{{ env('VAPID_PUBLIC_KEY') }}">

    <div class="max-w-2xl space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h1 class="text-lg font-semibold text-gray-900">Notificaciones push</h1>
            <p class="text-sm text-gray-600 mt-1">
                Activá notificaciones para recibir avisos en este dispositivo.
            </p>

            <div id="pushStatus" class="mt-4 text-sm"></div>

            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button"
                        id="btnEnablePush"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-teal-700 text-white hover:bg-teal-800 transition">
                    Activar en este dispositivo
                </button>

                <button type="button"
                        id="btnDisablePush"
                        class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-white text-gray-800 hover:bg-gray-50 transition border border-gray-200">
                    Desactivar en este dispositivo
                </button>

                <a href="{{ route('alumnos.push.mis') }}"
                   class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-gray-100 text-gray-800 hover:bg-gray-200 transition border border-gray-200">
                    Mis notificaciones
                </a>
            </div>

            <p class="text-xs text-gray-500 mt-4">
                Requiere HTTPS. En iPhone/iPad funciona al abrir desde “Agregar a Inicio”.
            </p>
        </div>
    </div>

    <script>
        window.studentShowPushStatus = (msg, type) => {
            const el = document.getElementById('pushStatus');
            if (!el) return;
            const cls = type === 'success'
                ? 'text-green-700 bg-green-50 border-green-200'
                : 'text-red-700 bg-red-50 border-red-200';
            el.className = `p-3 rounded-md border ${cls}`;
            el.textContent = msg;
        };

        document.getElementById('btnEnablePush')?.addEventListener('click', async () => {
            const p = await window.studentRequestPushPermission?.();
            if (p === 'denied') window.studentShowPushStatus('Permiso denegado. Habilitalo desde la configuración del navegador.', 'error');
            if (p === 'unsupported') window.studentShowPushStatus('Tu navegador no soporta notificaciones push en este contexto.', 'error');
        });

        document.getElementById('btnDisablePush')?.addEventListener('click', async () => {
            try {
                const reg = await navigator.serviceWorker.ready;
                const sub = await reg.pushManager.getSubscription();
                if (!sub) {
                    window.studentShowPushStatus('Este dispositivo no tiene notificaciones activadas.', 'error');
                    return;
                }
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const r = await fetch(@json(url('/notificaciones-push/api/unsubscribe')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    credentials: 'same-origin',
                    body: JSON.stringify({ endpoint: sub.endpoint }),
                });
                if (!r.ok) throw new Error(await r.text());
                await sub.unsubscribe();
                window.studentShowPushStatus('Notificaciones desactivadas para este dispositivo.', 'success');
            } catch (e) {
                window.studentShowPushStatus('No se pudo desactivar. ' + (e?.message || String(e)), 'error');
            }
        });
    </script>

    <script src="{{ url('/notificaciones-push/js/pwa-register.js') }}"></script>
@endsection

