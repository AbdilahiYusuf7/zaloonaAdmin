<?php

declare(strict_types=1);

final class SalonRepository
{
    /** @return array<int, array<string, mixed>> */
    public function findByStatus(string $status, int $limit = PAGE_SIZE, int $offset = 0): array
    {
        $stmt = db()->prepare(
            'SELECT id, name, owner_name, email, owner_phone, status, created_at
             FROM salons
             WHERE status = :status
             ORDER BY created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>> salons matching the given search text and status,
     *         newest first. Pass an empty string for either filter to skip it.
     */
    public function search(string $search, string $status, int $limit = PAGE_SIZE, int $offset = 0): array
    {
        [$where, $params] = $this->buildSearchWhere($search, $status);

        $stmt = db()->prepare(
            "SELECT id, name, owner_name, email, owner_phone, contact_phone, contact_phone_secondary,
                    address, description, status, logo_path, created_at
             FROM salons
             {$where}
             ORDER BY created_at DESC
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

    /** Total salons matching the same filters as search(), for computing pagination. */
    public function countSearch(string $search, string $status): int
    {
        [$where, $params] = $this->buildSearchWhere($search, $status);

        $stmt = db()->prepare("SELECT COUNT(*) FROM salons {$where}");

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
            $conditions[] = 'status = :status';
            $params['status'] = $status;
        }

        if ($search !== '') {
            // Native (non-emulated) prepares reject a repeated named placeholder, so each LIKE gets its own.
            $conditions[] = '(name LIKE :search_name OR owner_name LIKE :search_owner OR email LIKE :search_email)';
            $params['search_name'] = "%{$search}%";
            $params['search_owner'] = "%{$search}%";
            $params['search_email'] = "%{$search}%";
        }

        $where = $conditions !== [] ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$where, $params];
    }

    public function findById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM salons WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM salons WHERE email = :email';

        if ($excludeId !== null) {
            $sql .= ' AND id != :exclude_id';
        }

        $stmt = db()->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        if ($excludeId !== null) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO salons
                (name, owner_name, email, owner_phone, contact_phone, contact_phone_secondary, address, description, logo_path, status)
             VALUES
                (:name, :owner_name, :email, :owner_phone, :contact_phone, :contact_phone_secondary, :address, :description, :logo_path, :status)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'owner_name' => $data['owner_name'],
            'email' => $data['email'],
            'owner_phone' => $data['owner_phone'],
            'contact_phone' => $data['contact_phone'],
            'contact_phone_secondary' => $data['contact_phone_secondary'],
            'address' => $data['address'],
            'description' => $data['description'],
            'logo_path' => $data['logo_path'],
            'status' => $data['status'],
        ]);

        return (int) db()->lastInsertId();
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = db()->prepare('UPDATE salons SET status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $id]);
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): void
    {
        $stmt = db()->prepare(
            'UPDATE salons SET
                name = :name,
                owner_name = :owner_name,
                email = :email,
                owner_phone = :owner_phone,
                contact_phone = :contact_phone,
                contact_phone_secondary = :contact_phone_secondary,
                address = :address,
                description = :description,
                logo_path = :logo_path,
                updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'name' => $data['name'],
            'owner_name' => $data['owner_name'],
            'email' => $data['email'],
            'owner_phone' => $data['owner_phone'],
            'contact_phone' => $data['contact_phone'],
            'contact_phone_secondary' => $data['contact_phone_secondary'],
            'address' => $data['address'],
            'description' => $data['description'],
            'logo_path' => $data['logo_path'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM salons WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** @return array<string, int> total salons for each status */
    public function countByStatus(): array
    {
        $counts = array_fill_keys(SALON_STATUSES, 0);

        $stmt = db()->query('SELECT status, COUNT(*) AS total FROM salons GROUP BY status');

        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    /**
     * Count salons created more than $days ago, measured from the database's own clock —
     * comparing against a PHP-generated timestamp would silently misfire if the app server
     * and DB server clocks/timezones ever drift apart.
     */
    public function countOlderThanDays(int $days): int
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM salons WHERE created_at < NOW() - INTERVAL :days DAY');
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
