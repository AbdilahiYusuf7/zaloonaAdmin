<?php

declare(strict_types=1);

/**
 * Minimal .env loader — no Composer dependency.
 * Reads KEY=VALUE lines from .env and exposes them via env().
 */
function load_env(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        if ($key !== '' && !array_key_exists($key, $_ENV) && getenv($key) === false) {
            $_ENV[$key] = $value;

            // putenv() is disabled on some hosts (e.g. InfinityFree); best-effort only.
            @putenv("{$key}={$value}");
        }
    }
}

function env(string $key, ?string $default = null): ?string
{
    if (array_key_exists($key, $_ENV)) {
        return $_ENV[$key];
    }

    $value = getenv($key);

    return $value === false ? $default : $value;
}

load_env(dirname(__DIR__) . '/.env');
