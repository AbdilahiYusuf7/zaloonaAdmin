<?php

declare(strict_types=1);

final class DashboardService
{
    private const TREND_WINDOW_DAYS = 30;
    private const REVENUE_CHART_MONTHS = 6;
    private const PREVIEW_LIMIT = 5;

    public function __construct(
        private readonly SalonRepository $salons = new SalonRepository(),
        private readonly SubscriptionRepository $subscriptions = new SubscriptionRepository(),
        private readonly BookingRepository $bookings = new BookingRepository(),
        private readonly AuditLogRepository $auditLog = new AuditLogRepository(),
        private readonly SalonService $salonService = new SalonService()
    ) {
    }

    /** @return array<string, mixed> */
    public function summary(): array
    {
        $salonCounts = $this->salons->countByStatus();
        $totalSalons = array_sum($salonCounts);
        $salonsBeforeWindow = $this->salons->countOlderThanDays(self::TREND_WINDOW_DAYS);

        $subscriptionCounts = $this->subscriptions->countByStatus();
        $activeSubscriptions = $subscriptionCounts['active'];

        $bookingsLast30Days = $this->bookings->countInLastDays(self::TREND_WINDOW_DAYS);
        $bookingsPrior30Days = $this->bookings->countInPriorWindow(self::TREND_WINDOW_DAYS);

        return [
            'totalSalons' => $totalSalons,
            'salonGrowthPercent' => $this->percentChange($salonsBeforeWindow, $totalSalons),
            'pendingSalons' => $salonCounts['pending'],
            'subscriptionCounts' => $subscriptionCounts,
            'activeSubscriptions' => $activeSubscriptions,
            'activeSubscriptionShare' => $this->shareOf($activeSubscriptions, $totalSalons),
            'activeRevenue' => $this->subscriptions->activeRevenueTotal(),
            'bookingsLast30Days' => $bookingsLast30Days,
            'bookingGrowthPercent' => $this->percentChange($bookingsPrior30Days, $bookingsLast30Days),
            'revenuePerMonth' => $this->subscriptions->revenuePerMonth(self::REVENUE_CHART_MONTHS),
            'pendingQueue' => $this->salonService->pendingQueue(self::PREVIEW_LIMIT),
            'recentActivity' => $this->auditLog->recent(self::PREVIEW_LIMIT),
            'recentBookings' => $this->bookings->recent(self::PREVIEW_LIMIT),
            'topSalons' => $this->bookings->topSalonsByBookingCount(self::PREVIEW_LIMIT),
        ];
    }

    /** Percentage change from $before to $after, or null when there is no baseline to compare against. */
    private function percentChange(int $before, int $after): ?float
    {
        if ($before === 0) {
            return null;
        }

        return round((($after - $before) / $before) * 100, 1);
    }

    private function shareOf(int $part, int $whole): int
    {
        if ($whole === 0) {
            return 0;
        }

        return (int) round(($part / $whole) * 100);
    }
}
