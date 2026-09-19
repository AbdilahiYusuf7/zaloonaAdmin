<?php

declare(strict_types=1);

/** Call at the top of every protected page/endpoint. */
function require_login(): array
{
    $admin = current_admin();

    if ($admin === null) {
        redirect('/Auth/login.php');
    }

    return $admin;
}

/** Call instead of require_login() when an action is restricted to specific roles. */
function require_role(array $allowedRoles): array
{
    $admin = require_login();

    if (!in_array($admin['role'], $allowedRoles, true)) {
        http_response_code(403);
        exit('Forbidden: insufficient permissions.');
    }

    return $admin;
}
