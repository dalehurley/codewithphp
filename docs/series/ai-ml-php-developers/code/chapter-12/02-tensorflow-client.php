<?php

declare(strict_types=1);

/**
 * TensorFlow Serving REST API client.
 * 
 * Reusable class for communicating with TensorFlow Serving via HTTP.
 * Handles request formatting, error handling, and timeouts.
 */
final class TensorFlowClient
{
    public function __construct(
        private string $baseUrl = 'http://localhost:8501',
        private int $timeoutSeconds = 30,
    ) {}

    /**
     * Send a prediction request to TensorFlow Serving.
     *
     * @param string $modelName Model name (e.g., 'mobilenet')
     * @param array<mixed> $instances Array of input instances
     * @return array<mixed> Predictions array
     * @throws RuntimeException If request fails
     */
    public function predict(string $modelName, array $instances): array
    {
        $url = "{$this->baseUrl}/v1/models/{$modelName}:predict";

        $payload = ['instances' => $instances];
        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json),
            ],
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Handle cURL errors
        if ($response === false) {
            throw new RuntimeException(
                "TensorFlow Serving request failed: $curlError"
            );
        }

        // Handle HTTP errors
        if ($httpCode !== 200) {
            throw new RuntimeException(
                "TensorFlow Serving returned HTTP $httpCode: $response"
            );
        }

        $result = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        if (!isset($result['predictions'])) {
            throw new RuntimeException(
                'Invalid TensorFlow Serving response: missing predictions'
            );
        }

        return $result['predictions'];
    }

    /**
     * Get model metadata (version, status).
     *
     * @param string $modelName Model name
     * @return array<mixed> Model metadata
     */
    public function getModelMetadata(string $modelName): array
    {
        $url = "{$this->baseUrl}/v1/models/{$modelName}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            throw new RuntimeException(
                "Failed to get model metadata (HTTP $httpCode)"
            );
        }

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}

// Example usage
if (PHP_SAPI === 'cli') {
    echo "TensorFlow Serving Client Test\n";
    echo "================================\n\n";

    try {
        $client = new TensorFlowClient();

        // Check if model is available
        echo "Checking model status...\n";
        $metadata = $client->getModelMetadata('mobilenet');
        $version = $metadata['model_version_status'][0]['version'] ?? 'unknown';
        $state = $metadata['model_version_status'][0]['state'] ?? 'unknown';

        echo "Model version: $version\n";
        echo "Model state: $state\n\n";

        if ($state !== 'AVAILABLE') {
            echo "⚠ Model is not ready. Start TensorFlow Serving first.\n";
            echo "  ./start_tensorflow_serving.sh\n\n";
            exit(1);
        }

        // Test prediction with dummy data
        echo "Testing prediction with dummy data...\n";
        $dummyImage = array_fill(0, 224 * 224, [0.5, 0.5, 0.5]); // Gray image

        $startTime = microtime(true);
        $predictions = $client->predict('mobilenet', [['input' => $dummyImage]]);
        $duration = microtime(true) - $startTime;

        echo "✓ Prediction successful!\n";
        echo "Response contains " . count($predictions[0]) . " class probabilities\n";

        // Get top prediction
        $probabilities = $predictions[0];
        arsort($probabilities);
        $topClass = array_key_first($probabilities);
        $topProb = $probabilities[$topClass];

        echo "Top prediction: Class $topClass (" . round($topProb * 100, 2) . "%)\n";
        echo "Request time: " . round($duration * 1000, 2) . " ms\n\n";

        echo "✓ TensorFlowClient is working correctly!\n";
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n\n";
        echo "Make sure TensorFlow Serving is running:\n";
        echo "  ./start_tensorflow_serving.sh\n\n";
        exit(1);
    }
}
