<?php

declare(strict_types=1);

require_once '02-job-queue-system.php';

/**
 * Simple REST API endpoint for ML predictions
 * Accepts prediction requests and queues them for processing
 */

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get request body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true, 512, JSON_THROW_ON_ERROR);

    // Validate required fields
    if (!isset($data['type']) || !isset($data['features'])) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Missing required fields: type, features'
        ]);
        exit;
    }

    // Connect to Redis
    $redis = new Redis();
    $redis->connect(getenv('REDIS_HOST') ?: 'localhost', 6379);

    // Create and queue job
    $queue = new JobQueue($redis);

    $job = new PredictionJob(
        id: uniqid('pred_'),
        type: $data['type'],
        data: ['features' => $data['features']],
        priority: $data['priority'] ?? 0
    );

    $queue->push($job);

    // Return job ID for tracking
    http_response_code(202); // Accepted
    echo json_encode([
        'job_id' => $job->id,
        'status' => 'queued',
        'message' => 'Prediction request queued successfully',
        'check_url' => "/api/results/{$job->id}"
    ], JSON_PRETTY_PRINT);
} catch (JsonException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
    error_log("API Error: {$e->getMessage()}");
}
