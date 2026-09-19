<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

require_login();

$pageTitle = 'Dashboard';
$pageSubtitle = 'Overview of salons, subscriptions and platform activity.';

$summary = (new DashboardService())->summary();

$subscriptionColors = [
    'active' => 'var(--color-primary)',
    'trial' => 'var(--color-primary-light)',
    'past_due' => 'var(--color-text-muted)',
    'cancelled' => 'var(--color-error)',
    'expired' => 'var(--color-warning)',
];

$donutTotal = array_sum($summary['subscriptionCounts']);
$donutStops = [];
$cumulative = 0;

foreach ($summary['subscriptionCounts'] as $status => $count) {
    if ($count === 0) {
        continue;
    }

    $start = $donutTotal > 0 ? ($cumulative / $donutTotal) * 100 : 0;
    $cumulative += $count;
    $end = $donutTotal > 0 ? ($cumulative / $donutTotal) * 100 : 0;
    $donutStops[] = ($subscriptionColors[$status] ?? 'var(--color-text-muted)') . " {$start}% {$end}%";
}

$donutGradient = $donutStops === [] ? 'var(--color-border) 0% 100%' : implode(', ', $donutStops);
$activeShareOfTotal = $donutTotal > 0 ? (int) round(($summary['activeSubscriptions'] / $donutTotal) * 100) : 0;

$revenueByMonth = $summary['revenuePerMonth'];
$maxMonthlyRevenue = max(1.0, ...array_values($revenueByMonth));

/** @return array{icon: string, text: string, tone: string} */
function dashboard_activity_label(array $log): array
{
    $after = $log['after_value'] ?? '';

    if ($log['entity'] === 'salon') {
        return match (true) {
            $log['action'] === 'salon.created' => ['icon' => 'shop', 'text' => 'New salon added', 'tone' => 'success'],
            $after === 'approved' => ['icon' => 'check-circle', 'text' => 'Salon approved', 'tone' => 'success'],
            $after === 'rejected' => ['icon' => 'alert-circle', 'text' => 'Salon rejected', 'tone' => 'error'],
            $after === 'suspended' => ['icon' => 'alert-circle', 'text' => 'Salon suspended', 'tone' => 'muted'],
            default => ['icon' => 'shop', 'text' => 'Salon updated', 'tone' => 'muted'],
        };
    }

    if ($log['entity'] === 'subscription') {
        return match ($after) {
            'active' => ['icon' => 'crown', 'text' => 'Subscription activated', 'tone' => 'success'],
            'cancelled' => ['icon' => 'alert-circle', 'text' => 'Subscription cancelled', 'tone' => 'error'],
            'expired' => ['icon' => 'alert-circle', 'text' => 'Subscription expired', 'tone' => 'error'],
            'past_due' => ['icon' => 'alert-circle', 'text' => 'Subscription past due', 'tone' => 'muted'],
            default => ['icon' => 'card', 'text' => 'Subscription updated', 'tone' => 'muted'],
        };
    }

    return ['icon' => 'shop', 'text' => ucfirst(str_replace('.', ' ', $log['action'])), 'tone' => 'muted'];
}

