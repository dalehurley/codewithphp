<?php

declare(strict_types=1);

/**
 * Exercise 2 Solution: Top-K Confidence Filtering
 * 
 * Implements configurable confidence thresholds to filter
 * low-confidence predictions
 */

require_once __DIR__ . '/../02-cloud-vision-client.php';
require_once __DIR__ . '/../05-onnx-classifier.php';

/**
 * Filtered classifier with confidence threshold
 */
final class FilteredClassifier
{
    public function __construct(
        private readonly object $baseClassifier, // CloudVisionClient or ONNXClassifier
        private readonly float $minConfidence = 0.7,
    ) {
        if ($this->minConfidence < 0.0 || $this->minConfidence > 1.0) {
            throw new InvalidArgumentException('Confidence must be between 0 and 1');
        }
    }

    /**
     * Classify with confidence filtering
     */
    public function classifyImage(string $imagePath): array
    {
        $results = $this->baseClassifier->classifyImage($imagePath);

        $filtered = [];
        $filteredOut = [];

        foreach ($results as $result) {
            if ($result['confidence'] >= $this->minConfidence) {
                $filtered[] = $result;
            } else {
                $filteredOut[] = $result;
            }
        }

        return [
            'results' => $filtered,
            'filtered_count' => count($filteredOut),
            'total_count' => count($results),
            'threshold' => $this->minConfidence,
            'filtered_labels' => array_map(fn($r) => $r['label'], $filteredOut),
        ];
    }

    /**
     * Get confidence distribution statistics
     */
    public function getConfidenceDistribution(string $imagePath): array
    {
        $results = $this->baseClassifier->classifyImage($imagePath);

        $confidences = array_map(fn($r) => $r['confidence'], $results);

        if (empty($confidences)) {
            return [];
        }

        return [
            'count' => count($confidences),
            'min' => min($confidences),
            'max' => max($confidences),
            'avg' => array_sum($confidences) / count($confidences),
            'median' => $this->median($confidences),
            'above_threshold' => count(array_filter($confidences, fn($c) => $c >= $this->minConfidence)),
            'below_threshold' => count(array_filter($confidences, fn($c) => $c < $this->minConfidence)),
        ];
    }

    private function median(array $values): float
    {
        sort($values);
        $count = count($values);
        $middle = floor($count / 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return $values[$middle];
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    require_once __DIR__ . '/../.env.php';

    // Setup base classifier
    if (!empty($_ENV['GOOGLE_CLOUD_VISION_API_KEY'] ?? '')) {
        $baseClassifier = new CloudVisionClient(
            apiKey: $_ENV['GOOGLE_CLOUD_VISION_API_KEY'],
            maxResults: 10
        );
    } elseif (file_exists(__DIR__ . '/../models/mobilenetv2-7.onnx')) {
        $baseClassifier = new ONNXClassifier(
            modelPath: __DIR__ . '/../models/mobilenetv2-7.onnx',
            labelsPath: __DIR__ . '/../data/imagenet_labels.json',
            pythonScript: __DIR__ . '/../onnx_inference.py',
            maxResults: 10
        );
    } else {
        die("Error: No classifier available\n");
    }

    $imagePath = __DIR__ . '/../data/sample_images/cat.jpg';

    if (!file_exists($imagePath)) {
        die("Image not found: {$imagePath}\n");
    }

    echo "Top-K Confidence Filtering Demonstration\n";
    echo str_repeat('=', 60) . "\n\n";

    // Test different thresholds
    $thresholds = [0.5, 0.7, 0.9];

    foreach ($thresholds as $threshold) {
        echo "Threshold: " . ($threshold * 100) . "%\n";
        echo str_repeat('-', 60) . "\n";

        $filtered = new FilteredClassifier($baseClassifier, $threshold);
        $result = $filtered->classifyImage($imagePath);

        echo "Results above threshold:\n";
        foreach ($result['results'] as $r) {
            printf("  %-20s %5.1f%%\n", $r['label'], $r['confidence'] * 100);
        }

        echo "\nFiltered out: {$result['filtered_count']} labels\n";
        if (!empty($result['filtered_labels'])) {
            echo "  " . implode(', ', array_slice($result['filtered_labels'], 0, 5));
            if (count($result['filtered_labels']) > 5) {
                echo " +" . (count($result['filtered_labels']) - 5) . " more";
            }
            echo "\n";
        }

        $stats = $filtered->getConfidenceDistribution($imagePath);
        printf("\nConfidence Distribution:\n");
        printf(
            "  Min: %.1f%%, Max: %.1f%%, Avg: %.1f%%, Median: %.1f%%\n",
            $stats['min'] * 100,
            $stats['max'] * 100,
            $stats['avg'] * 100,
            $stats['median'] * 100
        );

        echo "\n\n";
    }

    echo "Summary:\n";
    echo "  Higher thresholds = Fewer but more confident predictions\n";
    echo "  Lower thresholds = More predictions but some may be uncertain\n";
    echo "  Recommendation: Use 70-80% threshold for production\n";
}
