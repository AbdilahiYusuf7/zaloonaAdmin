<?php

declare(strict_types=1);

$currentPage = basename($_SERVER['SCRIPT_NAME']);

$navSections = [
    'Overview' => [
        'dashboard.php' => ['label' => 'Dashboard', 'icon' => 'dashboard'],
    ],
    'Management' => [
        'salon-approval-queue.php' => ['label' => 'Approvals', 'icon' => 'check-list'],
        'salons.php' => ['label' => 'Salon List', 'icon' => 'shop'],
        'subscriptions.php' => ['label' => 'Subscriptions', 'icon' => 'card'],
    ],
    'System' => [
        'settings.php' => ['label' => 'Settings', 'icon' => 'settings'],
        'system-logs.php' => ['label' => 'System Logs', 'icon' => 'logs'],
        'help-support.php' => ['label' => 'Help & Support', 'icon' => 'help'],
    ],
];
?>
<aside class="app-sidebar">
    <div class="app-sidebar__brand">
        <span class="app-sidebar__logo-badge">
            <img src="/assets/images/logo-mark.png" alt="Zaloona" class="app-sidebar__logo">
        </span>
    </div>
    <nav class="app-sidebar__nav">
        <?php foreach ($navSections as $sectionLabel => $items): ?>
            <div class="app-sidebar__section"><?= e($sectionLabel) ?></div>
            <?php foreach ($items as $href => $item): ?>
                <a href="/pages/<?= e($href) ?>"
                   class="app-sidebar__link<?= $currentPage === $href ? ' is-active' : '' ?>">
                    <?= icon($item['icon']) ?>
                    <span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </nav>
    <a href="/Auth/logout.php" class="app-sidebar__logout">Log out</a>
</aside>
