<?php

return [
    /* Keep these values aligned with Google Business Profile. */
    'site_url' => env('SEO_SITE_URL', 'https://sharplync.com.au'),
    'site_name' => 'SharpLync',
    'default_title' => 'IT Support Stanthorpe & Granite Belt | SharpLync',
    'default_description' => 'Local IT support for Stanthorpe and the Granite Belt, including computer repairs, Microsoft 365, cybersecurity, Wi-Fi, backups and remote support.',
    'default_image' => '/images/og-sharplync.png',

    // Canonical, public pages emitted by `php artisan seo:generate-sitemap`.
    'sitemap' => [
        '/',
        '/it-support-stanthorpe',
        '/computer-repairs-stanthorpe',
        '/services',
        '/about',
        '/testimonials',
        '/contact',
        '/trend-micro',
        '/vendors',
        '/marketing/sharppulse',
        '/tools/cyber-glossary',
        '/tools/cyber-check',
        '/policies/hub',
        '/policies/terms',
        '/policies/privacy',
        '/policies/security',
        '/policies/support',
    ],

    'business' => [
        'id' => 'https://sharplync.com.au/#business',
        'name' => 'SharpLync Pty Ltd',
        'telephone' => '+61 492 014 463',
        'email' => 'info@sharplync.com.au',
        'logo' => 'https://sharplync.com.au/images/sharplync-logo.png',
        'locality' => 'Stanthorpe',
        'region' => 'QLD',
        'postal_code' => '4380',
        'country' => 'AU',
        'areas_served' => ['Stanthorpe', 'Granite Belt', 'Southern Downs'],
        'same_as' => [
            'https://www.linkedin.com/company/sharplync',
            'https://www.facebook.com/SharpLync',
            'https://x.com/sharplync',
        ],
    ],
];
