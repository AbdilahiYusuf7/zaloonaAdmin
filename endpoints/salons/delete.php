<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

$admin = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/salons.php');
}

csrf_verify();

$salonId = filter_input(INPUT_POST, 'salon_id', FILTER_VALIDATE_INT);

if ($salonId === false || $salonId === null) {
    flash_set('error', 'Invalid salon.');
    redirect('/pages/salons.php');
}

try {
    (new SalonService())->delete($salonId, (int) $admin['id']);
    flash_set('success', 'Salon deleted.');
} catch (Throwable) {
    flash_set('error', 'Could not delete this salon. Please try again.');
}

redirect('/pages/salons.php');
