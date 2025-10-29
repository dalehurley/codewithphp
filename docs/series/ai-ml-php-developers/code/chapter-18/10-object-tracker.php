<?php

declare(strict_types=1);

require_once __DIR__ . '/01-detect-yolo.php';

/**
 * Simple Object Tracker
 * 
 * Tracks objects across multiple frames/images using IoU (Intersection over Union).
 */

class ObjectTracker
{
    private array $tracks = [];
    private int $nextTrackId = 1;

    public function __construct(
        private float $iouThreshold = 0.3,
        private int $maxMissedFrames = 5
    ) {}

    /**
     * Update tracker with new detections from a frame.
     */
    public function update(array $detections, int $frameNumber): array
    {
        $assignedTracks = [];

        // Match new detections to existing tracks
        foreach ($detections as $detection) {
            $bestMatch = null;
            $bestIou = 0;

            foreach ($this->tracks as $trackId => &$track) {
                $iou = $this->calculateIoU($detection['bbox'], $track['bbox']);

                if ($iou > $this->iouThreshold && $iou > $bestIou) {
                    $bestIou = $iou;
                    $bestMatch = $trackId;
                }
            }

            if ($bestMatch !== null) {
                // Update existing track
                $this->tracks[$bestMatch]['bbox'] = $detection['bbox'];
                $this->tracks[$bestMatch]['class'] = $detection['class'];
                $this->tracks[$bestMatch]['confidence'] = $detection['confidence'];
                $this->tracks[$bestMatch]['last_seen'] = $frameNumber;
                $this->tracks[$bestMatch]['missed_frames'] = 0;
                $assignedTracks[] = $bestMatch;
            } else {
                // Create new track
                $trackId = $this->nextTrackId++;
                $this->tracks[$trackId] = [
                    'id' => $trackId,
                    'class' => $detection['class'],
                    'confidence' => $detection['confidence'],
                    'bbox' => $detection['bbox'],
                    'first_seen' => $frameNumber,
                    'last_seen' => $frameNumber,
                    'missed_frames' => 0
                ];
                $assignedTracks[] = $trackId;
            }
        }

        // Increment missed frames for tracks not matched
        foreach ($this->tracks as $trackId => &$track) {
            if (!in_array($trackId, $assignedTracks)) {
                $track['missed_frames']++;
            }
        }

        // Remove tracks that haven't been seen for too long
        $this->tracks = array_filter(
            $this->tracks,
            fn($track) => $track['missed_frames'] < $this->maxMissedFrames
        );

        return array_values($this->tracks);
    }

    /**
     * Calculate Intersection over Union (IoU) between two bounding boxes.
     */
    private function calculateIoU(array $bbox1, array $bbox2): float
    {
        $x1 = max($bbox1['x'], $bbox2['x']);
        $y1 = max($bbox1['y'], $bbox2['y']);
        $x2 = min(
            $bbox1['x'] + $bbox1['width'],
            $bbox2['x'] + $bbox2['width']
        );
        $y2 = min(
            $bbox1['y'] + $bbox1['height'],
            $bbox2['y'] + $bbox2['height']
        );

        // Calculate intersection area
        $intersectionWidth = max(0, $x2 - $x1);
        $intersectionHeight = max(0, $y2 - $y1);
        $intersectionArea = $intersectionWidth * $intersectionHeight;

        if ($intersectionArea === 0) {
            return 0.0;
        }

        // Calculate union area
        $area1 = $bbox1['width'] * $bbox1['height'];
        $area2 = $bbox2['width'] * $bbox2['height'];
        $unionArea = $area1 + $area2 - $intersectionArea;

        return $intersectionArea / $unionArea;
    }

    /**
     * Get all active tracks.
     */
    public function getTracks(): array
    {
        return array_values($this->tracks);
    }

    /**
     * Get tracking statistics.
     */
    public function getStatistics(): array
    {
        $lifespans = [];
        $classes = [];

        foreach ($this->tracks as $track) {
            $lifespan = $track['last_seen'] - $track['first_seen'] + 1;
            $lifespans[] = $lifespan;
            $classes[] = $track['class'];
        }

        return [
            'total_tracks' => count($this->tracks),
            'avg_lifespan' => !empty($lifespans) ? array_sum($lifespans) / count($lifespans) : 0,
            'max_lifespan' => !empty($lifespans) ? max($lifespans) : 0,
            'unique_classes' => count(array_unique($classes)),
            'class_counts' => array_count_values($classes)
        ];
    }

    /**
     * Reset tracker.
     */
    public function reset(): void
    {
        $this->tracks = [];
        $this->nextTrackId = 1;
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    if ($argc < 2) {
        echo "Usage: php 10-object-tracker.php <image_directory>\n";
        echo "\nTrack objects across multiple frames/images.\n";
        echo "Images should be numbered sequentially (frame_001.jpg, frame_002.jpg, etc.)\n";
        exit(1);
    }

    $directory = $argv[1];

    if (!is_dir($directory)) {
        die("Error: Directory not found: {$directory}\n");
    }

    // Get all images sorted by name
    $images = glob($directory . '/*.{jpg,jpeg,png}', GLOB_BRACE);
    sort($images);

    if (empty($images)) {
        die("Error: No images found in {$directory}\n");
    }

    echo "=== Object Tracking ===\n\n";
    echo "Processing " . count($images) . " frames...\n\n";

    try {
        $detector = new YoloDetector();
        $tracker = new ObjectTracker(
            iouThreshold: 0.3,
            maxMissedFrames: 5
        );

        foreach ($images as $frameNumber => $imagePath) {
            echo "[Frame " . ($frameNumber + 1) . "] " . basename($imagePath) . "... ";

            // Detect objects
            $result = $detector->detect($imagePath);

            // Update tracker
            $tracks = $tracker->update($result['detections'], $frameNumber + 1);

            echo "Found {$result['count']} objects, tracking " . count($tracks) . " objects\n";

            // Show new tracks
            foreach ($tracks as $track) {
                if ($track['first_seen'] === $frameNumber + 1) {
                    echo "  → New track #{$track['id']}: {$track['class']}\n";
                }
            }
        }

        echo "\n=== Tracking Statistics ===\n";
        $stats = $tracker->getStatistics();

        echo "Total tracked objects: {$stats['total_tracks']}\n";
        echo "Average lifespan: " . round($stats['avg_lifespan'], 1) . " frames\n";
        echo "Longest track: {$stats['max_lifespan']} frames\n";
        echo "Unique object classes: {$stats['unique_classes']}\n\n";

        echo "Objects tracked:\n";
        arsort($stats['class_counts']);
        foreach ($stats['class_counts'] as $class => $count) {
            echo "  {$class}: {$count}\n";
        }

        echo "\n=== Current Tracks ===\n";
        $currentTracks = $tracker->getTracks();

        foreach ($currentTracks as $track) {
            $lifespan = $track['last_seen'] - $track['first_seen'] + 1;
            echo "Track #{$track['id']}: {$track['class']}\n";
            echo "  Frames: {$track['first_seen']}-{$track['last_seen']} ({$lifespan} frames)\n";
            echo "  Last confidence: " . round($track['confidence'] * 100, 1) . "%\n";
        }
    } catch (Exception $e) {
        echo "\nError: " . $e->getMessage() . "\n";
        exit(1);
    }
}
