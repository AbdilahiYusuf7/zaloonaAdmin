<?php

declare(strict_types=1);

/**
 * Handles public, unauthenticated salon self-registration: a salon owner submits their details,
 * picks a plan, and reports the mobile-money transaction they paid with. No admin performs this
 * action, so unlike SalonService::create() it does not write to the (admin-attributed) audit log —
 * the resulting salon simply lands as 'pending' with a 'trial' subscription for an admin to
 * manually verify the payment reference and approve or reject via the existing approval queue.
 */
final class RegistrationService
{
    public function __construct(
        private readonly SalonService $salons = new SalonService(),
        private readonly SalonRepository $salonRepository = new SalonRepository(),
        private readonly SubscriptionRepository $subscriptions = new SubscriptionRepository(),
        private readonly LogoUploadService $logoUploads = new LogoUploadService()
    ) {
    }

    /**
     * @param array<string, string> $input salon/owner fields, same shape as SalonService::validate()
     * @param array<string, mixed> $files raw $_FILES entries
     * @return array<string, string> validation errors, keyed by field name; empty when valid
     */
    public function validate(array $input, array $files, string $planKey, string $paymentProvider, string $paymentReference): array
    {
        $errors = $this->salons->validate($input, $files);

        if (!isset(SUBSCRIPTION_PLANS[$planKey])) {
            $errors['plan'] = 'Please choose a plan.';
        }

        if (!isset(PAYMENT_PROVIDERS[$paymentProvider])) {
            $errors['payment_provider'] = 'Please choose which number you paid to.';
        }

        if (trim($paymentReference) === '') {
            $errors['payment_reference'] = 'Enter the transaction (TIX) number from your payment.';
        }

        return $errors;
    }

    /**
     * @param array<string, string> $input pre-validated via validate()
     * @param array<string, mixed> $files raw $_FILES entries
     */
    public function register(array $input, array $files, string $planKey, string $paymentProvider, string $paymentReference): int
    {
        $plan = SUBSCRIPTION_PLANS[$planKey];
        $logoPath = $this->logoUploads->store($files['logo'] ?? null);

        $db = db();
        $db->beginTransaction();

        try {
            $salonId = $this->salonRepository->create([
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

            $this->subscriptions->create([
                'salon_id' => $salonId,
                'plan' => $plan['name'],
                'status' => 'trial',
                'amount' => $plan['price'],
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+1 month')),
                'payment_provider' => $paymentProvider,
                'payment_reference' => trim($paymentReference),
            ]);

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            $this->logoUploads->delete($logoPath);

            throw $e;
        }

        return $salonId;
    }
}
