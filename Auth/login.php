<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if (current_admin() !== null) {
    redirect('/pages/dashboard.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT id, name, email, password_hash, role FROM admin_users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id' => $admin['id'],
            'name' => $admin['name'],
            'email' => $admin['email'],
            'role' => $admin['role'],
        ];
        redirect('/pages/dashboard.php');
    }

    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in · <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= filemtime(__DIR__ . '/../assets/css/app.css') ?>">
</head>
<body class="auth-body">
    <form class="auth-card" method="post" action="/Auth/login.php">
        <h1><?= e(APP_NAME) ?></h1>
        <?php if ($error): ?>
            <p class="flash flash--error"><?= e($error) ?></p>
        <?php endif; ?>
        <?= csrf_field() ?>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Log in</button>
    </form>
</body>
</html>
