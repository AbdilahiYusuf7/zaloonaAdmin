<?php

declare(strict_types=1);

final class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptions = new SubscriptionRepository(),
        private readonly AuditLogRepository $auditLog = new AuditLogRepository()
    ) {
    }

    /** @return array<int, array<string, mixed>> subscriptions matching the given filters, soonest-ending first */
    public function search(string $search, string $status, int $limit, int $offset): array
    {
        return $this->subscriptions->search($search, $status, $limit, $offset);
    }

    /** Total subscriptions matching the same filters as search(), for computing pagination. */
    public function countSearch(string $search, string $status): int
    {
        return $this->subscriptions->countSearch($search, $status);
    }

    public function updateStatus(int $subscriptionId, string $newStatus, int $adminUserId): void
    {
        if (!in_array($newStatus, SUBSCRIPTION_STATUSES, true)) {
            throw new InvalidArgumentException("Unknown subscription status: {$newStatus}");
        }

        $subscription = $this->subscriptions->findById($subscriptionId);

        if ($subscription === null) {
            throw new RuntimeException("Subscription {$subscriptionId} not found.");
        }

        $db = db();
        $db->beginTransaction();

        try {
            $this->subscriptions->updateStatus($subscriptionId, $newStatus);
            $this->auditLog->record(
                $adminUserId,
                'subscription.status_changed',
                'subscription',
                $subscriptionId,
                $subscription['status'],
                $newStatus
            );
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
