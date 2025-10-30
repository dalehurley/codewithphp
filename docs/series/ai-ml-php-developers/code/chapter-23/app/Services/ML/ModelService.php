<?php

declare(strict_types=1);

namespace App\Services\ML;

use App\Models\Prediction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

abstract class ModelService
{
    protected mixed $model = null;
    protected bool $modelLoaded = false;

    public function __construct(
        protected readonly string $modelName,
    ) {}

    /**
     * Load the ML model (implemented by child classes).
     */
    abstract protected function loadModel(): mixed;

    /**
     * Make a prediction (implemented by child classes).
     */
    abstract public function predict(mixed $input): mixed;

    /**
     * Get cache key for a given input.
     */
    protected function getCacheKey(mixed $input): string
    {
        $prefix = config('ml.cache.prefix', 'ml:');
        $hash = md5(serialize($input));
        return "{$prefix}{$this->modelName}:{$hash}";
    }

    /**
     * Get cached prediction or compute new one.
     */
    protected function cachedPredict(mixed $input, callable $predictor): mixed
    {
        if (!config('ml.cache.enabled', true)) {
            $startTime = microtime(true);
            $result = $predictor($input);
            $latency = (int) round((microtime(true) - $startTime) * 1000);

            $this->logPrediction($input, $result, $latency, false);
            return $result;
        }

        $cacheKey = $this->getCacheKey($input);
        $ttl = config('ml.cache.ttl', 3600);

        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            Log::info("ML cache HIT for {$this->modelName}");
            $this->logPrediction($input, $cached, 0, true);
            return $cached;
        }

        Log::info("ML cache MISS for {$this->modelName}");
        $startTime = microtime(true);
        $result = $predictor($input);
        $latency = (int) round((microtime(true) - $startTime) * 1000);

        Cache::put($cacheKey, $result, $ttl);
        $this->logPrediction($input, $result, $latency, false);

        return $result;
    }

    /**
     * Ensure model is loaded before predictions.
     */
    protected function ensureModelLoaded(): void
    {
        if ($this->modelLoaded) {
            return;
        }

        try {
            $this->model = $this->loadModel();
            $this->modelLoaded = true;
            Log::info("Model loaded: {$this->modelName}");
        } catch (\Exception $e) {
            Log::error("Failed to load model: {$this->modelName}", [
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException(
                "Failed to load {$this->modelName} model: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Execute prediction with timeout and error handling.
     */
    protected function executePrediction(callable $predictor): mixed
    {
        $timeout = config('ml.timeout', 30);

        set_time_limit($timeout);

        try {
            $startTime = microtime(true);
            $result = $predictor();
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info("Prediction completed: {$this->modelName}", [
                'duration_ms' => $duration,
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error("Prediction failed: {$this->modelName}", [
                'error' => $e->getMessage(),
            ]);

            if (config('ml.fallback.enabled', true)) {
                return $this->fallbackResponse();
            }

            throw $e;
        }
    }

    /**
     * Fallback response when prediction fails.
     */
    protected function fallbackResponse(): mixed
    {
        return [
            'error' => config('ml.fallback.response', 'ML service temporarily unavailable'),
            'fallback' => true,
        ];
    }

    /**
     * Log prediction for monitoring and auditing.
     */
    protected function logPrediction(
        mixed $input,
        mixed $output,
        int $latencyMs,
        bool $cacheHit,
        string $status = 'success',
        ?string $errorMessage = null
    ): void {
        try {
            Prediction::create([
                'service' => $this->modelName,
                'input_hash' => md5(serialize($input)),
                'input_data' => is_string($input) ? ['text' => substr($input, 0, 500)] : null,
                'output_data' => $output,
                'latency_ms' => $latencyMs,
                'cache_hit' => $cacheHit,
                'status' => $status,
                'error_message' => $errorMessage,
            ]);
        } catch (\Exception $e) {
            // Don't fail predictions if logging fails
            Log::warning("Failed to log prediction: {$e->getMessage()}");
        }
    }

    /**
     * Clear cache for this model.
     */
    public function clearCache(): void
    {
        $prefix = config('ml.cache.prefix', 'ml:');
        Cache::flush(); // In production, use more targeted cache clearing
        Log::info("Cache cleared for model: {$this->modelName}");
    }

    /**
     * Health check for this model service.
     */
    public function healthCheck(): array
    {
        try {
            $this->ensureModelLoaded();
            return [
                'model' => $this->modelName,
                'status' => 'healthy',
                'loaded' => $this->modelLoaded,
            ];
        } catch (\Exception $e) {
            return [
                'model' => $this->modelName,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }
}

