<?php

declare(strict_types=1);

/**
 * Shared PDO connection. Always parameterized — never build queries with
 * string concatenation of user input.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = env('DB_HOST', '127.0.0.1');
    $port = env('DB_PORT', '3306');
    $name = env('DB_NAME', 'zaloona_admin');
    $user = env('DB_USER', 'root');
    $pass = env('DB_PASS', '');
    $sslCa = env('DB_SSL_CA_PATH', null);

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    if ($sslCa !== null && $sslCa !== '') {
        $options[PDO::MYSQL_ATTR_SSL_CA] = dirname(__DIR__) . '/' . $sslCa;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }

    $pdo = new PDO($dsn, $user, $pass, $options);

    return $pdo;
}
