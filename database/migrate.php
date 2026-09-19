<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/db.php';

$sql = file_get_contents(__DIR__ . '/schema.sql');

if ($sql === false) {
    fwrite(STDERR, "Could not read schema.sql\n");
    exit(1);
}

foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
    db()->exec($statement);
}

echo "Migration complete.\n";
