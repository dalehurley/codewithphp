<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ProcessPredictionJob;
use App\Models\Prediction;
use App\Services\ML\SentimentAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MLController extends Controller
{
    public function __construct(
        private readonly SentimentAnalysisService $sentimentService,
    ) {}

    /**
     * Analyze sentiment of provided text (synchronous).
     */
    public function analyzeSentiment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|min:10|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->sentimentService->predict($request->input('text'));

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Sentiment analysis failed',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Analyze sentiment asynchronously (queued).
     */
    public function analyzeSentimentAsync(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|min:10|max:5000',
            'callback_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors(),
            ], 422);
        }

        $jobId = uniqid('job_', true);

        ProcessPredictionJob::dispatch(
            service: 'sentiment',
            input: $request->input('text'),
            callbackUrl: $request->input('callback_url'),
        )->onQueue('ml-predictions');

        return response()->json([
            'success' => true,
            'message' => 'Prediction queued for processing',
            'job_id' => $jobId,
            'status_url' => url("/api/ml/status/{$jobId}"),
        ], 202); // 202 Accepted
    }

    /**
     * Health check endpoint for ML services.
     */
    public function health(): JsonResponse
    {
        $sentimentHealth = $this->sentimentService->healthCheck();

        $allHealthy = $sentimentHealth['status'] === 'healthy';

        return response()->json([
            'services' => [
                'sentiment' => $sentimentHealth,
            ],
            'overall_status' => $allHealthy ? 'healthy' : 'degraded',
        ], $allHealthy ? 200 : 503);
    }

    /**
     * Get metrics for ML service performance.
     */
    public function metrics(Request $request): JsonResponse
    {
        $service = $request->input('service', 'sentiment');
        $hours = (int) $request->input('hours', 24);

        $cacheHitRate = Prediction::cacheHitRate($service, $hours);
        $avgLatency = Prediction::averageLatency($service, $hours);

        $since = now()->subHours($hours);
        $totalPredictions = Prediction::where('service', $service)
            ->where('created_at', '>=', $since)
            ->count();

        $errorCount = Prediction::where('service', $service)
            ->where('created_at', '>=', $since)
            ->where('status', 'error')
            ->count();

        return response()->json([
            'service' => $service,
            'period_hours' => $hours,
            'metrics' => [
                'total_predictions' => $totalPredictions,
                'cache_hit_rate_percent' => $cacheHitRate,
                'avg_latency_ms' => round($avgLatency, 2),
                'error_count' => $errorCount,
                'error_rate_percent' => $totalPredictions > 0
                    ? round(($errorCount / $totalPredictions) * 100, 2)
                    : 0,
            ],
        ]);
    }
}

