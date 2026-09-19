<?php

declare(strict_types=1);

final class SubscriptionRepository
{
    /**
     * @return array<int, array<string, mixed>> subscriptions matching the given search text and status,
     *         soonest-ending first. Pass an empty string for either filter to skip it.
     */
    public function search(string $search, string $status, int $limit = PAGE_SIZE, int $offset = 0): array
    {
        [$where, $params] = $this->buildSearchWhere($search, $status);

        $stmt = db()->prepare(
            "SELECT s.id, s.plan, s.status, s.amount, s.start_date, s.end_date,
                    sa.id AS salon_id, sa.name AS salon_name
             FROM subscriptions s
             INNER JOIN salons sa ON sa.id = s.salon_id
             {$where}
             ORDER BY s.end_date ASC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** Total subscriptions matching the same filters as search(), for computing pagination. */
    public function countSearch(string $search, string $status): int
    {
        [$where, $params] = $this->buildSearchWhere($search, $status);

        $stmt = db()->prepare(
            "SELECT COUNT(*)
             FROM subscriptions s
             INNER JOIN salons sa ON sa.id = s.salon_id
             {$where}"
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue(":{$key}", $value, PDO::PARAM_STR);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /** @return array{0: string, 1: array<string, string>} the WHERE clause (or '') and its bound params */
    private function buildSearchWhere(string $search, string $status): array
    {
        $conditions = [];
        $params = [];

        if ($status !== '') {
            $conditions[] = 's.status = :status';
            $params['status'] = $status;
        }

        if ($search !== '') {
            // Native (non-emulated) prepares reject a repeated named placeholder, so each LIKE gets its own.
            $conditions[] = '(sa.name LIKE :search_salon OR s.plan LIKE :search_plan)';
            $params['search_salon'] = "%{$search}%";
            $params['search_plan'] = "%{$search}%";
        }

        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$where, $params];
    }

    public function findById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM subscriptions WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    /** Most recent still-trial subscription for a salon, if any — used to settle it when the salon is approved/rejected. */
    public function findLatestTrialBySalonId(int $salonId): ?array
    {
        $stmt = db()->prepare(
            "SELECT * FROM subscriptions
             WHERE salon_id = :salon_id AND status = 'trial'
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $stmt->execute(['salon_id' => $salonId]);

        return $stmt->fetch() ?: null;
    }

    /**
     * @param array<int, int> $salonIds
     * @return array<int, array<string, mixed>> the most recent subscription per salon id, keyed by salon_id
     */
    public function findLatestBySalonIds(array $salonIds): array
    {
        if ($salonIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($salonIds), '?'));
        $stmt = db()->prepare(
            "SELECT * FROM subscriptions
             WHERE salon_id IN ({$placeholders})
             ORDER BY created_at DESC"
        );
        $stmt->execute(array_values($salonIds));

        $latestBySalon = [];
        foreach ($stmt->fetchAll() as $row) {
            $latestBySalon[(int) $row['salon_id']] ??= $row;
        }

        return $latestBySalon;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO subscriptions
                (salon_id, plan, status, amount, start_date, end_date, payment_provider, payment_reference)
             VALUES
                (:salon_id, :plan, :status, :amount, :start_date, :end_date, :payment_provider, :payment_reference)'
        );
        $stmt->execute([
            'salon_id' => $data['salon_id'],
            'plan' => $data['plan'],
            'status' => $data['status'],
            'amount' => $data['amount'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'payment_provider' => $data['payment_provider'],
            'payment_reference' => $data['payment_reference'],
        ]);

        return (int) db()->lastInsertId();
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = db()->prepare('UPDATE subscriptions SET status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $id]);
    }

    /** @return array<string, int> total subscriptions for each status */
    public function countByStatus(): array
    {
        $counts = array_fill_keys(SUBSCRIPTION_STATUSES, 0);

        $stmt = db()->query('SELECT status, COUNT(*) AS total FROM subscriptions GROUP BY status');

        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    public function activeRevenueTotal(): float
    {
        $stmt = db()->query("SELECT COALESCE(SUM(amount), 0) AS total FROM subscriptions WHERE status = 'active'");

        return (float) $stmt->fetchColumn();
    }

    /** @return array<string, float> revenue booked per month for the last N months, oldest first, keyed 'Y-m' */
    public function revenuePerMonth(int $months = 6): array
    {
        $stmt = db()->prepare(
            "SELECT DATE_FORMAT(start_date, '%Y-%m') AS month, SUM(amount) AS total
             FROM subscriptions
             WHERE start_date >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
             GROUP BY month"
        );
        $stmt->bindValue(':months', $months - 1, PDO::PARAM_INT);
        $stmt->execute();

        $totalsByMonth = [];
        foreach ($stmt->fetchAll() as $row) {
            $totalsByMonth[$row['month']] = (float) $row['total'];
        }

        $series = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = (new DateTimeImmutable("first day of -{$i} months"))->format('Y-m');
            $series[$month] = $totalsByMonth[$month] ?? 0.0;
        }

        return $series;
    }
}
