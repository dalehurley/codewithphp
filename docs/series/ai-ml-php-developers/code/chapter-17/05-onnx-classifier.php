<?php

declare(strict_types=1);

/**
 * ONNXClassifier - Local image classification using ONNX Runtime
 * 
 * Provides fast, offline image classification without external APIs
 */
final class ONNXClassifier
{
    public function __construct(
        private readonly string $modelPath,
        private readonly string $labelsPath,
        private readonly string $pythonScript,
        private readonly int $maxResults = 5,
    ) {
        if (!file_exists($this->modelPath)) {
            throw new InvalidArgumentException("Model file not found: {$this->modelPath}");
        }

        if (!file_exists($this->labelsPath)) {
            throw new InvalidArgumentException("Labels file not found: {$this->labelsPath}");
        }

        if (!file_exists($this->pythonScript)) {
            throw new InvalidArgumentException("Python script not found: {$this->pythonScript}");
        }
    }

    /**
     * Classify an image using local ONNX model
     *
     * @param string $imagePath Path to image file
     * @return array<array{label: string, confidence: float}>
     * @throws RuntimeException If classification fails
     */
    public function classifyImage(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new InvalidArgumentException("Image file not found: {$imagePath}");
        }

        $command = sprintf(
            'python3 %s %s %s %s %d 2>&1',
            escapeshellarg($this->pythonScript),
            escapeshellarg($this->modelPath),
            escapeshellarg($imagePath),
            escapeshellarg($this->labelsPath),
            $this->maxResults
        );

        $output = shell_exec($command);

        if ($output === null) {
            throw new RuntimeException('Failed to execute Python inference script');
        }

        $result = json_decode($output, true);

        if (!is_array($result)) {
            throw new RuntimeException("Invalid JSON output from inference script: {$output}");
        }

        if (isset($result['error'])) {
            throw new RuntimeException("Inference error: {$result['error']}");
        }

        return $result;
    }

    /**
     * Classify multiple images in batch
     *
     * @param array<string> $imagePaths Array of image file paths
     * @return array<string, array> Classification results keyed by image path
     */
    public function classifyBatch(array $imagePaths): array
    {
        $results = [];

        foreach ($imagePaths as $imagePath) {
            try {
                $results[$imagePath] = $this->classifyImage($imagePath);
            } catch (Exception $e) {
                $results[$imagePath] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Get model information
     */
    public function getModelInfo(): array
    {
        $labelsCount = 0;
        if (file_exists($this->labelsPath)) {
            $labels = json_decode(file_get_contents($this->labelsPath), true);
            $labelsCount = is_array($labels) ? count($labels) : 0;
        }

        return [
            'model' => 'MobileNetV2',
            'model_path' => $this->modelPath,
            'model_size_mb' => round(filesize($this->modelPath) / 1024 / 1024, 2),
            'classes' => $labelsCount,
            'inference_location' => 'local',
            'cost_per_image' => 0.0,
        ];
    }
}

// Example usage if run directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $classifier = new ONNXClassifier(
        modelPath: __DIR__ . '/models/mobilenetv2-7.onnx',
        labelsPath: __DIR__ . '/data/imagenet_labels.json',
        pythonScript: __DIR__ . '/onnx_inference.py',
        maxResults: 5
    );

    $imagePath = __DIR__ . '/data/sample_images/cat.jpg';

    if (file_exists($imagePath)) {
        echo "Local ONNX Classification\n";
        echo str_repeat('=', 50) . "\n\n";

        try {
            $startTime = microtime(true);
            $results = $classifier->classifyImage($imagePath);
            $duration = microtime(true) - $startTime;

            echo "Image: " . basename($imagePath) . "\n\n";

            foreach ($results as $result) {
                printf(
                    "%-25s %5.1f%%\n",
                    $result['label'],
                    $result['confidence'] * 100
                );
            }

            echo "\nInference time: " . round($duration * 1000) . "ms\n";
            echo "Cost: $0.00 (local inference)\n\n";

            // Model info
            $info = $classifier->getModelInfo();
            echo "Model Info:\n";
            echo "  Model: {$info['model']}\n";
            echo "  Size: {$info['model_size_mb']} MB\n";
            echo "  Classes: {$info['classes']}\n";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Sample image not found: {$imagePath}\n";
        echo "Add images to data/sample_images/ and try again.\n";
    }
}
