import './bootstrap';

import Alpine from 'alpinejs';

// resources/js/app.js
import '../css/app.css'; // ← tu Tailwind (si aplica)

// ✅ Icons webfont (ruta correcta con /dist/)
import '@tabler/icons-webfont/dist/tabler-icons.min.css';

// ✅ Tabler Core CSS
import '@tabler/core/dist/css/tabler.min.css';

// (Opcional) vendors si vas a usar selects, datepickers, charts de Tabler:
import '@tabler/core/dist/css/tabler-vendors.min.css';

// ✅ Tabler Core JS
import '@tabler/core/dist/js/tabler.min.js';

import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js'; window.bootstrap = bootstrap;


window.Alpine = Alpine;

Alpine.start();
