<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Authentication Settings
    |--------------------------------------------------------------------------
    |
    | By default, the app will use the "customer" guard and "crm_users" provider
    | so authentication flows use your CRM-linked user model.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'customer'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'crm_users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Define guards for your app. We keep only two:
    | - "web" (optional) for internal/admin
    | - "customer" for your CRM user accounts
    |
    */

    'guards' => [
        'customer' => [
            'driver' => 'session',
            'provider' => 'crm_users',
        ],

        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Providers tell Laravel how to fetch user records.
    | CRM users use App\Models\CRM\User and connect to your CRM DB.
    |
    */

    'providers' => [
        'crm_users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\CRM\User::class),
        ],

        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Reset Configuration
    |--------------------------------------------------------------------------
    |
    | Password reset table and expiration times for each user type.
    |
    */

    'passwords' => [
        'crm_users' => [
            'provider' => 'crm_users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Timeout
    |--------------------------------------------------------------------------
    |
    | Number of seconds before a password confirmation times out.
    | (3 hours by default)
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];