import './bootstrap';

/**
 * Carga de calificaciones: validación de notas permitidas en el cliente (sin request si es inválida).
 * Delegación `focusout` en `tbody[data-se-calif-tbody]` + toast liviano (sin SweetAlert).
 * Navegación con flechas entre celdas; Enter baja una fila (misma columna) o salta a la columna siguiente en la primera fila al llegar al final.
 */
function seCalifCampoConCatalogo(field) {
    return /^ic(0[1-9]|1[0-9]|2[0-8])$/.test(field) || field === 'dic' || field === 'feb';
}

function seCalifCallSaveCell(root, rowId, field, value) {
    const c = root && root.__livewire;
    if (!c || !c.$wire) {
        return false;
    }
    const w = c.$wire;
    if (typeof w.call === 'function') {
        w.call('saveCell', rowId, field, value);
        return true;
    }
    if (typeof w.saveCell === 'function') {
        w.saveCell(rowId, field, value);
        return true;
    }
    return false;
}

/** Filas × columnas de inputs de nota (orden DOM = orden visual en la tabla). */
function seCalifBuildNavMatrix(tbody) {
    const matrix = [];
    tbody.querySelectorAll(':scope > tr').forEach((tr) => {
        const row = [];
        tr.querySelectorAll('input[id^="se-calif-"]').forEach((inp) => {
            if (inp.type === 'checkbox') {
                return;
            }
            if (!/^se-calif-\d+-.+$/.test(String(inp.id || ''))) {
                return;
            }
            row.push(inp);
        });
        if (row.length) {
            matrix.push(row);
        }
    });
    return matrix;
}

function seCalifFindNavPos(matrix, el) {
    for (let r = 0; r < matrix.length; r++) {
        const c = matrix[r].indexOf(el);
        if (c >= 0) {
            return { row: r, col: c };
        }
    }
    return null;
}

function seCalifFocusNavCell(inp) {
    if (!inp) {
        return;
    }
    inp.focus();
    if (typeof inp.select === 'function') {
        inp.select();
    }
}

/**
 * Globo con flecha hacia la celda con nota inválida (visible con scroll largo).
 * @param {HTMLElement} [anchorEl] input de la celda; si no hay, aviso centrado arriba sin flecha.
 */
window.seCalifToastInvalida = function (anchorEl) {
    const GAP = 5;
    const M = 6;

    const wrap = document.createElement('div');
    wrap.style.position = 'fixed';
    wrap.style.zIndex = '9999';
    wrap.style.pointerEvents = 'none';
    wrap.className =
        'flex min-w-[8.5rem] max-w-[12rem] flex-col items-center';

    const bubble = document.createElement('div');
    bubble.setAttribute('role', 'alert');
    bubble.className =
        'rounded-lg border border-red-300 bg-red-50 px-2.5 py-1.5 text-center text-[11px] font-semibold leading-snug text-red-900 shadow-md';
    bubble.textContent = 'La Calificación no es Válida.';

    const arrowUp = () => {
        const a = document.createElement('div');
        a.setAttribute('aria-hidden', 'true');
        a.className =
            'h-0 w-0 shrink-0 border-x-[7px] border-b-[8px] border-x-transparent border-b-red-300 -mb-px';
        return a;
    };

    const arrowDown = () => {
        const a = document.createElement('div');
        a.setAttribute('aria-hidden', 'true');
        a.className =
            'h-0 w-0 shrink-0 border-x-[7px] border-t-[8px] border-x-transparent border-t-red-300 -mt-px';
        return a;
    };

    const setBelow = () => {
        wrap.replaceChildren(arrowUp(), bubble);
    };

    const setAbove = () => {
        wrap.replaceChildren(bubble, arrowDown());
    };

    const setFallback = () => {
        wrap.replaceChildren(bubble);
    };

    const place = () => {
        const okAnchor =
            anchorEl &&
            typeof anchorEl.getBoundingClientRect === 'function' &&
            document.body.contains(anchorEl);

        if (!okAnchor) {
            setFallback();
            if (!wrap.parentNode) {
                document.body.appendChild(wrap);
            }
            wrap.style.left = '50%';
            wrap.style.top = '4.5rem';
            wrap.style.transform = 'translateX(-50%)';
            return;
        }

        const r = anchorEl.getBoundingClientRect();

        setBelow();
        if (!wrap.parentNode) {
            document.body.appendChild(wrap);
        }

        let h = wrap.offsetHeight;
        let below = r.bottom + GAP + h <= window.innerHeight - M;
        if (!below && r.top - GAP - h >= M) {
            setAbove();
            h = wrap.offsetHeight;
            below = false;
        } else {
            below = true;
        }

        const w = wrap.offsetWidth;
        let left = r.left + r.width / 2 - w / 2;
        left = Math.max(M, Math.min(left, window.innerWidth - w - M));

        let top = below ? r.bottom + GAP : r.top - GAP - h;
        if (below && top + h > window.innerHeight - M) {
            top = Math.max(M, window.innerHeight - h - M);
        }
        if (!below && top < M) {
            top = M;
        }

        wrap.style.left = `${left}px`;
        wrap.style.top = `${top}px`;
        wrap.style.transform = '';
    };

    requestAnimationFrame(() => {
        requestAnimationFrame(place);
    });

    const onScrollOrResize = () => place();
    window.addEventListener('scroll', onScrollOrResize, true);
    window.addEventListener('resize', onScrollOrResize);

    window.setTimeout(() => {
        window.removeEventListener('scroll', onScrollOrResize, true);
        window.removeEventListener('resize', onScrollOrResize);
        wrap.remove();
    }, 3500);
};

