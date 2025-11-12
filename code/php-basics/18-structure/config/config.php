<?php

declare(strict_types=1);

/**
 * Application Configuration
 */

return [
    // Application settings
    'app' => [
        'name' => 'My PHP App',
        'env' => 'development', // development, production
        'debug' => true,
        'url' => 'http://localhost:8000',
    ],

    // Database settings
    'database' => [
        'driver' => 'sqlite',
        'path' => BASE_PATH . '/database.sqlite',
        // For MySQL:
        // 'driver' => 'mysql',
        // 'host' => 'localhost',
        // 'database' => 'myapp',
        // 'username' => 'root',
        // 'password' => '',
        // 'charset' => 'utf8mb4',
    ],

    // View settings
    'view' => [
        'path' => APP_PATH . '/views',
        'cache' => BASE_PATH . '/storage/cache',
    ],
];
