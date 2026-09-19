<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$admin = require_login();

$pageTitle = 'Subscription Status';
$pageSubtitle = 'Track plan status and revenue across all salons.';

$canManageSubscriptions = ($admin['role'] ?? '') === 'owner';

$pageSize = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim((string) ($_GET['search'] ?? ''));
$statusFilter = (string) ($_GET['status'] ?? '');

if (!in_array($statusFilter, SUBSCRIPTION_STATUSES, true)) {
    $statusFilter = '';
}

$subscriptionService = new SubscriptionService();
$totalSubscriptions = $subscriptionService->countSearch($search, $statusFilter);
$totalPages = max(1, (int) ceil($totalSubscriptions / $pageSize));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageSize;
$subscriptions = $subscriptionService->search($search, $statusFilter, $pageSize, $offset);

$queryParams = array_filter(['search' => $search, 'status' => $statusFilter]);

require __DIR__ . '/../includes/layout/header.php';
?>
<section class="card filters-bar">
    <div class="card__header">
        <h2><?= icon('filter') ?> Filters</h2>
    </div>
    <form class="filters-bar__form" method="get">
        <label class="filters-bar__search">
            <?= icon('search') ?>
            <input type="search" name="search" placeholder="Search by salon or plan" value="<?= e($search) ?>">
        </label>
        <label class="filters-bar__field">
            <span class="filters-bar__field-label">Status</span>
            <select name="status" class="filters-bar__select">
                <option value="">All statuses</option>
                <?php foreach (SUBSCRIPTION_STATUSES as $statusOption): ?>
                    <option value="<?= e($statusOption) ?>" <?= $statusFilter === $statusOption ? 'selected' : '' ?>>
                        <?= e(ucfirst(str_replace('_', ' ', $statusOption))) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="filters-bar__actions">
            <button type="submit" class="btn btn--primary"><?= icon('filter') ?> Filter</button>
            <?php if ($search !== '' || $statusFilter !== ''): ?>
                <a href="/pages/subscriptions.php" class="btn btn--secondary">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>Salon</th>
                <th>Plan</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Renews / Ends</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($subscriptions)): ?>
                <tr><td colspan="6">No subscriptions found.</td></tr>
            <?php endif; ?>
            <?php foreach ($subscriptions as $subscription): ?>
                <tr>
                    <td><?= e($subscription['salon_name']) ?></td>
                    <td><?= e($subscription['plan']) ?></td>
                    <td><span class="badge badge--<?= e($subscription['status']) ?>"><?= e(str_replace('_', ' ', $subscription['status'])) ?></span></td>
                    <td><?= number_format((float) $subscription['amount'], 2) ?></td>
                    <td><?= e($subscription['end_date']) ?></td>
                    <td class="table__actions">
                        <button type="button" class="table__icon-btn" data-modal-target="#view-subscription-<?= (int) $subscription['id'] ?>" title="View" aria-label="View <?= e($subscription['salon_name']) ?> subscription">
                            <?= icon('eye') ?>
                        </button>
                        <?php if ($canManageSubscriptions): ?>
                            <button type="button" class="table__icon-btn" data-modal-target="#edit-subscription-<?= (int) $subscription['id'] ?>" title="Edit status" aria-label="Edit <?= e($subscription['salon_name']) ?> subscription status">
                                <?= icon('pencil') ?>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination">
        <span class="pagination__info">
            <?php if ($totalSubscriptions === 0): ?>
                No results
            <?php else: ?>
                Showing <strong><?= $offset + 1 ?></strong>&ndash;<strong><?= $offset + count($subscriptions) ?></strong> of <strong><?= $totalSubscriptions ?></strong>
            <?php endif; ?>
        </span>
        <div class="pagination__controls">
            <a
                href="?<?= e(http_build_query($queryParams + ['page' => max(1, $page - 1)])) ?>"
                class="btn btn--secondary<?= $page <= 1 ? ' is-disabled' : '' ?>"
                <?= $page <= 1 ? 'aria-disabled="true" tabindex="-1"' : '' ?>
            ><?= icon('chevron-left') ?> Previous</a>
            <span class="pagination__pages">Page <?= $page ?> of <?= $totalPages ?></span>
            <a
                href="?<?= e(http_build_query($queryParams + ['page' => min($totalPages, $page + 1)])) ?>"
                class="btn btn--secondary<?= $page >= $totalPages ? ' is-disabled' : '' ?>"
                <?= $page >= $totalPages ? 'aria-disabled="true" tabindex="-1"' : '' ?>
            >Next <?= icon('chevron-right') ?></a>
        </div>
    </div>
