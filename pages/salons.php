<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_login();

$pageTitle = 'Salons';
$pageActions = '<button type="button" class="btn btn--primary" data-modal-target="#add-salon-modal">'
    . icon('plus') . ' Add Salon</button>';

$pageSize = 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim((string) ($_GET['search'] ?? ''));
$statusFilter = (string) ($_GET['status'] ?? '');

if (!in_array($statusFilter, SALON_STATUSES, true)) {
    $statusFilter = '';
}

$offset = ($page - 1) * $pageSize;

$salonService = new SalonService();
$totalSalons = $salonService->countSearch($search, $statusFilter);
$totalPages = max(1, (int) ceil($totalSalons / $pageSize));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageSize;
$salons = $salonService->search($search, $statusFilter, $pageSize, $offset);

$queryParams = array_filter(['search' => $search, 'status' => $statusFilter]);

$oldInput = old_input_get();
$formErrorsAll = form_errors_get();
$failedSalonId = isset($oldInput['id']) ? (int) $oldInput['id'] : null;

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
        <label class="filters-bar__field">
            <span class="filters-bar__field-label">Status</span>
            <select name="status" class="filters-bar__select">
                <option value="">All statuses</option>
                <?php foreach (SALON_STATUSES as $statusOption): ?>
                    <option value="<?= e($statusOption) ?>" <?= $statusFilter === $statusOption ? 'selected' : '' ?>>
                        <?= e(ucfirst($statusOption)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="filters-bar__actions">
            <button type="submit" class="btn btn--primary"><?= icon('filter') ?> Filter</button>
            <?php if ($search !== '' || $statusFilter !== ''): ?>
                <a href="/pages/salons.php" class="btn btn--secondary">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>Logo</th>
                <th>Owner Name</th>
                <th>Salon Name</th>
                <th>Owner Number</th>
                <th>Email</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($salons)): ?>
                <tr><td colspan="7">No salons found.</td></tr>
            <?php endif; ?>
            <?php foreach ($salons as $salon): ?>
                <tr>
                    <td>
                        <?php if (!empty($salon['logo_path'])): ?>
                            <img src="/uploads/<?= e($salon['logo_path']) ?>" alt="<?= e($salon['name']) ?> logo" class="table-avatar">
                        <?php else: ?>
                            <span class="table-avatar table-avatar--placeholder"><?= icon('image') ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= e($salon['owner_name']) ?></td>
                    <td><?= e($salon['name']) ?></td>
                    <td><?= e($salon['owner_phone']) ?></td>
                    <td><?= e($salon['email']) ?></td>
                    <td><?= e($salon['address'] ?: '—') ?></td>
                    <td class="table__actions">
                        <button type="button" class="table__icon-btn" data-modal-target="#view-salon-<?= (int) $salon['id'] ?>" title="View" aria-label="View <?= e($salon['name']) ?>">
                            <?= icon('eye') ?>
                        </button>
                        <button type="button" class="table__icon-btn" data-modal-target="#edit-salon-<?= (int) $salon['id'] ?>" title="Edit" aria-label="Edit <?= e($salon['name']) ?>">
                            <?= icon('pencil') ?>
                        </button>
                        <button type="button" class="table__icon-btn table__icon-btn--danger" data-confirm-trigger="#delete-salon-modal" data-salon-id="<?= (int) $salon['id'] ?>" data-salon-name="<?= e($salon['name']) ?>" title="Delete" aria-label="Delete <?= e($salon['name']) ?>">
                            <?= icon('trash') ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination">
        <span class="pagination__info">
            <?php if ($totalSalons === 0): ?>
                No results
            <?php else: ?>
                Showing <strong><?= $offset + 1 ?></strong>&ndash;<strong><?= $offset + count($salons) ?></strong> of <strong><?= $totalSalons ?></strong>
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

<div class="modal-overlay" id="delete-salon-modal">
    <div class="modal modal--small" role="dialog" aria-modal="true" aria-labelledby="delete-salon-title">
        <div class="modal__header">
            <div class="modal__header-info">
                <span class="modal__header-icon modal__header-icon--danger"><?= icon('trash') ?></span>
                <div>
                    <h2 id="delete-salon-title">Delete Salon</h2>
                    <p class="modal__header-subtitle">This action cannot be undone.</p>
                </div>
            </div>
            <button type="button" class="modal__close" data-modal-close aria-label="Close">
                <?= icon('x') ?>
            </button>
        </div>
        <form class="modal__form" method="post" action="/endpoints/salons/delete.php">
            <?= csrf_field() ?>
            <input type="hidden" name="salon_id" data-confirm-id-field value="">
            <div class="modal__body">
                <p class="modal__confirm-text">
                    Are you sure you want to delete <strong data-confirm-name-field>this salon</strong>?
                    Its subscriptions and bookings will be permanently removed as well.
                </p>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn--reject"><?= icon('trash') ?> Delete Salon</button>
            </div>
        </form>
    </div>
