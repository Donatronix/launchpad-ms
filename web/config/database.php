<?php
// use multiple database connection

return [
    'default' => 'launchpad',
    'migrations' => 'migrations',
    'connections' => [
        'launchpad' => [
            'driver' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD')
        ],
        'identity' => [
            'driver' => env('DB2_CONNECTION', 'mysql'),
            'host' => env('DB2_HOST', 'localhost'),
            'port' => env('DB2_PORT', '3306'),
            'database' => env('DB2_DATABASE'),
            'username' => env('DB2_USERNAME'),
            'password' => env('DB2_PASSWORD')
        ],
        'payments' => [
            'driver' => env('DB3_CONNECTION', 'mysql'),
            'host' => env('DB3_HOST', 'localhost'),
            'port' => env('DB3_PORT', '3306'),
            'database' => env('DB3_DATABASE'),
            'username' => env('DB3_USERNAME'),
            'password' => env('DB3_PASSWORD')
        ],
    ]
];
