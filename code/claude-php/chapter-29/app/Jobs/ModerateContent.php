<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ModerationItem;
use App\Services\ModerationQueueService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Moderate Content Job
 *
 * Processes content moderation in background queue
 */
class ModerateContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public ModerationItem $item
    ) {
    }

    /**
     * Execute the job
     */
    public function handle(ModerationQueueService $moderationQueue): void
    {
        $moderationQueue->process($this->item);
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        $this->item->update([
            'status' => 'error',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
