<?php

declare(strict_types=1);

/**
 * Chapter 16, Step 1: Understanding Images as Data
 * 
 * This example demonstrates how images are represented in PHP:
 * - Pixels and dimensions
 * - Color channels (RGB)
 * - Image properties
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ImageLoader.php';

section('Understanding Images as Data');

// Check if GD extension is available
if (!checkGdExtension()) {
    exit(1);
}

// Display GD information
$gdInfo = getGdInfo();
echo "GD Version: {$gdInfo['version']}\n";
echo "Supported formats:\n";
echo "  JPEG: " . ($gdInfo['jpeg_support'] ? '✓' : '❌') . "\n";
echo "  PNG:  " . ($gdInfo['png_support'] ? '✓' : '❌') . "\n";
echo "  GIF:  " . ($gdInfo['gif_support'] ? '✓' : '❌') . "\n";
echo "  WEBP: " . ($gdInfo['webp_support'] ? '✓' : '❌') . "\n\n";

// Load a sample image
$loader = new ImageLoader();
$imagePath = __DIR__ . '/data/sample.jpg';

echo "Loading image: {$imagePath}\n\n";

// Get image information
$info = $loader->getInfo($imagePath);
echo "Image Information:\n";
printf("  Dimensions: %d × %d pixels\n", $info['width'], $info['height']);
printf("  Total pixels: %s\n", number_format($info['pixels']));
printf("  Format: %s\n", $info['type']);
printf("  MIME type: %s\n", $info['mime']);
printf("  Color depth: %d bits\n", $info['bits']);
printf("  File size: %s\n", formatFileSize($info['filesize']));
echo "\n";

// Load the actual image
$image = $loader->load($imagePath);

// Sample pixels from different locations
section('Sampling Pixel Colors');

$samplePoints = [
    ['x' => 50, 'y' => 50, 'label' => 'Top-left region'],
    ['x' => $info['width'] / 2, 'y' => $info['height'] / 2, 'label' => 'Center'],
    ['x' => $info['width'] - 50, 'y' => 50, 'label' => 'Top-right region'],
    ['x' => 50, 'y' => $info['height'] - 50, 'label' => 'Bottom-left region'],
];

foreach ($samplePoints as $point) {
    $x = (int)$point['x'];
    $y = (int)$point['y'];
    $color = $loader->getPixelColor($image, $x, $y);

    echo "{$point['label']} (x:{$x}, y:{$y}):\n";
    printf("  Red channel:   %3d (%.1f%%)\n", $color['r'], ($color['r'] / 255) * 100);
    printf("  Green channel: %3d (%.1f%%)\n", $color['g'], ($color['g'] / 255) * 100);
    printf("  Blue channel:  %3d (%.1f%%)\n", $color['b'], ($color['b'] / 255) * 100);
    echo "\n";
}

// Explain what we learned
section('Key Concepts');
echo "✓ Images are 2D grids of pixels\n";
echo "✓ Each pixel has RGB color values (0-255)\n";
echo "✓ Total data points = width × height × 3 (for RGB)\n";
echo "✓ For this image: " . number_format($info['pixels'] * 3) . " values!\n\n";

// Calculate memory requirements
$memoryBytes = $info['pixels'] * 4; // 4 bytes per pixel (RGBA)
echo "Memory required: " . formatFileSize($memoryBytes) . " (uncompressed)\n";
echo "File size: " . formatFileSize($info['filesize']) . " (compressed)\n";
echo "Compression ratio: " . number_format($memoryBytes / $info['filesize'], 1) . ":1\n\n";

// Clean up
imagedestroy($image);

success('Image representation demonstration complete!');
