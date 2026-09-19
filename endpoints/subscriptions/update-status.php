<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

$admin = require_role(['owner']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/subscriptions.php');
}

csrf_verify();

$subscriptionId = filter_input(INPUT_POST, 'subscription_id', FILTER_VALIDATE_INT);
$status = trim($_POST['status'] ?? '');

if ($subscriptionId === false || $subscriptionId === null || !in_array($status, SUBSCRIPTION_STATUSES, true)) {
    flash_set('error', 'Invalid subscription update.');
    redirect('/pages/subscriptions.php');
}

try {
    (new SubscriptionService())->updateStatus($subscriptionId, $status, (int) $admin['id']);
    flash_set('success', 'Subscription updated.');
} catch (Throwable) {
    flash_set('error', 'Could not update this subscription. Please try again.');
}

redirect('/pages/subscriptions.php');
