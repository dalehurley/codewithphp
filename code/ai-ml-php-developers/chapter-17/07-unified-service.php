<?php

declare(strict_types=1);

/**
 * Unified Image Classification Service
 * 
 * Production-ready service that abstracts cloud and local classifiers
 * behind a common interface using the strategy pattern
 */

require_once __DIR__ . '/02-cloud-vision-client.php';
require_once __DIR__ . '/05-onnx-classifier.php';

/**
 * Image classifier interface
 */
interface ImageClassifier
{
    /**
     * Classify an image and return labels with confidence scores
     *
     * @return array<array{label: string, confidence: float}>
     */
    public function classifyImage(string $imagePath): array;

    /**
     * Get classifier type
     */
    public function getType(): string;
}

/**
 * Cloud-based classifier adapter
 */
final class CloudImageClassifier implements ImageClassifier
{
    public function __construct(
        private readonly CloudVisionClient $client
    ) {}

    public function classifyImage(string $imagePath): array
    {
        return $this->client->classifyImage($imagePath);
    }

    public function getType(): string
    {
        return 'cloud';
    }
}

/**
 * Local ONNX classifier adapter
 */
final class LocalImageClassifier implements ImageClassifier
{
    public function __construct(
        private readonly ONNXClassifier $classifier
    ) {}

    public function classifyImage(string $imagePath): array
    {
        return $this->classifier->classifyImage($imagePath);
    }

    public function getType(): string
    {
        return 'local';
    }
}

/**
 * Unified classification service with fallback strategy
 */
final class ImageClassificationService
{
    public function __construct(
        private readonly ImageClassifier $primaryClassifier,
        private readonly ?ImageClassifier $fallbackClassifier = null,
    ) {}

    /**
     * Classify image with automatic fallback
     *
     * @throws RuntimeException If all classifiers fail
     */
    public function classify(string $imagePath): array
    {
        try {
            $results = $this->primaryClassifier->classifyImage($imagePath);
            return [
                'success' => true,
                'results' => $results,
                'classifier' => $this->primaryClassifier->getType(),
                'used_fallback' => false,
            ];
        } catch (Exception $primaryError) {
            if ($this->fallbackClassifier === null) {
                throw new RuntimeException(
                    "Classification failed: " . $primaryError->getMessage(),
                    previous: $primaryError
                );
            }

            try {
                $results = $this->fallbackClassifier->classifyImage($imagePath);
                return [
                    'success' => true,
                    'results' => $results,
                    'classifier' => $this->fallbackClassifier->getType(),
                    'used_fallback' => true,
                    'primary_error' => $primaryError->getMessage(),
                ];
            } catch (Exception $fallbackError) {
                throw new RuntimeException(
                    "Both classifiers failed. Primary: {$primaryError->getMessage()}. " .
                        "Fallback: {$fallbackError->getMessage()}",
                    previous: $fallbackError
                );
            }
        }
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    require_once __DIR__ . '/.env.php';

    // Build classifiers
    $localClassifier = null;
    $cloudClassifier = null;

    // Try to create local classifier
    if (file_exists(__DIR__ . '/models/mobilenetv2-7.onnx')) {
        $localClassifier = new LocalImageClassifier(
            new ONNXClassifier(
                modelPath: __DIR__ . '/models/mobilenetv2-7.onnx',
                labelsPath: __DIR__ . '/data/imagenet_labels.json',
                pythonScript: __DIR__ . '/onnx_inference.py',
                maxResults: 5
            )
        );
    }

    // Try to create cloud classifier
    if (!empty($_ENV['GOOGLE_CLOUD_VISION_API_KEY'] ?? '')) {
        $cloudClassifier = new CloudImageClassifier(
            new CloudVisionClient(
                apiKey: $_ENV['GOOGLE_CLOUD_VISION_API_KEY'],
                maxResults: 5
            )
        );
    }

    // Create service with local as primary, cloud as fallback
    if ($localClassifier !== null) {
        $service = new ImageClassificationService(
            primaryClassifier: $localClassifier,
            fallbackClassifier: $cloudClassifier
        );
        echo "Using: Local (primary), Cloud (fallback)\n";
    } elseif ($cloudClassifier !== null) {
        $service = new ImageClassificationService(
            primaryClassifier: $cloudClassifier
        );
        echo "Using: Cloud only\n";
    } else {
        die("Error: No classifiers available. Setup cloud API key or local model.\n");
    }

    echo str_repeat('=', 60) . "\n\n";

    $testImages = glob(__DIR__ . '/data/sample_images/*.jpg');

    if (empty($testImages)) {
        die("No test images found in data/sample_images/\n");
    }

    foreach (array_slice($testImages, 0, 3) as $imagePath) {
        echo "Classifying: " . basename($imagePath) . "\n";

        try {
            $result = $service->classify($imagePath);

            echo "Classifier: " . $result['classifier'];
            if ($result['used_fallback']) {
                echo " (fallback used: " . $result['primary_error'] . ")";
            }
            echo "\n\n";

            foreach (array_slice($result['results'], 0, 3) as $classification) {
                printf(
                    "  %-20s %5.1f%%\n",
                    $classification['label'],
                    $classification['confidence'] * 100
                );
            }
            echo "\n";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n\n";
        }
    }
}
