<?php

declare(strict_types=1);

/**
 * PHP Environment Verification Script
 * 
 * Checks PHP version, extensions, and dependencies.
 */

echo "PHP Environment Verification\n";
echo "============================\n\n";

// Check PHP version
echo "1. PHP Version:\n";
$version = PHP_VERSION;
$versionOk = version_compare($version, '8.4.0', '>=');
echo "   " . ($versionOk ? '✓' : '✗') . " PHP {$version}\n";

if (!$versionOk) {
    echo "   ⚠️  PHP 8.4+ required\n";
}

echo "\n";

// Check extensions
echo "2. Required Extensions:\n";
$required = [
    'pdo' => 'Database access',
    'json' => 'JSON parsing',
    'mbstring' => 'Multi-byte strings',
    'curl' => 'HTTP requests',
    'intl' => 'Internationalization',
];

$allExtensionsOk = true;
foreach ($required as $ext => $purpose) {
    $loaded = extension_loaded($ext);
    $allExtensionsOk = $allExtensionsOk && $loaded;
    $status = $loaded ? '✓' : '✗';
    echo "   {$status} {$ext} ({$purpose})\n";
}

echo "\n";

// Check Composer
echo "3. Composer Dependencies:\n";
if (file_exists(__DIR__ . '/../../project-structure/vendor/autoload.php')) {
    echo "   ✓ Composer dependencies installed\n";
    
    // Check key packages
    require __DIR__ . '/../../project-structure/vendor/autoload.php';
    
    $packages = [
        'MathPHP\Statistics\Average' => 'markrogoyski/math-php',
        'League\Csv\Reader' => 'league/csv',
        'GuzzleHttp\Client' => 'guzzlehttp/guzzle',
        'Dotenv\Dotenv' => 'vlucas/phpdotenv',
        'Carbon\Carbon' => 'nesbot/carbon',
    ];
    
    foreach ($packages as $class => $package) {
        $exists = class_exists($class);
        $status = $exists ? '✓' : '✗';
        echo "   {$status} {$package}\n";
    }
} else {
    echo "   ✗ Composer dependencies not installed\n";
    echo "   Run: cd project-structure && composer install\n";
    $allExtensionsOk = false;
}

echo "\n";

// Check data directories
echo "4. Directory Structure:\n";
$dirs = [
    'project-structure/data/raw',
    'project-structure/data/processed',
    'project-structure/output/reports',
];

foreach ($dirs as $dir) {
    $path = __DIR__ . '/../../' . $dir;
    $exists = is_dir($path);
    $status = $exists ? '✓' : '✗';
    echo "   {$status} {$dir}\n";
}

echo "\n";

// Summary
echo "Summary:\n";
if ($versionOk && $allExtensionsOk) {
    echo "✓ PHP environment is ready for data science work!\n";
} else {
    echo "✗ Some issues need to be resolved.\n";
    exit(1);
}


