<?php

declare(strict_types=1);

require_once '02-tensorflow-client.php';
require_once '03-image-preprocessor.php';

/**
 * Complete image classification service.
 * 
 * Combines preprocessing, prediction, and label decoding for
 * end-to-end image classification with human-readable results.
 */
final class ImageClassifier
{
    private array $labels = [];

    public function __construct(
        private TensorFlowClient $client,
        private ImagePreprocessor $preprocessor,
        private string $modelName = 'mobilenet',
        ?string $labelsPath = null,
    ) {
        // Load ImageNet labels if provided
        if ($labelsPath && file_exists($labelsPath)) {
            $this->labels = json_decode(
                file_get_contents($labelsPath),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }
    }

    /**
     * Classify a single image.
     *
     * @param string $imagePath Path to image file
     * @param int $topK Number of top predictions to return
     * @return array<array{class: int, label: string, confidence: float}> Top predictions
     */
    public function classify(string $imagePath, int $topK = 5): array
    {
        // Preprocess image
        $pixels = $this->preprocessor->preprocessImage($imagePath);

        // Prepare request
        $instances = [['input' => $pixels]];

        // Get predictions from TensorFlow Serving
        $predictions = $this->client->predict($this->modelName, $instances);
        $probabilities = $predictions[0];

        // Get top K predictions
        return $this->getTopPredictions($probabilities, $topK);
    }

    /**
     * Classify multiple images in batch.
     *
     * @param array<string> $imagePaths Array of image file paths
     * @param int $topK Number of top predictions per image
     * @return array<array<array{class: int, label: string, confidence: float}>> Predictions per image
     */
    public function classifyBatch(array $imagePaths, int $topK = 5): array
    {
        // Preprocess all images
        $batch = $this->preprocessor->preprocessBatch($imagePaths);

        // Prepare batch request
        $instances = array_map(fn($pixels) => ['input' => $pixels], $batch);

        // Get predictions
        $predictions = $this->client->predict($this->modelName, $instances);

        // Process each prediction
        $results = [];
        foreach ($predictions as $probabilities) {
            $results[] = $this->getTopPredictions($probabilities, $topK);
        }

        return $results;
    }

    /**
     * Extract top K predictions from probability array.
     *
     * @param array<float> $probabilities Probability scores for all classes
     * @param int $topK Number of top predictions to return
     * @return array<array{class: int, label: string, confidence: float}> Sorted predictions
     */
    private function getTopPredictions(array $probabilities, int $topK): array
    {
        // Sort probabilities in descending order, keeping indices
        arsort($probabilities);

        // Take top K
        $topIndices = array_slice(array_keys($probabilities), 0, $topK, true);

        $results = [];
        foreach ($topIndices as $classIndex) {
            $confidence = $probabilities[$classIndex];
            $label = $this->labels[$classIndex] ?? "Class $classIndex";

            $results[] = [
                'class' => $classIndex,
                'label' => $label,
                'confidence' => $confidence,
            ];
        }

        return $results;
    }
}

// Example usage
if (PHP_SAPI === 'cli') {
    echo "Image Classifier Test\n";
    echo "======================\n\n";

    try {
        // Initialize components
        $client = new TensorFlowClient();
        $preprocessor = new ImagePreprocessor();
        $labelsPath = __DIR__ . '/data/imagenet_labels.json';

        $classifier = new ImageClassifier(
            client: $client,
            preprocessor: $preprocessor,
            labelsPath: $labelsPath
        );

        // Check if we have sample images
        $sampleDir = __DIR__ . '/data/sample_images';
        if (is_dir($sampleDir)) {
            $images = glob($sampleDir . '/*.{jpg,jpeg,png}', GLOB_BRACE);
            if (!empty($images)) {
                $testImage = $images[0];
                echo "Using sample image: " . basename($testImage) . "\n\n";
            }
        }

        // If no sample images, create a test image
        if (!isset($testImage)) {
            echo "Creating test image (solid color for testing)...\n";
            $testImage = '/tmp/test_classification.jpg';
            $img = imagecreatetruecolor(300, 300);
            $color = imagecolorallocate($img, 180, 120, 60);
            imagefill($img, 0, 0, $color);
            imagejpeg($img, $testImage, 90);
            imagedestroy($img);
            echo "✓ Test image created\n\n";
        }

        echo "Classifying image: $testImage\n\n";

        $startTime = microtime(true);
        $predictions = $classifier->classify($testImage, topK: 5);
        $duration = microtime(true) - $startTime;

        echo "Top 5 Predictions:\n";
        echo "==================\n\n";

        foreach ($predictions as $i => $pred) {
            $rank = $i + 1;
            $confidence = round($pred['confidence'] * 100, 2);
            $bar = str_repeat('█', (int)($confidence / 5));

            echo "$rank. {$pred['label']}\n";
            echo "   Confidence: $confidence% $bar\n";
            echo "   Class ID: {$pred['class']}\n\n";
        }

        echo "Classification time: " . round($duration * 1000, 2) . " ms\n\n";

        if (!file_exists($labelsPath)) {
            echo "Note: ImageNet labels not loaded (data/imagenet_labels.json not found)\n";
            echo "      Showing class indices instead of names.\n";
        }
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n\n";
        echo "Make sure:\n";
        echo "  1. TensorFlow Serving is running: ./start_tensorflow_serving.sh\n";
        echo "  2. ImageNet labels file exists (optional): data/imagenet_labels.json\n\n";
        exit(1);
    }
}
