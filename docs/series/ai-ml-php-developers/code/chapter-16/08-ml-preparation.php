<?php

declare(strict_types=1);

/**
 * Chapter 16, Step 8: Preparing Images for Machine Learning
 * 
 * Demonstrates how to prepare images for use in ML models:
 * standardization, normalization, vectorization, and batch processing.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ImageLoader.php';
require_once __DIR__ . '/ImageProcessor.php';
require_once __DIR__ . '/ColorConverter.php';
require_once __DIR__ . '/FeatureExtractor.php';

section('Preparing Images for Machine Learning');

$loader = new ImageLoader();
$processor = new ImageProcessor();
$converter = new ColorConverter();
$extractor = new FeatureExtractor();

// 1. Standardize image dimensions
section('1. Standardizing Image Dimensions');

echo "ML models require consistent input dimensions.\n";
echo "Let's standardize all images to 128×128:\n\n";

$images = [
    'sample.jpg' => __DIR__ . '/data/sample.jpg',
    'landscape.jpg' => __DIR__ . '/data/landscape.jpg',
    'face.jpg' => __DIR__ . '/data/face.jpg',
];

$standardSize = 128;
$standardizedImages = [];

foreach ($images as $name => $path) {
    $img = $loader->load($path);
    $original = $loader->getDimensions($img);

    // Resize to standard dimensions
    $standardized = $processor->resize($img, $standardSize, $standardSize, false);
    $standardizedImages[$name] = $standardized;

    printf(
        "%-15s: %4d×%4d → %d×%d\n",
        $name,
        $original['width'],
        $original['height'],
        $standardSize,
        $standardSize
    );

    imagedestroy($img);
}
echo "\n";

echo "Benefits of standardization:\n";
echo "  • All images have same dimensions\n";
echo "  • Fixed input size for neural networks\n";
echo "  • Batch processing becomes possible\n";
echo "  • Consistent feature vector length\n\n";

// 2. Convert to grayscale
section('2. Converting to Grayscale');

echo "Grayscale reduces dimensions from 3 channels to 1:\n\n";

$grayscaleImages = [];
foreach ($standardizedImages as $name => $img) {
    $gray = $converter->toGrayscaleLuminosity($img);
    $grayscaleImages[$name] = $gray;
    success("{$name} → grayscale");
}
echo "\n";

printf(
    "Data reduction: %d×%d×3 = %d values → %d×%d×1 = %d values\n",
    $standardSize,
    $standardSize,
    $standardSize * $standardSize * 3,
    $standardSize,
    $standardSize,
    $standardSize * $standardSize
);
echo "That's 66% less data!\n\n";

// 3. Flatten to feature vectors
section('3. Flattening to Feature Vectors');

echo "Converting images to 1D vectors for ML algorithms:\n\n";

$featureVectors = [];
foreach ($grayscaleImages as $name => $img) {
    $vector = $extractor->flattenToVector($img, 32, 32, true);
    $featureVectors[$name] = $vector;

    printf(
        "%-15s: %d features [%.3f, %.3f, ..., %.3f]\n",
        $name,
        count($vector),
        $vector[0],
        $vector[1],
        $vector[count($vector) - 1]
    );
}
echo "\n";

echo "These vectors can be used with:\n";
echo "  • K-Nearest Neighbors (KNN)\n";
echo "  • Support Vector Machines (SVM)\n";
echo "  • Neural Networks\n";
echo "  • Any algorithm accepting numeric arrays\n\n";

// 4. Extract statistical features
section('4. Extracting Statistical Features');

echo "Alternative: Use statistical features instead of raw pixels:\n\n";

$statisticalFeatures = [];
foreach ($standardizedImages as $name => $img) {
    $features = $extractor->extractAllFeatures($img);
    $statisticalFeatures[$name] = $features;

    printf("%-15s: %d features\n", $name, count($features));
}
echo "\n";

echo "Statistical features are:\n";
echo "  • Much smaller (11 vs 1024+ features)\n";
echo "  • Faster to train\n";
echo "  • More interpretable\n";
echo "  • Good for traditional ML algorithms\n\n";

// 5. Create training dataset format
section('5. Creating ML Dataset Format');

echo "Simulating a training dataset with labels:\n\n";

// Simulate labels
$labels = [
    'sample.jpg' => 'abstract',
    'landscape.jpg' => 'landscape',
    'face.jpg' => 'face',
];

echo "Using raw pixel features:\n";
$dataset = [];
foreach ($featureVectors as $name => $vector) {
    $dataset[] = [
        'features' => $vector,
        'label' => $labels[$name],
        'source' => $name,
    ];
}

echo "Dataset structure:\n";
echo "[\n";
foreach ($dataset as $i => $sample) {
    printf(
        "  [%d]: %d features → '%s' (from %s)\n",
        $i,
        count($sample['features']),
        $sample['label'],
        $sample['source']
    );
}
echo "]\n\n";

echo "Using statistical features:\n";
$statDataset = [];
foreach ($statisticalFeatures as $name => $features) {
    $statDataset[] = [
        'features' => $features,
        'label' => $labels[$name],
        'source' => $name,
    ];
}

echo "Dataset structure:\n";
echo "[\n";
foreach ($statDataset as $i => $sample) {
    printf(
        "  [%d]: %d features → '%s'\n",
        $i,
        count($sample['features']),
        $sample['label']
    );
    echo "       Features: [";
    echo implode(', ', array_map(
        fn($v) => sprintf('%.2f', $v),
        array_slice($sample['features'], 0, 5)
    ));
    echo ", ...]\n";
}
echo "]\n\n";

// 6. Normalization demonstration
section('6. Feature Normalization');

echo "Normalizing statistical features to 0-1 range:\n\n";

// Find min and max for each feature across all images
$featureCount = count($statisticalFeatures['sample.jpg']);
$mins = array_fill(0, $featureCount, PHP_FLOAT_MAX);
$maxs = array_fill(0, $featureCount, PHP_FLOAT_MIN);

foreach ($statisticalFeatures as $features) {
    foreach ($features as $i => $value) {
        $mins[$i] = min($mins[$i], $value);
        $maxs[$i] = max($maxs[$i], $value);
    }
}

// Normalize
$normalizedFeatures = [];
foreach ($statisticalFeatures as $name => $features) {
    $normalized = [];
    foreach ($features as $i => $value) {
        $range = $maxs[$i] - $mins[$i];
        $normalized[] = $range > 0 ? ($value - $mins[$i]) / $range : 0;
    }
    $normalizedFeatures[$name] = $normalized;
}

echo "Before normalization (sample.jpg):\n";
echo "  [" . implode(', ', array_map(
    fn($v) => sprintf('%.1f', $v),
    array_slice($statisticalFeatures['sample.jpg'], 0, 5)
)) . ", ...]\n\n";

echo "After normalization (sample.jpg):\n";
echo "  [" . implode(', ', array_map(
    fn($v) => sprintf('%.3f', $v),
    array_slice($normalizedFeatures['sample.jpg'], 0, 5)
)) . ", ...]\n\n";

echo "Why normalize?\n";
echo "  • Features on same scale (0-1)\n";
echo "  • Prevents large values from dominating\n";
echo "  • Improves ML algorithm convergence\n";
echo "  • Required for many algorithms (SVM, neural networks)\n\n";

// 7. Batch processing pipeline
section('7. Complete ML Preprocessing Pipeline');

function preprocessImageForML(
    string $imagePath,
    ImageLoader $loader,
    ImageProcessor $processor,
    ColorConverter $converter,
    FeatureExtractor $extractor,
    int $targetSize = 32,
    bool $useStatistical = false
): array {
    // Load image
    $img = $loader->load($imagePath);

    // Standardize size
    $img = $processor->resize($img, $targetSize, $targetSize, false);

    // Convert to grayscale
    $img = $converter->toGrayscaleLuminosity($img);

    // Extract features
    if ($useStatistical) {
        $features = $extractor->extractAllFeatures($img);
    } else {
        $features = $extractor->flattenToVector($img, $targetSize, $targetSize, true);
    }

    imagedestroy($img);

    return $features;
}

echo "Processing all images through pipeline:\n\n";

foreach ($images as $name => $path) {
    $features = measureTime(
        fn() => preprocessImageForML(
            $path,
            $loader,
            $processor,
            $converter,
            $extractor,
            32,
            true
        ),
        "  {$name}"
    );
    echo "    → " . count($features) . " features extracted\n";
}
echo "\n";

// Clean up
foreach ($standardizedImages as $img) {
    imagedestroy($img);
}
foreach ($grayscaleImages as $img) {
    imagedestroy($img);
}

// Summary
section('Summary');

echo "✓ Standardized images to consistent dimensions (128×128)\n";
echo "✓ Converted images to grayscale (66% data reduction)\n";
echo "✓ Flattened images to feature vectors (1D arrays)\n";
echo "✓ Extracted statistical features (compact representation)\n";
echo "✓ Created ML-ready dataset format with labels\n";
echo "✓ Normalized features to 0-1 range\n";
echo "✓ Built complete preprocessing pipeline\n\n";

echo "ML Preparation Checklist:\n";
echo "  ✓ Consistent dimensions\n";
echo "  ✓ Grayscale (or RGB channels handled separately)\n";
echo "  ✓ Normalized values (0-1 or -1 to 1)\n";
echo "  ✓ Flattened to vectors or statistical features\n";
echo "  ✓ Labels assigned\n";
echo "  ✓ Train/test split (not shown, do this next!)\n\n";

echo "Next chapter: Using these techniques to train an image classifier!\n\n";
