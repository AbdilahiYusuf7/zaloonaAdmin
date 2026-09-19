<?php

declare(strict_types=1);

final class AuditLogRepository
{
    public function record(
        int $adminUserId,
        string $action,
        string $entity,
        int $entityId,
        ?string $beforeValue,
        ?string $afterValue
    ): void {
        $stmt = db()->prepare(
            'INSERT INTO audit_logs (admin_user_id, action, entity, entity_id, before_value, after_value, ip_address, created_at)
             VALUES (:admin_user_id, :action, :entity, :entity_id, :before_value, :after_value, :ip_address, NOW())'
        );
        $stmt->execute([
            'admin_user_id' => $adminUserId,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'before_value' => $beforeValue,
            'after_value' => $afterValue,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    /** @return array<int, array<string, mixed>> most recent audit entries, newest first */
    public function recent(int $limit = 10): array
    {
        $stmt = db()->prepare(
            'SELECT al.action, al.entity, al.entity_id, al.after_value, al.created_at, au.name AS admin_name
             FROM audit_logs al
             INNER JOIN admin_users au ON au.id = al.admin_user_id
             ORDER BY al.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /** @return array<int, array<string, mixed>> newest first, paginated */
    public function findAll(int $limit = PAGE_SIZE, int $offset = 0): array
    {
        $stmt = db()->prepare(
            'SELECT al.action, al.entity, al.entity_id, al.before_value, al.after_value,
                    al.ip_address, al.created_at, au.name AS admin_name
             FROM audit_logs al
             INNER JOIN admin_users au ON au.id = al.admin_user_id
             ORDER BY al.created_at DESC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
