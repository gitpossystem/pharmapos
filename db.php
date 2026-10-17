<?php
// Shared PDO connection. Use it in every API file:  $pdo = db();
require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    if (DB_SSL_CA !== '') {
        $options[PDO::MYSQL_ATTR_SSL_CA] = DB_SSL_CA;
    }

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    return $pdo;
}
