<?php

declare(strict_types=1);

/**
 * Exercise 3 Solution: Custom Label Mapping
 * 
 * Maps ImageNet's 1,000 classes to application-specific categories
 * by grouping related labels together
 */

require_once __DIR__ . '/../02-cloud-vision-client.php';
require_once __DIR__ . '/../05-onnx-classifier.php';

/**
 * Label mapper that groups fine-grained labels into broader categories
 */
final class LabelMapper
{
    private array $mappings;

    public function __construct(string $mappingFile)
    {
        if (!file_exists($mappingFile)) {
            throw new InvalidArgumentException("Mapping file not found: {$mappingFile}");
        }

        $this->mappings = json_decode(file_get_contents($mappingFile), true);

        if (!is_array($this->mappings)) {
            throw new RuntimeException("Invalid mapping file format");
        }
    }

    /**
     * Map classifications to custom categories
     */
    public function mapResults(array $classifications): array
    {
        $categoryScores = [];
        $unmapped = [];

        foreach ($classifications as $result) {
            $label = strtolower($result['label']);
            $confidence = $result['confidence'];

            $mapped = false;
            foreach ($this->mappings as $category => $patterns) {
                foreach ($patterns as $pattern) {
                    if (
                        str_contains($label, strtolower($pattern)) ||
                        str_contains(strtolower($pattern), $label)
                    ) {

                        if (!isset($categoryScores[$category])) {
                            $categoryScores[$category] = [
                                'category' => $category,
                                'confidence' => 0.0,
                                'sources' => [],
                            ];
                        }

                        // Aggregate confidence (max of matching labels)
                        $categoryScores[$category]['confidence'] = max(
                            $categoryScores[$category]['confidence'],
                            $confidence
                        );
                        $categoryScores[$category]['sources'][] = $result['label'];

                        $mapped = true;
                        break 2;
                    }
                }
            }

            if (!$mapped) {
                $unmapped[] = $result;
            }
        }

        // Sort by confidence
        usort($categoryScores, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        return [
            'categories' => $categoryScores,
            'unmapped' => $unmapped,
        ];
    }

    /**
     * Create a default mapping file
     */
    public static function createDefaultMapping(string $outputFile): void
    {
        $defaultMappings = [
            'Dog' => [
                'dog',
                'puppy',
                'canine',
                'retriever',
                'terrier',
                'bulldog',
                'poodle',
                'shepherd',
                'hound',
                'spaniel',
                'collie',
                'husky',
                'beagle',
                'pug',
                'chihuahua',
                'labrador',
                'golden_retriever'
            ],
            'Cat' => [
                'cat',
                'kitten',
                'feline',
                'tabby',
                'persian',
                'siamese',
                'tiger_cat',
                'egyptian_cat',
                'lynx'
            ],
            'Vehicle' => [
                'car',
                'automobile',
                'truck',
                'van',
                'suv',
                'sedan',
                'convertible',
                'sports_car',
                'racer',
                'motor_vehicle'
            ],
            'Bicycle' => [
                'bicycle',
                'bike',
                'mountain_bike',
                'tricycle'
            ],
            'Animal' => [
                'bird',
                'fish',
                'reptile',
                'insect',
                'butterfly',
                'horse',
                'cow',
                'sheep',
                'elephant',
                'bear',
                'lion',
                'tiger'
            ],
            'Food' => [
                'food',
                'meal',
                'dish',
                'pizza',
                'burger',
                'sandwich',
                'fruit',
                'vegetable',
                'coffee',
                'tea',
                'drink',
                'beverage'
            ],
            'Building' => [
                'building',
                'house',
                'apartment',
                'skyscraper',
                'tower',
                'church',
                'castle',
                'monument'
            ],
            'Nature' => [
                'tree',
                'flower',
                'plant',
                'garden',
                'forest',
                'mountain',
                'lake',
                'ocean',
                'beach',
                'sky',
                'cloud',
                'sunset'
            ],
        ];

        file_put_contents($outputFile, json_encode($defaultMappings, JSON_PRETTY_PRINT));
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    require_once __DIR__ . '/../.env.php';

    // Create default mapping file if it doesn't exist
    $mappingFile = __DIR__ . '/label-mappings.json';
    if (!file_exists($mappingFile)) {
        LabelMapper::createDefaultMapping($mappingFile);
        echo "Created default label mappings: {$mappingFile}\n\n";
    }

    // Setup classifier
    if (file_exists(__DIR__ . '/../models/mobilenetv2-7.onnx')) {
        $classifier = new ONNXClassifier(
            modelPath: __DIR__ . '/../models/mobilenetv2-7.onnx',
            labelsPath: __DIR__ . '/../data/imagenet_labels.json',
            pythonScript: __DIR__ . '/../onnx_inference.py',
            maxResults: 20
        );
    } elseif (!empty($_ENV['GOOGLE_CLOUD_VISION_API_KEY'] ?? '')) {
        $classifier = new CloudVisionClient(
            apiKey: $_ENV['GOOGLE_CLOUD_VISION_API_KEY'],
            maxResults: 20
        );
    } else {
        die("Error: No classifier available\n");
    }

    $mapper = new LabelMapper($mappingFile);

    $imagePath = __DIR__ . '/../data/sample_images/dog.jpg';

    if (!file_exists($imagePath)) {
        die("Image not found: {$imagePath}\n");
    }

    echo "Custom Label Mapping Demonstration\n";
    echo str_repeat('=', 60) . "\n\n";

    // Classify image
    echo "Classifying: " . basename($imagePath) . "\n";
    $results = $classifier->classifyImage($imagePath);

    echo "\nOriginal Labels (" . count($results) . " total):\n";
    foreach (array_slice($results, 0, 5) as $r) {
        printf("  %-30s %5.1f%%\n", $r['label'], $r['confidence'] * 100);
    }
    echo "  ...\n\n";

    // Map to custom categories
    $mapped = $mapper->mapResults($results);

    echo "Mapped Categories:\n";
    foreach ($mapped['categories'] as $category) {
        printf(
            "  %-20s %5.1f%%  (from: %s)\n",
            $category['category'],
            $category['confidence'] * 100,
            implode(', ', array_slice($category['sources'], 0, 3))
        );
    }

    echo "\nUnmapped Labels: " . count($mapped['unmapped']) . "\n";
    if (!empty($mapped['unmapped'])) {
        foreach (array_slice($mapped['unmapped'], 0, 3) as $r) {
            echo "  - {$r['label']}\n";
        }
    }

    echo "\nBenefits:\n";
    echo "  • Simplifies 1,000 ImageNet classes into meaningful categories\n";
    echo "  • Aggregates confidence from multiple related labels\n";
    echo "  • Customizable for your application's needs\n";
}
