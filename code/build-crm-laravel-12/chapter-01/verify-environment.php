<?php

/**
 * Environment Verification Script for Build a CRM with Laravel 12
 *
 * This script checks your development environment to ensure you have all
 * required tools and versions for the Build a CRM with Laravel 12 series.
 *
 * Usage: php verify-environment.php
 *
 * Exit Codes:
 *   0 = Success: All required checks passed, environment is ready
 *   1 = Failure: One or more required checks failed, setup needed
 *
 * Required for series:
 *   - PHP 8.4.0+
 *   - Composer 2.0.0+
 *   - Node.js 18.0.0+
 *   - PHP Extensions: pdo, pdo_mysql, mbstring, json, openssl, curl, tokenizer
 *
 * Recommended but optional:
 *   - Docker Desktop (for Laravel Sail development environment)
 */

declare(strict_types=1);

// Configuration - required versions
const REQUIRED_PHP_VERSION = '8.4.0';
const REQUIRED_NODE_VERSION = '18.0.0';
const REQUIRED_COMPOSER_VERSION = '2.0.0';

// Colors for terminal output
const COLOR_GREEN = "\033[92m";
const COLOR_RED = "\033[91m";
const COLOR_YELLOW = "\033[93m";
const COLOR_BLUE = "\033[94m";
const COLOR_RESET = "\033[0m";
const COLOR_BOLD = "\033[1m";

// Check counters
$passed = 0;
$failed = 0;
$warnings = 0;

echo COLOR_BOLD . "Build a CRM with Laravel 12 - Environment Verification" . COLOR_RESET . "\n";
echo str_repeat("=", 60) . "\n\n";

/**
 * Display a check result with status
 */
function displayCheck(string $name, bool $checkPassed, string $message = '', ?string $version = null): void {
    global $passed, $failed, $warnings;

    if ($checkPassed) {
        $passed++;
        $status = COLOR_GREEN . "✓ PASS" . COLOR_RESET;
    } else {
        $failed++;
        $status = COLOR_RED . "✗ FAIL" . COLOR_RESET;
    }

    echo $status . " | " . COLOR_BOLD . $name . COLOR_RESET;

    if ($version) {
        echo " | Version: " . $version;
    }

    if ($message) {
        echo "\n         " . $message;
    }

    echo "\n";
}

/**
 * Compare two version strings
 */
function versionCompare(string $version1, string $version2): int {
    return version_compare($version1, $version2);
}

/**
 * Check PHP version
 */
echo COLOR_BLUE . "1. PHP Version Check" . COLOR_RESET . "\n";
echo str_repeat("-", 60) . "\n";

$phpVersion = PHP_VERSION;
$phpPassed = versionCompare($phpVersion, REQUIRED_PHP_VERSION) >= 0;

$phpMsg = $phpPassed ? "Great! You have PHP 8.4+ installed." : "Please install PHP " . REQUIRED_PHP_VERSION . " or higher.";
displayCheck(
    "PHP",
    $phpPassed,
    $phpMsg,
    $phpVersion
);

echo "\n";

/**
 * Check required PHP extensions
 */
echo COLOR_BLUE . "2. PHP Extensions Check" . COLOR_RESET . "\n";
echo str_repeat("-", 60) . "\n";

$requiredExtensions = [
    'pdo' => 'PDO (database access)',
    'pdo_mysql' => 'PDO MySQL (MySQL driver)',
    'mbstring' => 'Multibyte String',
    'json' => 'JSON support',
    'openssl' => 'OpenSSL (security)',
    'curl' => 'cURL (HTTP requests)',
    'tokenizer' => 'Tokenizer (Laravel requirement)',
];

$allExtensionsPassed = true;
foreach ($requiredExtensions as $ext => $name) {
    $ext_loaded = extension_loaded($ext);
    $msg = $ext_loaded ? "" : "Install the $name extension";
    displayCheck($name, $ext_loaded, $msg);
    if (!$ext_loaded) {
        $allExtensionsPassed = false;
    }
}

echo "\n";

/**
 * Check Composer
 */
echo COLOR_BLUE . "3. Composer Check" . COLOR_RESET . "\n";
echo str_repeat("-", 60) . "\n";

