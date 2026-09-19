<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if (current_admin() !== null) {
    redirect('/pages/dashboard.php');
}

$pageTitle = 'Register Your Salon';

$registrationService = new RegistrationService();

$defaultPlan = (string) array_key_first(SUBSCRIPTION_PLANS);
$selectedPlan = (string) ($_GET['plan'] ?? $defaultPlan);

if (!isset(SUBSCRIPTION_PLANS[$selectedPlan])) {
    $selectedPlan = $defaultPlan;
}

$formValues = [];
$formErrors = [];
$paymentProvider = '';
$paymentReference = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $formValues = [
        'name' => trim((string) ($_POST['name'] ?? '')),
        'owner_name' => trim((string) ($_POST['owner_name'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'owner_phone' => trim((string) ($_POST['owner_phone'] ?? '')),
        'contact_phone' => trim((string) ($_POST['contact_phone'] ?? '')),
        'contact_phone_secondary' => trim((string) ($_POST['contact_phone_secondary'] ?? '')),
        'address' => trim((string) ($_POST['address'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
    ];
    $selectedPlan = (string) ($_POST['plan'] ?? $selectedPlan);
    $paymentProvider = (string) ($_POST['payment_provider'] ?? '');
    $paymentReference = trim((string) ($_POST['payment_reference'] ?? ''));

    $formErrors = $registrationService->validate($formValues, $_FILES, $selectedPlan, $paymentProvider, $paymentReference);

    if ($formErrors === []) {
        try {
            $registrationService->register($formValues, $_FILES, $selectedPlan, $paymentProvider, $paymentReference);
            redirect('/Public/register-success.php?salon=' . urlencode($formValues['name']));
        } catch (Throwable) {
            $formErrors['general'] = 'Something went wrong while submitting your registration. Please try again.';
        }
    }
}

$fieldIdSuffix = '';

require __DIR__ . '/../includes/layout/public-header.php';
?>
<section class="public-section">
    <div class="public-section__inner public-section__inner--narrow">
        <div class="public-section__heading">
            <h2>Register Your Salon</h2>
            <p>Fill in your salon's details, choose a plan, and confirm your payment below.</p>
        </div>

        <div class="public-form-card">
            <?php if (!empty($formErrors['general'])): ?>
                <p class="flash flash--error"><?= e($formErrors['general']) ?></p>
            <?php endif; ?>
            <form method="post" action="/Public/register.php" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <h3 class="public-form-card__step">1. Salon &amp; Owner Details</h3>
                <?php require __DIR__ . '/../includes/layout/salon-form-fields.php'; ?>

                <h3 class="public-form-card__step">2. Choose a Plan</h3>
                <div class="form-field">
                    <div class="plan-options">
                        <?php foreach (SUBSCRIPTION_PLANS as $planKey => $plan): ?>
                            <label class="plan-option">
                                <input type="radio" name="plan" value="<?= e($planKey) ?>" <?= $selectedPlan === $planKey ? 'checked' : '' ?>>
                                <span class="plan-option__name"><?= e($plan['name']) ?></span>
                                <span class="plan-option__price">$<?= number_format($plan['price'], 0) ?><small>/mo</small></span>
                                <span class="plan-option__price-alt">SLSH <?= number_format($plan['price_slsh']) ?><small>/mo</small></span>
                                <span class="plan-option__tagline"><?= e($plan['tagline']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($formErrors['plan'])): ?><span class="form-error"><?= e($formErrors['plan']) ?></span><?php endif; ?>
                </div>

                <h3 class="public-form-card__step">3. Confirm Your Payment</h3>
                <div class="form-field">
                    <label>Pay via</label>
                    <div class="payment-options">
                        <?php foreach (PAYMENT_PROVIDERS as $providerKey => $provider): ?>
                            <label class="payment-option payment-option--<?= e($providerKey) ?>">
                                <input type="radio" name="payment_provider" value="<?= e($providerKey) ?>" <?= $paymentProvider === $providerKey ? 'checked' : '' ?>>
                                <span class="payment-option__badge">
                                    <img src="<?= e($provider['icon']) ?>" alt="<?= e($provider['label']) ?>">
                                </span>
                                <span class="payment-option__text">
                                    <span class="payment-option__label"><?= e($provider['label']) ?></span>
                                    <span class="payment-option__number"><?= e($provider['number']) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!empty($formErrors['payment_provider'])): ?><span class="form-error"><?= e($formErrors['payment_provider']) ?></span><?php endif; ?>
                    <span class="form-hint">Send your subscription payment to the number you select above.</span>
                </div>

                <div class="form-field">
                    <label for="payment_reference">Transaction (TIX) Number</label>
                    <input type="text" id="payment_reference" name="payment_reference" placeholder="e.g. TIX123456" value="<?= e($paymentReference) ?>" required>
                    <?php if (!empty($formErrors['payment_reference'])): ?><span class="form-error"><?= e($formErrors['payment_reference']) ?></span><?php endif; ?>
                    <span class="form-hint">Enter the transaction number you received after sending the payment. We'll verify it before approving your salon.</span>
                </div>

                <button type="submit" class="btn btn--primary btn--large public-form-card__submit"><?= icon('check-circle') ?> Complete Registration</button>
            </form>
        </div>
    </div>
</section>
<?php require __DIR__ . '/../includes/layout/public-footer.php'; ?>
