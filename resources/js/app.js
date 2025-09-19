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
function cargarNotificaciones() {
    fetch('/notificaciones/nuevas', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(({ count, items }) => {
            const badge = document.getElementById('notif-count');
            if (badge) badge.textContent = count > 9 ? '9+' : String(count ?? 0);

            const list = document.getElementById('notif-list');
            if (!list) return;

            list.innerHTML = '';
            if (!items || !items.length) {
                list.innerHTML = `<div class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">Sin notificaciones</div>`;
                return;
            }

            items.forEach(n => {
                const a = document.createElement('a');
                a.href = n.url || '/cargas';
                a.className = 'block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700';
                a.innerHTML = `
                    <div class="font-medium">${n.titulo ?? 'Notificación'}</div>
                    <div class="text-gray-500 dark:text-gray-400 text-xs">${n.mensaje ?? ''}</div>
                    <div class="text-gray-400 dark:text-gray-500 text-[10px] mt-1">${n.fecha ?? ''}</div>
                `;
                a.addEventListener('click', () => {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    fetch(`/notificaciones/${n.id}/leer`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token
                        }
                    }).catch(() => {});
                });
                list.appendChild(a);
            });

            const divider = document.createElement('div');
            divider.className = 'border-t border-gray-200 dark:border-gray-700 my-1';
            list.appendChild(divider);

            const verTodas = document.createElement('a');
            verTodas.href = '/cargas';
            verTodas.className = 'block px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-950/30';
            verTodas.textContent = 'Ir a cargas';
            list.appendChild(verTodas);
        })
        .catch(console.error);
}

document.addEventListener('DOMContentLoaded', () => {
    cargarNotificaciones();
    setInterval(cargarNotificaciones, 15000);
});
