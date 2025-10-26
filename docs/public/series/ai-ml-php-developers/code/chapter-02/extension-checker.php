<?php

declare(strict_types=1);

/**
 * PHP Extension Checker
 * 
 * Checks for required and recommended PHP extensions for AI/ML work.
 * Provides detailed information about each extension and installation instructions.
 */

echo "\n🔌 PHP Extension Checker\n";
echo "=========================\n\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Loaded Extensions: " . count(get_loaded_extensions()) . "\n\n";

// Define required and optional extensions
$requiredExtensions = [
    'json' => [
        'description' => 'JSON encoding/decoding for data interchange',
        'usage' => 'Required for working with JSON datasets and API responses',
    ],
    'mbstring' => [
        'description' => 'Multi-byte string handling',
        'usage' => 'Essential for NLP and processing international text',
    ],
    'curl' => [
        'description' => 'HTTP client for making requests',
        'usage' => 'Needed for calling external ML APIs and services',
    ],
];

$recommendedExtensions = [
    'dom' => [
        'description' => 'DOM document manipulation',
        'usage' => 'Parsing HTML/XML data sources',
    ],
    'zip' => [
        'description' => 'ZIP archive handling',
        'usage' => 'Reading compressed dataset files',
    ],
    'openssl' => [
        'description' => 'Cryptographic functions',
        'usage' => 'Secure API communications (HTTPS)',
    ],
    'pdo' => [
        'description' => 'PHP Data Objects',
        'usage' => 'Database access for training data',
    ],
];

// Check required extensions
echo "REQUIRED EXTENSIONS\n";
echo "===================\n\n";

$missingRequired = [];

foreach ($requiredExtensions as $ext => $info) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✅' : '❌';

    echo "{$status} {$ext}\n";
    echo "   {$info['description']}\n";
    echo "   Use: {$info['usage']}\n";

    if (!$loaded) {
        $missingRequired[] = $ext;
        echo "   Status: ❌ NOT INSTALLED\n";
    } else {
        echo "   Status: ✅ Installed\n";
    }
    echo "\n";
}

// Check recommended extensions
echo "RECOMMENDED EXTENSIONS\n";
echo "======================\n\n";

$missingRecommended = [];

foreach ($recommendedExtensions as $ext => $info) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✅' : '⚠️';

    echo "{$status} {$ext}\n";
    echo "   {$info['description']}\n";
    echo "   Use: {$info['usage']}\n";

    if (!$loaded) {
        $missingRecommended[] = $ext;
        echo "   Status: ⚠️  Not installed (optional)\n";
    } else {
        echo "   Status: ✅ Installed\n";
    }
    echo "\n";
}

// Installation instructions
if (!empty($missingRequired) || !empty($missingRecommended)) {
    echo "=========================\n";
    echo "INSTALLATION INSTRUCTIONS\n";
    echo "=========================\n\n";

    if (!empty($missingRequired)) {
        echo "❌ Missing REQUIRED extensions:\n";
        echo "   " . implode(', ', $missingRequired) . "\n\n";
    }

    if (!empty($missingRecommended)) {
        echo "⚠️  Missing RECOMMENDED extensions:\n";
        echo "   " . implode(', ', $missingRecommended) . "\n\n";
    }

    $allMissing = array_merge($missingRequired, $missingRecommended);

    echo "Ubuntu/Debian:\n";
    foreach ($allMissing as $ext) {
        echo "  sudo apt install php8.4-{$ext}\n";
    }
    echo "\n";

    echo "macOS (Homebrew):\n";
    echo "  brew reinstall php@8.4\n";
    echo "  (Most extensions included by default)\n\n";

    echo "Windows:\n";
    echo "  Edit php.ini and uncomment:\n";
    foreach ($allMissing as $ext) {
        echo "    extension={$ext}\n";
    }
    echo "  Then restart your web server\n\n";

    echo "Find php.ini location:\n";
    echo "  php --ini\n\n";
}

// Summary
echo "=========================\n";
echo "SUMMARY\n";
echo "=========================\n\n";

$totalRequired = count($requiredExtensions);
$installedRequired = $totalRequired - count($missingRequired);

$totalRecommended = count($recommendedExtensions);
$installedRecommended = $totalRecommended - count($missingRecommended);

echo "Required: {$installedRequired}/{$totalRequired} installed\n";
echo "Recommended: {$installedRecommended}/{$totalRecommended} installed\n\n";

if (empty($missingRequired)) {
    echo "✅ All required extensions are installed!\n";
    echo "   You're ready for AI/ML development.\n\n";

    if (!empty($missingRecommended)) {
        echo "⚠️  Some recommended extensions are missing.\n";
        echo "   Install them for full functionality.\n\n";
    }
    exit(0);
} else {
    echo "❌ Missing required extensions!\n";
    echo "   Install them before proceeding.\n\n";
    exit(1);
}
