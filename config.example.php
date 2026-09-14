<?php
// Copiar a config.php (gitignored) y completar con credenciales reales.
// Misma base de datos que usa admin/ — solo lectura desde acá, salvo
// api-quote-submit.php que inserta en quote_requests.

define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'travelonline');
define('DB_USER', 'travelonline');
define('DB_PASS', 'travelonline_local_only');

// URL pública del panel (admin/) — de ahí se sirven las imágenes subidas
// (/uploads/<id>.<ext>). En producción: https://app.travelonline.tuweb.cl
define('ADMIN_PUBLIC_URL', 'http://localhost:8801');

define('QUOTE_NOTIFICATION_EMAILS', 'reservas@travelonline.cl,gerencia@travelonline.cl');

// Google Identity Services — botón "Cotiza con tu cuenta Google" en /cotizar.
// Crear en https://console.cloud.google.com/apis/credentials (tipo "Aplicación
// web", sin redirect URI, con el dominio real en "Orígenes de JavaScript").
define('GOOGLE_CLIENT_ID', '');
