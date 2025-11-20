<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This controls the default mailer your application uses.
    | We are switching to Mailgun for all outgoing emails.
    |
    */
    'default' => env('MAIL_MAILER', 'mailgun'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | These are all the mailers your application knows about.
    | We removed the old Microsoft Graph transport entirely and added Mailgun.
    |
    */
    'mailers' => [

        // ✅ Mailgun Mailer (Primary)
        'mailgun' => [
            'transport' => 'mailgun',
        ],

        // Optional fallback (Office365 SMTP or Gmail if added later)
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

        // Log driver (for debugging)
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        // Array driver (for tests)
        'array' => [
            'transport' => 'array',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | All emails sent by your app will use this.
    |
    */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@sharplync.com.au'),
        'name' => env('MAIL_FROM_NAME', 'SharpLync Support'),
    ],

];
