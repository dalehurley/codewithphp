<?php

declare(strict_types=1);

/**
 * API Service Example - PHP HTTP Client
 * 
 * Demonstrates calling Python microservice via HTTP.
 * 
 * Requirements:
 * - composer require guzzlehttp/guzzle
 * - Python API server running (python3 api-server.py)
 */

// Simple HTTP client without Guzzle (no dependencies)
function callPredictionAPI(array $data, string $endpoint = '/predict'): array
{
    $url = "http://localhost:5000{$endpoint}";
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data),
            'timeout' => 5,
        ],
    ];
    
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        throw new RuntimeException("API request failed. Is the server running?");
    }
    
    $result = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException("Invalid JSON response");
    }
    
    return $result;
}

// Example usage
echo "API Service Example\n";
echo "===================\n\n";

// Check if server is running
try {
    $health = file_get_contents('http://localhost:5000/health');
    $healthData = json_decode($health, true);
    echo "✓ Server status: {$healthData['status']}\n\n";
} catch (Exception $e) {
    echo "❌ Server not running. Start it with: python3 api-server.py\n";
    exit(1);
}

// Single prediction
try {
    echo "Single Prediction:\n";
    $result = callPredictionAPI(['age' => 35, 'income' => 50000]);
    echo "  Score: {$result['score']}\n";
    echo "  Model: {$result['model']}\n\n";
} catch (RuntimeException $e) {
    echo "❌ Error: {$e->getMessage()}\n\n";
}

// Batch prediction
try {
    echo "Batch Prediction:\n";
    $batchData = [
        'records' => [
            ['age' => 25, 'income' => 40000],
            ['age' => 35, 'income' => 60000],
            ['age' => 45, 'income' => 80000],
        ]
    ];
    
    $results = callPredictionAPI($batchData, '/batch');
    echo "  Processed: {$results['count']} records\n";
    
    foreach ($results['predictions'] as $i => $pred) {
        echo "  Record " . ($i + 1) . ": score = {$pred['score']}\n";
    }
    
    echo "\n✓ API calls successful\n";
} catch (RuntimeException $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}


