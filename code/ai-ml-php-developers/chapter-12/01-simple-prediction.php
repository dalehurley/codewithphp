<?php

declare(strict_types=1);

/**
 * Simple TensorFlow Serving prediction example.
 * 
 * Demonstrates the basic HTTP request/response cycle for
 * sending data to TensorFlow Serving and receiving predictions.
 */

echo "Simple TensorFlow Serving Prediction\n";
echo "======================================\n\n";

// TensorFlow Serving endpoint
$servingUrl = 'http://localhost:8501/v1/models/mobilenet:predict';

// Create a simple test image (224x224x3, all gray pixels)
echo "Creating test data (224x224 gray image)...\n";
$pixels = [];
for ($i = 0; $i < 224 * 224; $i++) {
    $pixels[] = [0.5, 0.5, 0.5];  // Gray pixel (R, G, B all 0.5)
}

// Prepare request payload
// TensorFlow Serving expects: { "instances": [ { "input": [...] } ] }
$payload = [
    'instances' => [
        ['input' => $pixels]
    ]
];

$json = json_encode($payload);
echo "Payload size: " . number_format(strlen($json)) . " bytes\n\n";

// Send HTTP POST request
echo "Sending prediction request to TensorFlow Serving...\n";

$ch = curl_init($servingUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json),
    ],
    CURLOPT_POSTFIELDS => $json,
    CURLOPT_TIMEOUT => 30,
]);

$startTime = microtime(true);
$response = curl_exec($ch);
$duration = microtime(true) - $startTime;

$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Handle errors
if ($response === false) {
    echo "✗ cURL Error: $curlError\n";
    echo "\nMake sure TensorFlow Serving is running:\n";
    echo "  ./start_tensorflow_serving.sh\n\n";
    exit(1);
}

if ($httpCode !== 200) {
    echo "✗ HTTP Error: $httpCode\n";
    echo "Response: $response\n\n";
    exit(1);
}

// Parse response
$result = json_decode($response, true);

if (!isset($result['predictions'])) {
    echo "✗ Invalid response format\n";
    echo "Response: $response\n\n";
    exit(1);
}

// Extract predictions
$predictions = $result['predictions'][0];
$numClasses = count($predictions);

echo "✓ Prediction successful!\n\n";
echo "Response details:\n";
echo "  HTTP Status: $httpCode\n";
echo "  Request time: " . round($duration * 1000, 2) . " ms\n";
echo "  Classes returned: $numClasses\n\n";

// Find top prediction
arsort($predictions);
$topClass = array_key_first($predictions);
$topConfidence = $predictions[$topClass];

echo "Top prediction:\n";
echo "  Class index: $topClass\n";
echo "  Confidence: " . round($topConfidence * 100, 2) . "%\n\n";

// Show top 5 predictions
echo "Top 5 class indices:\n";
$count = 0;
foreach ($predictions as $classIndex => $confidence) {
    if ($count >= 5) break;
    $count++;
    echo "  $count. Class $classIndex: " . round($confidence * 100, 2) . "%\n";
}

echo "\n";
echo "Note: These are class indices (0-999 for ImageNet).\n";
echo "      Load imagenet_labels.json to get human-readable names.\n";
echo "      See 04-image-classifier.php for complete implementation.\n";
