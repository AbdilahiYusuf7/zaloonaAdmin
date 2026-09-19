<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_login();

$pageTitle = 'Salon Approval Queue';
$pageSubtitle = 'Review new salon applications and approve or reject them.';

$pageSize = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim((string) ($_GET['search'] ?? ''));

$salonService = new SalonService();
$totalPending = $salonService->countSearch($search, 'pending');
$totalPages = max(1, (int) ceil($totalPending / $pageSize));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageSize;
$pendingSalons = $salonService->search($search, 'pending', $pageSize, $offset);

$pendingSalonIds = array_map(static fn (array $salon): int => (int) $salon['id'], $pendingSalons);
$subscriptionsBySalonId = (new SubscriptionRepository())->findLatestBySalonIds($pendingSalonIds);

$queryParams = array_filter(['search' => $search]);

require __DIR__ . '/../includes/layout/header.php';
?>
<section class="card filters-bar">
    <div class="card__header">
        <h2><?= icon('filter') ?> Filters</h2>
    </div>
    <form class="filters-bar__form" method="get">
        <label class="filters-bar__search">
            <?= icon('search') ?>
            <input type="search" name="search" placeholder="Search by salon, owner, or email" value="<?= e($search) ?>">
        </label>
        <div class="filters-bar__actions">
            <button type="submit" class="btn btn--primary"><?= icon('filter') ?> Filter</button>
            <?php if ($search !== ''): ?>
                <a href="/pages/salon-approval-queue.php" class="btn btn--secondary">Reset</a>
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
                <th>Owner</th>
                <th>Email</th>
                <th>Payment</th>
                <th>Submitted</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($pendingSalons)): ?>
                <tr><td colspan="6">No salons waiting for approval.</td></tr>
            <?php endif; ?>
            <?php foreach ($pendingSalons as $salon): ?>
                <?php $subscription = $subscriptionsBySalonId[(int) $salon['id']] ?? null; ?>
                <tr>
                    <td><?= e($salon['name']) ?></td>
                    <td><?= e($salon['owner_name']) ?></td>
                    <td><?= e($salon['email']) ?></td>
                    <td>
                        <?php if ($subscription !== null && !empty($subscription['payment_reference'])): ?>
                            <?= e(PAYMENT_PROVIDERS[$subscription['payment_provider']]['label'] ?? $subscription['payment_provider']) ?>
                            &middot; <code><?= e($subscription['payment_reference']) ?></code>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td><?= e($salon['created_at']) ?></td>
                    <td class="table__actions">
                        <button type="button" class="table__icon-btn" data-modal-target="#view-salon-<?= (int) $salon['id'] ?>" title="View" aria-label="View <?= e($salon['name']) ?>">
                            <?= icon('eye') ?>
                        </button>
                        <form method="post" action="/endpoints/salons/approve.php">
                            <?= csrf_field() ?>
                            <input type="hidden" name="salon_id" value="<?= (int) $salon['id'] ?>">
                            <button type="submit" class="btn btn--approve"><?= icon('check-circle') ?> Approve</button>
                        </form>
                        <button type="button" class="btn btn--reject" data-confirm-trigger="#reject-salon-modal" data-salon-id="<?= (int) $salon['id'] ?>" data-salon-name="<?= e($salon['name']) ?>">
                            <?= icon('alert-circle') ?> Reject
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination">
        <span class="pagination__info">
            <?php if ($totalPending === 0): ?>
                No results
            <?php else: ?>
                Showing <strong><?= $offset + 1 ?></strong>&ndash;<strong><?= $offset + count($pendingSalons) ?></strong> of <strong><?= $totalPending ?></strong>
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

