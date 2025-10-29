<?php

declare(strict_types=1);

/**
 * PHP client for Flask ML API.
 * 
 * Advantages over shell execution:
 * - Lower latency (no process spawn)
 * - Better for high traffic
 * - Can use load balancing
 * - Standard HTTP protocol
 */

class MLApiClient
{
    public function __construct(
        private string $baseUrl = 'http://127.0.0.1:5000',
        private int $timeout = 30
    ) {}

    /**
     * Check if API is healthy and ready.
     */
    public function healthCheck(): bool
    {
        try {
            $response = $this->get('/health');
            return $response['status'] === 'healthy' && $response['model_loaded'];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Predict sentiment for a single text.
     */
    public function predict(string $text): array
    {
        return $this->post('/predict', ['text' => $text]);
    }

    /**
     * Predict sentiments for multiple texts (efficient batch operation).
     */
    public function predictBatch(array $texts): array
    {
        $response = $this->post('/predict/batch', ['texts' => $texts]);
        return $response['predictions'];
    }

    /**
     * Make GET request to API.
     */
    private function get(string $endpoint): array
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("API request failed: {$error}");
        }

        if ($httpCode !== 200) {
            throw new RuntimeException("API returned HTTP {$httpCode}");
        }

        return json_decode($response, true);
    }

    /**
     * Make POST request to API.
     */
    private function post(string $endpoint, array $data): array
    {
        $url = $this->baseUrl . $endpoint;
        $json = json_encode($data);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("API request failed: {$error}");
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $result['error'] ?? 'Unknown error';
            throw new RuntimeException("API error ({$httpCode}): {$errorMsg}");
        }

        return $result;
    }
}

// Example usage
try {
    $client = new MLApiClient();

    echo "=== Flask API Client Demo ===\n\n";

    // Check API health
    echo "Checking API health... ";
    if (!$client->healthCheck()) {
        throw new RuntimeException(
            "API is not available. Start it with:\n" .
                "  cd 04-rest-api-example\n" .
                "  python3 flask_server.py"
        );
    }
    echo "✅ API is healthy\n\n";

    // Single prediction
    echo "=== Single Prediction ===\n";
    $start = microtime(true);
    $result = $client->predict("This API is fantastic! Works perfectly.");
    $duration = (microtime(true) - $start) * 1000;

    echo "Text: {$result['text']}\n";
    echo "Sentiment: {$result['sentiment']} ";
    echo "(" . round($result['confidence'] * 100, 1) . "% confident)\n";
    echo "Latency: " . round($duration, 2) . "ms\n\n";

    // Batch prediction
    echo "=== Batch Prediction ===\n";
    $texts = [
        "Amazing product!",
        "Terrible experience.",
        "It's okay.",
        "Highly recommended!",
        "Not satisfied."
    ];

    $start = microtime(true);
    $results = $client->predictBatch($texts);
    $duration = (microtime(true) - $start) * 1000;

    foreach ($results as $result) {
        echo "• {$result['sentiment']}: \"{$result['text']}\"\n";
    }
    echo "\nProcessed " . count($results) . " texts in " . round($duration, 2) . "ms\n";
    echo "Average: " . round($duration / count($results), 2) . "ms per text\n\n";

    echo "✅ API integration working!\n";
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}



