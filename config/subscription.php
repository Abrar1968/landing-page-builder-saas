<?php

return [
    'plans' => [
        'free' => [
            'name' => 'Free',
            'price' => 0,
            'stripe_price_id' => null,
            'limits' => [
                'pages' => 1,
                'storage' => 100, // MB
                'custom_domains' => 0,
            ],
            'features' => [
                '1 landing page',
                '100MB storage',
                'Basic analytics',
                'PageBuilder subdomain',
            ],
        ],
        'pro' => [
            'name' => 'Pro',
            'price' => 19,
            'stripe_price_id' => env('STRIPE_PRO_PRICE_ID'),
            'limits' => [
                'pages' => 10,
                'storage' => 5120, // 5GB in MB
                'custom_domains' => 3,
            ],
            'features' => [
                '10 landing pages',
                '5GB storage',
                'Advanced analytics',
                '3 custom domains',
                'Priority support',
                'Remove branding',
            ],
        ],
        'business' => [
            'name' => 'Business',
            'price' => 49,
            'stripe_price_id' => env('STRIPE_BUSINESS_PRICE_ID'),
            'limits' => [
                'pages' => -1, // unlimited
                'storage' => 51200, // 50GB in MB
                'custom_domains' => -1, // unlimited
            ],
            'features' => [
                'Unlimited landing pages',
                '50GB storage',
                'Advanced analytics',
                'Unlimited custom domains',
                'Priority support',
                'Remove branding',
                'Team collaboration',
                'API access',
            ],
        ],
    ],
];
