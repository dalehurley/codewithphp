<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\ML\SentimentAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPredictionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly string $service,
        public readonly mixed $input,
        public readonly ?string $callbackUrl = null,
    ) {}

    public function handle(): void
    {
        try {
            $result = match ($this->service) {
                'sentiment' => app(SentimentAnalysisService::class)->predict($this->input),
                default => throw new \RuntimeException("Unknown service: {$this->service}"),
            };

            Log::info("Background prediction completed", [
                'service' => $this->service,
                'result' => $result,
            ]);

            // If callback URL provided, POST results
            if ($this->callbackUrl) {
                $this->sendCallback($result);
            }
        } catch (\Exception $e) {
            Log::error("Background prediction failed", [
                'service' => $this->service,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw for retry logic
        }
    }

    private function sendCallback(mixed $result): void
    {
        try {
            $ch = curl_init($this->callbackUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($result),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                Log::info("Callback succeeded", [
                    'url' => $this->callbackUrl,
                    'http_code' => $httpCode,
                ]);
            } else {
                Log::warning("Callback failed", [
                    'url' => $this->callbackUrl,
                    'http_code' => $httpCode,
                    'response' => $response,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("Callback exception: {$e->getMessage()}");
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Job failed after all retries", [
            'service' => $this->service,
            'error' => $exception->getMessage(),
        ]);
    }
}

