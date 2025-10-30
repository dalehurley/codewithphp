<?php

declare(strict_types=1);

/**
 * Simple test script to verify Docker setup is working
 */

echo "=== Docker Setup Test ===\n\n";

// Test 1: PHP Version
echo "1. PHP Version: " . PHP_VERSION . "\n";
if (version_compare(PHP_VERSION, '8.4.0', '>=')) {
    echo "   ✓ PHP 8.4+ detected\n\n";
} else {
    echo "   ✗ PHP 8.4+ required\n\n";
    exit(1);
}

// Test 2: Required Extensions
echo "2. Checking PHP Extensions:\n";
$required = ['redis', 'pcntl', 'sockets', 'json'];
$missing = [];

foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✓ {$ext}\n";
    } else {
        echo "   ✗ {$ext} (missing)\n";
        $missing[] = $ext;
    }
}

if (!empty($missing)) {
    echo "\n   Missing extensions: " . implode(', ', $missing) . "\n";
    exit(1);
}

echo "\n3. Environment Variables:\n";
$envVars = ['REDIS_HOST', 'REDIS_PORT', 'APP_ENV'];
foreach ($envVars as $var) {
    $value = getenv($var) ?: 'not set';
    echo "   {$var}: {$value}\n";
}

echo "\n4. Testing Redis Connection:\n";
try {
    $redis = new Redis();
    $host = getenv('REDIS_HOST') ?: 'localhost';
    $port = (int) (getenv('REDIS_PORT') ?: 6379);

    if ($redis->connect($host, $port, 2.0)) {
        echo "   ✓ Connected to Redis at {$host}:{$port}\n";

        if ($redis->ping()) {
            echo "   ✓ Redis PING successful\n";
        }

        $redis->close();
    } else {
        echo "   ✗ Could not connect to Redis\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ✗ Redis error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ All tests passed! Docker setup is working correctly.\n";
