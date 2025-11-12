<?php

declare(strict_types=1);

require_once __DIR__ . '/../01-detect-yolo.php';
require_once __DIR__ . '/../BoundingBoxDrawer.php';

/**
 * Exercise 3: Custom Object Filter
 * 
 * Filter detections by class, confidence, size, and region.
 */

class DetectionFilter
{
    private array $classFilter = [];
    private ?float $minConfidence = null;
    private ?int $minSize = null;
    private ?int $maxSize = null;
    private ?array $region = null;

    /**
     * Filter by object classes (chainable).
     */
    public function byClasses(array $classes): self
    {
        $this->classFilter = array_map('strtolower', $classes);
        return $this;
    }

    /**
     * Filter by minimum confidence (chainable).
     */
    public function byConfidence(float $minConfidence): self
    {
        $this->minConfidence = $minConfidence;
        return $this;
    }

    /**
     * Filter by bounding box size (chainable).
     */
    public function bySize(?int $minPixels = null, ?int $maxPixels = null): self
    {
        $this->minSize = $minPixels;
        $this->maxSize = $maxPixels;
        return $this;
    }

    /**
     * Filter by image region (chainable).
     */
    public function byRegion(int $x, int $y, int $width, int $height): self
    {
        $this->region = ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height];
        return $this;
    }

    /**
     * Apply all filters to detections.
     */
    public function apply(array $detections): array
    {
        $filtered = $detections;

        // Filter by class
        if (!empty($this->classFilter)) {
            $filtered = array_filter($filtered, function ($detection) {
                return in_array(strtolower($detection['class']), $this->classFilter);
            });
        }

        // Filter by confidence
        if ($this->minConfidence !== null) {
            $filtered = array_filter($filtered, function ($detection) {
                return $detection['confidence'] >= $this->minConfidence;
            });
        }

        // Filter by size
        if ($this->minSize !== null || $this->maxSize !== null) {
            $filtered = array_filter($filtered, function ($detection) {
                $size = $detection['bbox']['width'] * $detection['bbox']['height'];

                if ($this->minSize !== null && $size < $this->minSize) {
                    return false;
                }

                if ($this->maxSize !== null && $size > $this->maxSize) {
                    return false;
                }

                return true;
            });
        }

        // Filter by region
        if ($this->region !== null) {
            $filtered = array_filter($filtered, function ($detection) {
                return $this->isInRegion($detection['bbox'], $this->region);
            });
        }

        return array_values($filtered);
    }

    /**
     * Reset all filters.
     */
    public function reset(): self
    {
        $this->classFilter = [];
        $this->minConfidence = null;
        $this->minSize = null;
        $this->maxSize = null;
        $this->region = null;
        return $this;
    }

    /**
     * Check if bbox overlaps with region.
     */
    private function isInRegion(array $bbox, array $region): bool
    {
        // Check if bounding box center is in region
        $centerX = $bbox['x'] + $bbox['width'] / 2;
        $centerY = $bbox['y'] + $bbox['height'] / 2;

        return $centerX >= $region['x']
            && $centerX <= $region['x'] + $region['width']
            && $centerY >= $region['y']
            && $centerY <= $region['y'] + $region['height'];
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    if ($argc < 2) {
        echo "Usage: php exercise3-custom-filter.php <image_path>\n";
        echo "\nDemonstrates filtering detections by class, confidence, size, and region.\n";
        exit(1);
    }

    $imagePath = $argv[1];

    if (!file_exists($imagePath)) {
        die("Error: Image not found: {$imagePath}\n");
    }

    try {
        // Detect objects
        echo "Detecting objects...\n";
        $detector = new YoloDetector();
        $result = $detector->detect($imagePath);

        echo "Found {$result['count']} total objects\n\n";

        // Get image dimensions for region filtering
        list($imageWidth, $imageHeight) = getimagesize($imagePath);

        $filter = new DetectionFilter();

        // Example 1: Count only high-confidence people
        echo "=== Example 1: High-confidence people ===\n";
        $people = $filter
            ->byClasses(['person'])
            ->byConfidence(0.85)
            ->apply($result['detections']);

        echo "Found " . count($people) . " people with confidence ≥85%\n";
        foreach ($people as $p) {
            echo "  - Confidence: " . round($p['confidence'] * 100, 1) . "%\n";
        }

        // Example 2: Vehicles in left half of image
        echo "\n=== Example 2: Vehicles in left half ===\n";
        $filter->reset();
        $vehicles = $filter
            ->byClasses(['car', 'truck', 'bus', 'motorcycle'])
            ->byRegion(0, 0, (int)($imageWidth / 2), $imageHeight)
            ->apply($result['detections']);

        echo "Found " . count($vehicles) . " vehicles in left half\n";
        foreach ($vehicles as $v) {
            echo "  - {$v['class']} at [{$v['bbox']['x']}, {$v['bbox']['y']}]\n";
        }

        // Example 3: Large objects only
        echo "\n=== Example 3: Large objects (>50k pixels) ===\n";
        $filter->reset();
        $large = $filter
            ->bySize(minPixels: 50000)
            ->apply($result['detections']);

        echo "Found " . count($large) . " large objects\n";
        foreach ($large as $obj) {
            $size = $obj['bbox']['width'] * $obj['bbox']['height'];
            echo "  - {$obj['class']}: " . number_format($size) . " pixels\n";
        }

        // Example 4: Combine multiple filters
        echo "\n=== Example 4: Combined filters ===\n";
        $filter->reset();
        $filtered = $filter
            ->byClasses(['person', 'dog', 'cat'])
            ->byConfidence(0.7)
            ->bySize(minPixels: 5000)
            ->apply($result['detections']);

        echo "People and pets (confident, visible size): " . count($filtered) . "\n";

        // Draw filtered results
        if (!empty($filtered)) {
            $outputPath = __DIR__ . '/filtered_' . basename($imagePath);
            $drawer = new BoundingBoxDrawer();
            $drawer->draw($imagePath, $filtered, $outputPath);
            echo "\n✓ Filtered image saved to: {$outputPath}\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
