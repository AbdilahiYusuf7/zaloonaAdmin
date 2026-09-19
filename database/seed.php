<?php

declare(strict_types=1);

/**
 * Usage: php database/seed.php "email@example.com" "Admin Name" "password" [--demo]
 * --demo also inserts a couple of sample salons and subscriptions.
 */

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/db.php';

[$script, $email, $name, $password] = array_pad($argv, 4, null);

if ($email === null || $name === null || $password === null) {
    fwrite(STDERR, "Usage: php database/seed.php \"email\" \"name\" \"password\" [--demo]\n");
    exit(1);
}

$db = db();

$stmt = $db->prepare(
    'INSERT INTO admin_users (name, email, password_hash, role)
     VALUES (:name, :email, :password_hash, \'owner\')
     ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash)'
);
$stmt->execute([
    'name' => $name,
    'email' => $email,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
]);

echo "Admin user ready: {$email}\n";

if (in_array('--demo', $argv, true)) {
    $db->exec(
        "INSERT INTO salons (name, owner_name, email, owner_phone, status)
         VALUES
            ('Bella Hair Studio', 'Layla Musse', 'bella@example.com', '+252611111111', 'pending'),
            ('Nour Beauty Bar', 'Amina Diriye', 'nour@example.com', '+252622222222', 'approved')"
    );

    $db->exec(
        "INSERT INTO subscriptions (salon_id, plan, status, amount, start_date, end_date)
         SELECT id, 'Pro Monthly', 'active', 15.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
         FROM salons WHERE email = 'nour@example.com'"
    );

    $db->exec(
        "INSERT INTO bookings (salon_id, customer_name, service, scheduled_at, status)
         SELECT id, 'Amina Yusuf', 'Facial', DATE_ADD(NOW(), INTERVAL 1 DAY), 'confirmed'
         FROM salons WHERE email = 'nour@example.com'"
    );
    $db->exec(
        "INSERT INTO bookings (salon_id, customer_name, service, scheduled_at, status)
         SELECT id, 'Fadumo Ali', 'Hair Cut', DATE_ADD(NOW(), INTERVAL 2 DAY), 'confirmed'
         FROM salons WHERE email = 'nour@example.com'"
    );
    $db->exec(
        "INSERT INTO bookings (salon_id, customer_name, service, scheduled_at, status)
         SELECT id, 'Sahra Mohamed', 'Manicure', DATE_ADD(NOW(), INTERVAL 3 DAY), 'pending'
         FROM salons WHERE email = 'bella@example.com'"
    );

    echo "Demo salons, subscriptions, and bookings inserted.\n";
}
