<?php
return [
    // Zona horaria operativa
    'timezone' => 'America/Mexico_City',

    // Disparadores EXACTOS (sin rangos)
    'pre_open_days'   => [7, 1],       // antes de abrir (periodo siguiente)
    'pre_close_days'  => [14, 7, 1],   // antes de cerrar (periodo actual)
    'overdue_days'    => [1, 7],       // después de cerrar (periodo actual)

    // Sin open/close day exacto (solo los hitos solicitados)
    'notify_on_open_day'  => false,
    'notify_on_close_day' => false,

    // A quién se envía (Spatie roles)
    'recipient_roles' => ['administrador','capturista'],

    // Canales de Laravel Notification
    'channels' => ['database'], // añade 'mail' si ya tienes SMTP

    // Ruta del tablero al que mandará el botón de la notificación
    'dashboard_route' => 'programa-verificacion.index',

    'telegram' => [
    'enabled'   => true,
    'max_items' => 10,
],

];
