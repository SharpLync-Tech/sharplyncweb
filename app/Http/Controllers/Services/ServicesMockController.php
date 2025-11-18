<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;

class ServicesMockController extends Controller
{
    public function index()
    {
        $categories = [
            [
                'id'    => 1,
                'title' => 'Remote Support',
                'icon'  => '/images/support.png',
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
                'icon'  => '/images/security.png',
                'short' => 'Stay protected 24/7.',
                'long'  => 'We keep your business safe with layered security, active monitoring, and modern authentication methods.',
                'subs'  => [
                    'Antivirus management',
                    '2FA setup',
                    'Security monitoring',
                ],
            ],
            // Add more mock categories here
        ];

        return view('services.mock', compact('categories'));
    }
}
