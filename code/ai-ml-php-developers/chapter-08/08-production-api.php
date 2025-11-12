<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Estimator;
use Rubix\ML\Datasets\Unlabeled;

/**
 * Production ML Model Server
 * 
 * Singleton pattern ensures model loads once and serves many requests.
 * Includes validation, error handling, logging, and monitoring.
 */
class ModelServer
{
    private static ?Estimator $model = null;
    private static array $config = [];
    private static int $requestCount = 0;
    private static float $totalInferenceTime = 0;

    /**
     * Initialize server and load model once
     */
    public static function initialize(): void
    {
        // Load configuration
        self::$config = [
            'model_path' => __DIR__ . '/models/production.rbx',
            'expected_features' => 4,  // Iris dataset has 4 features
            'log_path' => __DIR__ . '/logs/predictions.log',
            'max_feature_value' => 10.0,  // Sanity check
            'min_feature_value' => 0.0,
        ];

        // Create logs directory if needed
        if (!is_dir(__DIR__ . '/logs')) {
            mkdir(__DIR__ . '/logs', 0755, true);
        }

        // Load model once at startup
        try {
            $persister = new Filesystem(self::$config['model_path']);
            self::$model = $persister->load();

            error_log(sprintf(
                "[ModelServer] Model loaded successfully (%s)",
                get_class(self::$model)
            ));
        } catch (Exception $e) {
            error_log("[ModelServer] FATAL: Failed to load model: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate input features
     * 
     * @throws InvalidArgumentException if validation fails
     */
    private static function validateFeatures(array $features): void
    {
        // Check feature count
        if (count($features) !== self::$config['expected_features']) {
            throw new InvalidArgumentException(sprintf(
                "Expected %d features, got %d",
                self::$config['expected_features'],
                count($features)
            ));
        }

        // Check all values are numeric
        foreach ($features as $i => $value) {
            if (!is_numeric($value)) {
                throw new InvalidArgumentException(
                    "Feature at index {$i} is not numeric: " . var_export($value, true)
                );
            }

            // Sanity check: reasonable range
            if (
                $value < self::$config['min_feature_value'] ||
                $value > self::$config['max_feature_value']
            ) {
                $min = self::$config['min_feature_value'];
                $max = self::$config['max_feature_value'];
                throw new InvalidArgumentException(
                    "Feature at index {$i} out of range [{$min}, {$max}]: {$value}"
                );
            }
        }
    }

    /**
     * Make prediction with timing and logging
     * 
     * @param array $features Input feature vector
     * @return array Prediction result with metadata
     */
    public static function predict(array $features): array
    {
        // Validate input
        self::validateFeatures($features);

        // Make prediction with timing
        $startTime = microtime(true);
        $prediction = self::$model->predictSample($features);
        $duration = (microtime(true) - $startTime) * 1000;

        // Get probability/confidence if available
        $confidence = null;
        if (method_exists(self::$model, 'proba')) {
            try {
                $proba = self::$model->proba(new Unlabeled([$features]))[0];
                $confidence = max($proba);
            } catch (Exception $e) {
                // Some models don't support proba - that's OK
                $confidence = null;
            }
        }

        // Update statistics
        self::$requestCount++;
        self::$totalInferenceTime += $duration;

        // Log prediction
        self::logPrediction($features, $prediction, $confidence, $duration);

        return [
            'prediction' => $prediction,
            'confidence' => $confidence,
            'processing_time_ms' => round($duration, 2),
            'request_id' => uniqid('pred_', true),
        ];
    }

    /**
     * Log prediction for monitoring and retraining
     */
    private static function logPrediction(
        array $features,
        $prediction,
        ?float $confidence,
        float $duration
    ): void {
        $logEntry = json_encode([
            'timestamp' => date('c'),
            'features' => $features,
            'prediction' => $prediction,
            'confidence' => $confidence,
            'duration_ms' => round($duration, 2),
            'model_class' => get_class(self::$model),
        ]) . "\n";

        file_put_contents(self::$config['log_path'], $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Get server statistics
     */
    public static function getStats(): array
    {
        return [
            'total_requests' => self::$requestCount,
            'avg_inference_time_ms' => self::$requestCount > 0
                ? round(self::$totalInferenceTime / self::$requestCount, 2)
                : 0,
            'model_class' => self::$model ? get_class(self::$model) : null,
            'uptime_seconds' => time() - $_SERVER['REQUEST_TIME'],
        ];
    }

    /**
     * Health check
     */
    public static function isHealthy(): bool
    {
        return self::$model !== null;
    }
}

// ============================================================
// Initialize model server on startup (runs once)
// ============================================================

try {
    ModelServer::initialize();
} catch (Exception $e) {
    http_response_code(503);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Service Unavailable',
        'message' => 'Failed to initialize model server',
    ]);
    exit(1);
}

// ============================================================
// Handle HTTP Requests
// ============================================================

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Route: POST /predict - Make prediction
if ($method === 'POST' && $uri === '/predict') {
    try {
        // Parse JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        if ($input === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }

        if (!isset($input['features'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing "features" field']);
            exit;
        }

        // Make prediction
        $result = ModelServer::predict($input['features']);

        http_response_code(200);
        echo json_encode($result);
    } catch (InvalidArgumentException $e) {
        // Client error - bad input
        http_response_code(400);
        echo json_encode([
            'error' => 'Bad Request',
            'message' => $e->getMessage(),
        ]);
    } catch (Exception $e) {
        // Server error - unexpected
        http_response_code(500);
        error_log("[ModelServer] Prediction error: " . $e->getMessage());
        echo json_encode([
            'error' => 'Internal Server Error',
            'message' => 'An unexpected error occurred',
        ]);
    }

    // Route: GET /health - Health check
} elseif ($method === 'GET' && $uri === '/health') {
    $healthy = ModelServer::isHealthy();

    http_response_code($healthy ? 200 : 503);
    echo json_encode([
        'status' => $healthy ? 'healthy' : 'unhealthy',
        'model_loaded' => $healthy,
        'timestamp' => date('c'),
    ]);

    // Route: GET /stats - Server statistics
} elseif ($method === 'GET' && $uri === '/stats') {
    http_response_code(200);
    echo json_encode(ModelServer::getStats());

    // Route not found
} else {
    http_response_code(404);
    echo json_encode([
        'error' => 'Not Found',
        'message' => "Route {$method} {$uri} not found",
        'available_routes' => [
            'POST /predict' => 'Make prediction',
            'GET /health' => 'Health check',
            'GET /stats' => 'Server statistics',
        ],
    ]);
}
