<?php

declare(strict_types=1);

/**
 * Exercise 2: Thumbnail Generator
 * 
 * Create thumbnails of an image at multiple sizes while maintaining
 * aspect ratio. Save them with descriptive filenames.
 */

require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../ImageLoader.php';
require_once __DIR__ . '/../ImageProcessor.php';

// Configuration
$thumbnailSizes = [
    'small' => ['width' => 150, 'height' => 150],
    'medium' => ['width' => 300, 'height' => 300],
    'large' => ['width' => 600, 'height' => 600],
    'square_small' => ['width' => 100, 'height' => 100, 'crop' => true],
    'square_medium' => ['width' => 250, 'height' => 250, 'crop' => true],
];

// Check command line arguments
if ($argc < 2) {
    echo "Usage: php exercise2-thumbnail-generator.php <image-path> [output-dir]\n";
    echo "Example: php exercise2-thumbnail-generator.php ../data/landscape.jpg ../output\n";
    exit(1);
}

$imagePath = $argv[1];
$outputDir = $argv[2] ?? __DIR__ . '/../output';

if (!file_exists($imagePath)) {
    error("Image file not found: {$imagePath}");
    exit(1);
}

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    success("Created output directory: {$outputDir}");
}

section('Thumbnail Generator');
echo "Source image: {$imagePath}\n";
echo "Output directory: {$outputDir}\n\n";

$loader = new ImageLoader();
$processor = new ImageProcessor();

// Load original image
try {
    $original = $loader->load($imagePath);
} catch (Exception $e) {
    error("Failed to load image: " . $e->getMessage());
    exit(1);
}

$dimensions = $loader->getDimensions($original);
echo "Original dimensions: {$dimensions['width']}×{$dimensions['height']}\n";
echo "Aspect ratio: " . number_format($dimensions['width'] / $dimensions['height'], 2) . ":1\n\n";

// Get base filename without extension
$pathInfo = pathinfo($imagePath);
$baseFilename = $pathInfo['filename'];
$extension = $pathInfo['extension'];

section('Generating Thumbnails');

$generatedFiles = [];

foreach ($thumbnailSizes as $sizeName => $config) {
    $maxWidth = $config['width'];
    $maxHeight = $config['height'];
    $shouldCrop = $config['crop'] ?? false;

    // Generate thumbnail
    if ($shouldCrop) {
        // For square crops: resize to cover the area, then crop center
        $aspectRatio = $dimensions['width'] / $dimensions['height'];

        if ($aspectRatio > 1) {
            // Wider than tall: scale by height
            $tempHeight = $maxHeight;
            $tempWidth = (int)($tempHeight * $aspectRatio);
        } else {
            // Taller than wide: scale by width
            $tempWidth = $maxWidth;
            $tempHeight = (int)($tempWidth / $aspectRatio);
        }

        $temp = $processor->resize($original, $tempWidth, $tempHeight, false);
        $thumbnail = $processor->cropCenter($temp, $maxWidth, $maxHeight);
        imagedestroy($temp);
    } else {
        // Maintain aspect ratio with max dimensions
        $thumbnail = $processor->thumbnail($original, $maxWidth, $maxHeight);
    }

    $thumbDimensions = $loader->getDimensions($thumbnail);

    // Generate output filename
    $outputFilename = "{$baseFilename}_{$sizeName}.{$extension}";
    $outputPath = "{$outputDir}/{$outputFilename}";

    // Save thumbnail
    $loader->save($thumbnail, $outputPath, 85);

    $fileSize = filesize($outputPath);

    printf(
        "%-15s: %4d×%4d → %3d×%3d  (%s) %s\n",
        ucfirst(str_replace('_', ' ', $sizeName)),
        $dimensions['width'],
        $dimensions['height'],
        $thumbDimensions['width'],
        $thumbDimensions['height'],
        formatFileSize($fileSize),
        $shouldCrop ? '[cropped]' : ''
    );

    $generatedFiles[$sizeName] = [
        'path' => $outputPath,
        'filename' => $outputFilename,
        'width' => $thumbDimensions['width'],
        'height' => $thumbDimensions['height'],
        'size' => $fileSize,
        'cropped' => $shouldCrop,
    ];

    imagedestroy($thumbnail);
}

echo "\n";

// Summary
section('Summary');

echo "Generated " . count($generatedFiles) . " thumbnails:\n\n";

$originalSize = filesize($imagePath);
$totalThumbnailSize = array_sum(array_column($generatedFiles, 'size'));

foreach ($generatedFiles as $sizeName => $info) {
    $sizeReduction = (1 - ($info['size'] / $originalSize)) * 100;
    printf(
        "  • %-20s: %s (%.1f%% smaller)\n",
        $info['filename'],
        formatFileSize($info['size']),
        $sizeReduction
    );
}

echo "\n";
printf("Original size: %s\n", formatFileSize($originalSize));
printf("Total thumbnails: %s\n", formatFileSize($totalThumbnailSize));
printf(
    "Space savings per thumbnail: %.1f%% average\n\n",
    (1 - ($totalThumbnailSize / ($originalSize * count($generatedFiles)))) * 100
);

// Generate HTML preview (optional)
$htmlPath = "{$outputDir}/thumbnail_preview.html";
$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Thumbnail Preview - {$baseFilename}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        h1 { color: #333; }
        .grid { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px; }
        .thumbnail { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .thumbnail img { display: block; border: 1px solid #ddd; }
        .thumbnail h3 { margin: 10px 0 5px 0; color: #555; }
        .thumbnail .info { font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <h1>Thumbnail Preview: {$baseFilename}.{$extension}</h1>
    <p>Original: {$dimensions['width']}×{$dimensions['height']} (" . formatFileSize($originalSize) . ")</p>
    <div class="grid">

HTML;

foreach ($generatedFiles as $sizeName => $info) {
    $displayName = ucwords(str_replace('_', ' ', $sizeName));
    $html .= <<<HTML
        <div class="thumbnail">
            <img src="{$info['filename']}" alt="{$displayName}">
            <h3>{$displayName}</h3>
            <div class="info">
                {$info['width']}×{$info['height']}<br>
                {$info['filename']}<br>
HTML;
    $html .= formatFileSize($info['size']);
    if ($info['cropped']) {
        $html .= " (cropped)";
    }
    $html .= <<<HTML

            </div>
        </div>

HTML;
}

$html .= <<<HTML
    </div>
</body>
</html>
HTML;

file_put_contents($htmlPath, $html);
success("HTML preview saved: {$htmlPath}");
echo "Open it in a browser to see all thumbnails\n\n";

// Clean up
imagedestroy($original);

success('Thumbnail generation complete!');
echo "\nCommon uses:\n";
echo "  • Website galleries (multiple sizes for responsive design)\n";
echo "  • Social media images (specific dimensions required)\n";
echo "  • Email newsletters (smaller file sizes)\n";
echo "  • Mobile app assets (various device resolutions)\n\n";
