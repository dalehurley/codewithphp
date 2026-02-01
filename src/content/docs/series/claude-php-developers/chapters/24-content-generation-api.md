---
title: "24: Content Generation API"
description: "Build a production-ready RESTful API for AI content generation. Implement templates, style guides, brand voice consistency, batch generation, API authentication, and usage tracking."
sidebar:
  label: "24: Content Generation API"
  order: 24
  badge:
    text: Advanced
    variant: danger
---
![24: Content Generation API](/images/claude-php/chapter-24-hero-full.webp)


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

## What You'll Build

By the end of this chapter, you will have created:

- A complete RESTful API for AI content generation
- Template system with variables and constraints
- Brand voice management system
- Batch content generation with job queuing
- API authentication with Laravel Sanctum
- Rate limiting and usage tracking
- Cost calculation and billing integration
- Webhook notifications for async operations
- API resources and request validation
- Database schema for templates, generations, and brand voices

## Prerequisites

Before starting, ensure you have:

- ✓ **Laravel 11+** with API routes configured
- ✓ **Laravel Sanctum** for API authentication
- ✓ **Queue system** configured (Redis recommended)
- ✓ **Claude service** from Chapter 21
- ✓ **Database** configured
- ✓ **Understanding of REST APIs**

## Objectives

By completing this chapter, you will:

- Design and implement a RESTful API for content generation
- Create a flexible template system with variable substitution
- Build brand voice management with style guide enforcement
- Implement batch generation with async job processing
- Set up API authentication and authorization
- Configure rate limiting and usage tracking
- Calculate costs and integrate billing systems
- Send webhook notifications for async operations
- Write API resources and request validation classes
- Design database schema for scalable content generation

## Quick Start

Here's a quick example of generating content via the API:

```bash
# 1. Get your API token (after authentication)
TOKEN="your-sanctum-token"

# 2. List available templates
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/templates

# 3. Generate content
curl -X POST http://localhost:8000/api/v1/generate \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "template_id": 1,
    "topic": "Laravel Best Practices",
    "target_audience": "PHP developers",
    "tone": "professional",
    "key_points": "Service containers, facades, eloquent ORM"
  }'

# 4. Check generation status
curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/v1/generations/123
```

This gives you a working API endpoint that generates AI content. Now let's build it step by step.

## Step 1: Set Up Database Schema (~15 min)

### Goal

Create the database tables needed for templates, content generations, and brand voices.

### Actions

1. **Create the migrations** for the three main tables:

The migrations define the structure for storing templates, tracking generations, and managing brand voices. Each table includes proper indexes for performance and foreign key constraints for data integrity.

### Expected Result

After running `php artisan migrate`, you'll have three tables:

- `content_templates` - Stores reusable content templates
- `content_generations` - Tracks all content generation requests
- `brand_voices` - Manages brand voice configurations per user

### Why It Works

The schema design separates concerns: templates define reusable patterns, generations track individual requests with status and costs, and brand voices ensure consistency. The `json` columns store flexible data structures (variables, constraints, parameters) without requiring schema changes for new features. Indexes on `user_id` and `status` optimize common queries like "show my pending generations."

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

## Step 2: Create Models (~10 min)

### Goal

Build Eloquent models with relationships and helper methods for template rendering and status management.

### Actions

1. **Create the ContentTemplate model** with prompt rendering logic:

The `renderPrompt()` method replaces template variables with actual data and appends constraints and style guides. This keeps prompt construction logic in the model where it belongs.

### Why It Works

Template variables use `{variable}` syntax that's replaced with actual values. Constraints and style guides are appended as structured text that Claude can parse. This approach allows templates to be stored in the database while keeping prompt construction logic in PHP for flexibility.

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

### Why It Works

The `ContentGeneration` model uses status methods (`markAsProcessing()`, `markAsCompleted()`, `markAsFailed()`) to encapsulate state transitions. This ensures status changes always update related fields like `completed_at` and `error_message` consistently. The `BelongsTo` relationships enable eager loading to reduce database queries.

The `BrandVoice` model's `getSystemPrompt()` method builds a comprehensive system prompt from stored characteristics, examples, and preferences. This prompt is passed to Claude to ensure all generated content follows the brand voice guidelines.

## Step 3: Build Content Generation Service (~20 min)

