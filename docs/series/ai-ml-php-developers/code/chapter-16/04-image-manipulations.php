<?php

declare(strict_types=1);

/**
 * Chapter 16, Step 4: Basic Image Manipulations
 * 
 * Demonstrates resizing, cropping, rotating, and other
 * basic image transformations.
 */

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ImageLoader.php';
require_once __DIR__ . '/ImageProcessor.php';

section('Basic Image Manipulations');

$loader = new ImageLoader();
$processor = new ImageProcessor();
$outputDir = __DIR__ . '/output';

// Load original image
$originalPath = __DIR__ . '/data/landscape.jpg';
$original = $loader->load($originalPath);
$originalDims = $loader->getDimensions($original);

echo "Original image: {$originalDims['width']}×{$originalDims['height']}\n\n";

// 1. Resize with aspect ratio maintained
section('1. Resizing (Maintaining Aspect Ratio)');

$resized = $processor->resize($original, 300, 200, true);
$resizedDims = $loader->getDimensions($resized);

echo "Target size: 300×200\n";
echo "Actual size: {$resizedDims['width']}×{$resizedDims['height']}\n";
echo "(Aspect ratio maintained)\n";

$loader->save($resized, "{$outputDir}/landscape_resized.jpg", 85);
success('Saved: landscape_resized.jpg');
imagedestroy($resized);
echo "\n";

// 2. Resize without maintaining aspect ratio (stretch)
$stretched = $processor->resize($original, 300, 200, false);
$stretchedDims = $loader->getDimensions($stretched);

echo "Stretched to: {$stretchedDims['width']}×{$stretchedDims['height']}\n";
$loader->save($stretched, "{$outputDir}/landscape_stretched.jpg", 85);
success('Saved: landscape_stretched.jpg');
imagedestroy($stretched);
echo "\n";

// 3. Create thumbnail
section('2. Creating Thumbnails');

$thumbnail = $processor->thumbnail($original, 150, 150);
$thumbDims = $loader->getDimensions($thumbnail);

echo "Thumbnail size: {$thumbDims['width']}×{$thumbDims['height']}\n";
$loader->save($thumbnail, "{$outputDir}/landscape_thumb.jpg", 85);
success('Saved: landscape_thumb.jpg');
imagedestroy($thumbnail);
echo "\n";

// 4. Crop from center
section('3. Cropping');

$cropped = $processor->cropCenter($original, 300, 300);
$croppedDims = $loader->getDimensions($cropped);

echo "Cropped to center: {$croppedDims['width']}×{$croppedDims['height']}\n";
$loader->save($cropped, "{$outputDir}/landscape_cropped.jpg", 85);
success('Saved: landscape_cropped.jpg');
imagedestroy($cropped);
echo "\n";

// 5. Custom crop
$customCrop = $processor->crop($original, 100, 50, 200, 150);
echo "Custom crop (x:100, y:50, 200×150)\n";
$loader->save($customCrop, "{$outputDir}/landscape_custom_crop.jpg", 85);
success('Saved: landscape_custom_crop.jpg');
imagedestroy($customCrop);
echo "\n";

// 6. Rotation
section('4. Rotating');

$rotations = [45, 90, 180];
foreach ($rotations as $degrees) {
    $rotated = $processor->rotate($original, $degrees);
    $rotatedDims = $loader->getDimensions($rotated);

    echo "Rotated {$degrees}°: {$rotatedDims['width']}×{$rotatedDims['height']}\n";
    $loader->save($rotated, "{$outputDir}/landscape_rotated_{$degrees}.jpg", 85);
    success("Saved: landscape_rotated_{$degrees}.jpg");
    imagedestroy($rotated);
}
echo "\n";

// 7. Flipping
section('5. Flipping');

// Reload original for flipping (since flip modifies in place)
$forFlip = $loader->load($originalPath);
$flippedH = $processor->flipHorizontal($forFlip);
$loader->save($flippedH, "{$outputDir}/landscape_flip_h.jpg", 85);
success('Saved: landscape_flip_h.jpg (horizontal flip)');
imagedestroy($flippedH);

$forFlip = $loader->load($originalPath);
$flippedV = $processor->flipVertical($forFlip);
$loader->save($flippedV, "{$outputDir}/landscape_flip_v.jpg", 85);
success('Saved: landscape_flip_v.jpg (vertical flip)');
imagedestroy($flippedV);
echo "\n";

// 8. Scaling by percentage
section('6. Scaling by Percentage');

$scales = [50, 75, 150, 200];
foreach ($scales as $percentage) {
    $scaled = $processor->scale($original, $percentage);
    $scaledDims = $loader->getDimensions($scaled);

    echo "{$percentage}% scale: {$scaledDims['width']}×{$scaledDims['height']}\n";
    $loader->save($scaled, "{$outputDir}/landscape_scale_{$percentage}.jpg", 85);
    success("Saved: landscape_scale_{$percentage}.jpg");
    imagedestroy($scaled);
}
echo "\n";

// Chain multiple operations
section('7. Chaining Operations');

echo "Creating a profile picture: resize → crop center → save\n";

$profilePic = $processor->resize($original, 400, 400, true);
$profilePic = $processor->cropCenter($profilePic, 300, 300);

$loader->save($profilePic, "{$outputDir}/landscape_profile.jpg", 90);
success('Saved: landscape_profile.jpg (300×300 square)');
imagedestroy($profilePic);
echo "\n";

// Clean up
imagedestroy($original);

// Summary
section('Summary');

echo "✓ Resized images (maintaining and ignoring aspect ratio)\n";
echo "✓ Created thumbnails with max dimensions\n";
echo "✓ Cropped images (center and custom positions)\n";
echo "✓ Rotated images at various angles\n";
echo "✓ Flipped images horizontally and vertically\n";
echo "✓ Scaled images by percentage\n";
echo "✓ Chained multiple operations\n\n";

echo "All manipulated images saved to: {$outputDir}/\n\n";

echo "Common use cases:\n";
echo "  • Thumbnails for galleries\n";
echo "  • Profile picture crops\n";
echo "  • Image orientation correction\n";
echo "  • Responsive image sizes\n";
echo "  • Image standardization for ML\n\n";
