<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

$admin = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/salon-approval-queue.php');
}

csrf_verify();

$salonId = filter_input(INPUT_POST, 'salon_id', FILTER_VALIDATE_INT);

if ($salonId === false || $salonId === null) {
    flash_set('error', 'Invalid salon.');
    redirect('/pages/salon-approval-queue.php');
}

try {
    (new SalonService())->reject($salonId, (int) $admin['id']);
    flash_set('success', 'Salon rejected.');
} catch (Throwable) {
    flash_set('error', 'Could not reject this salon. Please try again.');
}

redirect('/pages/salon-approval-queue.php');
