<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_login();

$pageTitle = 'System Logs';
$pageSubtitle = 'Audit trail of actions taken by admins.';

$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * PAGE_SIZE;
$logs = (new AuditLogRepository())->findAll(PAGE_SIZE, $offset);

require __DIR__ . '/../includes/layout/header.php';
?>
<section class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>Admin</th>
                <th>Action</th>
                <th>Entity</th>
                <th>Before</th>
                <th>After</th>
                <th>IP Address</th>
                <th>When</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="7">No audit log entries yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e($log['admin_name']) ?></td>
                    <td><?= e($log['action']) ?></td>
                    <td><?= e($log['entity']) ?> #<?= (int) $log['entity_id'] ?></td>
                    <td><?= e($log['before_value'] ?? '—') ?></td>
                    <td><?= e($log['after_value'] ?? '—') ?></td>
                    <td><?= e($log['ip_address'] ?? '—') ?></td>
                    <td><?= e($log['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="btn btn--secondary">Previous</a>
        <?php endif; ?>
        <?php if (count($logs) === PAGE_SIZE): ?>
            <a href="?page=<?= $page + 1 ?>" class="btn btn--secondary">Next</a>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/../includes/layout/footer.php'; ?>
