<?php
// Open this file in the browser to check the MySQL connection.
// DELETE it from the server once everything works.
require_once __DIR__ . '/db.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $pdo = db();
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    echo '<h2 style="color:green">Connected successfully</h2>';
    echo '<p>MySQL version: ' . htmlspecialchars($version) . '</p>';
    echo '<p>Tables found: ' . count($tables) . '</p>';
    echo '<ul>';
    foreach ($tables as $t) {
        echo '<li>' . htmlspecialchars($t) . '</li>';
    }
    echo '</ul>';
    if (count($tables) === 0) {
        echo '<p style="color:#b45309">Connection works, but the database is empty. Import schema.sql first.</p>';
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo '<h2 style="color:red">Connection failed</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
}
