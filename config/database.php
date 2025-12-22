<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    */
    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    */
    'connections' => [

        // ==========================================================
        // SQLite (Local Development / Testing)
        // ==========================================================
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        // ==========================================================
        // Default MySQL (SharpLync CMS)
        // ==========================================================
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'sharplync_cms'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql')
                ? array_filter([
                    PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                ])
                : [],
        ],

        // ==========================================================
        // SharpLync CRM (Customers, Users, Onboarding, etc.)
        // ==========================================================
        'crm' => [
            'driver' => 'mysql',
            'host' => env('CRM_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('CRM_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('CRM_DB_DATABASE', 'sharplync_crm'),
            'username' => env('CRM_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('CRM_DB_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket' => env('CRM_DB_SOCKET', env('DB_SOCKET', '')),
            'charset' => env('CRM_DB_CHARSET', env('DB_CHARSET', 'utf8mb4')),
            'collation' => env('CRM_DB_COLLATION', env('DB_COLLATION', 'utf8mb4_unicode_ci')),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql')
                ? array_filter([
                    PDO::MYSQL_ATTR_SSL_CA => env('CRM_MYSQL_ATTR_SSL_CA', env('MYSQL_ATTR_SSL_CA')),
                ])
                : [],
        ],

        // ==========================================================
        // SharpLync Facilities (Sites, Assets, Maintenance, etc.)
        // ==========================================================
        'sharplync_facilities' => [
            'driver' => 'mysql',
            'host' => env('FACILITIES_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('FACILITIES_DB_PORT', env('DB_PORT', '3306')),
            'database' => env('FACILITIES_DB_DATABASE', 'sharplync_facilities'),
            'username' => env('FACILITIES_DB_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('FACILITIES_DB_PASSWORD', env('DB_PASSWORD', '')),
            'unix_socket' => env('FACILITIES_DB_SOCKET', env('DB_SOCKET', '')),
            'charset' => env('FACILITIES_DB_CHARSET', env('DB_CHARSET', 'utf8mb4')),
            'collation' => env('FACILITIES_DB_COLLATION', env('DB_COLLATION', 'utf8mb4_unicode_ci')),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql')
                ? array_filter([
                    PDO::MYSQL_ATTR_SSL_CA => env('FACILITIES_MYSQL_ATTR_SSL_CA', env('MYSQL_ATTR_SSL_CA')),
                ])
                : [],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    */
    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    */
    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel')) . '_database_'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],
];
