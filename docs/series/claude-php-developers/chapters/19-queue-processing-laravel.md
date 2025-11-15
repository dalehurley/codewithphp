---
title: "19: Queue-Based Processing with Laravel"
description: "Handle long-running Claude API requests asynchronously using Laravel queues, job batching, progress tracking, webhook notifications, and background processing."
series: "claude-php-developers"
chapter: 19
order: 19
difficulty: "Expert"
prerequisites:
  - "Laravel 11+ experience"
  - "Laravel queues understanding"
  - "Redis or database queue driver"
  - "Chapter 17: Building a Claude Service Class"
---

![19: Queue-Based Processing with Laravel](/images/claude-php/chapter-19-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 19</span>
</div>

# Chapter 19: Queue-Based Processing with Laravel

## Overview

Long-running Claude API requests can block your application and frustrate users. Laravel's queue system provides the perfect solution for asynchronous AI processing—enabling background jobs, batch operations, progress tracking, and retry logic for failed requests.

This chapter teaches you to build production-ready queue-based Claude integrations with Laravel, handling everything from simple background jobs to complex batch processing with real-time progress updates.

## Prerequisites

Before diving in, ensure you have:

- ✓ **Laravel 11+** installed and configured
- ✓ **Laravel queues** basic understanding
- ✓ **Redis** or database queue driver configured
- ✓ **Chapter 17** completed (Service class knowledge)

**Estimated Time**: 60-90 minutes

## Queue Architecture for Claude

```
HTTP Request
  ↓
Dispatch Job → Queue
  ↓
Queue Worker picks up job
  ↓
Execute Claude API call
  ↓
Store result / Trigger notification
  ↓
Return to user (webhook/polling/event)
```

## Basic Queue Job Setup

### Create Claude Processing Job

```php
<?php
# filename: app/Jobs/ProcessClaudeRequest.php
declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\ClaudeServiceInterface;
use App\Models\ClaudeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessClaudeRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 10;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300;

    public function __construct(
        public ClaudeRequest $request
    ) {}

    public function handle(ClaudeServiceInterface $claude): void
    {
        Log::info('Processing Claude request', [
            'request_id' => $this->request->id,
            'prompt' => substr($this->request->prompt, 0, 50)
        ]);

        try {
            // Update status to processing
            $this->request->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            // Make Claude API call
            $result = $claude->generateWithMetadata(
                prompt: $this->request->prompt,
                options: [
                    'max_tokens' => $this->request->max_tokens ?? 4096,
                    'temperature' => $this->request->temperature ?? 1.0,
                    'model' => $this->request->model ?? 'claude-sonnet-4-20250514',
                ]
            );

            // Store result
            $this->request->update([
                'status' => 'completed',
                'response' => $result['text'],
                'metadata' => $result['metadata'],
                'completed_at' => now(),
            ]);

            Log::info('Claude request completed', [
                'request_id' => $this->request->id,
                'tokens_used' => $result['metadata']['usage']['output_tokens']
            ]);

        } catch (\Exception $e) {
            Log::error('Claude request failed', [
                'request_id' => $this->request->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            $this->request->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'failed_at' => now(),
            ]);

            // Re-throw to trigger retry logic
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Claude request permanently failed', [
            'request_id' => $this->request->id,
            'error' => $exception->getMessage()
        ]);

        $this->request->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'failed_at' => now(),
        ]);
    }
}
```

### Database Migration for Tracking

```php
<?php
# filename: database/migrations/2025_01_01_000000_create_claude_requests_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claude_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('prompt');
            $table->longText('response')->nullable();
            $table->json('metadata')->nullable();
            $table->string('model')->default('claude-sonnet-4-20250514');
            $table->integer('max_tokens')->default(4096);
            $table->decimal('temperature', 3, 2)->default(1.0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claude_requests');
    }
};
```

### Model Definition

```php
<?php
# filename: app/Models/ClaudeRequest.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaudeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'prompt',
        'response',
        'metadata',
        'model',
        'max_tokens',
        'temperature',
        'error_message',
        'started_at',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'max_tokens' => 'integer',
        'temperature' => 'float',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function getDuration(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }
}
```

## Controller Implementation

```php
<?php
# filename: app/Http/Controllers/ClaudeRequestController.php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ProcessClaudeRequest;
use App\Models\ClaudeRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClaudeRequestController extends Controller
{
    /**
     * Submit a new Claude request (queued)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:50000',
            'max_tokens' => 'nullable|integer|min:1|max:200000',
            'temperature' => 'nullable|numeric|min:0|max:1',
            'model' => 'nullable|string|in:claude-opus-4-20250514,claude-sonnet-4-20250514,claude-haiku-4-20250514',
        ]);

        // Create request record
        $claudeRequest = ClaudeRequest::create([
            'user_id' => $request->user()->id,
            'prompt' => $validated['prompt'],
            'max_tokens' => $validated['max_tokens'] ?? 4096,
            'temperature' => $validated['temperature'] ?? 1.0,
            'model' => $validated['model'] ?? 'claude-sonnet-4-20250514',
            'status' => 'pending',
        ]);

        // Dispatch to queue
        ProcessClaudeRequest::dispatch($claudeRequest);

        return response()->json([
            'success' => true,
            'request_id' => $claudeRequest->id,
            'status' => $claudeRequest->status,
            'message' => 'Request queued for processing',
        ], 202);
    }

    /**
     * Get request status
     */
    public function show(ClaudeRequest $claudeRequest): JsonResponse
    {
        $this->authorize('view', $claudeRequest);

        return response()->json([
            'id' => $claudeRequest->id,
            'status' => $claudeRequest->status,
            'prompt' => $claudeRequest->prompt,
            'response' => $claudeRequest->response,
            'metadata' => $claudeRequest->metadata,
            'error_message' => $claudeRequest->error_message,
            'created_at' => $claudeRequest->created_at,
            'started_at' => $claudeRequest->started_at,
            'completed_at' => $claudeRequest->completed_at,
            'duration_seconds' => $claudeRequest->getDuration(),
        ]);
    }

    /**
     * List user's requests
     */
    public function index(Request $request): JsonResponse
    {
        $requests = ClaudeRequest::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($requests);
    }
}
```

## Batch Processing

Process multiple prompts in a batch with progress tracking:

```php
<?php
# filename: app/Jobs/ProcessClaudeBatch.php
declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\ClaudeServiceInterface;
use App\Models\ClaudeBatch;
use App\Models\ClaudeBatchItem;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessClaudeBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public ClaudeBatchItem $item
    ) {}

    public function handle(ClaudeServiceInterface $claude): void
    {
        // Check if batch has been cancelled
        if ($this->batch()?->cancelled()) {
            return;
        }

        try {
            $this->item->update(['status' => 'processing']);

            $result = $claude->generateWithMetadata(
                prompt: $this->item->prompt,
                options: [
                    'max_tokens' => $this->item->batch->max_tokens,
                    'temperature' => $this->item->batch->temperature,
                    'model' => $this->item->batch->model,
                ]
            );

            $this->item->update([
                'status' => 'completed',
                'response' => $result['text'],
                'metadata' => $result['metadata'],
                'completed_at' => now(),
            ]);

        } catch (\Exception $e) {
            $this->item->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'failed_at' => now(),
            ]);

            throw $e;
        }
    }
}
```

### Batch Controller

```php
<?php
# filename: app/Http/Controllers/ClaudeBatchController.php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ProcessClaudeBatch;
use App\Models\ClaudeBatch;
use App\Models\ClaudeBatchItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class ClaudeBatchController extends Controller
{
    /**
     * Create and process a batch of Claude requests
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompts' => 'required|array|min:1|max:100',
            'prompts.*' => 'required|string|max:50000',
            'max_tokens' => 'nullable|integer|min:1|max:200000',
            'temperature' => 'nullable|numeric|min:0|max:1',
            'model' => 'nullable|string',
        ]);

        // Create batch
        $batch = ClaudeBatch::create([
            'user_id' => $request->user()->id,
            'total_items' => count($validated['prompts']),
            'max_tokens' => $validated['max_tokens'] ?? 4096,
            'temperature' => $validated['temperature'] ?? 1.0,
            'model' => $validated['model'] ?? 'claude-sonnet-4-20250514',
        ]);

        // Create batch items
        $jobs = [];
        foreach ($validated['prompts'] as $index => $prompt) {
            $item = ClaudeBatchItem::create([
                'batch_id' => $batch->id,
                'prompt' => $prompt,
                'position' => $index,
                'status' => 'pending',
            ]);

            $jobs[] = new ProcessClaudeBatch($item);
        }

        // Dispatch batch
        $laravelBatch = Bus::batch($jobs)
            ->then(function () use ($batch) {
                $batch->update(['status' => 'completed', 'completed_at' => now()]);
            })
            ->catch(function () use ($batch) {
                $batch->update(['status' => 'failed', 'failed_at' => now()]);
            })
            ->finally(function () use ($batch) {
                // Any cleanup
            })
            ->name("Claude Batch {$batch->id}")
            ->onQueue('claude')
            ->dispatch();

        $batch->update(['batch_id' => $laravelBatch->id]);

        return response()->json([
            'success' => true,
            'batch_id' => $batch->id,
            'total_items' => $batch->total_items,
            'status' => $batch->status,
        ], 202);
    }

    /**
     * Get batch status and progress
     */
    public function show(ClaudeBatch $batch): JsonResponse
    {
        $this->authorize('view', $batch);

        $batch->load('items');

        $completed = $batch->items->where('status', 'completed')->count();
        $failed = $batch->items->where('status', 'failed')->count();
        $processing = $batch->items->where('status', 'processing')->count();
        $pending = $batch->items->where('status', 'pending')->count();

        return response()->json([
            'id' => $batch->id,
            'status' => $batch->status,
            'total_items' => $batch->total_items,
            'progress' => [
                'completed' => $completed,
                'failed' => $failed,
                'processing' => $processing,
                'pending' => $pending,
                'percentage' => ($completed + $failed) / $batch->total_items * 100,
            ],
            'items' => $batch->items->map(fn($item) => [
                'id' => $item->id,
                'position' => $item->position,
                'status' => $item->status,
                'prompt' => substr($item->prompt, 0, 100),
                'response' => $item->response ? substr($item->response, 0, 100) : null,
                'error_message' => $item->error_message,
            ]),
            'created_at' => $batch->created_at,
            'completed_at' => $batch->completed_at,
        ]);
    }
}
```

## Real-Time Progress with WebSockets

Use Laravel Broadcasting for real-time updates:

```php
<?php
# filename: app/Events/ClaudeRequestCompleted.php
declare(strict_types=1);

namespace App\Events;

use App\Models\ClaudeRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClaudeRequestCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ClaudeRequest $request
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('users.' . $this->request->user_id);
    }

    public function broadcastAs(): string
    {
        return 'claude.request.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'request_id' => $this->request->id,
            'status' => $this->request->status,
            'response' => $this->request->response,
            'metadata' => $this->request->metadata,
        ];
    }
}
```

Update the job to fire the event:

```php
<?php
# filename: app/Jobs/ProcessClaudeRequest.php (updated)

// In the handle method, after successful completion:
$this->request->update([
    'status' => 'completed',
    'response' => $result['text'],
    'metadata' => $result['metadata'],
    'completed_at' => now(),
]);

// Fire event
event(new \App\Events\ClaudeRequestCompleted($this->request));
```

### Frontend Integration

```javascript
// resources/js/claude-listener.js

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});

// Listen for Claude request completions
Echo.private(`users.${userId}`)
    .listen('.claude.request.completed', (event) => {
        console.log('Claude request completed:', event);

        // Update UI
        updateRequestStatus(event.request_id, {
            status: event.status,
            response: event.response,
            metadata: event.metadata
        });
    });

function updateRequestStatus(requestId, data) {
    const element = document.querySelector(`[data-request-id="${requestId}"]`);
    if (element) {
        element.querySelector('.status').textContent = data.status;
        element.querySelector('.response').textContent = data.response;
        element.classList.add('completed');
    }
}
```

## Advanced Queue Configuration

### Priority Queues

```php
<?php
# filename: config/queue.php

return [
    'connections' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 300,
            'block_for' => null,
            'after_commit' => false,
        ],
    ],
];
```

```php
<?php
# Dispatch with priority

// High priority (fast model for urgent requests)
ProcessClaudeRequest::dispatch($request)
    ->onQueue('claude-high');

// Normal priority
ProcessClaudeRequest::dispatch($request)
    ->onQueue('claude-normal');

// Low priority (batch processing)
ProcessClaudeRequest::dispatch($request)
    ->onQueue('claude-low');
```

### Worker Configuration

```bash
# filename: supervisor-claude-workers.conf

[program:claude-high-priority]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --queue=claude-high --tries=3 --timeout=300
autostart=true
autorestart=true
numprocs=3
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/laravel/claude-high.log

[program:claude-normal]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --queue=claude-normal --tries=3 --timeout=300
autostart=true
autorestart=true
numprocs=5
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/laravel/claude-normal.log

[program:claude-low]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --queue=claude-low --tries=2 --timeout=600
autostart=true
autorestart=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/laravel/claude-low.log
```

## Rate Limiting with Queues

```php
<?php
# filename: app/Jobs/ProcessClaudeRequest.php (with rate limiting)

use Illuminate\Support\Facades\RateLimiter;

public function handle(ClaudeServiceInterface $claude): void
{
    // Rate limit: 50 requests per minute
    RateLimiter::attempt(
        'claude-api',
        50,
        function () use ($claude) {
            // Original processing logic here
            $this->processRequest($claude);
        },
        60
    );
}

private function processRequest(ClaudeServiceInterface $claude): void
{
    // ... processing logic
}
```

## Webhook Notifications

```php
<?php
# filename: app/Jobs/SendClaudeWebhook.php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\ClaudeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendClaudeWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ClaudeRequest $request,
        public string $webhookUrl
    ) {}

    public function handle(): void
    {
        $payload = [
            'event' => 'claude.request.completed',
            'request_id' => $this->request->id,
            'status' => $this->request->status,
            'response' => $this->request->response,
            'metadata' => $this->request->metadata,
            'timestamp' => now()->toIso8601String(),
        ];

        Http::timeout(10)
            ->retry(3, 100)
            ->post($this->webhookUrl, $payload);
    }
}
```

## Troubleshooting

**Jobs not processing?**
- Ensure queue worker is running: `php artisan queue:work`
- Check queue connection in `.env`: `QUEUE_CONNECTION=redis`
- Verify Redis is running: `redis-cli ping`
- Check failed jobs table: `php artisan queue:failed-table`

**Jobs timing out?**
- Increase timeout in job: `public int $timeout = 600;`
- Increase worker timeout: `php artisan queue:work --timeout=600`
- Check max execution time in `php.ini`

**Memory leaks in workers?**
- Restart workers regularly: `php artisan queue:restart`
- Use `--max-jobs` flag: `php artisan queue:work --max-jobs=100`
- Use `--max-time` flag: `php artisan queue:work --max-time=3600`

**Failed jobs not retrying?**
- Check `$tries` property in job class
- Verify backoff strategy: `public int $backoff = 10;`
- Review failed jobs: `php artisan queue:failed`
- Retry specific job: `php artisan queue:retry {id}`

## Key Takeaways

- ✓ Queues enable asynchronous processing of long-running Claude requests
- ✓ Laravel's job batching handles multiple requests efficiently
- ✓ Real-time progress updates enhance user experience
- ✓ Priority queues allow different SLAs for different request types
- ✓ Rate limiting prevents API quota exhaustion
- ✓ Webhook notifications enable external integrations
- ✓ Proper error handling and retries ensure reliability

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="19"
  label="You've mastered queue-based Claude processing with Laravel!"
/>

---

Continue to [Chapter 20: Real-time Chat with WebSockets](/series/claude-php-developers/chapters/20-realtime-chat-websockets) to build interactive chat applications.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 19 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-19)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-19
composer install
php artisan migrate
php artisan queue:work redis --queue=claude
```
