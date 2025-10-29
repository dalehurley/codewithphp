<?php

declare(strict_types=1);

/**
 * Chapter 16, Step 7: Image Filters and Effects
 * 
 * Demonstrates various filters and effects that can be applied
 * to images, useful for preprocessing and creative applications.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ImageLoader.php';
require_once __DIR__ . '/ImageFilter.php';

section('Image Filters and Effects');

$loader = new ImageLoader();
$filter = new ImageFilter();
$outputDir = __DIR__ . '/output';

// Load original image
$originalPath = __DIR__ . '/data/landscape.jpg';

// 1. Blur filters
section('1. Blur Effects');

$blurLevels = [1, 3, 5];
foreach ($blurLevels as $passes) {
    $img = $loader->load($originalPath);
    $blurred = $filter->blur($img, $passes);

    $filename = "landscape_blur_{$passes}.jpg";
    $loader->save($blurred, "{$outputDir}/{$filename}", 85);
    success("Saved: {$filename} ({$passes} blur passes)");
    imagedestroy($blurred);
}
echo "\n";

// Selective blur
$img = $loader->load($originalPath);
$selectiveBlur = $filter->selectiveBlur($img, 2);
$loader->save($selectiveBlur, "{$outputDir}/landscape_selective_blur.jpg", 85);
success('Saved: landscape_selective_blur.jpg (preserves edges better)');
imagedestroy($selectiveBlur);
echo "\n";

echo "Blur is useful for:\n";
echo "  • Noise reduction\n";
echo "  • Background effects\n";
echo "  • Privacy (blurring faces/plates)\n\n";

// 2. Sharpen
section('2. Sharpening');

$img = $loader->load($originalPath);
$sharpened = $filter->sharpen($img);
$loader->save($sharpened, "{$outputDir}/landscape_sharpen.jpg", 85);
success('Saved: landscape_sharpen.jpg (built-in sharpen)');
imagedestroy($sharpened);

$img = $loader->load($originalPath);
$sharpenCustom = $filter->sharpenCustom($img, 0.7);
$loader->save($sharpenCustom, "{$outputDir}/landscape_sharpen_custom.jpg", 85);
success('Saved: landscape_sharpen_custom.jpg (custom sharpen)');
imagedestroy($sharpenCustom);
echo "\n";

// 3. Edge detection
section('3. Edge Detection');

$img = $loader->load($originalPath);
$edges = $filter->edgeDetect($img);
$loader->save($edges, "{$outputDir}/landscape_edges.jpg", 85);
success('Saved: landscape_edges.jpg');
imagedestroy($edges);

$img = $loader->load($originalPath);
$edgeEnhanced = $filter->edgeEnhance($img);
$loader->save($edgeEnhanced, "{$outputDir}/landscape_edge_enhance.jpg", 85);
success('Saved: landscape_edge_enhance.jpg (enhanced, not isolated)');
imagedestroy($edgeEnhanced);
echo "\n";

echo "Edge detection is crucial for:\n";
echo "  • Object detection preprocessing\n";
echo "  • Feature extraction\n";
echo "  • Shape recognition\n\n";

// 4. Artistic effects
section('4. Artistic Effects');

$img = $loader->load($originalPath);
$embossed = $filter->emboss($img);
$loader->save($embossed, "{$outputDir}/landscape_emboss.jpg", 85);
success('Saved: landscape_emboss.jpg');
imagedestroy($embossed);

$img = $loader->load($originalPath);
$sketch = $filter->sketch($img);
$loader->save($sketch, "{$outputDir}/landscape_sketch.jpg", 85);
success('Saved: landscape_sketch.jpg');
imagedestroy($sketch);

$img = $loader->load($originalPath);
$sepia = $filter->sepia($img);
$loader->save($sepia, "{$outputDir}/landscape_sepia.jpg", 85);
success('Saved: landscape_sepia.jpg');
imagedestroy($sepia);

$img = $loader->load($originalPath);
$pixelated = $filter->pixelate($img, 15);
$loader->save($pixelated, "{$outputDir}/landscape_pixelate.jpg", 85);
success('Saved: landscape_pixelate.jpg');
imagedestroy($pixelated);
echo "\n";

// 5. Color effects
section('5. Color Manipulation');

$img = $loader->load($originalPath);
$negated = $filter->negate($img);
$loader->save($negated, "{$outputDir}/landscape_negate.jpg", 85);
success('Saved: landscape_negate.jpg (color inversion)');
imagedestroy($negated);

// Colorize with different tints
$tints = [
    ['r' => 50, 'g' => 0, 'b' => 0, 'name' => 'red'],
    ['r' => 0, 'g' => 50, 'b' => 0, 'name' => 'green'],
    ['r' => 0, 'g' => 0, 'b' => 50, 'name' => 'blue'],
];

foreach ($tints as $tint) {
    $img = $loader->load($originalPath);
    $colorized = $filter->colorize($img, $tint['r'], $tint['g'], $tint['b']);

    $filename = "landscape_tint_{$tint['name']}.jpg";
    $loader->save($colorized, "{$outputDir}/{$filename}", 85);
    success("Saved: {$filename}");
    imagedestroy($colorized);
}
echo "\n";

// 6. Smooth filter
section('6. Smoothing');

$smoothLevels = [5, 10, 20];
foreach ($smoothLevels as $level) {
    $img = $loader->load($originalPath);
    $smoothed = $filter->smooth($img, $level);

    $filename = "landscape_smooth_{$level}.jpg";
    $loader->save($smoothed, "{$outputDir}/{$filename}", 85);
    success("Saved: {$filename} (smoothness: {$level})");
    imagedestroy($smoothed);
}
echo "\n";

// 7. Custom convolution
section('7. Custom Convolution Filters');

// Custom edge detection matrix
$edgeMatrix = [
    [-1, -1, -1],
    [-1,  8, -1],
    [-1, -1, -1],
];

$img = $loader->load($originalPath);
$customEdge = $filter->convolution($img, $edgeMatrix, 1, 0);
$loader->save($customEdge, "{$outputDir}/landscape_custom_edge.jpg", 85);
success('Saved: landscape_custom_edge.jpg (custom edge matrix)');
imagedestroy($customEdge);
echo "\n";

echo "Convolution allows custom filters for:\n";
echo "  • Edge detection variants\n";
echo "  • Custom sharpening/blurring\n";
echo "  • Special effects\n\n";

// 8. Chaining multiple filters
section('8. Chaining Multiple Filters');

echo "Creating a stylized image: blur → enhance edges → sepia\n\n";

$img = $loader->load($originalPath);
$stylized = $filter->applyMultiple($img, [
    fn($i) => $filter->blur($i, 1),
    fn($i) => $filter->edgeEnhance($i),
    fn($i) => $filter->sepia($i),
]);

$loader->save($stylized, "{$outputDir}/landscape_stylized.jpg", 85);
success('Saved: landscape_stylized.jpg');
imagedestroy($stylized);
echo "\n";

// Demonstrate with face image
section('9. Filters on Face Image');

$facePath = __DIR__ . '/data/face.jpg';

$faceFilters = [
    'blur' => fn($i) => $filter->blur($i, 2),
    'sharpen' => fn($i) => $filter->sharpen($i),
    'edges' => fn($i) => $filter->edgeDetect($i),
    'sketch' => fn($i) => $filter->sketch($i),
];

foreach ($faceFilters as $name => $filterFn) {
    $img = $loader->load($facePath);
    $filtered = $filterFn($img);

    $filename = "face_{$name}.jpg";
    $loader->save($filtered, "{$outputDir}/{$filename}", 85);
    success("Saved: {$filename}");
    imagedestroy($filtered);
}
echo "\n";

// Summary
section('Summary');

echo "✓ Applied blur filters (Gaussian and selective)\n";
echo "✓ Sharpened images (built-in and custom)\n";
echo "✓ Detected and enhanced edges\n";
echo "✓ Applied artistic effects (emboss, sketch, sepia, pixelate)\n";
echo "✓ Manipulated colors (negate, tints)\n";
echo "✓ Applied smoothing filters\n";
echo "✓ Used custom convolution matrices\n";
echo "✓ Chained multiple filters together\n\n";

echo "All filtered images saved to: {$outputDir}/\n\n";

echo "Filters in ML preprocessing:\n";
echo "  • Edge detection → Shape recognition\n";
echo "  • Blur → Noise reduction\n";
echo "  • Sharpen → Feature enhancement\n";
echo "  • Grayscale conversion → Dimensionality reduction\n\n";
