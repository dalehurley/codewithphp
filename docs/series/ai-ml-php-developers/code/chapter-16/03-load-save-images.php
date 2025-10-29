<?php

declare(strict_types=1);

/**
 * Chapter 16, Step 3: Loading and Saving Images
 * 
 * Demonstrates how to load images from files, manipulate them,
 * and save them in different formats.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ImageLoader.php';

section('Loading and Saving Images');

$loader = new ImageLoader();

// Load original image
$inputPath = __DIR__ . '/data/landscape.jpg';
echo "Loading: {$inputPath}\n";

$image = measureTime(
    fn() => $loader->load($inputPath),
    'Image loading'
);

// Display image information
$info = $loader->getInfo($inputPath);
printArray($info, 'Image Information');

// Get dimensions
$dimensions = $loader->getDimensions($image);
echo "Dimensions: {$dimensions['width']} × {$dimensions['height']}\n\n";

// Save in different formats
section('Saving in Multiple Formats');

$outputDir = __DIR__ . '/output';

// Save as JPEG with different quality levels
$qualities = [100, 75, 50];
foreach ($qualities as $quality) {
    $outputPath = "{$outputDir}/landscape_q{$quality}.jpg";

    measureTime(
        fn() => $loader->save($image, $outputPath, $quality),
        "Saving JPEG (quality {$quality})"
    );

    $savedSize = filesize($outputPath);
    echo "  Size: " . formatFileSize($savedSize) . "\n";
    success("Saved: {$outputPath}");
    echo "\n";
}

// Save as PNG (lossless)
$pngPath = "{$outputDir}/landscape.png";
measureTime(
    fn() => $loader->save($image, $pngPath, 90),
    'Saving PNG'
);
$pngSize = filesize($pngPath);
echo "  Size: " . formatFileSize($pngSize) . "\n";
success("Saved: {$pngPath}");
echo "\n";

// Save as WEBP (modern format)
$webpPath = "{$outputDir}/landscape.webp";
measureTime(
    fn() => $loader->save($image, $webpPath, 80),
    'Saving WEBP'
);
$webpSize = filesize($webpPath);
echo "  Size: " . formatFileSize($webpSize) . "\n";
success("Saved: {$webpPath}");
echo "\n";

// Compare file sizes
section('Format Comparison');

$originalSize = filesize($inputPath);
echo "Original JPEG: " . formatFileSize($originalSize) . " (100%)\n";
echo "JPEG Q100:     " . formatFileSize($qualities[100] ?? $originalSize) . "\n";
echo "JPEG Q75:      " . formatFileSize(filesize("{$outputDir}/landscape_q75.jpg")) .
    " (" . round((filesize("{$outputDir}/landscape_q75.jpg") / $originalSize) * 100) . "%)\n";
echo "JPEG Q50:      " . formatFileSize(filesize("{$outputDir}/landscape_q50.jpg")) .
    " (" . round((filesize("{$outputDir}/landscape_q50.jpg") / $originalSize) * 100) . "%)\n";
echo "PNG:           " . formatFileSize($pngSize) .
    " (" . round(($pngSize / $originalSize) * 100) . "%)\n";
echo "WEBP:          " . formatFileSize($webpSize) .
    " (" . round(($webpSize / $originalSize) * 100) . "%)\n";
echo "\n";

// Load the saved images to verify they work
section('Verifying Saved Images');

$testFiles = [
    "{$outputDir}/landscape_q75.jpg",
    "{$outputDir}/landscape.png",
    "{$outputDir}/landscape.webp",
];

foreach ($testFiles as $file) {
    try {
        $testImage = $loader->load($file);
        $testDims = $loader->getDimensions($testImage);
        success(basename($file) . " - {$testDims['width']}×{$testDims['height']}");
        imagedestroy($testImage);
    } catch (Exception $e) {
        error(basename($file) . " - Failed: " . $e->getMessage());
    }
}
echo "\n";

// Demonstrate error handling
section('Error Handling');

try {
    $loader->load(__DIR__ . '/data/nonexistent.jpg');
    error('Should have thrown exception!');
} catch (RuntimeException $e) {
    success('Caught expected exception: ' . $e->getMessage());
}

try {
    $loader->save($image, __DIR__ . '/output/test.xyz', 90);
    error('Should have thrown exception for unsupported format!');
} catch (RuntimeException $e) {
    success('Caught expected exception: ' . $e->getMessage());
}
echo "\n";

// Clean up
imagedestroy($image);

section('Summary');
echo "✓ Successfully loaded JPEG image\n";
echo "✓ Saved image in JPEG, PNG, and WEBP formats\n";
echo "✓ Compared file sizes across formats\n";
echo "✓ Verified all saved images can be loaded\n";
echo "✓ Demonstrated error handling\n\n";

echo "Output files are in: {$outputDir}/\n\n";
