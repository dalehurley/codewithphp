<?php

declare(strict_types=1);

/**
 * Quick Start Environment Verification
 * 
 * A minimal 2-minute check to verify your PHP environment is ready for AI/ML work.
 * For comprehensive verification, run verify-installation.php instead.
 */

echo "\n⚡ Quick Environment Check\n";
echo "==========================\n\n";

$checks = ['passed' => 0, 'failed' => 0];

// Check 1: PHP Version
echo "1. PHP Version... ";
$phpVersion = PHP_VERSION;
$versionOk = version_compare($phpVersion, '8.6.0', '>=');

if ($versionOk) {
    echo "✅ {$phpVersion}\n";
    $checks['passed']++;
} else {
    echo "❌ {$phpVersion} (need 8.6+)\n";
    $checks['failed']++;
}

// Check 2: Critical Extensions
echo "2. Critical Extensions...\n";
$requiredExtensions = ['json', 'mbstring', 'curl'];

foreach ($requiredExtensions as $ext) {
    echo "   {$ext}... ";
    if (extension_loaded($ext)) {
        echo "✅\n";
        $checks['passed']++;
    } else {
        echo "❌\n";
        $checks['failed']++;
    }
}

// Check 3: Composer
echo "3. Composer... ";
exec('composer --version 2>&1', $output, $returnCode);
if ($returnCode === 0) {
    echo "✅ Installed\n";
    $checks['passed']++;
} else {
    echo "❌ Not found\n";
    $checks['failed']++;
}

// Summary
echo "\n==========================\n";
if ($checks['failed'] === 0) {
    echo "🎉 All checks passed!\n";
    echo "Your environment is ready.\n\n";
    echo "Next step: Run the comprehensive check\n";
    echo "  → php verify-installation.php\n\n";
    exit(0);
} else {
    $total = $checks['passed'] + $checks['failed'];
    echo "⚠️  {$checks['failed']} issue(s) found\n";
    echo "Passed: {$checks['passed']}/{$total}\n\n";
    echo "Please fix the issues above.\n";
    echo "See Chapter 2 for installation instructions.\n\n";
    exit(1);
}
