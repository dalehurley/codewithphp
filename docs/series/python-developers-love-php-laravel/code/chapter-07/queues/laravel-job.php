<?php

declare(strict_types=1);

/**
 * Laravel Job example comparing to Celery tasks.
 * 
 * File: app/Jobs/SendEmailJob.php
 * Run worker: php artisan queue:work
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $to,
        public string $subject,
        public string $body
    ) {}

    public function handle(): void
    {
        // Send email
        Mail::to($this->to)->send(new \App\Mail\WelcomeMail($this->subject, $this->body));
    }

    public function failed(\Throwable $exception): void
    {
        // Handle job failure
        logger()->error('Email job failed', [
            'to' => $this->to,
            'error' => $exception->getMessage()
        ]);
    }
}

// Usage example:
// use App\Jobs\SendEmailJob;
//
// // Dispatch job
// SendEmailJob::dispatch('user@example.com', 'Welcome', 'Welcome to our app!');
//
// // Dispatch with delay
// SendEmailJob::dispatch('user@example.com', 'Welcome', 'Welcome!')
//     ->delay(now()->addMinutes(5));
//
// // Dispatch to specific queue
// SendEmailJob::dispatch('user@example.com', 'Welcome', 'Welcome!')
//     ->onQueue('emails');

