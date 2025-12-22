<?php

use Illuminate\Support\Str;

return [

    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        // ==========================================================
        // SharpLync CMS (DEFAULT)
        // ==========================================================
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? [
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ] : [],
        ],

        // ==========================================================
        // SharpLync CRM
        // ==========================================================
        'crm' => [
            'driver' => 'mysql',
            'host' => env('CRM_DB_HOST'),
            'port' => env('CRM_DB_PORT', '3306'),
            'database' => env('CRM_DB_DATABASE'),
            'username' => env('CRM_DB_USERNAME'),
            'password' => env('CRM_DB_PASSWORD'),
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? [
                PDO::MYSQL_ATTR_SSL_CA => env('CRM_MYSQL_ATTR_SSL_CA', env('MYSQL_ATTR_SSL_CA')),
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ] : [],
        ],

        // ==========================================================
        // SharpLync Facilities
        // ==========================================================
        'sharplync_facilities' => [
            'driver' => 'mysql',
            'host' => env('FACILITIES_DB_HOST'),
            'port' => env('FACILITIES_DB_PORT', '3306'),
            'database' => env('FACILITIES_DB_DATABASE'),
            'username' => env('FACILITIES_DB_USERNAME'),
            'password' => env('FACILITIES_DB_PASSWORD'),
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? [
                PDO::MYSQL_ATTR_SSL_CA => env('FACILITIES_MYSQL_ATTR_SSL_CA', env('MYSQL_ATTR_SSL_CA')),
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ] : [],
        ],

    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel')) . '_database_'),
            'persistent' => false,
        ],

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],

        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
        ],
    ],
];
