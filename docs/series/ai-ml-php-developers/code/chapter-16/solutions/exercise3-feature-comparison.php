<?php

declare(strict_types=1);

/**
 * Exercise 3: Feature Comparison
 * 
 * Compare features between two images and calculate similarity scores.
 * Useful for image comparison, duplicate detection, and similarity search.
 */

require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../ImageLoader.php';
require_once __DIR__ . '/../ColorConverter.php';
require_once __DIR__ . '/../FeatureExtractor.php';

// Check command line arguments
if ($argc < 3) {
    echo "Usage: php exercise3-feature-comparison.php <image1-path> <image2-path>\n";
    echo "Example: php exercise3-feature-comparison.php ../data/sample.jpg ../data/landscape.jpg\n";
    exit(1);
}

$image1Path = $argv[1];
$image2Path = $argv[2];

if (!file_exists($image1Path)) {
    error("Image 1 not found: {$image1Path}");
    exit(1);
}

if (!file_exists($image2Path)) {
    error("Image 2 not found: {$image2Path}");
    exit(1);
}

section('Image Feature Comparison');
echo "Comparing:\n";
echo "  Image 1: {$image1Path}\n";
echo "  Image 2: {$image2Path}\n\n";

$loader = new ImageLoader();
$converter = new ColorConverter();
$extractor = new FeatureExtractor();

// Load both images
$image1 = $loader->load($image1Path);
$image2 = $loader->load($image2Path);

// 1. Dimension Comparison
section('1. Dimension Comparison');

$dims1 = $loader->getDimensions($image1);
$dims2 = $loader->getDimensions($image2);

printf(
    "Image 1: %d×%d (%s pixels)\n",
    $dims1['width'],
    $dims1['height'],
    number_format($dims1['width'] * $dims1['height'])
);
printf(
    "Image 2: %d×%d (%s pixels)\n",
    $dims2['width'],
    $dims2['height'],
    number_format($dims2['width'] * $dims2['height'])
);

$aspectRatio1 = $dims1['width'] / $dims1['height'];
$aspectRatio2 = $dims2['width'] / $dims2['height'];

printf("\nAspect Ratios: %.2f:1 vs %.2f:1\n", $aspectRatio1, $aspectRatio2);
$aspectSimilarity = 1 - min(1, abs($aspectRatio1 - $aspectRatio2) / max($aspectRatio1, $aspectRatio2));
printf("Aspect Ratio Similarity: %.1f%%\n\n", $aspectSimilarity * 100);

// 2. Color Comparison
section('2. Color Analysis');

$avg1 = $converter->getAverageColor($image1);
$avg2 = $converter->getAverageColor($image2);

echo "Average Colors:\n";
printColor($avg1, '  Image 1');
printColor($avg2, '  Image 2');
echo "\n";

$dom1 = $converter->getDominantColor($image1);
$dom2 = $converter->getDominantColor($image2);

echo "Dominant Colors:\n";
printColor($dom1, '  Image 1');
printColor($dom2, '  Image 2');
echo "\n";

// Calculate color distance (Euclidean distance in RGB space)
$colorDistance = sqrt(
    pow($avg1['r'] - $avg2['r'], 2) +
        pow($avg1['g'] - $avg2['g'], 2) +
        pow($avg1['b'] - $avg2['b'], 2)
);

$maxColorDistance = sqrt(3 * pow(255, 2)); // Maximum possible distance
$colorSimilarity = 1 - ($colorDistance / $maxColorDistance);

printf("Color Distance: %.2f (max: %.2f)\n", $colorDistance, $maxColorDistance);
printf("Color Similarity: %.1f%%\n\n", $colorSimilarity * 100);

// 3. Statistical Feature Comparison
section('3. Statistical Features');

$features1 = $extractor->extractBasicFeatures($image1);
$features2 = $extractor->extractBasicFeatures($image2);

echo "Feature Comparison:\n";
$featureNames = [
    'width',
    'height',
    'aspect_ratio',
    'avg_red',
    'avg_green',
    'avg_blue',
    'avg_brightness',
    'std_red',
    'std_green',
    'std_blue'
];

foreach ($featureNames as $name) {
    if (isset($features1[$name]) && isset($features2[$name])) {
        $val1 = $features1[$name];
        $val2 = $features2[$name];
        $diff = abs($val1 - $val2);
        printf(
            "  %-16s: %8.2f vs %8.2f (diff: %6.2f)\n",
            ucwords(str_replace('_', ' ', $name)),
            $val1,
            $val2,
            $diff
        );
    }
}
echo "\n";

