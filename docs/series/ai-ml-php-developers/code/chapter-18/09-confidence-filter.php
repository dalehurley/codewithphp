<?php

declare(strict_types=1);

require_once __DIR__ . '/01-detect-yolo.php';
require_once __DIR__ . '/BoundingBoxDrawer.php';

/**
 * Confidence-Based Detection Filtering
 * 
 * Filter detections by confidence threshold to reduce false positives.
 */

class ConfidenceFilter
{
    public function __construct(
        private float $threshold = 0.5
    ) {}

    /**
     * Filter detections by minimum confidence.
     */
    public function filter(array $detections, ?float $threshold = null): array
    {
        $threshold = $threshold ?? $this->threshold;

        return array_values(array_filter(
            $detections,
            fn($detection) => $detection['confidence'] >= $threshold
        ));
    }

    /**
     * Group detections by confidence ranges.
     */
    public function groupByConfidence(array $detections): array
    {
        $groups = [
            'very_high' => [],  // >= 0.9
            'high' => [],       // 0.7 - 0.9
            'medium' => [],     // 0.5 - 0.7
            'low' => []         // < 0.5
        ];

        foreach ($detections as $detection) {
            $conf = $detection['confidence'];

            if ($conf >= 0.9) {
                $groups['very_high'][] = $detection;
            } elseif ($conf >= 0.7) {
                $groups['high'][] = $detection;
            } elseif ($conf >= 0.5) {
                $groups['medium'][] = $detection;
            } else {
                $groups['low'][] = $detection;
            }
        }

        return $groups;
    }

    /**
     * Find optimal confidence threshold to keep N detections.
     */
    public function findThresholdForCount(array $detections, int $targetCount): float
    {
        // Sort by confidence descending
        $confidences = array_column($detections, 'confidence');
        rsort($confidences);

        if (count($confidences) <= $targetCount) {
            return 0.0; // Keep all
        }

        // Return confidence of Nth detection
        return $confidences[$targetCount - 1];
    }

    /**
     * Get detection statistics.
     */
    public function getStatistics(array $detections): array
    {
        if (empty($detections)) {
            return [
                'count' => 0,
                'avg_confidence' => 0,
                'min_confidence' => 0,
                'max_confidence' => 0,
                'median_confidence' => 0
            ];
        }

        $confidences = array_column($detections, 'confidence');
        sort($confidences);

        $count = count($confidences);
        $median = $count % 2 === 0
            ? ($confidences[$count / 2 - 1] + $confidences[$count / 2]) / 2
            : $confidences[floor($count / 2)];

        return [
            'count' => $count,
            'avg_confidence' => array_sum($confidences) / $count,
            'min_confidence' => min($confidences),
            'max_confidence' => max($confidences),
            'median_confidence' => $median,
            'std_deviation' => $this->calculateStdDev($confidences)
        ];
    }

    private function calculateStdDev(array $values): float
    {
        $count = count($values);
        if ($count === 0) return 0.0;

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(
            fn($x) => ($x - $mean) ** 2,
            $values
        )) / $count;

        return sqrt($variance);
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    if ($argc < 2) {
        echo "Usage: php 09-confidence-filter.php <image_path> [threshold]\n";
        echo "\nFilter detections by confidence to reduce false positives.\n";
        exit(1);
    }

    $imagePath = $argv[1];
    $threshold = isset($argv[2]) ? floatval($argv[2]) : 0.5;

    if (!file_exists($imagePath)) {
        die("Error: Image not found: {$imagePath}\n");
    }

    try {
        // Detect with low threshold to get all detections
        $detector = new YoloDetector(confidenceThreshold: 0.1);
        $result = $detector->detect($imagePath);

        echo "=== Confidence-Based Filtering ===\n\n";
        echo "Total raw detections: {$result['count']}\n\n";

        $filter = new ConfidenceFilter();

        // Show statistics
        $stats = $filter->getStatistics($result['detections']);
        echo "Detection Statistics:\n";
        echo "  Average confidence: " . round($stats['avg_confidence'] * 100, 1) . "%\n";
        echo "  Median confidence: " . round($stats['median_confidence'] * 100, 1) . "%\n";
        echo "  Range: " . round($stats['min_confidence'] * 100, 1) . "% - " . round($stats['max_confidence'] * 100, 1) . "%\n";
        echo "  Std deviation: " . round($stats['std_deviation'] * 100, 1) . "%\n\n";

        // Group by confidence
        $groups = $filter->groupByConfidence($result['detections']);
        echo "Confidence Distribution:\n";
        echo "  Very High (≥90%): " . count($groups['very_high']) . " detections\n";
        echo "  High (70-90%): " . count($groups['high']) . " detections\n";
        echo "  Medium (50-70%): " . count($groups['medium']) . " detections\n";
        echo "  Low (<50%): " . count($groups['low']) . " detections\n\n";

        // Filter by threshold
        $filtered = $filter->filter($result['detections'], $threshold);
        echo "After filtering (threshold: " . ($threshold * 100) . "%):\n";
        echo "  Kept: " . count($filtered) . " detections\n";
        echo "  Removed: " . ($result['count'] - count($filtered)) . " low-confidence detections\n\n";

        // Show filtered detections
        echo "High-confidence detections:\n";
        foreach ($filtered as $i => $detection) {
            printf(
                "  %d. %s (%.1f%%)\n",
                $i + 1,
                ucfirst($detection['class']),
                $detection['confidence'] * 100
            );
        }

        // Draw filtered results
        $outputPath = __DIR__ . '/data/test_results/filtered_' . basename($imagePath);
        $drawer = new BoundingBoxDrawer();
        $drawer->draw($imagePath, $filtered, $outputPath);

        echo "\n✓ Filtered image saved to: {$outputPath}\n";

        // Find optimal threshold for top 10
        if ($result['count'] > 10) {
            $optimalThreshold = $filter->findThresholdForCount($result['detections'], 10);
            echo "\nOptimal threshold to keep top 10: " . round($optimalThreshold * 100, 1) . "%\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
