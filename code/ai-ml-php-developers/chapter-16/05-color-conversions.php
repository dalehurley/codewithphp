<?php

declare(strict_types=1);

/**
 * Chapter 16, Step 5: Color Space Conversions
 * 
 * Demonstrates converting between color spaces and
 * extracting color information from images.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ImageLoader.php';
require_once __DIR__ . '/ColorConverter.php';

section('Color Space Conversions');

$loader = new ImageLoader();
$converter = new ColorConverter();
$outputDir = __DIR__ . '/output';

// Load original image
$originalPath = __DIR__ . '/data/sample.jpg';
$original = $loader->load($originalPath);

echo "Loading: {$originalPath}\n\n";

// 1. Convert to grayscale
section('1. Grayscale Conversion');

echo "Converting to grayscale...\n";

$grayscale = $converter->toGrayscale($original);
$loader->save($grayscale, "{$outputDir}/sample_grayscale.jpg", 90);
success('Saved: sample_grayscale.jpg');

// Compare with luminosity method
$grayscaleLum = $converter->toGrayscaleLuminosity($original);
$loader->save($grayscaleLum, "{$outputDir}/sample_grayscale_luminosity.jpg", 90);
success('Saved: sample_grayscale_luminosity.jpg (luminosity method)');

echo "\nGrayscale is useful for:\n";
echo "  • Reducing data dimensions (3 channels → 1)\n";
echo "  • Simplifying ML models\n";
echo "  • Edge detection preprocessing\n\n";

imagedestroy($grayscale);
imagedestroy($grayscaleLum);

// 2. Extract color channels
section('2. Color Channel Extraction');

$redChannel = $converter->extractRedChannel($original);
$loader->save($redChannel, "{$outputDir}/sample_red_channel.jpg", 90);
success('Saved: sample_red_channel.jpg');
imagedestroy($redChannel);

$greenChannel = $converter->extractGreenChannel($original);
$loader->save($greenChannel, "{$outputDir}/sample_green_channel.jpg", 90);
success('Saved: sample_green_channel.jpg');
imagedestroy($greenChannel);

$blueChannel = $converter->extractBlueChannel($original);
$loader->save($blueChannel, "{$outputDir}/sample_blue_channel.jpg", 90);
success('Saved: sample_blue_channel.jpg');
imagedestroy($blueChannel);

echo "\nChannel extraction reveals:\n";
echo "  • Which colors dominate the image\n";
echo "  • Color distribution patterns\n";
echo "  • Useful for color-based ML features\n\n";

// 3. Get average color
section('3. Average Color Analysis');

$avgColor = $converter->getAverageColor($original);
printColor($avgColor, 'Average Color');

echo "\nThis represents the overall color tone of the image.\n";
echo "Useful for quick color-based categorization.\n\n";

// 4. Get dominant color
section('4. Dominant Color Detection');

$dominantColor = $converter->getDominantColor($original, 5);
printColor($dominantColor, 'Dominant Color');

echo "\nDominant color is the most frequent color in the image.\n";
echo "Different from average color (which can be a mix).\n\n";

// 5. Brightness and contrast adjustments
section('5. Brightness and Contrast Adjustments');

// Brightness variations
$brightnessLevels = [-50, 50];
foreach ($brightnessLevels as $level) {
    $adjusted = $loader->load($originalPath); // Fresh copy
    $adjusted = $converter->adjustBrightness($adjusted, $level);

    $filename = $level > 0 ? "sample_bright_+{$level}.jpg" : "sample_bright_{$level}.jpg";
    $loader->save($adjusted, "{$outputDir}/{$filename}", 90);
    success("Saved: {$filename} (brightness {$level})");
    imagedestroy($adjusted);
}
echo "\n";

// Contrast variations
$contrastLevels = [-30, 30];
foreach ($contrastLevels as $level) {
    $adjusted = $loader->load($originalPath); // Fresh copy
    $adjusted = $converter->adjustContrast($adjusted, $level);

    $filename = $level > 0 ? "sample_contrast_+{$level}.jpg" : "sample_contrast_{$level}.jpg";
    $loader->save($adjusted, "{$outputDir}/{$filename}", 90);
    success("Saved: {$filename} (contrast {$level})");
    imagedestroy($adjusted);
}
echo "\n";

// 6. Compare multiple images
section('6. Color Comparison Across Images');

$images = [
    'sample.jpg' => __DIR__ . '/data/sample.jpg',
    'landscape.jpg' => __DIR__ . '/data/landscape.jpg',
    'face.jpg' => __DIR__ . '/data/face.jpg',
];

echo "Comparing color properties:\n\n";

foreach ($images as $name => $path) {
    $img = $loader->load($path);
    $avg = $converter->getAverageColor($img);
    $dominant = $converter->getDominantColor($img);

    echo "{$name}:\n";
    printf("  Average:  RGB(%d, %d, %d)\n", $avg['r'], $avg['g'], $avg['b']);
    printf("  Dominant: RGB(%d, %d, %d)\n", $dominant['r'], $dominant['g'], $dominant['b']);
    echo "\n";

    imagedestroy($img);
}

// Clean up
imagedestroy($original);

// Summary
section('Summary');

echo "✓ Converted images to grayscale (two methods)\n";
echo "✓ Extracted individual color channels (R, G, B)\n";
echo "✓ Calculated average and dominant colors\n";
echo "✓ Adjusted brightness and contrast\n";
echo "✓ Compared color properties across images\n\n";

echo "All color conversions saved to: {$outputDir}/\n\n";

echo "Why color conversions matter for ML:\n";
echo "  • Grayscale reduces input dimensions by 66%\n";
echo "  • Color statistics provide simple features\n";
echo "  • Channel separation reveals patterns\n";
echo "  • Normalization improves model training\n\n";