require __DIR__ . '/../includes/layout/header.php';
?>
<section class="stat-grid">
    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--salons"><?= icon('shop') ?></div>
        <div class="stat-card__body">
            <span class="stat-card__label">Total Salons</span>
            <span class="stat-card__value"><?= $summary['totalSalons'] ?></span>
            <?php if ($summary['salonGrowthPercent'] !== null): ?>
                <span class="stat-card__trend stat-card__trend--<?= $summary['salonGrowthPercent'] >= 0 ? 'up' : 'down' ?>">
                    <?= $summary['salonGrowthPercent'] >= 0 ? '&uarr;' : '&darr;' ?> <?= abs($summary['salonGrowthPercent']) ?>% from last month
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--pending"><?= icon('clock') ?></div>
        <div class="stat-card__body">
            <span class="stat-card__label">Pending Approvals</span>
            <span class="stat-card__value"><?= $summary['pendingSalons'] ?></span>
            <span class="stat-card__hint">Need your review</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--subscriptions"><?= icon('crown') ?></div>
        <div class="stat-card__body">
            <span class="stat-card__label">Active Subscriptions</span>
            <span class="stat-card__value"><?= $summary['activeSubscriptions'] ?></span>
            <span class="stat-card__hint"><?= $summary['activeSubscriptionShare'] ?>% of total salons</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card__icon stat-card__icon--bookings"><?= icon('calendar') ?></div>
        <div class="stat-card__body">
            <span class="stat-card__label">Total Bookings (30 days)</span>
            <span class="stat-card__value"><?= $summary['bookingsLast30Days'] ?></span>
            <?php if ($summary['bookingGrowthPercent'] !== null): ?>
                <span class="stat-card__trend stat-card__trend--<?= $summary['bookingGrowthPercent'] >= 0 ? 'up' : 'down' ?>">
                    <?= $summary['bookingGrowthPercent'] >= 0 ? '&uarr;' : '&darr;' ?> <?= abs($summary['bookingGrowthPercent']) ?>% from last month
                </span>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="dashboard-stack">
        <section class="charts-row">
            <div class="card chart-card">
                <div class="card__header">
                    <h2>Subscription Status</h2>
                </div>
                <div class="donut-widget">
                    <div class="donut" style="background: conic-gradient(<?= $donutGradient ?>);">
                        <div class="donut__center">
                            <span class="donut__total"><?= $donutTotal ?></span>
                            <span class="donut__label">Total Salons</span>
                        </div>
                    </div>
                    <ul class="donut-legend">
                        <?php foreach ($summary['subscriptionCounts'] as $status => $count): ?>
                            <?php if ($count === 0) continue; ?>
                            <li>
                                <span class="donut-legend__dot" style="background: <?= $subscriptionColors[$status] ?? 'var(--color-text-muted)' ?>;"></span>
                                <span class="donut-legend__label"><?= e(ucfirst(str_replace('_', ' ', $status))) ?></span>
                                <span class="donut-legend__value"><?= $count ?></span>
                                <span class="donut-legend__percent"><?= $donutTotal > 0 ? round(($count / $donutTotal) * 100) : 0 ?>%</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="card chart-card">
                <div class="card__header">
                    <h2>Revenue per Month</h2>
                    <span class="card__header-meta">Last 6 months</span>
                </div>
                <div class="bar-chart">
                    <?php foreach ($revenueByMonth as $month => $revenue): ?>
                        <div class="bar-chart__col">
                            <span class="bar-chart__value">$<?= number_format($revenue, 0) ?></span>
                            <div class="bar-chart__track">
                                <span class="bar-chart__bar" style="height: <?= round(($revenue / $maxMonthlyRevenue) * 100) ?>%"></span>
                            </div>
                            <span class="bar-chart__label"><?= e(date('M', strtotime($month . '-01'))) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card__header">
                <h2>Salon Approval Queue <?php if ($summary['pendingSalons'] > 0): ?><span class="card__header-badge"><?= $summary['pendingSalons'] ?></span><?php endif; ?></h2>
                <a href="/pages/salon-approval-queue.php" class="card__header-link">View all <?= icon('chevron-right') ?></a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Salon</th>
                        <th>Owner</th>
                        <th>Email</th>
                        <th>Submitted</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($summary['pendingQueue'])): ?>
                        <tr><td colspan="5">No salons waiting for approval.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($summary['pendingQueue'] as $salon): ?>
                        <tr>
                            <td><?= e($salon['name']) ?></td>
                            <td><?= e($salon['owner_name']) ?></td>
                            <td><?= e($salon['email']) ?></td>
                            <td><?= e($salon['created_at']) ?></td>
                            <td><span class="badge badge--pending">Pending</span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="lists-row">
            <div class="card">
                <div class="card__header"><h2>Top Performing Salons</h2></div>
                <?php $topMax = max(1, $summary['topSalons'][0]['total'] ?? 1); ?>
                <ul class="ranked-list">
                    <?php if (empty($summary['topSalons'])): ?>
                        <li class="ranked-list__empty">No bookings recorded yet.</li>
                    <?php endif; ?>
                    <?php foreach ($summary['topSalons'] as $rank => $salon): ?>
                        <li class="ranked-list__item">
                            <span class="ranked-list__rank"><?= $rank + 1 ?></span>
                            <div class="ranked-list__body">
                                <span class="ranked-list__name"><?= e($salon['salon_name']) ?></span>
                                <div class="ranked-list__bar">
                                    <span style="width: <?= round(((int) $salon['total'] / $topMax) * 100) ?>%"></span>
                                </div>
                            </div>
                            <span class="ranked-list__value"><?= (int) $salon['total'] ?> bookings</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card">
                <div class="card__header"><h2>Recent Bookings</h2></div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Salon</th>
                            <th>Service</th>
                            <th>Date &amp; Time</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($summary['recentBookings'])): ?>
                            <tr><td colspan="5">No bookings yet.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($summary['recentBookings'] as $booking): ?>
                            <tr>
                                <td><?= e($booking['customer_name']) ?></td>
                                <td><?= e($booking['salon_name']) ?></td>
                                <td><?= e($booking['service']) ?></td>
                                <td><?= e(date('M j, g:i A', strtotime($booking['scheduled_at']))) ?></td>
                                <td><span class="badge badge--<?= e($booking['status']) ?>"><?= e(ucfirst($booking['status'])) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="lists-row">
            <div class="card">
                <div class="card__header"><h2>Recent Activity</h2></div>
                <ul class="activity-feed">
                    <?php if (empty($summary['recentActivity'])): ?>
                        <li class="activity-feed__empty">No activity recorded yet.</li>
                    <?php endif; ?>
                    <?php foreach ($summary['recentActivity'] as $log): ?>
                        <?php $meta = dashboard_activity_label($log); ?>
                        <li class="activity-feed__item">
                            <span class="activity-feed__icon activity-feed__icon--<?= e($meta['tone']) ?>"><?= icon($meta['icon']) ?></span>
                            <div class="activity-feed__body">
                                <span class="activity-feed__text"><?= e($meta['text']) ?></span>
                                <span class="activity-feed__meta"><?= e(ucfirst($log['entity'])) ?> #<?= (int) $log['entity_id'] ?></span>
                            </div>
                            <span class="activity-feed__time"><?= e(time_ago($log['created_at'])) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card">
                <div class="card__header"><h2>Subscription Status Overview</h2></div>
                <div class="subscription-overview">
                    <span class="subscription-overview__label">Active Subscriptions</span>
                    <div class="subscription-overview__row">
                        <span class="subscription-overview__value"><?= $summary['activeSubscriptions'] ?></span>
                        <span class="subscription-overview__percent"><?= $activeShareOfTotal ?>%</span>
                    </div>
                    <div class="subscription-overview__bar">
                        <span style="width: <?= $activeShareOfTotal ?>%"></span>
                    </div>
                    <div class="subscription-overview__mini-stats">
                        <div><strong><?= $summary['subscriptionCounts']['trial'] ?></strong><span>Trial</span></div>
                        <div><strong><?= $summary['subscriptionCounts']['expired'] ?></strong><span>Expired</span></div>
                        <div><strong><?= $summary['subscriptionCounts']['cancelled'] ?></strong><span>Cancelled</span></div>
                    </div>
                </div>
            </div>
        </section>
</div>
<?php require __DIR__ . '/../includes/layout/footer.php'; ?>