</section>

<?php foreach ($subscriptions as $subscription): ?>
    <div class="modal-overlay" id="view-subscription-<?= (int) $subscription['id'] ?>">
        <div class="modal modal--small" role="dialog" aria-modal="true" aria-labelledby="view-subscription-title-<?= (int) $subscription['id'] ?>">
            <div class="modal__header">
                <div class="modal__header-info">
                    <span class="modal__header-icon"><?= icon('card') ?></span>
                    <div>
                        <h2 id="view-subscription-title-<?= (int) $subscription['id'] ?>"><?= e($subscription['salon_name']) ?></h2>
                        <p class="modal__header-subtitle">Subscription details</p>
                    </div>
                </div>
                <button type="button" class="modal__close" data-modal-close aria-label="Close">
                    <?= icon('x') ?>
                </button>
            </div>
            <div class="modal__body">
                <dl class="detail-list">
                    <div><dt>Salon</dt><dd><?= e($subscription['salon_name']) ?></dd></div>
                    <div><dt>Plan</dt><dd><?= e($subscription['plan']) ?></dd></div>
                    <div><dt>Status</dt><dd><span class="badge badge--<?= e($subscription['status']) ?>"><?= e(str_replace('_', ' ', $subscription['status'])) ?></span></dd></div>
                    <div><dt>Amount</dt><dd><?= number_format((float) $subscription['amount'], 2) ?></dd></div>
                    <div><dt>Start Date</dt><dd><?= e($subscription['start_date']) ?></dd></div>
                    <div><dt>End Date</dt><dd><?= e($subscription['end_date']) ?></dd></div>
                </dl>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--secondary" data-modal-close>Close</button>
            </div>
        </div>
    </div>

    <?php if ($canManageSubscriptions): ?>
        <div class="modal-overlay" id="edit-subscription-<?= (int) $subscription['id'] ?>">
            <div class="modal modal--small" role="dialog" aria-modal="true" aria-labelledby="edit-subscription-edit-title-<?= (int) $subscription['id'] ?>">
                <div class="modal__header">
                    <div class="modal__header-info">
                        <span class="modal__header-icon"><?= icon('pencil') ?></span>
                        <div>
                            <h2 id="edit-subscription-edit-title-<?= (int) $subscription['id'] ?>">Update Status</h2>
                            <p class="modal__header-subtitle"><?= e($subscription['salon_name']) ?> &middot; <?= e($subscription['plan']) ?></p>
                        </div>
                    </div>
                    <button type="button" class="modal__close" data-modal-close aria-label="Close">
                        <?= icon('x') ?>
                    </button>
                </div>
                <form class="modal__form" method="post" action="/endpoints/subscriptions/update-status.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="subscription_id" value="<?= (int) $subscription['id'] ?>">
                    <div class="modal__body">
                        <div class="form-field">
                            <label for="status_<?= (int) $subscription['id'] ?>">Status</label>
                            <select id="status_<?= (int) $subscription['id'] ?>" name="status">
                                <?php foreach (SUBSCRIPTION_STATUSES as $statusOption): ?>
                                    <option value="<?= e($statusOption) ?>" <?= $subscription['status'] === $statusOption ? 'selected' : '' ?>>
                                        <?= e(ucfirst(str_replace('_', ' ', $statusOption))) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal__footer">
                        <button type="button" class="btn btn--secondary" data-modal-close>Cancel</button>
                        <button type="submit" class="btn btn--primary"><?= icon('check-circle') ?> Save</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/layout/footer.php'; ?>
