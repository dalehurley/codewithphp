<?php

declare(strict_types=1);

/**
 * Test Google Cloud Vision API connection and authentication
 * 
 * This script verifies that your API key is working and the Vision API
 * is properly enabled in your Google Cloud project.
 */

// Load environment variables
require_once __DIR__ . '/.env.php';

$apiKey = $_ENV['GOOGLE_CLOUD_VISION_API_KEY'] ?? '';

if (empty($apiKey)) {
    die("Error: GOOGLE_CLOUD_VISION_API_KEY not set in .env file\n" .
        "Copy env.example to .env and add your API key\n");
}

// Test API with a simple request
$url = "https://vision.googleapis.com/v1/images:annotate?key={$apiKey}";

// Create a small test image (1x1 pixel PNG)
$testImageBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

$testRequest = [
    'requests' => [
        [
            'image' => ['content' => $testImageBase64],
            'features' => [['type' => 'LABEL_DETECTION', 'maxResults' => 1]]
        ]
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testRequest));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

echo "Testing Google Cloud Vision API...\n";
echo str_repeat('=', 50) . "\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    die("cURL Error: {$error}\n");
}

if ($httpCode === 200) {
    $result = json_decode($response, true);

    if (isset($result['responses'])) {
        echo "✓ Authentication successful!\n";
        echo "✓ Vision API is enabled and working\n";
        echo "✓ You're ready to classify images\n\n";

        echo "API Key: " . substr($apiKey, 0, 10) . "...\n";
        echo "HTTP Status: {$httpCode} OK\n";
    } else {
        echo "✗ Unexpected response format\n";
        echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
    }
} elseif ($httpCode === 403) {
    echo "✗ Authentication Error (HTTP 403)\n\n";
    echo "Possible causes:\n";
    echo "- Invalid API key\n";
    echo "- Vision API not enabled in your project\n";
    echo "- API key restrictions blocking the request\n\n";
    echo "Response: " . $response . "\n";
} elseif ($httpCode === 429) {
    echo "✗ Rate Limit Exceeded (HTTP 429)\n\n";
    echo "You've hit the API rate limit. Try again in a few minutes.\n";
} else {
    echo "✗ HTTP Error {$httpCode}\n\n";
    $errorData = json_decode($response, true);
    if (isset($errorData['error']['message'])) {
        echo "Error: " . $errorData['error']['message'] . "\n";
    } else {
        echo "Response: " . $response . "\n";
    }
}