</div>

<?php
$formValues = $failedSalonId === null ? $oldInput : [];
$formErrors = $failedSalonId === null ? $formErrorsAll : [];
$fieldIdSuffix = '';
?>
<div class="modal-overlay<?= ($formErrors !== []) ? ' is-open' : '' ?>" id="add-salon-modal">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="add-salon-title">
        <div class="modal__header">
            <div class="modal__header-info">
                <span class="modal__header-icon"><?= icon('shop') ?></span>
                <div>
                    <h2 id="add-salon-title">Add New Salon</h2>
                    <p class="modal__header-subtitle">Enter the owner and salon details below.</p>
                </div>
            </div>
            <button type="button" class="modal__close" data-modal-close aria-label="Close">
                <?= icon('x') ?>
            </button>
        </div>
        <form class="modal__form" method="post" action="/endpoints/salons/create.php" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal__body">
                <?php require __DIR__ . '/../includes/layout/salon-form-fields.php'; ?>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn btn--primary"><?= icon('check-circle') ?> Save Salon</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($salons as $salon): ?>
    <?php $isEditingThis = $failedSalonId === (int) $salon['id']; ?>

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
                    <img src="/uploads/<?= e($salon['logo_path']) ?>" alt="<?= e($salon['name']) ?> logo" class="salon-view__logo">
                <?php endif; ?>
                <dl class="detail-list">
                    <div><dt>Owner</dt><dd><?= e($salon['owner_name']) ?></dd></div>
                    <div><dt>Owner Number</dt><dd><?= e($salon['owner_phone']) ?></dd></div>
                    <div><dt>Owner Email</dt><dd><?= e($salon['email']) ?></dd></div>
                    <div><dt>Saloon Contact</dt><dd><?= e($salon['contact_phone'] ?: '—') ?></dd></div>
                    <div><dt>Saloon Contact 2</dt><dd><?= e($salon['contact_phone_secondary'] ?: '—') ?></dd></div>
                    <div><dt>Address</dt><dd><?= e($salon['address'] ?: '—') ?></dd></div>
                    <div><dt>Status</dt><dd><span class="badge badge--<?= e($salon['status']) ?>"><?= e($salon['status']) ?></span></dd></div>
                    <div><dt>Created</dt><dd><?= e($salon['created_at']) ?></dd></div>
                </dl>
                <?php if (!empty($salon['description'])): ?>
                    <div class="detail-list__description">
                        <dt>Description</dt>
                        <p><?= nl2br(e($salon['description'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--secondary" data-modal-close>Close</button>
            </div>
        </div>
    </div>

    <?php
    $formValues = $isEditingThis ? $oldInput : $salon;
    $formErrors = $isEditingThis ? $formErrorsAll : [];
    $fieldIdSuffix = '_edit_' . $salon['id'];
    ?>
    <div class="modal-overlay<?= ($formErrors !== []) ? ' is-open' : '' ?>" id="edit-salon-<?= (int) $salon['id'] ?>">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="edit-salon-title-<?= (int) $salon['id'] ?>">
            <div class="modal__header">
                <div class="modal__header-info">
                    <span class="modal__header-icon"><?= icon('pencil') ?></span>
                    <div>
                        <h2 id="edit-salon-title-<?= (int) $salon['id'] ?>">Edit Salon</h2>
                        <p class="modal__header-subtitle">Update the owner and salon details below.</p>
                    </div>
                </div>
                <button type="button" class="modal__close" data-modal-close aria-label="Close">
                    <?= icon('x') ?>
                </button>
            </div>
            <form class="modal__form" method="post" action="/endpoints/salons/update.php" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="salon_id" value="<?= (int) $salon['id'] ?>">
                <div class="modal__body">
                    <?php require __DIR__ . '/../includes/layout/salon-form-fields.php'; ?>
                </div>
                <div class="modal__footer">
                    <button type="button" class="btn btn--secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn btn--primary"><?= icon('check-circle') ?> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/layout/footer.php'; ?>
