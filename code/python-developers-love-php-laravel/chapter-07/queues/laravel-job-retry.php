<?php

declare(strict_types=1);

/**
 * Laravel Job with retry logic example comparing to Celery retry.
 * 
 * File: app/Jobs/ProcessPaymentJob.php
 * Run worker: php artisan queue:work
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Max retries
    public $backoff = [60, 120, 300]; // Delay between retries (seconds)

    public function __construct(
        public int $userId,
        public float $amount
    ) {}

    public function handle(): void
    {
        if ($this->amount < 0) {
            throw new \InvalidArgumentException('Invalid amount');
        }

        // Process payment
        logger()->info('Processing payment', [
            'user_id' => $this->userId,
            'amount' => $this->amount
        ]);
    }
}

// Usage example:
// use App\Jobs\ProcessPaymentJob;
//
// // Dispatch job (will retry up to 3 times on failure)
// ProcessPaymentJob::dispatch(1, 100.00);