### Goal

Create a service class that handles the core content generation logic, cost calculation, and batch processing.

### Actions

1. **Create the ContentGenerationService** with generation, cost calculation, and batch processing methods:

The service encapsulates all Claude API interactions, handles errors gracefully, and calculates costs based on token usage. Batch generation queues jobs for async processing to avoid blocking API requests.

### Why It Works

The service pattern separates business logic from controllers, making the code testable and reusable. Cost calculation uses per-million-token pricing from Anthropic's pricing model. The `generateFromTemplate()` method creates a generation record, processes it synchronously, and returns the completed result—perfect for simple API calls. `batchGenerate()` creates records and queues jobs for async processing, ideal for bulk operations.

### Expected Result

You'll have a service that can:

- Generate content from templates with brand voice
- Calculate accurate costs based on token usage
- Process batches asynchronously via queues
- Handle errors and update generation status

## Content Generation Service

```php
<?php
# filename: app/Services/ContentGenerationService.php
declare(strict_types=1);

namespace App\Services;

use App\Models\BrandVoice;
use App\Models\ContentGeneration;
use App\Models\ContentTemplate;
use ClaudePhp\ClaudePhp;

class ContentGenerationService
{
    private const PRICING = [
        'claude-opus-4-1' => ['input' => 15.00, 'output' => 75.00],
        'claude-sonnet-4-5' => ['input' => 3.00, 'output' => 15.00],
        'claude-haiku-4-5-20251001' => ['input' => 0.25, 'output' => 1.25],
    ];

    public function __construct(
        private readonly ClaudePhp $client
    ) {}

    public function generate(
        ContentGeneration $generation,
        ?BrandVoice $brandVoice = null
    ): void {
        $generation->markAsProcessing();

        try {
            $systemPrompt = $brandVoice?->getSystemPrompt();

            $model = $generation->parameters['model'] ?? config('claude.default_model');
            $temperature = (float) ($generation->parameters['temperature'] ?? 0.7);
            $maxTokens = (int) ($generation->parameters['max_tokens'] ?? 2048);

            $response = $this->client->messages()->create(
                'model' => $model,
                'max_tokens' => $maxTokens,
                'messages' => [
                    ['role' => 'user', 'content' => $generation->prompt]
                ],
                'system' => $systemPrompt,
                'temperature' => $temperature
            );

            $inputTokens = $response->usage->inputTokens ?? 0;
            $outputTokens = $response->usage->outputTokens ?? 0;
            $content = $response->content[0]->text ?? '';

            $cost = $this->calculateCost(
                $model,
                $inputTokens,
                $outputTokens
            );

            $generation->markAsCompleted(
                $content,
                $inputTokens,
                $outputTokens,
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

## Step 4: Create API Controllers (~25 min)

### Goal

Build RESTful controllers that handle HTTP requests, validate input, and return properly formatted API responses.

### Actions

1. **Create ContentGenerationController** for content generation endpoints:

The controller uses dependency injection to receive the service, validates requests using form request classes, and returns API resources for consistent response formatting.

### Why It Works

Controllers are thin—they handle HTTP concerns (request/response) while delegating business logic to services. Form request validation runs before controller methods execute, ensuring data integrity. API resources transform models into consistent JSON responses, hiding internal structure and including only relevant data based on context (e.g., showing prompts only to owners).

### Expected Result

You'll have controllers that:

- Validate all inputs before processing
- Return consistent JSON responses
- Handle authorization properly
- Support pagination and filtering

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
        $query = ContentGeneration::where('user_id', $request->user()->id)
            ->with('template');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search in generated content
        if ($request->has('search')) {
            $query->where('generated_content', 'like', '%' . $request->search . '%');
        }

        $generations = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

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

## Step 5: Add Request Validation and API Resources (~15 min)

### Goal

Create form request classes for validation and API resources for consistent response formatting.

### Actions

1. **Create GenerateContentRequest** to validate content generation requests:

The request dynamically adds validation rules based on the selected template's required variables. This ensures all template variables are provided before generation starts.

### Why It Works

Form requests centralize validation logic and provide clear error messages. Dynamic rule generation based on template variables makes the API flexible—different templates can require different fields without code changes. The `authorize()` method returns `true` because Sanctum middleware handles authentication before the request reaches validation.

### Expected Result

You'll have validation that:

- Ensures all required template variables are provided
- Validates model names, temperature, and token limits
- Returns clear error messages for missing or invalid data

## Request Validation Classes

### GenerateContentRequest

```php
<?php
# filename: app/Http/Requests/Api/GenerateContentRequest.php
declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GenerateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Sanctum middleware
    }

    public function rules(): array
    {
        $template = \App\Models\ContentTemplate::find($this->template_id);

        $rules = [
            'template_id' => 'required|exists:content_templates,id',
            'brand_voice_id' => 'nullable|exists:brand_voices,id',
            'parameters' => 'nullable|array',
            'parameters.model' => 'nullable|string|in:claude-opus-4-1,claude-sonnet-4-5,claude-haiku-4-5',
            'parameters.temperature' => 'nullable|numeric|min:0|max:1',
            'parameters.max_tokens' => 'nullable|integer|min:1|max:4096',
        ];

        // Add rules for template variables
        if ($template) {
            foreach ($template->variables as $variable) {
                $rules[$variable] = 'required|string';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'template_id.required' => 'Please select a content template.',
            'template_id.exists' => 'The selected template does not exist.',
        ];
    }
}
```

### StoreBrandVoiceRequest

```php
<?php
# filename: app/Http/Requests/Api/StoreBrandVoiceRequest.php
declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandVoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by Sanctum middleware
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'characteristics' => 'required|array',
            'characteristics.tone' => 'required|string',
            'characteristics.style' => 'required|string',
            'characteristics.language_level' => 'nullable|string',
            'do_examples' => 'nullable|array',
            'do_examples.*' => 'string|max:500',
            'dont_examples' => 'nullable|array',
            'dont_examples.*' => 'string|max:500',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:50',
            'avoid_words' => 'nullable|array',
            'avoid_words.*' => 'string|max:50',
            'is_default' => 'nullable|boolean',
        ];
    }
}
```

## API Resources

### ContentTemplateResource

```php
<?php
# filename: app/Http/Resources/ContentTemplateResource.php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentTemplateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'variables' => $this->variables,
            'constraints' => $this->constraints,
            'style_guide' => $this->style_guide,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
