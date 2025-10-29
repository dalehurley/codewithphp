<?php

declare(strict_types=1);

/**
 * CloudVisionClient - Google Cloud Vision API Client
 * 
 * Production-ready client for image classification using Google Cloud Vision API.
 * Features: retry logic, error handling, cost estimation.
 */
final class CloudVisionClient
{
    private const API_URL = 'https://vision.googleapis.com/v1/images:annotate';

    public function __construct(
        private readonly string $apiKey,
        private readonly int $maxResults = 10,
        private readonly int $timeoutSeconds = 30,
    ) {
        if (empty($this->apiKey)) {
            throw new InvalidArgumentException('API key cannot be empty');
        }
    }

    /**
     * Classify an image and return labels with confidence scores
     *
     * @param string $imagePath Path to image file
     * @return array<array{label: string, confidence: float}> Classification results
     * @throws RuntimeException If API request fails
     */
    public function classifyImage(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new InvalidArgumentException("Image file not found: {$imagePath}");
        }

        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            throw new RuntimeException("Failed to read image: {$imagePath}");
        }

        return $this->classifyImageData($imageData);
    }

    /**
     * Classify image from raw binary data
     *
     * @param string $imageData Raw image binary data
     * @return array<array{label: string, confidence: float}>
     */
    public function classifyImageData(string $imageData): array
    {
        $base64Image = base64_encode($imageData);

        $requestBody = [
            'requests' => [
                [
                    'image' => ['content' => $base64Image],
                    'features' => [
                        [
                            'type' => 'LABEL_DETECTION',
                            'maxResults' => $this->maxResults
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->sendRequest($requestBody);

        return $this->parseLabels($response);
    }

    /**
     * Send HTTP request to Vision API with retry logic
     */
    private function sendRequest(array $requestBody, int $attempt = 1): array
    {
        $url = self::API_URL . '?key=' . $this->apiKey;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException("cURL error: {$curlError}");
        }

        if ($httpCode !== 200) {
            // Retry on server errors (500-599) up to 3 times
            if ($httpCode >= 500 && $attempt < 3) {
                usleep(1000000 * $attempt); // Exponential backoff: 1s, 2s
                return $this->sendRequest($requestBody, $attempt + 1);
            }

            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Unknown error';

            throw new RuntimeException(
                "Vision API error (HTTP {$httpCode}): {$errorMessage}"
            );
        }

        $result = json_decode($response, true);

        if (!is_array($result)) {
            throw new RuntimeException('Invalid JSON response from Vision API');
        }

        return $result;
    }

    /**
     * Parse label annotations from API response
     *
     * @return array<array{label: string, confidence: float}>
     */
    private function parseLabels(array $response): array
    {
        $labels = $response['responses'][0]['labelAnnotations'] ?? [];

        $results = [];
        foreach ($labels as $annotation) {
            $results[] = [
                'label' => $annotation['description'] ?? 'Unknown',
                'confidence' => $annotation['score'] ?? 0.0,
            ];
        }

        // Sort by confidence descending
        usort($results, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        return $results;
    }

    /**
     * Get current API pricing estimate (as of 2024)
     *
     * @param int $imagesPerMonth Number of images to classify monthly
     * @return float Estimated monthly cost in USD
     */
    public static function estimateMonthlyCost(int $imagesPerMonth): float
    {
        // Google Cloud Vision pricing (approximate):
        // First 1000 images/month: Free
        // 1,001-5,000,000: $1.50 per 1000 images

        if ($imagesPerMonth <= 1000) {
            return 0.0;
        }

        $billableImages = $imagesPerMonth - 1000;
        return ($billableImages / 1000) * 1.50;
    }
}

// Example usage if run directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    require_once __DIR__ . '/.env.php';

    $apiKey = $_ENV['GOOGLE_CLOUD_VISION_API_KEY'] ?? '';

    if (empty($apiKey)) {
        die("Error: GOOGLE_CLOUD_VISION_API_KEY not set in .env file\n");
    }

    $client = new CloudVisionClient(
        apiKey: $apiKey,
        maxResults: 5
    );

    $imagePath = __DIR__ . '/data/sample_images/cat.jpg';

    if (!file_exists($imagePath)) {
        die("Sample image not found: {$imagePath}\n" .
            "Please add sample images to data/sample_images/\n");
    }

    try {
        echo "Classifying: {$imagePath}\n";
        echo str_repeat('=', 50) . "\n\n";

        $startTime = microtime(true);
        $results = $client->classifyImage($imagePath);
        $duration = microtime(true) - $startTime;

        foreach ($results as $result) {
            printf(
                "%-25s %5.1f%%\n",
                $result['label'],
                $result['confidence'] * 100
            );
        }

        echo "\nProcessing time: " . round($duration * 1000) . "ms\n";

        // Cost estimates
        echo "\nCost estimates:\n";
        echo "  10,000 images/month: $" . CloudVisionClient::estimateMonthlyCost(10000) . "\n";
        echo " 100,000 images/month: $" . CloudVisionClient::estimateMonthlyCost(100000) . "\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
