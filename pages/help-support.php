<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_login();

$pageTitle = 'Help & Support';
$pageSubtitle = 'Guidance for using the Zaloona admin panel.';

require __DIR__ . '/../includes/layout/header.php';
?>
<section class="card">
    <p>Need a hand? Reach the platform team at <a href="mailto:support@zaloona.test">support@zaloona.test</a>.</p>
</section>
<section class="card">
    <p>A searchable knowledge base and ticketing will be added here as the support workflow is built out.</p>
</section>
<?php require __DIR__ . '/../includes/layout/footer.php'; ?>
