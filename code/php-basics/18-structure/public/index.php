<?php

declare(strict_types=1);

/**
 * Front Controller - Application Entry Point
 * 
 * All HTTP requests are routed through this file.
 */

// Start session
session_start();

// Define paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');

// Load configuration
require CONFIG_PATH . '/config.php';

// Simple autoloader
spl_autoload_register(function (string $class) {
    $file = APP_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Load router
require APP_PATH . '/Router.php';

// Create router instance
$router = new Router();

// Define routes
require BASE_PATH . '/routes.php';

// Dispatch the request
$router->dispatch();
