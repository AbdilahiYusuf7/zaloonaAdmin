<?php

declare(strict_types=1);

/** @var string $pageTitle expected to be set by the including page */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? PUBLIC_APP_NAME) ?> · <?= e(PUBLIC_APP_NAME) ?></title>
    <link rel="stylesheet" href="/assets/css/app.css?v=<?= filemtime(__DIR__ . '/../../assets/css/app.css') ?>">
</head>
<body class="public-body">
<header class="public-nav">
    <div class="public-nav__inner">
        <a href="/" class="public-nav__brand">
            <span class="public-nav__brand-mark">
                <img src="/assets/images/logo-mark.png" alt="<?= e(PUBLIC_APP_NAME) ?>">
            </span>
            <?= e(PUBLIC_APP_NAME) ?>
        </a>
        <nav class="public-nav__links">
            <a href="/#features">Features</a>
        </nav>
        <div class="public-nav__actions">
            <a href="/Auth/login.php" class="btn btn--secondary">Admin Login</a>
            <a href="/Public/register.php" class="btn btn--primary">Register Your Salon</a>
        </div>
    </div>
</header>
<main class="public-main">