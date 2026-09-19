<?php

declare(strict_types=1);

final class BookingRepository
{
    /**
     * Count bookings created within a trailing window measured from the database's own clock —
     * comparing against a PHP-generated timestamp would silently misfire if the app server and
     * DB server clocks/timezones ever drift apart.
     */
    public function countInLastDays(int $days): int
    {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM bookings WHERE created_at >= NOW() - INTERVAL :days DAY'
        );
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /** Count bookings created in the window immediately before the trailing $days window. */
    public function countInPriorWindow(int $days): int
    {
        $stmt = db()->prepare(
            'SELECT COUNT(*) FROM bookings
             WHERE created_at >= NOW() - INTERVAL :startDays DAY
               AND created_at < NOW() - INTERVAL :endDays DAY'
        );
        $stmt->bindValue(':startDays', $days * 2, PDO::PARAM_INT);
        $stmt->bindValue(':endDays', $days, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /** @return array<int, array<string, mixed>> most recently placed bookings */
    public function recent(int $limit = 5): array
    {
        $stmt = db()->prepare(
            'SELECT b.customer_name, b.service, b.scheduled_at, b.status, s.name AS salon_name
             FROM bookings b
             INNER JOIN salons s ON s.id = b.salon_id
             ORDER BY b.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> salon_id, salon_name, total — highest booking count first */
    public function topSalonsByBookingCount(int $limit = 5): array
    {
        $stmt = db()->prepare(
            'SELECT s.id AS salon_id, s.name AS salon_name, COUNT(b.id) AS total
             FROM bookings b
             INNER JOIN salons s ON s.id = b.salon_id
             GROUP BY s.id, s.name
             ORDER BY total DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
