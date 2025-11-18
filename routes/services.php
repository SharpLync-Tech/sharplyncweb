<?php

use Illuminate\Support\Facades\Route;

Route::get('/services-mock', function () {
    $categories = [
        [
            'id'    => 1,
            'title' => 'Remote Support',
            'icon'  => '/images/mock/remote.svg',
            'short' => 'Instant help wherever you are.',
            'long'  => 'Our remote support keeps your business moving with fast troubleshooting, software fixes, and ongoing helpdesk assistance.',
            'subs'  => [
                'Remote troubleshooting',
                'Software fixes & updates',
                'Performance checks',
            ],
        ],
        [
            'id'    => 2,
            'title' => 'Cybersecurity',
            'icon'  => '/images/mock/security.svg',
            'short' => 'Stay protected 24/7.',
            'long'  => 'We keep your business safe with layered security, active monitoring, and modern authentication methods.',
            'subs'  => [
                'Antivirus management',
                '2FA setup',
                'Security monitoring',
            ],
        ],

        // Add more mock categories later as needed
    ];

    return view('services.mock', compact('categories'));
});
