<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages. The "graph" mailer is now available for Microsoft 365 sending.
    |
    */
    'default' => env('MAIL_MAILER', 'graph'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application.
    | We’ve added a custom "graph" transport that uses the Microsoft Graph API
    | for sending emails through Azure AD instead of SMTP.
    |
    */
    'mailers' => [

        // ✅ Microsoft Graph Mailer
        'graph' => [
            'transport' => 'graph',
        ],

        // Standard SMTP (e.g. fallback)
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.office365.com'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env(
                'MAIL_EHLO_DOMAIN',
                parse_url((string) env('APP_URL', 'https://sharplync.com.au'), PHP_URL_HOST)
            ),
        ],

        // Logs all emails to laravel.log (for testing)
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        // Keeps emails in memory (mainly for tests)
        'array' => [
            'transport' => 'array',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | All emails sent by the application will use this sender address.
    |
    */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'info@sharplync.com.au'),
        'name' => env('MAIL_FROM_NAME', 'SharpLync'),
    ],

];