function bindCalifCargaTablas() {
    document.querySelectorAll('[data-se-calif-tbody]').forEach((tbody) => {
        if (tbody._seCalifBound) {
            return;
        }
        tbody._seCalifBound = true;

        tbody.addEventListener(
            'focusin',
            (e) => {
                const el = e.target;
                if (!el || el.tagName !== 'INPUT' || el.type === 'checkbox') {
                    return;
                }
                el.dataset.seCalifLast = el.value ?? '';
            },
            true,
        );

        tbody.addEventListener(
            'focusout',
            (e) => {
                const el = e.target;
                if (!el || el.tagName !== 'INPUT' || el.type === 'checkbox') {
                    return;
                }
                const m = el.id && String(el.id).match(/^se-calif-(\d+)-(.+)$/);
                if (!m) {
                    return;
                }
                const rowId = parseInt(m[1], 10);
                const field = m[2];
                if (field === 'calif') {
                    return;
                }
                const val = (el.value || '').trim();

                const activa = tbody.getAttribute('data-se-calif-activa') === '1';
                let allowed = [];
                try {
                    allowed = JSON.parse(tbody.getAttribute('data-se-calif-allowed') || '[]');
                } catch {
                    allowed = [];
                }
                // Todo como string (por si el JSON trae números p. ej. 10 vs "10").
                const set = new Set(allowed.map((x) => String(x).trim()));

                if (activa && seCalifCampoConCatalogo(field) && val !== '' && !set.has(val)) {
                    el.value = el.dataset.seCalifLast ?? '';
                    window.seCalifToastInvalida(el);
                    queueMicrotask(() => {
                        el.focus();
                        if (typeof el.select === 'function') {
                            el.select();
                        }
                    });
                    return;
                }

                const root = el.closest('[wire\\:id]');
                if (!root) {
                    return;
                }
                seCalifCallSaveCell(root, rowId, field, el.value);
            },
            true,
        );

        tbody.addEventListener(
            'keydown',
            (e) => {
                if (e.ctrlKey || e.metaKey || e.altKey) {
                    return;
                }
                const el = e.target;
                if (!el || el.tagName !== 'INPUT' || el.type === 'checkbox') {
                    return;
                }
                if (!/^se-calif-\d+-.+$/.test(String(el.id || ''))) {
                    return;
                }
                const navKeys = ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Enter'];
                if (!navKeys.includes(e.key)) {
                    return;
                }

                const matrix = seCalifBuildNavMatrix(tbody);
                const pos = seCalifFindNavPos(matrix, el);
                if (!pos) {
                    return;
                }

                const nrows = matrix.length;
                const ncols = matrix[0] ? matrix[0].length : 0;
                if (!nrows || !ncols) {
                    return;
                }

                const { row, col } = pos;
                let nr = row;
                let nc = col;

                if (e.key === 'ArrowLeft') {
                    nc = col - 1;
                } else if (e.key === 'ArrowRight') {
                    nc = col + 1;
                } else if (e.key === 'ArrowUp') {
                    nr = row - 1;
                } else if (e.key === 'ArrowDown') {
                    nr = row + 1;
                } else if (e.key === 'Enter') {
                    if (row + 1 < nrows) {
                        nr = row + 1;
                        nc = col;
                    } else if (col + 1 < ncols) {
                        nr = 0;
                        nc = col + 1;
                    } else {
                        return;
                    }
                }

                if (nr < 0 || nr >= nrows || nc < 0 || nc >= ncols) {
                    return;
                }

                const next = matrix[nr][nc];
                if (!next || next === el) {
                    return;
                }

                e.preventDefault();
                seCalifFocusNavCell(next);
            },
            true,
        );
    });
}

document.addEventListener('DOMContentLoaded', () => queueMicrotask(bindCalifCargaTablas));

function triggerSeSidebarOverflowSync() {
    const shell = document.getElementById('se-shell');
    if (!shell) {
        return;
    }

    const Alpine = window.Alpine;
    if (Alpine && typeof Alpine.$data === 'function') {
        try {
            const data = Alpine.$data(shell);
            if (data && typeof data.syncSidebarCollapse === 'function') {
                data.syncSidebarCollapse();
                return;
            }
        } catch (e) {
            // Alpine aún no hidrató el shell
        }
    }

    shell.dispatchEvent(new CustomEvent('se-sidebar-sync-overflow', { bubbles: false }));
}

document.addEventListener('livewire:navigated', () => {
    queueMicrotask(bindCalifCargaTablas);
    queueMicrotask(triggerSeSidebarOverflowSync);
    window.setTimeout(triggerSeSidebarOverflowSync, 200);
});

document.addEventListener('livewire:init', () => {
    const L = window.Livewire;
    if (L && typeof L.hook === 'function') {
        L.hook('morph.updated', () => {
            queueMicrotask(bindCalifCargaTablas);
            queueMicrotask(triggerSeSidebarOverflowSync);
        });
    }
});

// Alpine.js es inyectado y gestionado por Livewire 4.
// NO importar ni iniciar Alpine aquí para evitar conflictos.
// Si necesitás plugins de Alpine, usá el hook de Livewire:
//
// import collapse from '@alpinejs/collapse';
// import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
// Alpine.plugin(collapse);
// Livewire.start();