$composerPath = shell_exec('which composer 2>/dev/null') ?: 'Not found';
$composerPath = trim($composerPath);
$composerExists = $composerPath !== 'Not found' && file_exists($composerPath);

if ($composerExists) {
    $composerVersion = trim(shell_exec('composer --version 2>&1') ?: '');
    if (preg_match('/Composer version ([\d.]+)/', $composerVersion, $matches)) {
        $version = $matches[1];
        $composerPassed = versionCompare($version, REQUIRED_COMPOSER_VERSION) >= 0;
        displayCheck(
            "Composer",
            $composerPassed,
            $composerPassed ? "Composer is installed and ready." : "Please update Composer to version " . REQUIRED_COMPOSER_VERSION . " or higher.",
            $version
        );
    } else {
        displayCheck("Composer", false, "Could not determine Composer version", "Unknown");
    }
} else {
    displayCheck("Composer", false, "Composer is not installed. Download from: https://getcomposer.org/download/");
}

echo "\n";

/**
 * Check Node.js
 */
echo COLOR_BLUE . "4. Node.js Check" . COLOR_RESET . "\n";
echo str_repeat("-", 60) . "\n";

$nodePath = shell_exec('which node 2>/dev/null') ?: 'Not found';
$nodePath = trim($nodePath);
$nodeExists = $nodePath !== 'Not found' && file_exists($nodePath);

if ($nodeExists) {
    $nodeVersion = trim(shell_exec('node --version 2>&1') ?: '');
    // Remove 'v' prefix from version string
    $nodeVersion = ltrim($nodeVersion, 'v');
    $nodePassed = versionCompare($nodeVersion, REQUIRED_NODE_VERSION) >= 0;
    displayCheck(
        "Node.js",
        $nodePassed,
        $nodePassed ? "Node.js is installed and ready." : "Please update Node.js to version " . REQUIRED_NODE_VERSION . " or higher.",
        $nodeVersion
    );
} else {
    displayCheck("Node.js", false, "Node.js is not installed. Download from: https://nodejs.org/", "Not found");
}

echo "\n";

/**
 * Check Docker (optional but recommended)
 */
echo COLOR_BLUE . "5. Docker Check (Optional but Recommended)" . COLOR_RESET . "\n";
echo str_repeat("-", 60) . "\n";

$dockerPath = shell_exec('which docker 2>/dev/null') ?: 'Not found';
$dockerPath = trim($dockerPath);
$dockerExists = $dockerPath !== 'Not found' && file_exists($dockerPath);

if ($dockerExists) {
    $dockerVersion = trim(shell_exec('docker --version 2>&1') ?: '');
    displayCheck("Docker", true, "Docker is installed. This is recommended for Laravel Sail.", $dockerVersion);
} else {
    echo COLOR_YELLOW . "⚠ WARNING" . COLOR_RESET . " | Docker is not installed\n";
    echo "         You can still follow this series, but Laravel Sail (recommended development\n";
    echo "         environment) requires Docker. Install from: https://www.docker.com/products/docker-desktop/\n";
    $warnings++;
}

echo "\n";

/**
 * Summary
 */
echo str_repeat("=", 60) . "\n";
echo COLOR_BOLD . "Summary" . COLOR_RESET . "\n";
echo str_repeat("=", 60) . "\n";

$total = $passed + $failed;
echo "Passed: " . COLOR_GREEN . "$passed" . COLOR_RESET . " | ";
echo "Failed: " . COLOR_RED . "$failed" . COLOR_RESET;

if ($warnings > 0) {
    echo " | Warnings: " . COLOR_YELLOW . "$warnings" . COLOR_RESET;
}

echo "\n\n";

if ($failed === 0) {
    echo COLOR_GREEN . COLOR_BOLD . "✓ Excellent! Your environment is ready for the series." . COLOR_RESET . "\n";
    echo "\nYou can now proceed with Chapter 02: Installation & Environment Setup.\n";
    exit(0);
} else {
    echo COLOR_RED . COLOR_BOLD . "✗ Some issues were found. Please address the failures above." . COLOR_RESET . "\n";
    echo "\nInstall missing tools and re-run this script to verify.\n";
    exit(1);
}

