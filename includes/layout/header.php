<?php

declare(strict_types=1);

/** @var string $pageTitle expected to be set by the including page */
$admin = current_admin();
$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?> · <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= filemtime(__DIR__ . '/../../assets/css/app.css') ?>">
</head>
<body>
<div class="app-shell">
    <?php require __DIR__ . '/sidebar.php'; ?>
    <div class="app-sidebar-backdrop"></div>
    <main class="app-main">
        <header class="app-topbar">
            <button type="button" class="app-topbar__menu-toggle" aria-label="Toggle navigation">
                <?= icon('menu') ?>
            </button>
            <label class="app-topbar__search">
                <?= icon('search') ?>
                <input type="search" placeholder="Search salons, owners, or anything...">
            </label>
            <div class="app-topbar__actions">
                <button type="button" class="app-topbar__icon-btn" aria-label="Notifications">
                    <?= icon('bell') ?>
                </button>
                <div class="app-topbar__admin">
                    <span class="app-topbar__avatar"><?= e(strtoupper(substr($admin['name'] ?? '?', 0, 1))) ?></span>
                    <div class="app-topbar__admin-info">
                        <span class="app-topbar__admin-name"><?= e($admin['name'] ?? '') ?></span>
                        <span class="app-topbar__admin-role"><?= e($admin['role'] ?? '') ?></span>
                    </div>
                </div>
            </div>
        </header>
        <div class="app-page-heading">
            <div class="app-page-heading__text">
                <h1><?= e($pageTitle ?? '') ?></h1>
                <?php if (!empty($pageSubtitle)): ?>
                    <p><?= e($pageSubtitle) ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($pageActions)): ?>
                <div class="app-page-heading__actions"><?= $pageActions ?></div>
            <?php endif; ?>
        </div>

        <div class="toast-viewport" id="toast-viewport">
            <?php if ($flash): ?>
                <div class="toast toast--<?= e($flash['type']) ?>" role="status" data-toast>
                    <span class="toast__icon"><?= icon($flash['type'] === 'success' ? 'check-circle' : 'alert-circle') ?></span>
                    <span class="toast__message"><?= e($flash['message']) ?></span>
                    <button type="button" class="toast__close" data-toast-close aria-label="Dismiss">
                        <?= icon('x') ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
