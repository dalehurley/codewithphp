<?php

declare(strict_types=1);

/**
 * Composer Setup Verification
 * 
 * Verifies that Composer is installed and working correctly.
 * Also checks for common Composer configuration issues.
 */

echo "\n🎵 Composer Setup Verification\n";
echo "================================\n\n";

// Check 1: Composer executable
echo "1. Checking Composer installation...\n";
exec('composer --version 2>&1', $output, $returnCode);

if ($returnCode !== 0) {
    echo "   ❌ Composer not found\n\n";
    echo "Installation instructions:\n";
    echo "  macOS/Linux:\n";
    echo "    curl -sS https://getcomposer.org/installer | php\n";
    echo "    sudo mv composer.phar /usr/local/bin/composer\n\n";
    echo "  Windows:\n";
    echo "    Download and run: https://getcomposer.org/Composer-Setup.exe\n\n";
    exit(1);
}

echo "   ✅ Composer found\n";
echo "   Version: " . $output[0] . "\n\n";

// Check 2: Composer version
$versionLine = $output[0];
if (preg_match('/Composer version (\d+\.\d+)/', $versionLine, $matches)) {
    $version = $matches[1];
    $versionOk = version_compare($version, '2.0', '>=');

    echo "2. Checking Composer version...\n";
    if ($versionOk) {
        echo "   ✅ Version {$version} (Composer 2.x)\n\n";
    } else {
        echo "   ⚠️  Version {$version} (old)\n";
        echo "   Recommendation: Update to Composer 2.x\n";
        echo "   Run: composer self-update\n\n";
    }
}

// Check 3: Composer memory limit
echo "3. Checking memory configuration...\n";
$memoryLimit = getenv('COMPOSER_MEMORY_LIMIT');
if ($memoryLimit === '-1' || $memoryLimit === false) {
    if ($memoryLimit === '-1') {
        echo "   ✅ Unlimited memory configured\n\n";
    } else {
        echo "   ⚠️  No memory limit set\n";
        echo "   Recommendation: Set unlimited for ML libraries\n";
        echo "   Run: export COMPOSER_MEMORY_LIMIT=-1\n";
        echo "   Add to ~/.bashrc or ~/.zshrc to make permanent\n\n";
    }
} else {
    echo "   ⚠️  Memory limit: {$memoryLimit}\n";
    echo "   May cause issues with large ML libraries\n";
    echo "   Recommendation: Set to -1 (unlimited)\n\n";
}

// Check 4: composer.json exists
echo "4. Checking for composer.json...\n";
if (file_exists(__DIR__ . '/composer.json')) {
    echo "   ✅ Found composer.json\n\n";

    // Check 5: Vendor directory
    echo "5. Checking vendor directory...\n";
    if (is_dir(__DIR__ . '/vendor')) {
        echo "   ✅ Vendor directory exists\n";

        // Check autoloader
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            echo "   ✅ Autoloader found\n\n";

            // Try to require it
            echo "6. Testing autoloader...\n";
            try {
                require __DIR__ . '/vendor/autoload.php';
                echo "   ✅ Autoloader works\n\n";
            } catch (Exception $e) {
                echo "   ❌ Autoloader error: {$e->getMessage()}\n\n";
            }
        } else {
            echo "   ❌ Autoloader not found\n";
            echo "   Run: composer dump-autoload\n\n";
        }
    } else {
        echo "   ❌ Vendor directory not found\n";
        echo "   Run: composer install\n\n";
    }
} else {
    echo "   ⚠️  No composer.json in this directory\n";
    echo "   This is normal if you haven't initialized a project yet\n";
    echo "   Run: composer init\n\n";
}

// Summary
echo "================================\n";
echo "Composer Status: ";
if ($returnCode === 0) {
    echo "✅ Working\n\n";
    echo "You can now install packages:\n";
    echo "  composer require php-ai/php-ml\n";
    echo "  composer require rubix/ml\n\n";
} else {
    echo "❌ Needs attention\n\n";
    echo "Fix the issues above before proceeding.\n\n";
}
