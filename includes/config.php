<?php

declare(strict_types=1);

const APP_NAME = 'Zaloona Admin';
const PUBLIC_APP_NAME = 'Zaloona';
const PAGE_SIZE = 20;
const UPLOAD_ROOT = __DIR__ . '/../uploads';

/** Android install link for the owner app (EAS preview build). Not yet on the Play Store. */
const ANDROID_APP_DOWNLOAD_URL = 'https://expo.dev/accounts/abdalle/projects/mobile/builds/311ac79e-9307-4b3d-a50b-4fdff7bb2cd3';

const SALON_STATUSES = ['pending', 'approved', 'rejected', 'suspended'];
const SUBSCRIPTION_STATUSES = ['trial', 'active', 'past_due', 'cancelled', 'expired'];
const BOOKING_STATUSES = ['pending', 'confirmed', 'completed', 'cancelled'];

const ADMIN_ROLES = ['owner', 'support'];

/**
 * Plan offered on the public registration page. `price` is the USD amount recorded on the
 * subscription; `price_slsh` is shown alongside it for registrants who pay in Somaliland
 * shilling. Keys are stored nowhere sensitive; they're just used to look up the plan a
 * registrant picked before we record its name/price on the subscription.
 */
const SUBSCRIPTION_PLANS = [
    'standard' => [
        'name' => 'Standard',
        'price' => 10.00,
        'price_slsh' => 110000,
        'tagline' => 'Everything your salon needs to get online and get paid.',
        'features' => [
            'Booking management',
            'Customer records',
            'Subscription & revenue tracking',
        ],
    ],
];

/** Mobile-money numbers registrants pay to; the TIX/transaction number they get back is verified manually before approval. */
const PAYMENT_PROVIDERS = [
    'zaad' => ['label' => 'ZAAD', 'number' => '0637939755', 'icon' => '/assets/images/zaad_icon.jpg'],
    'e_dahab' => ['label' => 'e-Dahab', 'number' => '0657939755', 'icon' => '/assets/images/_icon.jpg'],
];
