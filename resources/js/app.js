import './bootstrap';
import Alpine from 'alpinejs';

// Bootstrap antes que Tabler (JS)
import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js';
window.bootstrap = bootstrap;

// Tabler JS (solo JS, sin CSS aquí)
import '@tabler/core/dist/js/tabler.min.js';

window.Alpine = Alpine;
Alpine.start();

// ---------------- Notificaciones (polling navbar) ----------------
function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function postJSON(url, data = null) {
    return fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf(),
            ...(data ? { 'Content-Type': 'application/json' } : {})
        },
        body: data ? JSON.stringify(data) : null
    });
}

function setNotifBadge(count) {
    const badge = document.getElementById('notif-count');
    if (!badge) return;
    const safe = Number.isFinite(count) ? count : 0;
    badge.textContent = safe > 9 ? '9+' : String(safe);
}

function renderEmptyList(listEl) {
    listEl.innerHTML = `<div class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">Sin notificaciones</div>`;
}

function construirHeader(listEl, count, onMarkAll) {
    const header = document.createElement('div');
    header.className = 'px-3 py-2 flex items-center justify-between';

    // Botón "Marcar todas"
    if (count > 0) {
        const markAll = document.createElement('button');
        markAll.type = 'button';
        markAll.className = 'text-xs font-medium text-blue-600 hover:underline dark:text-blue-400';
        markAll.textContent = 'Marcar todas';
        markAll.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                await postJSON('/notificaciones/leer-todas');
                await cargarNotificaciones(true); // refresh forzado
            } catch (_) {}
        });
        header.appendChild(markAll);
    } else {
        const span = document.createElement('span');
        span.className = 'text-xs text-gray-500';
        span.textContent = 'Sin nuevas';
        header.appendChild(span);
    }

    // Link "Ir a cargas" (o cambia aquí si quieres a otra ruta)
    const verTodas = document.createElement('a');
    verTodas.href = '/cargas';
    verTodas.className = 'text-xs text-blue-600 hover:underline dark:text-blue-400';
    verTodas.textContent = 'Ir a cargas';
    header.appendChild(verTodas);

    listEl.appendChild(header);

    const divider = document.createElement('div');
    divider.className = 'border-t border-gray-200 dark:border-gray-700';
    listEl.appendChild(divider);
}

function construirItem(n, onMarked) {
    // Contenedor del item (no es <a> para poder tener botón independiente)
    const item = document.createElement('div');
    item.className = 'px-4 py-2 flex items-start justify-between gap-2 hover:bg-gray-100 dark:hover:bg-gray-700';

    // Área clickeable que navega (y marca como leída)
    const link = document.createElement('a');
    link.href = n.url || '/cargas';
    link.className = 'min-w-0 flex-1';
    link.innerHTML = `
        <div class="font-medium truncate">${n.titulo ?? 'Notificación'}</div>
        <div class="text-gray-500 dark:text-gray-400 text-xs truncate">${n.mensaje ?? ''}</div>
        <div class="text-gray-400 dark:text-gray-500 text-[10px] mt-1">${n.fecha ?? ''}</div>
    `;
    link.addEventListener('click', async () => {
        try { await postJSON(`/notificaciones/${n.id}/leer`); } catch (_) {}
        // dejamos que navegue
    });

    // Botón "Marcar" individual (no navega)
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'shrink-0 text-xs px-2 py-1 rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600';
    btn.title = 'Marcar como leída';
    btn.textContent = 'Marcar';
    btn.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        try {
            await postJSON(`/notificaciones/${n.id}/leer`);
            onMarked?.(item);
        } catch (_) {}
    });

    item.appendChild(link);
    item.appendChild(btn);
    return item;
}

async function cargarNotificaciones(force = false) {
    try {
        const r = await fetch('/notificaciones/nuevas', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const { count, items } = await r.json();

        // Actualiza badge
        setNotifBadge(count ?? 0);

        // Rellena lista
        const list = document.getElementById('notif-list');
        if (!list) return;

        list.innerHTML = '';
        construirHeader(list, count ?? 0);

        if (!items || !items.length) {
            renderEmptyList(list);
            return;
        }

        // Estado local del contador (para restar sin pedir al server)
        let remaining = Number.isFinite(count) ? count : items.length;

        items.forEach((n) => {
            const itemEl = construirItem(n, (el) => {
                // Al marcar: quitar el item, decrementar y refrescar vacío si corresponde
                el.remove();
                remaining = Math.max(0, remaining - 1);
                setNotifBadge(remaining);

                // Si el listado visible quedó sin elementos (más allá del header/divider), lo refrescamos completo
                const visibles = list.querySelectorAll('div.px-4.py-2.flex.items-start.justify-between');
                if (visibles.length === 0) {
                    renderEmptyList(list);
                }
            });
            list.appendChild(itemEl);
        });
    } catch (err) {
        console.error(err);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    cargarNotificaciones();
    setInterval(cargarNotificaciones, 15000);
});

if (document.getElementById('vehiculos-app')) {
  import('./vehiculos/index.js');    // se compila como chunk y queda en el manifest
}

