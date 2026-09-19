<?php

declare(strict_types=1);

final class SalonService
{
    public function __construct(
        private readonly SalonRepository $salons = new SalonRepository(),
        private readonly SubscriptionRepository $subscriptions = new SubscriptionRepository(),
        private readonly AuditLogRepository $auditLog = new AuditLogRepository(),
        private readonly LogoUploadService $logoUploads = new LogoUploadService()
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function pendingQueue(int $limit = PAGE_SIZE): array
    {
        return $this->salons->findByStatus('pending', $limit);
    }

    /** @return array<int, array<string, mixed>> salons matching the given filters, newest first */
    public function search(string $search, string $status, int $limit, int $offset): array
    {
        return $this->salons->search($search, $status, $limit, $offset);
    }

    /** Total salons matching the same filters as search(), for computing pagination. */
    public function countSearch(string $search, string $status): int
    {
        return $this->salons->countSearch($search, $status);
    }

    public function find(int $id): ?array
    {
        return $this->salons->findById($id);
    }

    /**
     * @param array<string, string> $input
     * @param array<string, mixed> $files raw $_FILES entries
     * @param int|null $excludeId when validating an edit, the salon's own id (so its own email doesn't collide with itself)
     * @return array<string, string> validation errors, keyed by field name; empty when valid
     */
    public function validate(array $input, array $files, ?int $excludeId = null): array
    {
        $errors = [];

        if (trim($input['owner_name'] ?? '') === '') {
            $errors['owner_name'] = 'Owner name is required.';
        }

        if (trim($input['owner_phone'] ?? '') === '') {
            $errors['owner_phone'] = 'Owner number is required.';
        }

        $email = trim($input['email'] ?? '');
        if ($email === '') {
            $errors['email'] = 'Owner email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif ($this->salons->emailExists($email, $excludeId)) {
            $errors['email'] = 'This email is already registered to another salon.';
        }

        if (trim($input['name'] ?? '') === '') {
            $errors['name'] = 'Salon name is required.';
        }

        if (trim($input['contact_phone'] ?? '') === '') {
            $errors['contact_phone'] = 'Salon contact number is required.';
        }

        if (trim($input['address'] ?? '') === '') {
            $errors['address'] = 'Address is required.';
        }

        $logoError = $this->logoUploads->validate($files['logo'] ?? null);
        if ($logoError !== null) {
            $errors['logo'] = $logoError;
        }

        return $errors;
    }

    /**
     * @param array<string, string> $input pre-validated via validate()
     * @param array<string, mixed> $files raw $_FILES entries
     */
    public function create(array $input, array $files, int $adminUserId): int
    {
        $logoPath = $this->logoUploads->store($files['logo'] ?? null);

        $db = db();
        $db->beginTransaction();

        try {
            $salonId = $this->salons->create([
                'name' => trim($input['name']),
                'owner_name' => trim($input['owner_name']),
                'email' => trim($input['email']),
                'owner_phone' => trim($input['owner_phone']),
                'contact_phone' => trim($input['contact_phone']),
                'contact_phone_secondary' => trim($input['contact_phone_secondary'] ?? '') ?: null,
                'address' => trim($input['address']),
                'description' => trim($input['description'] ?? '') ?: null,
                'logo_path' => $logoPath,
                'status' => 'pending',
            ]);

            $this->auditLog->record($adminUserId, 'salon.created', 'salon', $salonId, null, 'pending');
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            $this->logoUploads->delete($logoPath);

            throw $e;
        }

        return $salonId;
    }

    /**
     * @param array<string, string> $input pre-validated via validate()
     * @param array<string, mixed> $files raw $_FILES entries
     */
    public function update(int $id, array $input, array $files, int $adminUserId): void
    {
        $salon = $this->salons->findById($id);

        if ($salon === null) {
            throw new RuntimeException("Salon {$id} not found.");
        }

        $newLogoPath = $this->logoUploads->store($files['logo'] ?? null);
        $logoPath = $newLogoPath ?? $salon['logo_path'];

        $db = db();
        $db->beginTransaction();

        try {
            $this->salons->update($id, [
                'name' => trim($input['name']),
                'owner_name' => trim($input['owner_name']),
                'email' => trim($input['email']),
                'owner_phone' => trim($input['owner_phone']),
                'contact_phone' => trim($input['contact_phone']),
                'contact_phone_secondary' => trim($input['contact_phone_secondary'] ?? '') ?: null,
                'address' => trim($input['address']),
                'description' => trim($input['description'] ?? '') ?: null,
                'logo_path' => $logoPath,
            ]);

            $this->auditLog->record($adminUserId, 'salon.updated', 'salon', $id, $salon['name'], trim($input['name']));
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            $this->logoUploads->delete($newLogoPath);

            throw $e;
        }

        // Only remove the old file once the new one is safely committed in its place.
        if ($newLogoPath !== null) {
            $this->logoUploads->delete($salon['logo_path']);
        }
    }

    public function delete(int $id, int $adminUserId): void
    {
        $salon = $this->salons->findById($id);

        if ($salon === null) {
            throw new RuntimeException("Salon {$id} not found.");
        }

        $db = db();
        $db->beginTransaction();

        try {
            $this->auditLog->record($adminUserId, 'salon.deleted', 'salon', $id, $salon['name'], null);
            $this->salons->delete($id);
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        $this->logoUploads->delete($salon['logo_path']);
    }

    public function approve(int $salonId, int $adminUserId): void
    {
        $this->transitionStatus($salonId, 'approved', $adminUserId);
    }

    public function reject(int $salonId, int $adminUserId): void
    {
        $this->transitionStatus($salonId, 'rejected', $adminUserId);
    }

    /**
     * Approving/rejecting a salon also settles the trial subscription created at self-registration
     * (if any) — approved activates it, rejected cancels it — so a single decision finalizes both.
     * Admin-added salons have no such subscription, so this is a no-op for them.
     */
    private function transitionStatus(int $salonId, string $newStatus, int $adminUserId): void
    {
        if (!in_array($newStatus, SALON_STATUSES, true)) {
            throw new InvalidArgumentException("Unknown salon status: {$newStatus}");
        }

        $salon = $this->salons->findById($salonId);

        if ($salon === null) {
            throw new RuntimeException("Salon {$salonId} not found.");
        }

        $db = db();
        $db->beginTransaction();

        try {
            $this->salons->updateStatus($salonId, $newStatus);
            $this->auditLog->record(
                $adminUserId,
                'salon.status_changed',
                'salon',
                $salonId,
                $salon['status'],
                $newStatus
            );

            $trialSubscription = $this->subscriptions->findLatestTrialBySalonId($salonId);

            if ($trialSubscription !== null) {
                $this->subscriptions->updateStatus(
                    $trialSubscription['id'],
                    $newStatus === 'approved' ? 'active' : 'cancelled'
                );
            }

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
