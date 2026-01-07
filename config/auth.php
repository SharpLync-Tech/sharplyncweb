<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Authentication Settings
    |--------------------------------------------------------------------------
    |
    | By default, the app will use the "customer" guard and "crm_users" 
    | provider so authentication flows use your CRM-linked user model.
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
    | Define guards for your app.
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

        'api' => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | CRM users come from your CRM DB. Internal/admin users come from CMS DB.
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
    | HERE is where we tell Laravel which DB connection the broker should use.
    | This is the fix.
    |
    */

    'passwords' => [

        'crm_users' => [
            'provider' => 'crm_users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
            'connection' => 'crm',   // 👈 CRITICAL FIX — NOW RESET TOKENS USE CRM DB
        ],

        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
            // Admin/CMS resets remain on default MySQL (CMS DB)
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
