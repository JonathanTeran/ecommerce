<?php

return [
    'company_name' => env('LEGAL_COMPANY_NAME', 'AmePhia'),
    'company_website' => env('LEGAL_COMPANY_WEBSITE', 'https://amephia.com/'),
    'contact_email' => env('LEGAL_CONTACT_EMAIL', env('MAIL_FROM_ADDRESS', 'legal@example.com')),
    'jurisdiction' => env('LEGAL_JURISDICTION', 'Ecuador'),
    'global' => [
        'service_region' => env('LEGAL_SERVICE_REGION', 'Global'),
        'governing_law' => env('LEGAL_GOVERNING_LAW', 'Leyes de Ecuador'),
        'dispute_resolution' => env('LEGAL_DISPUTE_RESOLUTION', 'Arbitraje en Quito, Ecuador'),
        'consumer_protection_notice' => env('LEGAL_CONSUMER_NOTICE', 'Cuando una norma imperativa local otorgue mayor protección al consumidor, esa norma prevalecerá.'),
        'restricted_countries_notice' => env('LEGAL_RESTRICTED_COUNTRIES_NOTICE', 'El servicio no está disponible en jurisdicciones sancionadas o restringidas por normativa aplicable.'),
        'prices_include_taxes' => (bool) env('LEGAL_PRICES_INCLUDE_TAXES', false),
    ],
    'billing' => [
        'grace_period_days' => (int) env('LEGAL_BILLING_GRACE_DAYS', 30),
        'termination_days' => (int) env('LEGAL_BILLING_TERMINATION_DAYS', 60),
        'reactivation_hours' => (int) env('LEGAL_BILLING_REACTIVATION_HOURS', 24),
        'data_retention_days' => (int) env('LEGAL_BILLING_DATA_RETENTION_DAYS', 30),
    ],
    'intellectual_property' => [
        'owner_name' => env('LEGAL_IP_OWNER_NAME', 'AmePhia'),
        'owner_website' => env('LEGAL_IP_OWNER_WEBSITE', 'https://amephia.com/'),
        'infringement_contact_email' => env('LEGAL_IP_INFRINGEMENT_CONTACT_EMAIL', env('LEGAL_CONTACT_EMAIL', env('MAIL_FROM_ADDRESS', 'legal@example.com'))),
        'takedown_response_days' => (int) env('LEGAL_IP_TAKEDOWN_RESPONSE_DAYS', 10),
    ],
    'terms' => [
        'version' => env('LEGAL_TERMS_VERSION', '2026-03-05'),
        'effective_date' => env('LEGAL_TERMS_EFFECTIVE_DATE', '2026-03-05'),
    ],
    'privacy' => [
        'version' => env('LEGAL_PRIVACY_VERSION', '2026-03-05'),
        'effective_date' => env('LEGAL_PRIVACY_EFFECTIVE_DATE', '2026-03-05'),
    ],
    'acceptable_use' => [
        'version' => env('LEGAL_AUP_VERSION', '2026-03-05'),
        'effective_date' => env('LEGAL_AUP_EFFECTIVE_DATE', '2026-03-05'),
    ],
];
