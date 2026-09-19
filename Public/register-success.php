<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$pageTitle = 'Registration Received';
$salonName = trim((string) ($_GET['salon'] ?? ''));

require __DIR__ . '/../includes/layout/public-header.php';
?>
<section class="public-section">
    <div class="public-section__inner public-section__inner--narrow">
        <div class="public-form-card public-success">
            <span class="public-success__icon"><?= icon('check-circle') ?></span>
            <h2>
                Thanks<?= $salonName !== '' ? ', ' . e($salonName) : '' ?>! Your registration is under review.
            </h2>
            <p>
                We've received your salon details, plan choice, and payment reference. Here's what happens next.
            </p>

            <div class="success-steps">
                <div class="success-step">
                    <span class="success-step__icon"><?= icon('shield') ?></span>
                    <h4>Payment Verification</h4>
                    <p>Our team checks your transaction (TIX) number against the payment you sent.</p>
                </div>
                <div class="success-step">
                    <span class="success-step__icon"><?= icon('check-circle') ?></span>
                    <h4>Salon Approved</h4>
                    <p>Once verified, your salon is approved and activated on <?= e(PUBLIC_APP_NAME) ?>.</p>
                </div>
                <div class="success-step">
                    <span class="success-step__icon"><?= icon('shop') ?></span>
                    <h4>Start Managing</h4>
                    <p>Log in to manage bookings, customers, and your subscription.</p>
                </div>
            </div>

            <div class="public-success__app">
                <h3><?= icon('shop') ?> Get the <?= e(PUBLIC_APP_NAME) ?> mobile app</h3>
                <p>Manage bookings and your salon on the go &mdash; our mobile app is launching soon.</p>
                <div class="store-badges">
                    <span class="store-badge" aria-disabled="true">
                        <span class="store-badge__icon">
                            <svg viewBox="0 0 384 512" fill="currentColor" aria-hidden="true">
                                <path d="M318.7 268.7c-.2-36.7 16.4-64.4 50-84.8-18.8-26.9-47.2-41.7-84.7-44.6-35.5-2.8-74.3 20.7-88.5 20.7-15 0-49.4-19.7-76.4-19.7C63.3 141.2 4 184.8 4 273.5c0 26.2 4.8 53.3 14.4 81.2 12.8 37.4 59 129.3 107 127.6 25.2-.9 43-17.9 75.8-17.9 31.8 0 48.3 17.9 76.4 17.9 48.4-.7 90.4-84.1 102.6-121.6-65.2-30.7-61.5-90-61.5-92zM255.7 88.5c26.4-31.3 24-59.9 23.2-70.2-23.1 1.3-49.8 15.8-64.8 33.5-16.5 19-26.2 42.5-24.1 68.7 24.5 1.9 46.9-10.4 65.7-32z"/>
                            </svg>
                        </span>
                        <span class="store-badge__text">
                            <small>Coming soon on the</small>
                            <strong>App Store</strong>
                        </span>
                    </span>
                    <a href="<?= e(ANDROID_APP_DOWNLOAD_URL) ?>" class="store-badge" target="_blank" rel="noopener">
                        <span class="store-badge__icon">
                            <svg viewBox="0 0 512 512" aria-hidden="true">
                                <path fill="#00d9a5" d="M99.6 42.3C93.8 48.5 90.3 58 90.3 70.3v371.4c0 12.3 3.5 21.8 9.3 28l177.3-199.8z"/>
                                <path fill="#ffc700" d="M356.8 220.5l-69.9-40.1-56.9 64.1 56.9 64.1 71.2-40.9c14.3-8.6 22.9-19 22.9-23.2 0-4.2-9-14.6-24.2-23.2z"/>
                                <path fill="#ff3859" d="M286.9 180.4l-187.3-138C104.1 39 111.7 37 120.2 37c4.9 0 10.2 1.1 15.7 4.2l190.9 109.1z"/>
                                <path fill="#00b6f5" d="M286.9 307.6l-187.3 138c4.5 3.3 12.1 5.3 20.6 5.3 4.9 0 10.2-1.1 15.7-4.2l190.9-109.1z"/>
                            </svg>
                        </span>
                        <span class="store-badge__text">
                            <small>Download for</small>
                            <strong>Android</strong>
                        </span>
                    </a>
                </div>
            </div>

            <a href="/" class="btn btn--secondary">Back to Home</a>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../includes/layout/public-footer.php'; ?>
