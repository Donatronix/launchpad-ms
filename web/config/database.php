<?php
// use multiple database connection

return [
    'default' => 'launchpad',
    'migrations' => 'migrations',
    'connections' => [
        'launchpad' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        'identity' => [
            'driver' => 'mysql',
            'host' => env('DB2_HOST', 'localhost'),
            'port' => env('DB2_PORT', '3306'),
            'database' => env('DB2_DATABASE'),
            'username' => env('DB2_USERNAME'),
            'password' => env('DB2_PASSWORD'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
    ]
];
