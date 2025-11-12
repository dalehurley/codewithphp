<?php

declare(strict_types=1);

/**
 * Exercise 1 Solution: AWS Rekognition Integration
 * 
 * Implements AWS Rekognition as an alternative cloud provider
 * for image classification
 */

/**
 * AWS Rekognition Client
 * 
 * Note: This is a simplified implementation. Production code should use
 * the official AWS SDK for PHP: https://aws.amazon.com/sdk-for-php/
 */
final class AWSRekognitionClient
{
    private const REGION = 'us-east-1';

    public function __construct(
        private readonly string $accessKeyId,
        private readonly string $secretAccessKey,
        private readonly string $region = self::REGION,
        private readonly int $maxLabels = 10,
    ) {
        if (empty($this->accessKeyId) || empty($this->secretAccessKey)) {
            throw new InvalidArgumentException('AWS credentials cannot be empty');
        }
    }

    /**
     * Classify image using AWS Rekognition DetectLabels API
     *
     * @return array<array{label: string, confidence: float}>
     */
    public function classifyImage(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new InvalidArgumentException("Image not found: {$imagePath}");
        }

        $imageBytes = file_get_contents($imagePath);
        if ($imageBytes === false) {
            throw new RuntimeException("Failed to read image: {$imagePath}");
        }

        // AWS Rekognition API endpoint
        $endpoint = "https://rekognition.{$this->region}.amazonaws.com/";

        // Request payload
        $payload = json_encode([
            'Image' => [
                'Bytes' => base64_encode($imageBytes),
            ],
            'MaxLabels' => $this->maxLabels,
            'MinConfidence' => 50,
        ]);

        // AWS Signature V4 (simplified - use AWS SDK in production)
        $headers = $this->generateAwsHeaders($payload);

        // Send request
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new RuntimeException("AWS Rekognition error (HTTP {$httpCode}): {$response}");
        }

        $result = json_decode($response, true);

        return $this->parseLabels($result);
    }

    /**
     * Parse AWS Rekognition response
     */
    private function parseLabels(array $response): array
    {
        $labels = $response['Labels'] ?? [];

        $results = [];
        foreach ($labels as $label) {
            $results[] = [
                'label' => $label['Name'] ?? 'Unknown',
                'confidence' => ($label['Confidence'] ?? 0.0) / 100, // Convert to 0-1 range
            ];
        }

        // Sort by confidence
        usort($results, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        return $results;
    }

    /**
     * Generate AWS Signature V4 headers (simplified)
     * 
     * Production code should use AWS SDK which handles this properly
     */
    private function generateAwsHeaders(string $payload): array
    {
        $date = gmdate('Ymd\THis\Z');

        return [
            'Content-Type: application/x-amz-json-1.1',
            'X-Amz-Target: RekognitionService.DetectLabels',
            'X-Amz-Date: ' . $date,
            // Note: Proper AWS signature omitted for brevity
            // Use AWS SDK in production: composer require aws/aws-sdk-php
        ];
    }

    /**
     * Estimate AWS Rekognition costs
     */
    public static function estimateMonthlyCost(int $imagesPerMonth): float
    {
        // AWS Rekognition pricing (approximate as of 2024):
        // First 1M images/month: $1.00 per 1000 images
        // Over 1M: tiered pricing

        if ($imagesPerMonth <= 0) {
            return 0.0;
        }

        return ($imagesPerMonth / 1000) * 1.00;
    }
}

// Example usage and comparison
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    echo "AWS Rekognition vs Google Cloud Vision Comparison\n";
    echo str_repeat('=', 60) . "\n\n";

    echo "Cost Comparison (1,000 images):\n";
    echo "  AWS Rekognition: $" . AWSRekognitionClient::estimateMonthlyCost(1000) . "\n";

    require_once __DIR__ . '/../02-cloud-vision-client.php';
    echo "  Google Vision: $" . CloudVisionClient::estimateMonthlyCost(1000) . "\n\n";

    echo "Cost Comparison (100,000 images):\n";
    echo "  AWS Rekognition: $" . AWSRekognitionClient::estimateMonthlyCost(100000) . "\n";
    echo "  Google Vision: $" . CloudVisionClient::estimateMonthlyCost(100000) . "\n\n";

    echo "Note: To use AWS Rekognition:\n";
    echo "1. Install AWS SDK: composer require aws/aws-sdk-php\n";
    echo "2. Set AWS credentials in .env:\n";
    echo "   AWS_ACCESS_KEY_ID=your_key\n";
    echo "   AWS_SECRET_ACCESS_KEY=your_secret\n";
    echo "3. Use the official SDK instead of this simplified example\n";
}
