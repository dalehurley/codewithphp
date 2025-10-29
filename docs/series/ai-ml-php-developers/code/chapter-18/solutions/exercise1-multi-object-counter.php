<?php

declare(strict_types=1);

require_once __DIR__ . '/../01-detect-yolo.php';

/**
 * Exercise 1: Multi-Object Counter
 * 
 * Count objects by category across a dataset and generate summary statistics.
 */

class ObjectCounter
{
    private array $counts = [];
    private array $imageObjects = [];
    private int $totalImages = 0;
    private int $totalObjects = 0;

    public function processDirectory(string $directory): void
    {
        $images = glob($directory . '/*.{jpg,jpeg,png,webp}', GLOB_BRACE);

        if (empty($images)) {
            throw new RuntimeException("No images found in {$directory}");
        }

        echo "Processing " . count($images) . " images...\n\n";

        $detector = new YoloDetector();

        foreach ($images as $imagePath) {
            $basename = basename($imagePath);
            echo "Processing {$basename}... ";

            try {
                $result = $detector->detect($imagePath);

                if ($result['success']) {
                    echo "✓ {$result['count']} objects\n";

                    $this->totalImages++;
                    $this->totalObjects += $result['count'];
                    $objectsInImage = [];

                    foreach ($result['detections'] as $detection) {
                        $class = $detection['class'];
                        $this->counts[$class] = ($this->counts[$class] ?? 0) + 1;
                        $objectsInImage[$class] = true;
                    }

                    $this->imageObjects[$basename] = [
                        'count' => $result['count'],
                        'classes' => array_keys($objectsInImage)
                    ];
                }
            } catch (Exception $e) {
                echo "✗ Error: " . $e->getMessage() . "\n";
            }
        }
    }

    public function generateReport(): void
    {
        echo "\n=== Object Counter Report ===\n";
        echo "Images processed: {$this->totalImages}\n";
        echo "Total objects: {$this->totalObjects}\n";

        if ($this->totalImages > 0) {
            $avgObjectsPerImage = $this->totalObjects / $this->totalImages;
            echo "Average objects per image: " . round($avgObjectsPerImage, 1) . "\n\n";
        }

        // Sort by count
        arsort($this->counts);

        // Top 10 objects
        echo "Top " . min(10, count($this->counts)) . " objects:\n";
        $rank = 1;
        foreach (array_slice($this->counts, 0, 10, true) as $class => $count) {
            $frequency = $this->calculateFrequency($class);
            $percentImages = round($frequency * 100);

            printf(
                "  %d. %s: %d (appears in %d/%d images = %d%%)\n",
                $rank++,
                $class,
                $count,
                (int)round($frequency * $this->totalImages),
                $this->totalImages,
                $percentImages
            );
        }

        // Find most crowded image
        $mostCrowded = null;
        $maxObjects = 0;

        foreach ($this->imageObjects as $filename => $data) {
            if ($data['count'] > $maxObjects) {
                $maxObjects = $data['count'];
                $mostCrowded = $filename;
            }
        }

        if ($mostCrowded) {
            echo "\nMost crowded image: {$mostCrowded} ({$maxObjects} objects)\n";
        }
    }

    private function calculateFrequency(string $class): float
    {
        $imagesWithClass = 0;

        foreach ($this->imageObjects as $data) {
            if (in_array($class, $data['classes'])) {
                $imagesWithClass++;
            }
        }

        return $this->totalImages > 0 ? $imagesWithClass / $this->totalImages : 0;
    }

    public function exportJSON(string $filename): void
    {
        $data = [
            'summary' => [
                'total_images' => $this->totalImages,
                'total_objects' => $this->totalObjects,
                'avg_objects_per_image' => $this->totalImages > 0
                    ? $this->totalObjects / $this->totalImages
                    : 0,
                'unique_classes' => count($this->counts)
            ],
            'class_counts' => $this->counts,
            'images' => $this->imageObjects
        ];

        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
        echo "\n✓ Exported to JSON: {$filename}\n";
    }

    public function exportCSV(string $filename): void
    {
        $fp = fopen($filename, 'w');

        fputcsv($fp, ['Class', 'Count', 'Frequency']);

        arsort($this->counts);

        foreach ($this->counts as $class => $count) {
            $frequency = $this->calculateFrequency($class);
            fputcsv($fp, [$class, $count, round($frequency * 100, 1) . '%']);
        }

        fclose($fp);
        echo "✓ Exported to CSV: {$filename}\n";
    }
}

// Main execution
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    if ($argc < 2) {
        echo "Usage: php exercise1-multi-object-counter.php <image_directory>\n";
        exit(1);
    }

    $directory = $argv[1];

    if (!is_dir($directory)) {
        die("Error: Directory not found: {$directory}\n");
    }

    try {
        $counter = new ObjectCounter();
        $counter->processDirectory($directory);
        $counter->generateReport();

        // Export results
        $counter->exportJSON(__DIR__ . '/object_counts.json');
        $counter->exportCSV(__DIR__ . '/object_counts.csv');
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
