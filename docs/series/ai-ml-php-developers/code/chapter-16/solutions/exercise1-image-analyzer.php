<?php

declare(strict_types=1);

/**
 * Exercise 1: Image Analyzer
 * 
 * Create a comprehensive image analysis tool that loads an image
 * and displays all available information and features.
 */

require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../ImageLoader.php';
require_once __DIR__ . '/../ColorConverter.php';
require_once __DIR__ . '/../FeatureExtractor.php';

// Check command line argument
if ($argc < 2) {
    echo "Usage: php exercise1-image-analyzer.php <image-path>\n";
    echo "Example: php exercise1-image-analyzer.php ../data/landscape.jpg\n";
    exit(1);
}

$imagePath = $argv[1];

if (!file_exists($imagePath)) {
    error("Image file not found: {$imagePath}");
    exit(1);
}

section('Comprehensive Image Analysis');
echo "Analyzing: {$imagePath}\n\n";

$loader = new ImageLoader();
$converter = new ColorConverter();
$extractor = new FeatureExtractor();

// Load image
try {
    $image = $loader->load($imagePath);
} catch (Exception $e) {
    error("Failed to load image: " . $e->getMessage());
    exit(1);
}

// 1. Basic Information
section('1. Basic Image Information');
$info = $loader->getInfo($imagePath);
printArray($info, '');

// 2. Color Analysis
section('2. Color Analysis');
$avgColor = $converter->getAverageColor($image);
$dominantColor = $converter->getDominantColor($image);

printColor($avgColor, 'Average Color');
printColor($dominantColor, 'Dominant Color');
echo "\n";

// 3. Statistical Features
section('3. Statistical Features');
$basicFeatures = $extractor->extractBasicFeatures($image);
printArray($basicFeatures, '');

// 4. Color Statistics
section('4. Detailed Color Statistics');
$colorStats = $extractor->calculateColorStatistics($image);
printArray($colorStats, '');

// 5. Edge Density
section('5. Texture Analysis');
$edgeDensity = $extractor->extractEdgeDensity($image);
printf(
    "Edge Density: %.4f (%.2f%% of pixels are edges)\n\n",
    $edgeDensity,
    $edgeDensity * 100
);

if ($edgeDensity > 0.3) {
    echo "Assessment: High texture/complexity (detailed image)\n";
} elseif ($edgeDensity > 0.15) {
    echo "Assessment: Moderate texture (normal image)\n";
} else {
    echo "Assessment: Low texture (smooth/simple image)\n";
}
echo "\n";

// 6. Color Histogram
section('6. Color Distribution (Histogram)');
$histogram = $extractor->extractColorHistogram($image, 8);

echo "Red Channel:\n";
displayHistogram($histogram['red'], 30);
echo "\n";

echo "Green Channel:\n";
displayHistogram($histogram['green'], 30);
echo "\n";

echo "Blue Channel:\n";
displayHistogram($histogram['blue'], 30);
echo "\n";

// 7. Brightness Analysis
section('7. Brightness Analysis');
$brightness = $extractor->getBrightness($image);
printf(
    "Average Brightness: %.2f / 255 (%.1f%%)\n",
    $brightness,
    ($brightness / 255) * 100
);

if ($brightness > 170) {
    echo "Classification: Bright/Overexposed\n";
} elseif ($brightness > 85) {
    echo "Classification: Well-exposed\n";
} else {
    echo "Classification: Dark/Underexposed\n";
}
echo "\n";

// 8. Color Balance
section('8. Color Balance');
$red = $colorStats['avg_red'];
$green = $colorStats['avg_green'];
$blue = $colorStats['avg_blue'];

echo "Channel Averages:\n";
printf("  Red:   %.1f\n", $red);
printf("  Green: %.1f\n", $green);
printf("  Blue:  %.1f\n", $blue);
echo "\n";

$dominant_channel = 'balanced';
if ($red > $green + 20 && $red > $blue + 20) {
    $dominant_channel = 'red';
} elseif ($green > $red + 20 && $green > $blue + 20) {
    $dominant_channel = 'green';
} elseif ($blue > $red + 20 && $blue > $green + 20) {
    $dominant_channel = 'blue';
}

echo "Color Cast: " . ($dominant_channel === 'balanced' ? 'Neutral/Balanced' :
    ucfirst($dominant_channel) . ' dominant') . "\n\n";

// 9. ML Features
section('9. Machine Learning Feature Vector');
$mlFeatures = $extractor->extractAllFeatures($image);
echo "Comprehensive feature vector: " . count($mlFeatures) . " features\n";
echo "Values: [";
echo implode(', ', array_map(fn($v) => sprintf('%.2f', $v), array_slice($mlFeatures, 0, 5)));
echo ", ..., " . sprintf('%.2f', $mlFeatures[count($mlFeatures) - 1]) . "]\n\n";

// 10. Summary and Recommendations
section('10. Summary');
echo "Image Type Assessment:\n";

// Simple heuristic classification
$hasHighEdges = $edgeDensity > 0.25;
$isGreenDominant = $green > ($red + $blue) / 2 + 20;
$isBlueDominant = $blue > ($red + $green) / 2 + 20;
$isBright = $brightness > 150;

if ($isGreenDominant && $hasHighEdges) {
    echo "  • Likely: Nature/Landscape photograph\n";
}
if ($isBlueDominant && $isBright) {
    echo "  • Likely: Sky/Water scene\n";
}
if (!$hasHighEdges && $brightness > 100 && $brightness < 180) {
    echo "  • Likely: Portrait or simple composition\n";
}
if ($edgeDensity > 0.35) {
    echo "  • Likely: Urban/Architecture scene or abstract\n";
}

echo "\nRecommendations for ML:\n";
echo "  • Suitable for feature-based classification: " .
    ($info['pixels'] < 1000000 ? "Yes" : "Yes, but consider resizing") . "\n";
echo "  • Preprocessing needed: " .
    ($colorStats['std_red'] > 60 ? "Normalize colors" : "Minimal") . "\n";
echo "  • Complexity: " .
    ($hasHighEdges ? "High (many features)" : "Low (smooth regions)") . "\n\n";

// Clean up
imagedestroy($image);

success('Analysis complete!');
