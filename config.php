<?php
// WebPharmacy - database settings.
// Paste the values your online MySQL provider gives you.
// Keep this file private: never upload it to a public GitHub repo.

define('DB_HOST', '');      // e.g. sql123.example.com (NOT "localhost" for remote hosts)
define('DB_PORT', );                   // some providers use a different port
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');

// Some cloud MySQL providers require SSL. If yours does, download their CA
// certificate, put it next to this file and set the file name here.
define('DB_SSL_CA', '');                   // e.g. __DIR__ . '/ca.pem'  (leave '' if not needed)
