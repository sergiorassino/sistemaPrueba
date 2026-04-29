/**
 * Registro del Service Worker y suscripción a notificaciones push.
 */
(function () {
    'use strict';

    var base = document.querySelector('meta[name="pwa-base"]');
    var basePath = base ? base.getAttribute('content') : '';
    var scopeMeta = document.querySelector('meta[name="pwa-scope"]');
    var scope = scopeMeta ? scopeMeta.getAttribute('content') : (basePath || './');
    var swUrlMeta = document.querySelector('meta[name="pwa-sw-url"]');
    var swUrl = (swUrlMeta && swUrlMeta.getAttribute('content')) ? swUrlMeta.getAttribute('content') : (basePath || '') + 'sw.js';

    window.studentRequestPushPermission = function () {
        return Promise.resolve('unsupported');
    };

    if (!('serviceWorker' in navigator)) {
        if (typeof window.studentShowPushStatus === 'function') {
            var isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent) || (navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform));
            var msg = isIOS
                ? 'En iPhone/iPad agregá esta página al Inicio (Compartir → Agregar a Inicio) y abrila desde el ícono; las notificaciones no funcionan en Safari en una pestaña normal.'
                : 'Las notificaciones push requieren HTTPS y un navegador que soporte Service Worker. Probá desde una URL que empiece con https://';
            window.studentShowPushStatus(msg, 'error');
        }
        return;
    }

    function show(msg, type) {
        if (typeof window.studentShowPushStatus === 'function') {
            window.studentShowPushStatus(msg, type || 'error');
        }
        console.warn('[PWA]', msg);
    }

    navigator.serviceWorker.register(swUrl, { scope: scope || './' })
        .then(function (reg) {
            window.dispatchEvent(new CustomEvent('pwa-sw-registered', { detail: reg }));
            return checkPushSupport(reg);
        })
        .catch(function (err) {
            show('Service Worker: ' + (err.message || String(err)), 'error');
        });

    function checkPushSupport(reg) {
        if (!('PushManager' in window)) return Promise.resolve();
        if (!('Notification' in window) || Notification.permission === 'denied') return Promise.resolve();

        var vapidMeta = document.querySelector('meta[name="vapid-public-key"]');
        var vapidKey = vapidMeta ? vapidMeta.getAttribute('content') : null;
        if (!vapidKey) return Promise.resolve();

        return reg.pushManager.getSubscription().then(function (sub) {
            if (sub) {
                sendSubscriptionToServer(sub, basePath);
                return;
            }
            if (Notification.permission === 'granted') {
                subscribeUser(reg, vapidKey, basePath);
            }
        });
    }

    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - base64String.length % 4) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var rawData = window.atob(base64);
        var arr = new Uint8Array(rawData.length);
        for (var i = 0; i < rawData.length; i++) arr[i] = rawData.charCodeAt(i);
        return arr;
    }

    function subscribeUser(reg, vapidKey, basePath) {
        reg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(vapidKey)
        }).then(function (sub) {
            sendSubscriptionToServer(sub, basePath);
        }).catch(function (e) {
            show('Suscripción push: ' + (e.message || String(e)));
        });
    }

    function sendSubscriptionToServer(subscription, basePath) {
        var endpoint = (basePath || '') + 'api/subscribe';
        var payload = subscription.toJSON();
        var csrf = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrf ? csrf.getAttribute('content') : '';

        function doSend() {
            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            }).then(function (r) {
                if (!r.ok) {
                    return r.text().then(function (body) {
                        throw new Error(r.status + ' ' + (body || r.statusText));
                    });
                }
                if (typeof window.studentShowPushStatus === 'function') {
                    window.studentShowPushStatus('Notificaciones activadas para este dispositivo.', 'success');
                }
                try {
                    window.dispatchEvent(new CustomEvent('student-push-subscribed'));
                } catch (err) {}
            }).catch(function (e) {
                show('Servidor (guardar suscripción): ' + (e.message || String(e)));
            });
        }

        if (navigator.userAgentData && typeof navigator.userAgentData.getHighEntropyValues === 'function') {
            navigator.userAgentData.getHighEntropyValues(['brands', 'platform', 'mobile']).then(function (hints) {
                payload.client_hints = hints;
                doSend();
            }).catch(function () { doSend(); });
        } else {
            doSend();
        }
    }

    window.studentRequestPushPermission = function () {
        if (!('Notification' in window)) return Promise.resolve('unsupported');
        if (Notification.permission === 'granted') return Promise.resolve('granted');
        if (Notification.permission === 'denied') return Promise.resolve('denied');
        return Notification.requestPermission().then(function (p) {
            if (p === 'granted' && navigator.serviceWorker.ready) {
                navigator.serviceWorker.ready.then(function (reg) {
                    var vapidMeta = document.querySelector('meta[name="vapid-public-key"]');
                    if (vapidMeta) subscribeUser(reg, vapidMeta.getAttribute('content'), basePath || '');
                });
            }
            return p;
        });
    };
})();

