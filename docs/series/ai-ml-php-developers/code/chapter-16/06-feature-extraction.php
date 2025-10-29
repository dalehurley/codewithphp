<?php

declare(strict_types=1);

/**
 * Chapter 16, Step 6: Extracting Image Features
 * 
 * Demonstrates how to extract numeric features from images
 * that can be used as input to machine learning algorithms.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ImageLoader.php';
require_once __DIR__ . '/FeatureExtractor.php';

section('Extracting Image Features for ML');

$loader = new ImageLoader();
$extractor = new FeatureExtractor();

// Load sample images
$images = [
    'sample' => __DIR__ . '/data/sample.jpg',
    'landscape' => __DIR__ . '/data/landscape.jpg',
    'face' => __DIR__ . '/data/face.jpg',
];

// 1. Extract basic features from each image
section('1. Basic Statistical Features');

foreach ($images as $name => $path) {
    echo "Analyzing {$name}.jpg:\n";

    $img = $loader->load($path);
    $features = $extractor->extractBasicFeatures($img);

    printArray($features, '');

    imagedestroy($img);
}

// 2. Color statistics
section('2. Detailed Color Statistics');

$img = $loader->load($images['landscape']);
$colorStats = $extractor->calculateColorStatistics($img);

echo "Landscape image color analysis:\n";
printf("  Average Red:   %.2f ± %.2f\n", $colorStats['avg_red'], $colorStats['std_red']);
printf("  Average Green: %.2f ± %.2f\n", $colorStats['avg_green'], $colorStats['std_green']);
printf("  Average Blue:  %.2f ± %.2f\n", $colorStats['avg_blue'], $colorStats['std_blue']);
printf("  Brightness:    %.2f / 255\n", $colorStats['avg_brightness']);
echo "\n";

echo "What this tells us:\n";
if (
    $colorStats['avg_green'] > $colorStats['avg_red'] &&
    $colorStats['avg_green'] > $colorStats['avg_blue']
) {
    echo "  → Image has a green bias (likely vegetation/landscape)\n";
}
if ($colorStats['avg_brightness'] > 150) {
    echo "  → Bright/light image\n";
} elseif ($colorStats['avg_brightness'] < 100) {
    echo "  → Dark image\n";
}
echo "\n";

imagedestroy($img);

// 3. Color histograms
section('3. Color Histograms');

$img = $loader->load($images['sample']);
$histogram = $extractor->extractColorHistogram($img, 16);

echo "Red channel histogram (16 bins):\n";
displayHistogram($histogram['red'], 40);
echo "\n";

echo "Green channel histogram (16 bins):\n";
displayHistogram($histogram['green'], 40);
echo "\n";

echo "Blue channel histogram (16 bins):\n";
displayHistogram($histogram['blue'], 40);
echo "\n";

echo "Histograms show color distribution across intensity ranges.\n";
echo "Each bin represents a range of values (0-15, 16-31, etc.)\n\n";

imagedestroy($img);

// 4. Edge density (texture/complexity measure)
section('4. Edge Density Analysis');

foreach ($images as $name => $path) {
    $img = $loader->load($path);
    $edgeDensity = $extractor->extractEdgeDensity($img);

    printf(
        "%-12s: %.3f (%.1f%% edge pixels)\n",
        ucfirst($name),
        $edgeDensity,
        $edgeDensity * 100
    );

    imagedestroy($img);
}

echo "\nEdge density indicates:\n";
echo "  • Higher values = more complex/detailed images\n";
echo "  • Lower values = smooth/simple images\n";
echo "  • Useful for distinguishing image types\n\n";

// 5. Flatten to vector (for ML input)
section('5. Flattening Images to Feature Vectors');

$img = $loader->load($images['face']);

echo "Original image dimensions: " . imagesx($img) . "×" . imagesy($img) . "\n\n";

// Flatten to different sizes
$sizes = [
    ['width' => 32, 'height' => 32, 'grayscale' => true],
    ['width' => 16, 'height' => 16, 'grayscale' => true],
    ['width' => 16, 'height' => 16, 'grayscale' => false],
];

foreach ($sizes as $config) {
    $vector = $extractor->flattenToVector(
        $img,
        $config['width'],
        $config['height'],
        $config['grayscale']
    );

    $channels = $config['grayscale'] ? 1 : 3;
    $expectedSize = $config['width'] * $config['height'] * $channels;

    printf(
        "Flattened to %dx%d (%s): %d features\n",
        $config['width'],
        $config['height'],
        $config['grayscale'] ? 'grayscale' : 'RGB',
        count($vector)
    );

    printf("  Expected: %d features ✓\n", $expectedSize);
    printf(
        "  Sample values: [%.3f, %.3f, %.3f, ..., %.3f]\n",
        $vector[0],
        $vector[1],
        $vector[2],
        $vector[count($vector) - 1]
    );
    echo "\n";
}

echo "Flattened vectors can be used as input to ML algorithms:\n";
echo "  • Each pixel becomes a feature\n";
echo "  • Values normalized to 0-1 range\n";
echo "  • Smaller sizes = faster training\n";
echo "  • Grayscale = 1/3 the features\n\n";

imagedestroy($img);

// 6. Extract all features for classification
section('6. Comprehensive Feature Extraction');

echo "Extracting all features for ML classification:\n\n";

foreach ($images as $name => $path) {
    $img = $loader->load($path);
    $allFeatures = $extractor->extractAllFeatures($img);

    echo ucfirst($name) . " feature vector:\n";
    echo "  [";
    foreach (array_slice($allFeatures, 0, 5) as $i => $value) {
        echo ($i > 0 ? ', ' : '') . sprintf('%.2f', $value);
    }
    echo ", ..., " . sprintf('%.2f', $allFeatures[count($allFeatures) - 1]);
    echo "] (" . count($allFeatures) . " features)\n\n";

    imagedestroy($img);
}

echo "These features could train a classifier to distinguish:\n";
echo "  • Image categories (landscape vs face vs abstract)\n";
echo "  • Image quality (sharp vs blurry)\n";
echo "  • Content type (photo vs illustration)\n\n";

// Summary
section('Summary');

echo "✓ Extracted basic statistical features (dimensions, colors, brightness)\n";
echo "✓ Calculated color channel statistics with standard deviations\n";
echo "✓ Generated color histograms showing distribution\n";
echo "✓ Measured edge density (image complexity)\n";
echo "✓ Flattened images to feature vectors for ML input\n";
echo "✓ Created comprehensive feature sets for classification\n\n";

echo "Feature extraction workflow:\n";
echo "  1. Load image\n";
echo "  2. Extract relevant features\n";
echo "  3. Create feature vector\n";
echo "  4. Feed to ML algorithm\n";
echo "  5. Train classifier or use for prediction\n\n";

echo "Next step: Use these features to train an image classifier!\n\n";
