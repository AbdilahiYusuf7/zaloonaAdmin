<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$admin = require_login();

$pageTitle = 'Settings';
$pageSubtitle = 'Your admin account details.';

require __DIR__ . '/../includes/layout/header.php';
?>
<section class="card">
    <table class="table">
        <tbody>
        <tr><th>Name</th><td><?= e($admin['name']) ?></td></tr>
        <tr><th>Email</th><td><?= e($admin['email']) ?></td></tr>
        <tr><th>Role</th><td><span class="badge badge--approved"><?= e(ucfirst($admin['role'])) ?></span></td></tr>
        </tbody>
    </table>
</section>
<section class="card">
    <p>Platform-wide settings (branding, notification preferences, roles &amp; permissions) will appear here as they're built.</p>
</section>
<?php require __DIR__ . '/../includes/layout/footer.php'; ?>
