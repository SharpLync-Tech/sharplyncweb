<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;

class ServicesController extends Controller
{
    public function index()
    {
        $categories = [
            [
                'id'    => 'remote-support',
                'title' => 'Remote Support',
                'short' => 'Instant help wherever you are.',
                'long'  => 'Fast, friendly remote support to keep your people working, without waiting days for someone to show up. Screensharing, quick fixes, and real humans on the other end.',
                'subs'  => [
                    'Remote troubleshooting & fixes',
                    'Application and OS support',
                    'Printer, email & access issues',
                ],
                'icon'  => asset('images/support.png'),
                'image' => asset('images/remote_support.png'),
            ],

            [
                'id'    => 'cybersecurity',
                'title' => 'Cybersecurity',
                'short' => 'Stay protected 24/7.',
                'long'  => 'Layered security that protects your people, devices and data, without the scare tactics. Practical security that fits how you actually work.',
                'subs'  => [
                    'Managed antivirus / endpoint protection',
                    '2FA / MFA rollout & support',
                    'Security monitoring & alerting',
                    'Security reviews & hardening',
                ],
                'icon'  => asset('images/security.png'),
                'image' => asset('images/cyber_security.png'),
            ],

            [
                'id'    => 'cloud-m365',
                'title' => 'Cloud & Microsoft 365',
                'short' => 'Email, files and collaboration that just works.',
                'long'  => 'We look after Microsoft 365, Azure and your core cloud apps so your team can log in, get to their stuff, and get on with their day.',
                'subs'  => [
                    'Microsoft 365 setup & admin',
                    'Licensing & tenant management',
                    'SharePoint / OneDrive structure',
                    'Teams setup and best-practice',
                ],
                'icon'  => asset('images/cloud.png'),
                'image' => asset('images/m365_cloud.png'),
            ],

            [
                'id'    => 'onsite-support',
                'title' => 'On-Site Support (Local Region)',
                'short' => 'Boots-on-ground support where it makes sense.',
                'long'  => 'When the job needs a human on site — cabling, hardware swaps, WiFi tuning — we provide on-site support across the Granite Belt and surrounds by arrangement.',
                'subs'  => [
                    'On-site fault finding & fixes',
                    'New equipment rollouts',
                    'Office moves & changes',
                ],
                'icon'  => asset('images/boots.png'),
                'image' => asset('images/onsite_support.png'),
            ],

            [
                'id'    => 'network-wifi',
                'title' => 'Networking & Wi-Fi',
                'short' => 'Solid networks. No mystery drop-outs.',
                'long'  => 'From modem to meeting room, we design and support networks that stay up, stay fast and stay secure — wired, wireless and everything in between.',
                'subs'  => [
                    'Business-grade Wi-Fi design',
                    'Switching & VLAN configuration',
                    'Firewall & router management',
                ],
                'icon'  => asset('images/wifi.png'),
                'image' => asset('images/network.png'),
            ],

            [
                'id'    => 'backup-continuity',
                'title' => 'Backup & Business Continuity',
                'short' => 'If something breaks, you’re not stuck.',
                'long'  => 'We make sure your critical data is backed up, tested and recoverable — whether it lives on a server, in the cloud or on a laptop.',
                'subs'  => [
                    'Cloud & on-prem backups',
                    'Workstation & server protection',
                    'Recovery testing & run-books',
                ],
                'icon'  => asset('images/backup.png'),
                'image' => asset('images/backup-continuity.png'),


            ],

            [
                'id'    => 'endpoint-patching',
                'title' => 'Endpoint Protection & Patching',
                'short' => 'Laptops and PCs kept locked-down and up-to-date.',
                'long'  => 'We keep your devices healthy with monitored antivirus, patching and health checks — lowering risk without annoying your staff every 5 minutes.',
                'subs'  => [
                    'Managed antivirus / EDR',
                    'Windows & third-party patching',
                    'Health & compliance reporting',
                ],
                'icon'  => asset('images/protection.png'),
                'image' => asset('images/patching.png'),
            ],

            [
                'id'    => 'it-planning',
                'title' => 'IT Planning & Advisory',
                'short' => 'Clear, practical guidance — no buzzwords.',
                'long'  => 'Straight-talking advice on where to take your systems next: what to keep, what to replace and how to budget for it over time.',
                'subs'  => [
                    'IT roadmap & refresh planning',
                    'Budget planning & lifecycle advice',
                    'Vendor & project guidance',
                ],
                'icon'  => asset('images/consulting.png'),
                'image' => asset('images/it_consulting.png'),
            ],

              [
                'id'    => 'hardware-repair',
                'title' => 'Laptop & Desktop Repairs',
                'short' => 'Laptop screens, battery replacements & more.',
                'long'  => 'We source, repalcement parts and can fix laptops and desktops.',
                'subs'  => [
                    'Laptops, desktops, batteries & screens',
                    'Networking & Wi-Fi hardware',
                    'Pre-config & install to your standards',
                ],
                'icon'  => asset('images/repair.png'),
                'image' => asset('images/it_repair.png'),
            ],

            [
                'id'    => 'hardware-procurement',
                'title' => 'Hardware & Procurement',
                'short' => 'Business-ready gear, ordered and prepped for you.',
                'long'  => 'We source, configure and deliver the right hardware for the job — desktops, laptops, networking, and more — so it arrives ready for your staff to log in and go.',
                'subs'  => [
                    'New laptops, desktops & screens',
                    'Networking & Wi-Fi hardware',
                    'Pre-config & install to your standards',
                ],
                'icon'  => asset('images/sales.png'),
                'image' => asset('images/it_sales.png'),
            ],
        ];

        return view('service.services', compact('categories'));
    }
}
