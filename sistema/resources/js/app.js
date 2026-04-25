import './bootstrap';

function tryCollapseSidebarAfterNavigation() {
    if (localStorage.getItem('sidebarCollapseNext') !== '1') {
        return;
    }

    const shell = document.getElementById('se-shell');
    if (!shell) {
        return;
    }

    const Alpine = window.Alpine;
    if (Alpine && typeof Alpine.$data === 'function') {
        try {
            const data = Alpine.$data(shell);
            if (data && typeof data.applyPostNavCollapse === 'function') {
                data.applyPostNavCollapse();
                return;
            }
        } catch (e) {
            // Alpine aún no hidrató el shell
        }
    }

    shell.dispatchEvent(new CustomEvent('se-sidebar-post-nav-collapse', { bubbles: false }));
}

// Livewire 4: también en la primera carga; no duplicar listeners (este bundle se carga una vez).
document.addEventListener('livewire:navigated', () => {
    queueMicrotask(tryCollapseSidebarAfterNavigation);
    window.setTimeout(tryCollapseSidebarAfterNavigation, 200);
});

// Alpine.js es inyectado y gestionado por Livewire 4.
// NO importar ni iniciar Alpine aquí para evitar conflictos.
// Si necesitás plugins de Alpine, usá el hook de Livewire:
//
// import collapse from '@alpinejs/collapse';
// import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
// Alpine.plugin(collapse);
// Livewire.start();
