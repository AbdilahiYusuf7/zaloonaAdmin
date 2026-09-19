<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (current_admin() !== null) {
    redirect('/pages/dashboard.php');
}

$pageTitle = 'Salon Management, Simplified';

require __DIR__ . '/includes/layout/public-header.php';
?>
<section class="public-hero">
    <div class="public-hero__bg" aria-hidden="true">
        <span class="public-hero__float public-hero__float--1"><?= icon('calendar') ?></span>
        <span class="public-hero__float public-hero__float--2"><?= icon('shop') ?></span>
        <span class="public-hero__float public-hero__float--3"><?= icon('card') ?></span>
        <span class="public-hero__float public-hero__float--4"><?= icon('shield') ?></span>
    </div>
    <div class="public-hero__inner">
        <span class="public-hero__eyebrow">For salon owners</span>
        <h1>Run your salon business, professionally.</h1>
        <p>
            <?= e(PUBLIC_APP_NAME) ?> gives your salon booking management, customer records, and subscription
            billing in one place &mdash; register today and get approved fast.
        </p>
        <div class="public-hero__actions">
            <a href="/Public/register.php" class="btn btn--primary btn--large"><?= icon('shop') ?> Register Your Salon</a>
            <a href="#features" class="btn btn--secondary btn--large"><?= icon('arrow-right') ?> See How It Works</a>
        </div>
    </div>
</section>

<section class="public-section" id="features">
    <div class="public-section__inner">
        <div class="public-section__heading">
            <h2>Everything your salon needs</h2>
            <p>A lightweight system built for real salon operations, not just spreadsheets.</p>
        </div>
        <div class="feature-grid">
            <div class="feature-card">
                <span class="feature-card__icon"><?= icon('calendar') ?></span>
                <h3>Booking Management</h3>
                <p>Keep track of customer bookings and appointments without the paperwork.</p>
            </div>
            <div class="feature-card">
                <span class="feature-card__icon"><?= icon('card') ?></span>
                <h3>Subscription Billing</h3>
                <p>Simple monthly plans with clear pricing &mdash; no hidden fees.</p>
            </div>
            <div class="feature-card">
                <span class="feature-card__icon"><?= icon('shield') ?></span>
                <h3>Verified &amp; Approved</h3>
                <p>Every salon is reviewed before going live, keeping the platform trustworthy.</p>
            </div>
            <div class="feature-card">
                <span class="feature-card__icon"><?= icon('dashboard') ?></span>
                <h3>Owner Dashboard</h3>
                <p>See your salon's activity and subscription status at a glance.</p>
            </div>
        </div>
    </div>
</section>

<section class="public-cta">
    <div class="public-cta__inner">
        <h2>Ready to bring your salon online?</h2>
        <p>Registration takes a few minutes. Your salon goes live as soon as it's approved.</p>
        <a href="/Public/register.php" class="btn btn--primary btn--large"><?= icon('shop') ?> Register Your Salon</a>
    </div>
</section>
<?php require __DIR__ . '/includes/layout/public-footer.php'; ?>