```

### BrandVoiceResource

```php
<?php
# filename: app/Http/Resources/BrandVoiceResource.php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandVoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'characteristics' => $this->characteristics,
            'do_examples' => $this->do_examples,
            'dont_examples' => $this->dont_examples,
            'keywords' => $this->keywords,
            'avoid_words' => $this->avoid_words,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

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

### Why It Works

API Resources transform Eloquent models into JSON responses with conditional fields. The `when()` method only includes data when conditions are met (e.g., showing generated content only when status is 'completed'). This keeps responses clean and secure—users only see data they're authorized to view.

## Step 6: Configure API Routes and Middleware (~10 min)

### Goal

Set up API routes with proper authentication, rate limiting, and versioning.

### Actions

1. **Add routes to `routes/api.php`**:

Routes are grouped under `/api/v1` prefix for versioning. Sanctum middleware ensures only authenticated users can access endpoints. Rate limiting prevents abuse—60 requests per minute for single generation, 10 per minute for batch operations.

### Why It Works

Route versioning (`/v1`) allows future API changes without breaking existing clients. Sanctum provides token-based authentication perfect for API access. Rate limiting uses Laravel's throttle middleware to prevent abuse and ensure fair usage. The `apiResource` helper automatically creates RESTful routes for brand voices (index, store, show, update, destroy).

### Expected Result

You'll have API endpoints that:

- Require authentication via Bearer tokens
- Enforce rate limits to prevent abuse
- Follow RESTful conventions
- Support versioning for future changes

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

## Step 7: Implement Async Job Processing (~10 min)

### Goal

Create a queue job for asynchronous content generation to avoid blocking API requests.

### Actions

1. **Create GenerateContentJob** for async processing:

The job receives generation and brand voice IDs (not full models) to minimize serialization size. After generation completes, it dispatches a webhook notification job if the user has configured a webhook URL.

### Why It Works

Queue jobs allow long-running operations (like AI generation) to run in the background without blocking HTTP requests. This improves API response times and user experience. Jobs are serialized and stored in the queue, so only IDs are passed to minimize payload size. The webhook notification is dispatched as a separate job to avoid blocking the generation job if webhook delivery is slow.