<div class="modal-overlay" id="reject-salon-modal">
    <div class="modal modal--small" role="dialog" aria-modal="true" aria-labelledby="reject-salon-title">
        <div class="modal__header">
            <div class="modal__header-info">
                <span class="modal__header-icon modal__header-icon--danger"><?= icon('alert-circle') ?></span>
                <div>
                    <h2 id="reject-salon-title">Reject Salon</h2>
                    <p class="modal__header-subtitle">The owner will be notified of this decision.</p>
                </div>
            </div>
            <button type="button" class="modal__close" data-modal-close aria-label="Close">
                <?= icon('x') ?>
            </button>
        </div>
        <form class="modal__form" method="post" action="/endpoints/salons/reject.php">
            <?= csrf_field() ?>
            <input type="hidden" name="salon_id" data-confirm-id-field value="">
            <div class="modal__body">
                <p class="modal__confirm-text">
                    Are you sure you want to reject <strong data-confirm-name-field>this salon</strong>?
                    Its application will be marked as rejected until it's edited and resubmitted.
                </p>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn--reject"><?= icon('alert-circle') ?> Reject Salon</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($pendingSalons as $salon): ?>
    <div class="modal-overlay" id="view-salon-<?= (int) $salon['id'] ?>">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="view-salon-title-<?= (int) $salon['id'] ?>">
            <div class="modal__header">
                <div class="modal__header-info">
                    <span class="modal__header-icon"><?= icon('eye') ?></span>
                    <div>
                        <h2 id="view-salon-title-<?= (int) $salon['id'] ?>"><?= e($salon['name']) ?></h2>
                        <p class="modal__header-subtitle">Salon details</p>
                    </div>
                </div>
                <button type="button" class="modal__close" data-modal-close aria-label="Close">
                    <?= icon('x') ?>
                </button>
            </div>
            <div class="modal__body">
                <?php if (!empty($salon['logo_path'])): ?>
                    <img src="<?= e(salon_logo_url($salon['logo_path'])) ?>" alt="<?= e($salon['name']) ?> logo" class="salon-view__logo">
                <?php endif; ?>
                <dl class="detail-list">
                    <div><dt>Owner</dt><dd><?= e($salon['owner_name']) ?></dd></div>
                    <div><dt>Owner Number</dt><dd><?= e($salon['owner_phone']) ?></dd></div>
                    <div><dt>Owner Email</dt><dd><?= e($salon['email']) ?></dd></div>
                    <div><dt>Saloon Contact</dt><dd><?= e($salon['contact_phone'] ?: '—') ?></dd></div>
                    <div><dt>Saloon Contact 2</dt><dd><?= e($salon['contact_phone_secondary'] ?: '—') ?></dd></div>
                    <div><dt>Address</dt><dd><?= e($salon['address'] ?: '—') ?></dd></div>
                    <div><dt>Status</dt><dd><span class="badge badge--<?= e($salon['status']) ?>"><?= e($salon['status']) ?></span></dd></div>
                    <div><dt>Submitted</dt><dd><?= e($salon['created_at']) ?></dd></div>
                </dl>
                <?php if (!empty($salon['description'])): ?>
                    <div class="detail-list__description">
                        <dt>Description</dt>
                        <p><?= nl2br(e($salon['description'])) ?></p>
                    </div>
                <?php endif; ?>
                <?php $subscription = $subscriptionsBySalonId[(int) $salon['id']] ?? null; ?>
                <?php if ($subscription !== null): ?>
                    <div class="payment-verify">
                        <h3><?= icon('card') ?> Payment to verify</h3>
                        <dl class="detail-list">
                            <div><dt>Plan</dt><dd><?= e($subscription['plan']) ?> ($<?= number_format((float) $subscription['amount'], 2) ?>/mo)</dd></div>
                            <div><dt>Paid via</dt><dd><?= e(PAYMENT_PROVIDERS[$subscription['payment_provider']]['label'] ?? $subscription['payment_provider']) ?></dd></div>
                            <div><dt>Transaction (TIX) Number</dt><dd><code><?= e($subscription['payment_reference'] ?? '—') ?></code></dd></div>
                        </dl>
                        <p class="payment-verify__hint">Check this transaction number in your mobile money dashboard before approving.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--secondary" data-modal-close>Close</button>
                <form method="post" action="/endpoints/salons/approve.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="salon_id" value="<?= (int) $salon['id'] ?>">
                    <button type="submit" class="btn btn--approve"><?= icon('check-circle') ?> Approve</button>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/layout/footer.php'; ?>