// 4. Edge Density Comparison
section('4. Texture/Complexity Comparison');

$edges1 = $extractor->extractEdgeDensity($image1);
$edges2 = $extractor->extractEdgeDensity($image2);

printf("Image 1 Edge Density: %.4f (%.1f%%)\n", $edges1, $edges1 * 100);
printf("Image 2 Edge Density: %.4f (%.1f%%)\n", $edges2, $edges2 * 100);

$edgeSimilarity = 1 - min(1, abs($edges1 - $edges2));
printf("Texture Similarity: %.1f%%\n\n", $edgeSimilarity * 100);

// 5. Histogram Comparison
section('5. Color Distribution Comparison');

$hist1 = $extractor->extractColorHistogram($image1, 16);
$hist2 = $extractor->extractColorHistogram($image2, 16);

// Calculate histogram similarity using correlation
function histogramCorrelation(array $hist1, array $hist2): float
{
    $sum1 = array_sum($hist1);
    $sum2 = array_sum($hist2);

    // Normalize histograms
    $norm1 = array_map(fn($v) => $v / $sum1, $hist1);
    $norm2 = array_map(fn($v) => $v / $sum2, $hist2);

    // Calculate correlation
    $correlation = 0;
    for ($i = 0; $i < count($norm1); $i++) {
        $correlation += sqrt($norm1[$i] * $norm2[$i]);
    }

    return $correlation;
}

$redCorr = histogramCorrelation($hist1['red'], $hist2['red']);
$greenCorr = histogramCorrelation($hist1['green'], $hist2['green']);
$blueCorr = histogramCorrelation($hist1['blue'], $hist2['blue']);

printf("Red Channel Correlation:   %.3f (%.1f%% similar)\n", $redCorr, $redCorr * 100);
printf("Green Channel Correlation: %.3f (%.1f%% similar)\n", $greenCorr, $greenCorr * 100);
printf("Blue Channel Correlation:  %.3f (%.1f%% similar)\n", $blueCorr, $blueCorr * 100);

$histogramSimilarity = ($redCorr + $greenCorr + $blueCorr) / 3;
printf("\nOverall Histogram Similarity: %.1f%%\n\n", $histogramSimilarity * 100);

// 6. Overall Similarity Score
section('6. Overall Similarity Score');

$weights = [
    'aspect' => 0.1,
    'color' => 0.3,
    'histogram' => 0.4,
    'texture' => 0.2,
];

$overallScore =
    $weights['aspect'] * $aspectSimilarity +
    $weights['color'] * $colorSimilarity +
    $weights['histogram'] * $histogramSimilarity +
    $weights['texture'] * $edgeSimilarity;

echo "Weighted Similarity Components:\n";
printf(
    "  Aspect Ratio:  %.1f%% (weight: %.0f%%)\n",
    $aspectSimilarity * 100,
    $weights['aspect'] * 100
);
printf(
    "  Average Color: %.1f%% (weight: %.0f%%)\n",
    $colorSimilarity * 100,
    $weights['color'] * 100
);
printf(
    "  Histogram:     %.1f%% (weight: %.0f%%)\n",
    $histogramSimilarity * 100,
    $weights['histogram'] * 100
);
printf(
    "  Texture:       %.1f%% (weight: %.0f%%)\n",
    $edgeSimilarity * 100,
    $weights['texture'] * 100
);
echo "\n";

printf("═══════════════════════════════════════\n");
printf("Overall Similarity: %.1f%%\n", $overallScore * 100);
printf("═══════════════════════════════════════\n\n");

// Interpretation
if ($overallScore > 0.9) {
    echo "Interpretation: Images are very similar (likely variants of the same image)\n";
} elseif ($overallScore > 0.7) {
    echo "Interpretation: Images are quite similar (same category or subject)\n";
} elseif ($overallScore > 0.5) {
    echo "Interpretation: Images have some similarities (related content)\n";
} elseif ($overallScore > 0.3) {
    echo "Interpretation: Images are somewhat different\n";
} else {
    echo "Interpretation: Images are very different\n";
}
echo "\n";

// 7. Recommendations
section('7. Use Cases');

echo "This comparison technique is useful for:\n\n";

if ($overallScore > 0.8) {
    echo "  • Duplicate image detection\n";
    echo "  • Finding edited versions of the same image\n";
}

echo "  • Image similarity search\n";
echo "  • Content-based image retrieval\n";
echo "  • Automatic image categorization\n";
echo "  • Copyright detection\n";
echo "  • Visual search engines\n\n";

// Clean up
imagedestroy($image1);
imagedestroy($image2);

success('Comparison complete!');