### Expected Result

You'll have async processing that:

- Handles long-running generations without blocking
- Sends webhook notifications when complete
- Retries automatically on failure
- Scales horizontally with multiple queue workers

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

### Webhook Notification Job

The webhook job is referenced but not implemented. Here's the complete implementation:

```php
<?php
# filename: app/Jobs/SendWebhookNotification.php
declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhookNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // Wait 60 seconds between retries

    public function __construct(
        private readonly string $webhookUrl,
        private readonly array $payload
    ) {}

    public function handle(): void
    {
        try {
            $response = Http::timeout(10)
                ->retry(2, 100) // Retry 2 more times (3 total)
                ->post($this->webhookUrl, $this->payload);

            if (!$response->successful()) {
                Log::warning('Webhook delivery failed', [
                    'url' => $this->webhookUrl,
                    'status' => $response->status(),
                    'payload' => $this->payload,
                ]);
                throw new \RuntimeException("Webhook delivery failed with status: {$response->status()}");
            }
        } catch (\Exception $e) {
            Log::error('Webhook notification error', [
                'url' => $this->webhookUrl,
                'error' => $e->getMessage(),
                'payload' => $this->payload,
            ]);
            throw $e; // Re-throw to trigger job retry
        }
    }
}
```

### Why It Works

The webhook job runs in a separate queue, so failures don't block content generation. The `$tries` property limits retry attempts, and `$backoff` adds delays between retries to avoid overwhelming webhook endpoints. HTTP retries handle transient network issues, while job retries handle persistent failures. Logging helps debug webhook delivery issues.

## Step 8: Create Template Seeders (~5 min)

### Goal

Seed the database with example templates that users can immediately use.

### Actions

1. **Create ContentTemplateSeeder** with example templates:

The seeder creates three common template types: blog posts, product descriptions, and social media posts. Each template includes variables, constraints, and style guides that demonstrate the system's capabilities.

### Why It Works

Seeders provide example data that helps users understand the system quickly. Templates use HEREDOC syntax for multi-line prompt templates, making them easy to read and modify. Variables are defined as arrays, constraints and style guides as associative arrays—this structure matches how the `renderPrompt()` method processes them.

### Expected Result

After running `php artisan db:seed --class=ContentTemplateSeeder`, you'll have:

- Three ready-to-use content templates
- Examples of different template types
- Demonstrations of variables, constraints, and style guides

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

## Step 9: Add Result Caching (~10 min)

### Goal

Implement caching for identical generation requests to reduce API costs and improve response times.

### Actions

1. **Add caching to ContentGenerationService**:

```php
<?php
# Add to ContentGenerationService.php

use Illuminate\Support\Facades\Cache;

public function generateFromTemplate(
    ContentTemplate $template,
    array $data,
    int $userId,
    ?BrandVoice $brandVoice = null
): ContentGeneration {
    // Create cache key from template, data, and brand voice
    $cacheKey = 'generation:' . md5(
        $template->id .
        serialize($data) .
        ($brandVoice?->id ?? 'none')
    );

    // Check cache first
    $cached = Cache::get($cacheKey);
    if ($cached) {
        // Return existing generation
        return ContentGeneration::findOrFail($cached);
    }

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

    // Cache successful generations for 24 hours
    if ($generation->status === 'completed') {
        Cache::put($cacheKey, $generation->id, now()->addHours(24));
    }

    return $generation->fresh();
}
```

### Why It Works

Caching identical requests prevents redundant API calls. The cache key includes template ID, data, and brand voice ID, ensuring different configurations get different results. Only completed generations are cached to avoid caching failures. The 24-hour TTL balances freshness with cost savings.

### Expected Result

You'll have caching that:

- Reduces API costs for repeated requests
- Improves response times for cached results
- Only caches successful generations
- Respects different configurations

## Step 10: Test the API (~10 min)

### Goal

Verify the API works end-to-end by making actual requests.

### Actions

1. **Run migrations and seeders**:

```bash
php artisan migrate
php artisan db:seed --class=ContentTemplateSeeder
```

