<?php

declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';

$admin = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/salons.php');
}

csrf_verify();

$input = [
    'name' => trim((string) ($_POST['name'] ?? '')),
    'owner_name' => trim((string) ($_POST['owner_name'] ?? '')),
    'email' => trim((string) ($_POST['email'] ?? '')),
    'owner_phone' => trim((string) ($_POST['owner_phone'] ?? '')),
    'contact_phone' => trim((string) ($_POST['contact_phone'] ?? '')),
    'contact_phone_secondary' => trim((string) ($_POST['contact_phone_secondary'] ?? '')),
    'address' => trim((string) ($_POST['address'] ?? '')),
    'description' => trim((string) ($_POST['description'] ?? '')),
];

$salonService = new SalonService();
$errors = $salonService->validate($input, $_FILES);

if ($errors !== []) {
    old_input_set($input);
    form_errors_set($errors);
    flash_set('error', 'Please fix the highlighted fields and try again.');
    redirect('/pages/salons.php');
}

try {
    $salonService->create($input, $_FILES, (int) $admin['id']);
    flash_set('success', 'Salon added and is awaiting approval.');
} catch (Throwable) {
    old_input_set($input);
    flash_set('error', 'Could not save this salon. Please try again.');
}

redirect('/pages/salons.php');
