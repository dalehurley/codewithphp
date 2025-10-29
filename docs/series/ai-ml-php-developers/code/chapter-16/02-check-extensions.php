<?php

declare(strict_types=1);

/**
 * Chapter 16, Step 2: Setting Up PHP Image Processing
 * 
 * This script verifies that your PHP installation has the necessary
 * extensions and features for image processing.
 */

require_once __DIR__ . '/helpers.php';

section('PHP Image Processing Setup Check');

// Check PHP version
$phpVersion = PHP_VERSION;
$minVersion = '8.4.0';
$versionOk = version_compare($phpVersion, $minVersion, '>=');

echo "PHP Version Check:\n";
echo "  Current: {$phpVersion}\n";
echo "  Required: {$minVersion}+\n";
echo "  Status: " . ($versionOk ? '✓ OK' : '❌ UPGRADE NEEDED') . "\n\n";

if (!$versionOk) {
    error('Please upgrade to PHP 8.4 or higher');
    exit(1);
}

// Check GD extension
section('GD Extension Check');

if (!extension_loaded('gd')) {
    error('GD extension is not installed');
    echo "\nInstallation instructions:\n";
    echo "  Ubuntu/Debian: sudo apt-get install php8.4-gd\n";
    echo "  macOS (Homebrew): brew install php@8.4\n";
    echo "  Windows: Enable extension=gd in php.ini\n\n";
    exit(1);
}

success('GD extension is installed');

// Get detailed GD information
$gdInfo = gd_info();

echo "\nGD Library Information:\n";
echo "  Version: " . ($gdInfo['GD Version'] ?? 'Unknown') . "\n";
echo "  FreeType Support: " . (($gdInfo['FreeType Support'] ?? false) ? '✓' : '❌') . "\n";
echo "  Font Path: " . ($gdInfo['FreeType Linkage'] ?? 'N/A') . "\n\n";

echo "Image Format Support:\n";
echo "  JPEG: " . (($gdInfo['JPEG Support'] ?? false) ? '✓ Yes' : '❌ No') . "\n";
echo "  PNG:  " . (($gdInfo['PNG Support'] ?? false) ? '✓ Yes' : '❌ No') . "\n";
echo "  GIF:  " . (($gdInfo['GIF Create Support'] ?? false) ? '✓ Yes' : '❌ No') . "\n";
echo "  WEBP: " . (($gdInfo['WebP Support'] ?? false) ? '✓ Yes' : '❌ No') . "\n";
echo "  BMP:  " . (($gdInfo['BMP Support'] ?? false) ? '✓ Yes' : '❌ No') . "\n\n";

// Check for Imagick (optional, more advanced)
section('Imagick Extension Check (Optional)');

if (extension_loaded('imagick')) {
    success('Imagick extension is installed');

    $imagick = new Imagick();
    $version = $imagick->getVersion();
    echo "  Version: " . ($version['versionString'] ?? 'Unknown') . "\n";

    $formats = $imagick->queryFormats();
    echo "  Supported formats: " . count($formats) . "\n";
    echo "  (JPEG, PNG, GIF, WEBP, TIFF, PDF, SVG, and many more)\n\n";
} else {
    echo "⚠️  Imagick extension not installed (optional)\n";
    echo "   GD is sufficient for this chapter, but Imagick provides more features.\n\n";
}

// Check memory limit
section('PHP Configuration');

$memoryLimit = ini_get('memory_limit');
echo "Memory Limit: {$memoryLimit}\n";

// Convert to bytes for comparison
$memoryBytes = 0;
if (preg_match('/^(\d+)(.)$/', $memoryLimit, $matches)) {
    $memoryBytes = $matches[1];
    $unit = $matches[2];
    $memoryBytes *= match ($unit) {
        'G' => 1024 * 1024 * 1024,
        'M' => 1024 * 1024,
        'K' => 1024,
        default => 1
    };
}

if ($memoryBytes < 128 * 1024 * 1024 && $memoryLimit !== '-1') {
    echo "⚠️  Memory limit may be too low for processing large images\n";
    echo "   Recommended: 128M or higher\n\n";
} else {
    success('Memory limit is adequate');
    echo "\n";
}

$maxFilesize = ini_get('upload_max_filesize');
$postMaxSize = ini_get('post_max_size');
echo "Upload Max Filesize: {$maxFilesize}\n";
echo "POST Max Size: {$postMaxSize}\n\n";

// Check if sample images exist
section('Sample Images Check');

$dataDir = __DIR__ . '/data';
$sampleImages = ['sample.jpg', 'landscape.jpg', 'face.jpg'];

if (!is_dir($dataDir)) {
    error("Data directory not found: {$dataDir}");
    echo "Run generate-sample-images.php to create sample images\n\n";
    exit(1);
}

foreach ($sampleImages as $filename) {
    $path = $dataDir . '/' . $filename;
    if (file_exists($path)) {
        $size = filesize($path);
        success("{$filename} exists (" . formatFileSize($size) . ")");
    } else {
        error("{$filename} not found");
    }
}
echo "\n";

// Check output directory
$outputDir = __DIR__ . '/output';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    success('Created output directory');
} else {
    success('Output directory exists');
}

if (!is_writable($outputDir)) {
    error('Output directory is not writable');
    exit(1);
}
echo "\n";

// Final summary
section('Setup Summary');

$allGood = $versionOk && extension_loaded('gd') &&
    ($memoryBytes >= 128 * 1024 * 1024 || $memoryLimit === '-1');

if ($allGood) {
    echo "🎉 Your environment is ready for Chapter 16!\n\n";
    echo "You can now:\n";
    echo "  ✓ Load and save images in multiple formats\n";
    echo "  ✓ Process images with GD library\n";
    echo "  ✓ Run all chapter examples\n\n";
} else {
    error('Some issues need to be resolved before continuing');
    echo "Please address the errors above and run this script again.\n\n";
    exit(1);
}