2. **Create a test user and generate a token** (or use Sanctum's token generation):

```bash
php artisan tinker
```

```php
$user = \App\Models\User::first();
$token = $user->createToken('api-token')->plainTextToken;
echo $token;
```

3. **Test the API** using the curl commands in the Usage Example section below.

### Why It Works

Testing with real requests validates the entire stack: authentication, validation, service logic, and response formatting. The token-based authentication allows testing without browser sessions, perfect for API development.

### Expected Result

You'll be able to:

- Authenticate with Bearer tokens
- List available templates
- Generate content from templates
- View generation status and results
- See usage statistics and costs

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

### Why It Works

The API returns a structured JSON response with generation data, status, usage statistics, and costs. The `ContentGenerationResource` formats the response consistently, including conditional fields based on status and authorization. This makes it easy for API clients to parse responses and display results to users.

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

**Webhook not firing?**

- Verify queue worker is running: `php artisan queue:work`
- Check job failed table: `php artisan queue:failed`
- Ensure user has `webhook_url` set in database
- Check webhook job exists: `app/Jobs/SendWebhookNotification.php`

**Template variables not replacing?**

- Verify variable names match exactly (case-sensitive)
- Check template has variables defined in JSON array
- Ensure data array includes all required variables
- Use `{variable}` syntax, not `{{variable}}` or `$variable`

**Authorization failing?**

- Verify policy is registered in `AuthServiceProvider`
- Check user owns the resource (policy checks `user_id`)
- Ensure Sanctum middleware is applied to routes
- Check token is valid and not expired

**Tests failing?**

- Ensure database is migrated: `php artisan migrate --env=testing`
- Check factories exist for models
- Verify Sanctum is configured in test setup
- Mock Claude API calls to avoid real API usage

## Step 11: Add API Tests (~15 min)

### Goal

Write comprehensive tests for the API endpoints to ensure reliability and catch regressions.

### Actions

1. **Create feature tests** for the API:

```php
<?php
# filename: tests/Feature/Api/ContentGenerationTest.php
declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\ContentGeneration;
use App\Models\ContentTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContentGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_content_from_template(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $template = ContentTemplate::factory()->create([
            'variables' => ['topic', 'audience'],
        ]);

        $response = $this->postJson('/api/v1/generate', [
            'template_id' => $template->id,
            'topic' => 'Laravel Best Practices',
            'audience' => 'PHP developers',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'type',
                    'status',
                    'generated_content',
                ],
            ]);

        $this->assertDatabaseHas('content_generations', [
            'user_id' => $user->id,
            'template_id' => $template->id,
            'status' => 'completed',
        ]);
    }

    public function test_requires_authentication(): void
    {
        $template = ContentTemplate::factory()->create();

        $response = $this->postJson('/api/v1/generate', [
            'template_id' => $template->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_validates_required_template_variables(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $template = ContentTemplate::factory()->create([
            'variables' => ['topic', 'audience'],
        ]);

        $response = $this->postJson('/api/v1/generate', [
            'template_id' => $template->id,
            'topic' => 'Test',
            // Missing 'audience'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['audience']);
    }

    public function test_user_can_only_view_own_generations(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Sanctum::actingAs($user1);

        $generation = ContentGeneration::factory()->create([
            'user_id' => $user2->id,
        ]);

        $response = $this->getJson("/api/v1/generations/{$generation->id}");

        $response->assertStatus(403);
    }

    public function test_can_filter_generations_by_status(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        ContentGeneration::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        ContentGeneration::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/v1/generations?status=completed');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
```

### Why It Works

Feature tests verify the entire request/response cycle, including authentication, validation, authorization, and database interactions. Using factories creates test data without manual setup. Sanctum's `actingAs()` method simulates authenticated requests. Testing edge cases (missing variables, unauthorized access) ensures the API behaves correctly.

### Expected Result

You'll have tests that:

- Verify successful content generation
- Test authentication requirements
- Validate input data
- Check authorization policies
- Test filtering and pagination

## Step 12: Add Authorization Policies (~5 min)

### Goal

Implement authorization policies to ensure users can only access their own content generations.

### Actions

1. **Create ContentGenerationPolicy**:

The policy checks that the user owns the generation before allowing access. This prevents users from viewing or modifying other users' content.

### Why It Works

Laravel policies provide a clean way to centralize authorization logic. The `authorize()` method in controllers automatically uses the policy, making authorization checks consistent across the application. Policies can be easily tested and modified without touching controller code.

### Expected Result

You'll have authorization that:

- Prevents users from accessing others' generations
- Works automatically via `authorize()` calls
- Is easy to test and maintain
- Can be extended for more complex rules

## Policies

### ContentGenerationPolicy

```php
<?php
# filename: app/Policies/ContentGenerationPolicy.php
declare(strict_types=1);

namespace App\Policies;

use App\Models\ContentGeneration;
use App\Models\User;

class ContentGenerationPolicy
{
    /**
     * Determine if the user can view the generation.
     */
    public function view(User $user, ContentGeneration $generation): bool
    {
        return $user->id === $generation->user_id;
    }

    /**
     * Determine if the user can update the generation.
     */
    public function update(User $user, ContentGeneration $generation): bool
    {
        return $user->id === $generation->user_id;
    }

    /**
     * Determine if the user can delete the generation.
     */
    public function delete(User $user, ContentGeneration $generation): bool
    {
        return $user->id === $generation->user_id;
    }
}
```

Register the policy in `app/Providers/AuthServiceProvider.php`:

```php
<?php
# filename: app/Providers/AuthServiceProvider.php
namespace App\Providers;

use App\Models\ContentGeneration;
use App\Policies\ContentGenerationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        ContentGeneration::class => ContentGenerationPolicy::class,
    ];

    public function boot(): void
    {
        // Policy registration happens automatically
    }
}
```

### Why It Works

Laravel automatically discovers policies when they follow naming conventions (`ModelPolicy` for `Model`). Registering policies in `AuthServiceProvider` makes them available throughout the application. The `authorize()` method in controllers uses these policies automatically.

## Further Reading

- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community resources and examples for Claude with PHP
- **[Claude-PHP-SDK Composer Package](https://packagist.org/packages/claude-php/claude-php-sdk)** — Official package on Packagist
- **[Anthropic API Documentation](https://docs.anthropic.com)** — Complete API reference and guides

## Wrap-up

Congratulations! You've built a production-ready content generation API. Here's what you've accomplished:

- ✓ **RESTful API Design** — Clean, versioned API endpoints following REST principles
- ✓ **Template System** — Flexible templates with variable substitution and constraints
- ✓ **Brand Voice Management** — Consistent brand voice enforcement across all content
- ✓ **Batch Generation** — Efficient batch processing with async job queuing
- ✓ **API Authentication** — Secure authentication using Laravel Sanctum
- ✓ **Rate Limiting** — Protection against abuse with configurable limits
- ✓ **Usage Tracking** — Complete tracking of tokens, costs, and generation history
- ✓ **Cost Calculation** — Transparent pricing based on model usage
- ✓ **Webhook Notifications** — Async notifications for completed generations
- ✓ **Request Validation** — Comprehensive validation for all API inputs
- ✓ **API Resources** — Clean, consistent API response formatting
- ✓ **Authorization Policies** — Proper access control for user resources

Your content generation API is now ready for production use. The template system enables reusable content patterns, brand voice ensures consistency, and the async job system handles high-volume generation efficiently.

In the next chapter, you'll add AI superpowers to Laravel admin panels, bringing intelligent features to content management systems.

## Key Takeaways

- ✓ **Template System** enables reusable content patterns
- ✓ **Brand Voice** ensures consistent output
- ✓ **Batch Processing** improves efficiency
- ✓ **API Authentication** protects resources
- ✓ **Rate Limiting** prevents abuse
- ✓ **Usage Tracking** enables billing
- ✓ **Async Jobs** handle long operations
- ✓ **Cost Calculation** supports transparent pricing

## Further Reading

- [Laravel API Resources](https://laravel.com/docs/eloquent-resources) — Official guide to API resource formatting
- [Laravel Sanctum](https://laravel.com/docs/sanctum) — API authentication for SPAs and mobile applications
- [Laravel Queue System](https://laravel.com/docs/queues) — Asynchronous job processing
- [RESTful API Design](https://restfulapi.net/) — Best practices for REST API design
- [Laravel Request Validation](https://laravel.com/docs/validation) — Form request validation
- [Laravel Policies](https://laravel.com/docs/authorization#creating-policies) — Authorization policies for resource access
- [API Rate Limiting](https://laravel.com/docs/routing#rate-limiting) — Throttling API requests


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
