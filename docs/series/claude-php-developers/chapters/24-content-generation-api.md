---
title: "24: Content Generation API"
description: "Build a production-ready RESTful API for AI content generation. Implement templates, style guides, brand voice consistency, batch generation, API authentication, and usage tracking."
series: "claude-php-developers"
chapter: 24
order: 24
difficulty: "Advanced"
prerequisites:
  - "Laravel 11+ with API routes"
  - "Understanding of REST APIs"
  - "Laravel Sanctum for authentication"
  - "Completion of Chapter 21"
---

![24: Content Generation API](/images/claude-php/chapter-24-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 24</span>
</div>

# Chapter 24: Content Generation API

## Overview

In this chapter, you'll build a complete, production-ready RESTful API for AI-powered content generation. This API will enable clients to generate blog posts, product descriptions, social media content, and marketing copy with consistent brand voice, customizable templates, and intelligent style guides.

You'll implement API authentication, rate limiting, usage tracking, batch generation, and webhook notifications—everything needed for a commercial content generation service.

**What You'll Learn:**
- RESTful API design for content generation
- Template system with variables and constraints
- Brand voice and style guide enforcement
- Multi-format content generation (blog, social, email)
- Batch generation with job queuing
- API authentication with Laravel Sanctum
- Rate limiting and quota management
- Usage tracking and analytics
- Webhook notifications for async operations
- Cost calculation and billing integration
- API versioning and documentation

**Estimated Time**: 120-150 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Laravel 11+** with API routes configured
- ✓ **Laravel Sanctum** for API authentication
- ✓ **Queue system** configured (Redis recommended)
- ✓ **Claude service** from Chapter 21
- ✓ **Database** configured
- ✓ **Understanding of REST APIs**

## Database Schema

### Migrations

```php
<?php
# filename: database/migrations/2024_01_01_000001_create_content_templates_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // blog_post, product_description, social_media, etc.
            $table->text('description')->nullable();
            $table->text('prompt_template');
            $table->json('variables'); // Required variables
            $table->json('constraints')->nullable(); // Word limits, tone, etc.
            $table->json('style_guide')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_templates');
    }
};
```

```php
<?php
# filename: database/migrations/2024_01_01_000002_create_content_generations_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('content_templates')->nullOnDelete();
            $table->string('type');
            $table->text('prompt');
            $table->longText('generated_content')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('parameters')->nullable();
            $table->integer('input_tokens')->nullable();
            $table->integer('output_tokens')->nullable();
            $table->decimal('cost', 10, 6)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_generations');
    }
};
```

```php
<?php
# filename: database/migrations/2024_01_01_000003_create_brand_voices_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_voices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description');
            $table->json('characteristics'); // tone, style, language level
            $table->json('do_examples')->nullable(); // Examples of good content
            $table->json('dont_examples')->nullable(); // Examples to avoid
            $table->json('keywords')->nullable(); // Preferred terminology
            $table->json('avoid_words')->nullable(); // Words to avoid
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_voices');
    }
};
```

## Models

### ContentTemplate Model

```php
<?php
# filename: app/Models/ContentTemplate.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'prompt_template',
        'variables',
        'constraints',
        'style_guide',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'constraints' => 'array',
        'style_guide' => 'array',
        'is_active' => 'boolean',
    ];

    public function renderPrompt(array $data): string
    {
        $prompt = $this->prompt_template;

        // Replace variables
        foreach ($this->variables as $variable) {
            $value = $data[$variable] ?? '';
            $prompt = str_replace("{{$variable}}", $value, $prompt);
        }

        // Add constraints
        if ($this->constraints) {
            $prompt .= "\n\nConstraints:\n";
            foreach ($this->constraints as $key => $value) {
                $prompt .= "- {$key}: {$value}\n";
            }
        }

        // Add style guide
        if ($this->style_guide) {
            $prompt .= "\n\nStyle Guide:\n";
            foreach ($this->style_guide as $key => $value) {
                $prompt .= "- {$key}: {$value}\n";
            }
        }

        return $prompt;
    }
}
```

### ContentGeneration Model

```php
<?php
# filename: app/Models/ContentGeneration.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentGeneration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'type',
        'prompt',
        'generated_content',
        'status',
        'parameters',
        'input_tokens',
        'output_tokens',
        'cost',
        'error_message',
        'completed_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'cost' => 'decimal:6',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ContentTemplate::class);
    }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markAsCompleted(string $content, int $inputTokens, int $outputTokens, float $cost): void
    {
        $this->update([
            'status' => 'completed',
            'generated_content' => $content,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'cost' => $cost,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'completed_at' => now(),
        ]);
    }
}
```

### BrandVoice Model

```php
<?php
# filename: app/Models/BrandVoice.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandVoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'characteristics',
        'do_examples',
        'dont_examples',
        'keywords',
        'avoid_words',
        'is_default',
    ];

    protected $casts = [
        'characteristics' => 'array',
        'do_examples' => 'array',
        'dont_examples' => 'array',
        'keywords' => 'array',
        'avoid_words' => 'array',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSystemPrompt(): string
    {
        $prompt = "You are a content writer following this brand voice:\n\n";
        $prompt .= "**Brand:** {$this->name}\n";
        $prompt .= "**Description:** {$this->description}\n\n";

        if ($this->characteristics) {
            $prompt .= "**Characteristics:**\n";
            foreach ($this->characteristics as $key => $value) {
                $prompt .= "- {$key}: {$value}\n";
            }
            $prompt .= "\n";
        }

        if ($this->keywords) {
            $prompt .= "**Preferred Keywords:** " . implode(', ', $this->keywords) . "\n\n";
        }

        if ($this->avoid_words) {
            $prompt .= "**Avoid These Words:** " . implode(', ', $this->avoid_words) . "\n\n";
        }

        if ($this->do_examples) {
            $prompt .= "**Good Examples:**\n";
            foreach ($this->do_examples as $example) {
                $prompt .= "- {$example}\n";
            }
            $prompt .= "\n";
        }

        if ($this->dont_examples) {
            $prompt .= "**Bad Examples (avoid this style):**\n";
            foreach ($this->dont_examples as $example) {
                $prompt .= "- {$example}\n";
            }
        }

        return $prompt;
    }
}
```

## Content Generation Service

```php
<?php
# filename: app/Services/ContentGenerationService.php
declare(strict_types=1);

namespace App\Services;

use App\Facades\Claude;
use App\Models\BrandVoice;
use App\Models\ContentGeneration;
use App\Models\ContentTemplate;

class ContentGenerationService
{
    private const PRICING = [
        'claude-opus-4-20250514' => ['input' => 15.00, 'output' => 75.00],
        'claude-sonnet-4-20250514' => ['input' => 3.00, 'output' => 15.00],
        'claude-haiku-4-20250514' => ['input' => 0.25, 'output' => 1.25],
    ];

    public function generate(
        ContentGeneration $generation,
        ?BrandVoice $brandVoice = null
    ): void {
        $generation->markAsProcessing();

        try {
            $systemPrompt = $brandVoice?->getSystemPrompt();

            $model = $generation->parameters['model'] ?? config('claude.default_model');
            $temperature = $generation->parameters['temperature'] ?? 0.7;
            $maxTokens = $generation->parameters['max_tokens'] ?? 2048;

            $result = Claude::withModel($model)
                ->chat(
                    $generation->prompt,
                    [],
                    $systemPrompt
                );

            $cost = $this->calculateCost(
                $model,
                $result['usage']['input_tokens'],
                $result['usage']['output_tokens']
            );

            $generation->markAsCompleted(
                $result['response'],
                $result['usage']['input_tokens'],
                $result['usage']['output_tokens'],
                $cost
            );

        } catch (\Exception $e) {
            $generation->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    public function generateFromTemplate(
        ContentTemplate $template,
        array $data,
        int $userId,
        ?BrandVoice $brandVoice = null
    ): ContentGeneration {
        $prompt = $template->renderPrompt($data);

        $generation = ContentGeneration::create([
            'user_id' => $userId,
            'template_id' => $template->id,
            'type' => $template->type,
            'prompt' => $prompt,
            'parameters' => $data['parameters'] ?? [],
            'status' => 'pending',
        ]);

        $this->generate($generation, $brandVoice);

        return $generation->fresh();
    }

    public function batchGenerate(
        array $requests,
        int $userId,
        ?BrandVoice $brandVoice = null
    ): array {
        $generations = [];

        foreach ($requests as $request) {
            $template = ContentTemplate::findOrFail($request['template_id']);

            $generation = ContentGeneration::create([
                'user_id' => $userId,
                'template_id' => $template->id,
                'type' => $template->type,
                'prompt' => $template->renderPrompt($request['data']),
                'parameters' => $request['parameters'] ?? [],
                'status' => 'pending',
            ]);

            // Queue for async processing
            \App\Jobs\GenerateContentJob::dispatch($generation->id, $brandVoice?->id);

            $generations[] = $generation;
        }

        return $generations;
    }

    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = self::PRICING[$model] ?? ['input' => 0, 'output' => 0];

        $inputCost = ($inputTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($outputTokens / 1_000_000) * $pricing['output'];

        return $inputCost + $outputCost;
    }
}
```

## API Controllers

### ContentGenerationController

```php
<?php
# filename: app/Http/Controllers/Api/ContentGenerationController.php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GenerateContentRequest;
use App\Http\Resources\ContentGenerationResource;
use App\Models\BrandVoice;
use App\Models\ContentGeneration;
use App\Models\ContentTemplate;
use App\Services\ContentGenerationService;
use Illuminate\Http\Request;

class ContentGenerationController extends Controller
{
    public function __construct(
        private readonly ContentGenerationService $service
    ) {}

    /**
     * Generate content from template
     */
    public function generate(GenerateContentRequest $request)
    {
        $template = ContentTemplate::findOrFail($request->template_id);

        $brandVoice = null;
        if ($request->has('brand_voice_id')) {
            $brandVoice = BrandVoice::where('user_id', $request->user()->id)
                ->findOrFail($request->brand_voice_id);
        }

        $generation = $this->service->generateFromTemplate(
            $template,
            $request->validated(),
            $request->user()->id,
            $brandVoice
        );

        return new ContentGenerationResource($generation);
    }

    /**
     * Batch generate multiple content pieces
     */
    public function batchGenerate(Request $request)
    {
        $request->validate([
            'requests' => 'required|array|max:10',
            'requests.*.template_id' => 'required|exists:content_templates,id',
            'requests.*.data' => 'required|array',
            'brand_voice_id' => 'nullable|exists:brand_voices,id',
        ]);

        $brandVoice = null;
        if ($request->has('brand_voice_id')) {
            $brandVoice = BrandVoice::where('user_id', $request->user()->id)
                ->findOrFail($request->brand_voice_id);
        }

        $generations = $this->service->batchGenerate(
            $request->requests,
            $request->user()->id,
            $brandVoice
        );

        return ContentGenerationResource::collection($generations);
    }

    /**
     * Get generation status
     */
    public function show(Request $request, ContentGeneration $generation)
    {
        $this->authorize('view', $generation);

        return new ContentGenerationResource($generation);
    }

    /**
     * List user's generations
     */
    public function index(Request $request)
    {
        $generations = ContentGeneration::where('user_id', $request->user()->id)
            ->with('template')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return ContentGenerationResource::collection($generations);
    }
}
```

### TemplateController

```php
<?php
# filename: app/Http/Controllers/Api/TemplateController.php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContentTemplateResource;
use App\Models\ContentTemplate;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * List available templates
     */
    public function index(Request $request)
    {
        $query = ContentTemplate::where('is_active', true);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $templates = $query->get();

        return ContentTemplateResource::collection($templates);
    }

    /**
     * Get template details
     */
    public function show(ContentTemplate $template)
    {
        return new ContentTemplateResource($template);
    }
}
```

### BrandVoiceController

```php
<?php
# filename: app/Http/Controllers/Api/BrandVoiceController.php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBrandVoiceRequest;
use App\Http\Resources\BrandVoiceResource;
use App\Models\BrandVoice;
use Illuminate\Http\Request;

class BrandVoiceController extends Controller
{
    /**
     * List user's brand voices
     */
    public function index(Request $request)
    {
        $brandVoices = BrandVoice::where('user_id', $request->user()->id)
            ->get();

        return BrandVoiceResource::collection($brandVoices);
    }

    /**
     * Create brand voice
     */
    public function store(StoreBrandVoiceRequest $request)
    {
        // If setting as default, unset other defaults
        if ($request->is_default) {
            BrandVoice::where('user_id', $request->user()->id)
                ->update(['is_default' => false]);
        }

        $brandVoice = BrandVoice::create([
            'user_id' => $request->user()->id,
            ...$request->validated(),
        ]);

        return new BrandVoiceResource($brandVoice);
    }

    /**
     * Update brand voice
     */
    public function update(StoreBrandVoiceRequest $request, BrandVoice $brandVoice)
    {
        $this->authorize('update', $brandVoice);

        if ($request->is_default) {
            BrandVoice::where('user_id', $request->user()->id)
                ->where('id', '!=', $brandVoice->id)
                ->update(['is_default' => false]);
        }

        $brandVoice->update($request->validated());

        return new BrandVoiceResource($brandVoice);
    }

    /**
     * Delete brand voice
     */
    public function destroy(BrandVoice $brandVoice)
    {
        $this->authorize('delete', $brandVoice);

        $brandVoice->delete();

        return response()->json(['message' => 'Brand voice deleted successfully']);
    }
}
```

## API Resources

### ContentGenerationResource

```php
<?php
# filename: app/Http/Resources/ContentGenerationResource.php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentGenerationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'template' => $this->when($this->template, fn() => [
                'id' => $this->template->id,
                'name' => $this->template->name,
            ]),
            'prompt' => $this->when($request->user()->can('view', $this->resource), $this->prompt),
            'generated_content' => $this->when($this->status === 'completed', $this->generated_content),
            'usage' => $this->when($this->status === 'completed', [
                'input_tokens' => $this->input_tokens,
                'output_tokens' => $this->output_tokens,
                'total_tokens' => $this->input_tokens + $this->output_tokens,
                'cost' => (float) $this->cost,
            ]),
            'error' => $this->when($this->status === 'failed', $this->error_message),
            'created_at' => $this->created_at,
            'completed_at' => $this->completed_at,
        ];
    }
}
```

## API Routes

```php
<?php
# filename: routes/api.php

use App\Http\Controllers\Api\BrandVoiceController;
use App\Http\Controllers\Api\ContentGenerationController;
use App\Http\Controllers\Api\TemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // Templates
    Route::get('/templates', [TemplateController::class, 'index']);
    Route::get('/templates/{template}', [TemplateController::class, 'show']);

    // Content Generation
    Route::post('/generate', [ContentGenerationController::class, 'generate'])
        ->middleware('throttle:60,1'); // 60 per minute
    Route::post('/generate/batch', [ContentGenerationController::class, 'batchGenerate'])
        ->middleware('throttle:10,1'); // 10 per minute
    Route::get('/generations', [ContentGenerationController::class, 'index']);
    Route::get('/generations/{generation}', [ContentGenerationController::class, 'show']);

    // Brand Voices
    Route::apiResource('brand-voices', BrandVoiceController::class);
});
```

## Job for Async Generation

```php
<?php
# filename: app/Jobs/GenerateContentJob.php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\BrandVoice;
use App\Models\ContentGeneration;
use App\Services\ContentGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $generationId,
        private readonly ?int $brandVoiceId = null
    ) {}

    public function handle(ContentGenerationService $service): void
    {
        $generation = ContentGeneration::findOrFail($this->generationId);

        $brandVoice = $this->brandVoiceId
            ? BrandVoice::find($this->brandVoiceId)
            : null;

        $service->generate($generation, $brandVoice);

        // Send webhook notification if configured
        if ($generation->user->webhook_url) {
            \App\Jobs\SendWebhookNotification::dispatch(
                $generation->user->webhook_url,
                [
                    'event' => 'generation.completed',
                    'generation_id' => $generation->id,
                    'status' => $generation->status,
                ]
            );
        }
    }
}
```

## Seeder for Templates

```php
<?php
# filename: database/seeders/ContentTemplateSeeder.php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContentTemplate;
use Illuminate\Database\Seeder;

class ContentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        ContentTemplate::create([
            'name' => 'Blog Post',
            'type' => 'blog_post',
            'description' => 'Generate a complete blog post with introduction, body, and conclusion',
            'prompt_template' => <<<PROMPT
Write a comprehensive blog post about: {topic}

Target Audience: {target_audience}
Tone: {tone}
Key Points to Cover:
{key_points}

Include:
- Engaging introduction
- Well-structured body sections
- Actionable insights
- Compelling conclusion with call-to-action
PROMPT,
            'variables' => ['topic', 'target_audience', 'tone', 'key_points'],
            'constraints' => [
                'word_count' => '800-1200 words',
                'structure' => 'Introduction, 3-5 main sections, conclusion',
                'seo' => 'Include relevant keywords naturally',
            ],
            'style_guide' => [
                'format' => 'Markdown with headings',
                'voice' => 'Professional yet conversational',
                'readability' => 'Grade 8-10 reading level',
            ],
        ]);

        ContentTemplate::create([
            'name' => 'Product Description',
            'type' => 'product_description',
            'description' => 'Generate compelling product descriptions for e-commerce',
            'prompt_template' => <<<PROMPT
Write a compelling product description for: {product_name}

Product Category: {category}
Key Features:
{features}

Benefits:
{benefits}

Target Customer: {target_customer}

Create a description that:
- Highlights unique value proposition
- Appeals to target customer emotions
- Includes key features and benefits
- Ends with a call-to-action
PROMPT,
            'variables' => ['product_name', 'category', 'features', 'benefits', 'target_customer'],
            'constraints' => [
                'length' => '150-250 words',
                'tone' => 'Persuasive and benefit-focused',
            ],
        ]);

        ContentTemplate::create([
            'name' => 'Social Media Post',
            'type' => 'social_media',
            'description' => 'Create engaging social media content',
            'prompt_template' => <<<PROMPT
Create a {platform} post about: {topic}

Message: {message}
Call-to-Action: {cta}

Requirements:
- Engaging hook in first line
- Platform-appropriate length and style
- Include relevant emojis
- Hashtag suggestions
PROMPT,
            'variables' => ['platform', 'topic', 'message', 'cta'],
            'constraints' => [
                'twitter' => 'Max 280 characters',
                'linkedin' => '100-200 words, professional tone',
                'instagram' => 'Visual description, hashtags',
            ],
        ]);
    }
}
```

## Usage Example

```bash
# Generate content via API
curl -X POST https://api.example.com/v1/generate \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "template_id": 1,
    "topic": "Laravel Best Practices",
    "target_audience": "PHP developers",
    "tone": "professional",
    "key_points": "Service containers, facades, eloquent ORM",
    "brand_voice_id": 1
  }'

# Response
{
  "data": {
    "id": 123,
    "type": "blog_post",
    "status": "completed",
    "generated_content": "# Laravel Best Practices...",
    "usage": {
      "input_tokens": 245,
      "output_tokens": 1203,
      "total_tokens": 1448,
      "cost": 0.018795
    },
    "created_at": "2024-01-15T10:30:00Z",
    "completed_at": "2024-01-15T10:30:45Z"
  }
}
```

## Exercises

### Exercise 1: Add Content Variations

Generate multiple variations of the same content:

```php
<?php
public function generateVariations(
    ContentTemplate $template,
    array $data,
    int $count = 3
): array {
    // TODO: Generate N variations with different temperatures
    // TODO: Return array of generations
}
```

### Exercise 2: Content Refinement Endpoint

Allow users to refine generated content:

```php
<?php
public function refine(ContentGeneration $generation, string $instructions): ContentGeneration
{
    // TODO: Take existing content and refinement instructions
    // TODO: Generate improved version
    // TODO: Track refinement history
}
```

### Exercise 3: Analytics Dashboard

Build usage analytics:

```php
<?php
public function getAnalytics(int $userId, string $period = '30days'): array
{
    // TODO: Calculate total generations
    // TODO: Cost breakdown by template type
    // TODO: Success rate
    // TODO: Average generation time
}
```

<details>
<summary>Solution Hints</summary>

**Exercise 1**: Loop N times, vary temperature (0.7, 0.9, 1.0) or add variation instructions. Return all generations with unique IDs.

**Exercise 2**: Create new generation with prompt: "Improve this content based on: {instructions}\n\nOriginal:{content}". Link to original via metadata.

**Exercise 3**: Query ContentGeneration model grouped by type, date range. Calculate sum(cost), avg(output_tokens), count by status. Return formatted analytics.

</details>

## Troubleshooting

**Rate limiting too strict?**
- Adjust throttle values in routes
- Implement tiered rate limits based on user plan
- Add burst allowance for premium users

**Batch jobs timing out?**
- Reduce batch size limit
- Increase job timeout in queue config
- Process batches in smaller chunks

**Inconsistent brand voice?**
- Provide more detailed examples in brand voice
- Use lower temperature (0.5-0.7)
- Include specific do/don't examples

**High API costs?**
- Use Haiku for simple templates
- Implement aggressive result caching
- Offer preview mode with truncated output

## Key Takeaways

- ✓ **Template System** enables reusable content patterns
- ✓ **Brand Voice** ensures consistent output
- ✓ **Batch Processing** improves efficiency
- ✓ **API Authentication** protects resources
- ✓ **Rate Limiting** prevents abuse
- ✓ **Usage Tracking** enables billing
- ✓ **Async Jobs** handle long operations
- ✓ **Cost Calculation** supports transparent pricing

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="24"
  label="You've built a production-ready content generation API!"
/>

---

Continue to [Chapter 25: Admin Panel with AI Features](/series/claude-php-developers/chapters/25-admin-panel-ai) to add AI superpowers to Laravel admin panels.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 24 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-24)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-24
composer install
cp .env.example .env
# Add your ANTHROPIC_API_KEY to .env
php artisan migrate --seed
php artisan serve
```
