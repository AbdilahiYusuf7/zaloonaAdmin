<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth-guard.php';
require_once __DIR__ . '/csrf.php';

// Autoload service/ and repository/ classes by matching class name to file name.
spl_autoload_register(function (string $class): void {
    foreach (['service', 'repository'] as $dir) {
        $path = __DIR__ . "/{$dir}/{$class}.php";

        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});
