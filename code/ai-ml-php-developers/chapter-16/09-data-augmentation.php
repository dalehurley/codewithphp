<?php

declare(strict_types=1);

/**
 * Chapter 16, Step 9: Image Augmentation for ML Training
 * 
 * Demonstrates how to generate multiple variations of training images
 * to expand limited datasets and improve model generalization.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ImageLoader.php';
require_once __DIR__ . '/ImageProcessor.php';
require_once __DIR__ . '/ColorConverter.php';
require_once __DIR__ . '/ImageAugmentor.php';

section('Image Augmentation for ML Training');

$loader = new ImageLoader();
$processor = new ImageProcessor();
$converter = new ColorConverter();
$augmentor = new ImageAugmentor($processor, $converter);

$outputDir = __DIR__ . '/output/augmented';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    success("Created augmentation output directory");
}

// Load original image
$originalPath = __DIR__ . '/data/face.jpg';
echo "Loading: {$originalPath}\n\n";

$original = $loader->load($originalPath);
$originalDims = $loader->getDimensions($original);

echo "Original image: {$originalDims['width']}×{$originalDims['height']}\n\n";

// 1. Generate standard augmentation set (reproducible)
section('1. Standard Augmentation Set');

echo "Generating standard augmentation variations...\n\n";

$standardSet = measureTime(
    fn() => $augmentor->generateStandardSet($original),
    'Standard set generation'
);

echo "Generated " . count($standardSet) . " variations:\n\n";

foreach ($standardSet as $name => $augmentedImage) {
    $filename = "face_{$name}.jpg";
    $filepath = "{$outputDir}/{$filename}";

    $loader->save($augmentedImage, $filepath, 85);

    $dims = $loader->getDimensions($augmentedImage);
    echo "  ✓ {$filename} ({$dims['width']}×{$dims['height']})\n";

    imagedestroy($augmentedImage);
}

echo "\n";
echo "Standard augmentations include:\n";
echo "  • Original (baseline)\n";
echo "  • Horizontal and vertical flips\n";
echo "  • 90° and 180° rotations\n";
echo "  • Brightness adjustments (±30)\n";
echo "  • Contrast variations\n\n";

// 2. Generate random augmentations
section('2. Random Augmentation Pipeline');

echo "Generating 10 random augmented variations...\n\n";

$config = [
    'flip' => true,
    'rotate' => true,
    'rotation_angles' => [0, 15, 30, 45, 90, 180, 270],
    'rotate_probability' => 70,
    'brightness' => true,
    'brightness_range' => ['min' => -40, 'max' => 40],
    'brightness_probability' => 60,
    'contrast' => true,
    'contrast_range' => ['min' => -25, 'max' => 25],
    'contrast_probability' => 60,
    'crop' => true,
    'crop_scale_range' => ['min' => 0.8, 'max' => 1.0],
    'crop_probability' => 50,
    'zoom' => true,
    'zoom_range' => ['min' => 0.9, 'max' => 1.2],
    'zoom_probability' => 40,
];

$randomAugmented = measureTime(
    fn() => $augmentor->augment($original, 10, $config),
    'Random augmentation (10 images)'
);

echo "\nSaving random augmentations...\n";

foreach ($randomAugmented as $i => $augmentedImage) {
    $filename = sprintf("face_random_%02d.jpg", $i + 1);
    $filepath = "{$outputDir}/{$filename}";

    $loader->save($augmentedImage, $filepath, 85);
    echo "  ✓ {$filename}\n";

    imagedestroy($augmentedImage);
}

echo "\n";

// 3. Demonstrate dataset expansion
section('3. Dataset Expansion Example');

echo "Original dataset: 1 image\n";
echo "After augmentation: " . (count($standardSet) + 10) . " images\n";
echo "Expansion factor: " . (count($standardSet) + 10) . "x\n\n";

echo "Benefits of augmentation:\n";
echo "  • Increases effective dataset size without collecting new data\n";
echo "  • Teaches model to recognize objects from different angles\n";
echo "  • Reduces overfitting by introducing variations\n";
echo "  • Improves model generalization to real-world scenarios\n";
echo "  • Makes models robust to lighting and orientation changes\n\n";

// 4. Comparison: different augmentation strategies
section('4. Augmentation Strategies');

echo "Conservative (for sensitive data like medical images):\n";
$conservativeConfig = [
    'flip' => true,
    'rotate' => false,
    'brightness' => true,
    'brightness_range' => ['min' => -10, 'max' => 10],
    'brightness_probability' => 30,
    'contrast' => false,
    'crop' => false,
    'zoom' => false,
];

$conservative = $augmentor->augment($original, 3, $conservativeConfig);
echo "  Generated: " . count($conservative) . " variations (minimal transformations)\n";
foreach ($conservative as $img) imagedestroy($img);
echo "\n";

echo "Aggressive (for robust object detection):\n";
$aggressiveConfig = [
    'flip' => true,
    'rotate' => true,
    'rotation_angles' => range(0, 360, 15),
    'rotate_probability' => 90,
    'brightness' => true,
    'brightness_range' => ['min' => -50, 'max' => 50],
    'brightness_probability' => 80,
    'contrast' => true,
    'contrast_range' => ['min' => -30, 'max' => 30],
    'contrast_probability' => 80,
    'crop' => true,
    'crop_probability' => 70,
    'zoom' => true,
    'zoom_probability' => 60,
];

$aggressive = $augmentor->augment($original, 3, $aggressiveConfig);
echo "  Generated: " . count($aggressive) . " variations (extensive transformations)\n";
foreach ($aggressive as $img) imagedestroy($img);
echo "\n";

// 5. Batch augmentation example
section('5. Batch Augmentation Pipeline');

$allImages = glob(__DIR__ . '/data/*.jpg');
echo "Processing " . count($allImages) . " images...\n\n";

$totalGenerated = 0;
$batchOutputDir = $outputDir . '/batch';

if (!is_dir($batchOutputDir)) {
    mkdir($batchOutputDir, 0755, true);
}

foreach ($allImages as $imagePath) {
    $basename = basename($imagePath, '.jpg');
    $img = $loader->load($imagePath);

    // Generate 5 augmentations per image
    $augmented = $augmentor->augment($img, 5);

    foreach ($augmented as $i => $augImg) {
        $filename = sprintf("%s_aug_%02d.jpg", $basename, $i + 1);
        $loader->save($augImg, "{$batchOutputDir}/{$filename}", 85);
        imagedestroy($augImg);
        $totalGenerated++;
    }

    echo "  ✓ Processed: {$basename} → 5 variations\n";
    imagedestroy($img);
}

echo "\nBatch processing complete:\n";
echo "  Input: " . count($allImages) . " images\n";
echo "  Output: {$totalGenerated} augmented images\n";
echo "  Saved to: {$batchOutputDir}/\n\n";

// Clean up
imagedestroy($original);

// Summary
section('Summary');

echo "✓ Generated standard augmentation set (8 variations)\n";
echo "✓ Created random augmentations with custom pipeline\n";
echo "✓ Demonstrated dataset expansion (1 → 18+ images)\n";
echo "✓ Compared conservative vs aggressive strategies\n";
echo "✓ Processed batch of images with augmentation\n\n";

echo "All augmented images saved to: {$outputDir}/\n\n";

echo "When to use augmentation:\n";
echo "  ✓ Training models from scratch with limited data\n";
echo "  ✓ Fine-tuning pre-trained models on custom datasets\n";
echo "  ✓ Creating robust models for varied real-world conditions\n\n";

echo "When NOT to use augmentation:\n";
echo "  ✗ Inference/prediction (use original images)\n";
echo "  ✗ Testing/validation sets (need unmodified data)\n";
echo "  ✗ When augmented variations aren't realistic\n";
echo "  ✗ Tasks where orientation matters (e.g., text recognition)\n\n";

echo "Best practices:\n";
echo "  • Apply augmentation during training only\n";
echo "  • Keep validation/test sets unaugmented\n";
echo "  • Choose augmentations that match real-world variations\n";
echo "  • Start conservative, increase if model underfits\n";
echo "  • Save augmented images or generate on-the-fly\n\n";
