---
title: Capstone Project and Future Trends
description: Build SmartDash, a comprehensive AI-powered analytics dashboard integrating chatbot, recommendations, forecasting, and image tagging. Explore future trends including ONNX Runtime, generative AI, vector databases, and AI ethics.
series: ai-ml-php-developers
chapter: 25-capstone-project-and-future-trends
order: 25
difficulty: advanced
prerequisites:
  [
    15-language-models-and-text-generation-with-openai-apis,
    17-image-classification-project-with-pre-trained-models,
    20-time-series-forecasting-project,
    21-recommender-systems-theory-and-use-cases,
    22-building-a-recommendation-engine-in-php,
    23-integrating-ai-models-into-web-applications,
    24-deploying-and-scaling-ai-powered-php-services,
  ]
---

![Capstone Project and Future Trends](/images/ai-ml-php-developers/chapter-25-capstone-future-trends-hero-full.webp)

# Capstone Project and Future Trends

## Overview

Welcome to the final chapter of the AI/ML for PHP Developers series! You've learned individual AI techniques—chatbots, recommendations, forecasting, computer vision—but real-world applications rarely use just one. In this capstone project, you'll integrate multiple AI services into **SmartDash**, a production-ready analytics dashboard that demonstrates how these technologies work together harmoniously.

SmartDash showcases four distinct AI features: an intelligent customer support chatbot powered by OpenAI's GPT-4, a collaborative filtering recommendation engine, automated sales forecasting using time series analysis, and automatic image classification with Google Cloud Vision API. Each feature operates as an independent service but shares common infrastructure: Laravel's queue system for async processing, a unified caching strategy, RESTful APIs for frontend integration, and comprehensive error handling.

This project synthesizes architectural patterns you'll use professionally: service layers that encapsulate AI logic, strategy patterns for swappable backends (cloud vs. local inference), job queues that prevent timeouts, and API-first design that enables mobile apps and third-party integrations. You'll implement production considerations like cost tracking, rate limiting, graceful degradation, and monitoring—skills that separate proof-of-concepts from deployed systems.

Beyond building SmartDash, we'll explore emerging trends shaping PHP's AI future: ONNX Runtime for high-performance local inference, vector databases enabling semantic search, generative AI for images and audio, fine-tuning custom models, and ethical considerations around bias, privacy, and transparency. By the end, you'll have both a portfolio-worthy project and a roadmap for staying current in this rapidly evolving field.

## Prerequisites

Before starting, ensure you have:

- **PHP 8.4+** installed with extensions: `pdo`, `mbstring`, `openssl`, `curl`, `gd` or `imagick`
- **Composer** for dependency management
- **Laravel 11** familiarity (routing, Eloquent, Blade, jobs)
- **Database** (MySQL 8.0+, PostgreSQL 13+, or SQLite 3.35+)
- **OpenAI API key** with credits ([platform.openai.com](https://platform.openai.com/))
- **Google Cloud Vision API key** ([console.cloud.google.com](https://console.cloud.google.com/))
- **Node.js 18+** and npm for frontend assets (Tailwind CSS compilation)
- **Git** for version control
- **Code editor/IDE** with PHP support (VS Code, PHPStorm, etc.)
- **Understanding of**:
  - Laravel service containers and dependency injection
  - Database migrations and Eloquent relationships
  - Queue workers and background jobs
  - RESTful API design
  - Basic frontend JavaScript (for AJAX interactions)

**Estimated Time**: 3-4 hours to complete all steps

**Cost Awareness**: This project makes API calls to OpenAI (~$0.03 per conversation with GPT-4) and Google Vision (~$0.0015 per image after free tier). Budget approximately $5-10 for testing.

## What You'll Build

A complete Laravel 11 application featuring:

- **ChatbotService**: GPT-4 powered conversational AI with context management, caching, and token tracking
- **RecommenderService**: Collaborative filtering engine with cold start handling and interaction tracking
- **ForecastService**: Time series forecasting with multiple algorithms (moving average, linear regression)
- **VisionService**: Image classification using Google Cloud Vision API or local ONNX models
- **Unified Dashboard**: Responsive Blade templates with Tailwind CSS showcasing all features
- **RESTful API**: JSON endpoints for all AI services, enabling programmatic access
- **Background Jobs**: Async processing for expensive ML operations (forecasting, image processing)
- **Database Schema**: Migrations for conversations, messages, recommendations, forecasts, images, and tags
- **Caching Layer**: Strategic caching to reduce API costs and improve performance
- **Comprehensive Testing**: Standalone scripts to verify each component
- **Production Patterns**: Error handling, logging, retry logic, cost tracking
- **Documentation**: README, API docs, troubleshooting guides

## Objectives

By completing this chapter, you will:

1. **Architect a multi-AI system** where chatbot, recommendations, forecasting, and vision work together
2. **Implement production-ready patterns**: service layers, strategy pattern, queue/job pattern, API-first design
3. **Optimize for cost and performance**: caching strategies, async processing, algorithm selection
4. **Handle real-world challenges**: API failures, rate limits, cold starts, graceful degradation
5. **Build scalable infrastructure**: queue workers, database indexes, horizontal scaling patterns
6. **Monitor and debug AI systems**: logging, error tracking, token usage, forecast accuracy
7. **Explore future trends**: ONNX Runtime, vector databases, generative AI, ethical AI practices

## Step 1: Project Setup and Dependencies (~15 min)

### Goal

Create a new Laravel 11 project with all required dependencies for AI services, configure environment variables, and verify the installation.

### Actions

1. **Create Laravel project**:

```bash
composer create-project laravel/laravel smartdash "11.*"
cd smartdash
```

2. **Install AI and HTTP dependencies**:

```bash
composer require openai-php/laravel
composer require rubixml/ml
composer require guzzlehttp/guzzle
```

3. **Configure environment** (`.env`):

```ini
# filename: .env
APP_NAME=SmartDash
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartdash
DB_USERNAME=root
DB_PASSWORD=

# Queue (use database for simplicity, Redis recommended for production)
QUEUE_CONNECTION=database

# Cache
CACHE_DRIVER=file

# AI Services
OPENAI_API_KEY=sk-your-openai-key-here
OPENAI_ORGANIZATION=org-your-org-id-optional

GOOGLE_CLOUD_VISION_KEY=your-google-vision-key-here
VISION_PROVIDER=cloud  # or 'local' for ONNX models

# AI Configuration
AI_CACHE_TTL=3600  # 1 hour cache for AI responses
FORECAST_CACHE_TTL=1800  # 30 minutes for forecasts
```

4. **Create database and run migrations**:

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE smartdash"

# Or for SQLite
touch database/database.sqlite

# Generate queue tables
php artisan queue:table
php artisan migrate
```

5. **Install frontend dependencies**:

```bash
npm install
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

6. **Configure Tailwind CSS** (`tailwind.config.js`):

```js
# filename: tailwind.config.js
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
```

7. **Update app.css**:

```css
# filename: resources/css/app.css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

8. **Compile assets**:

```bash
npm run dev
```

### Expected Result

```bash
# Verify Laravel installation
php artisan --version
# Laravel Framework 11.x.x

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();
# Should return PDO object without errors

# Verify OpenAI package
php artisan vendor:publish --provider="OpenAI\Laravel\ServiceProvider"
# Configuration file published

# Start development server
php artisan serve
# Server started at http://localhost:8000
```

### Why It Works

Laravel 11 provides a modern PHP framework with built-in support for queues, caching, and database migrations—essential infrastructure for AI applications. The `openai-php/laravel` package wraps OpenAI's API with Laravel-friendly syntax, automatic error handling, and configuration management. Rubix ML offers pure-PHP machine learning algorithms for recommendations and forecasting without Python dependencies. Guzzle handles HTTP requests to external APIs like Google Vision. Tailwind CSS enables rapid UI development with utility classes, creating a professional dashboard without custom CSS.

### Troubleshooting

- **Error: "Class 'OpenAI' not found"** — Run `composer dump-autoload` and ensure `openai-php/laravel` is in `composer.json`
- **Database connection failed** — Verify database credentials in `.env`, ensure MySQL/PostgreSQL is running
- **npm errors** — Delete `node_modules` and `package-lock.json`, run `npm install` again
- **Port 8000 already in use** — Use `php artisan serve --port=8001` or kill the process using port 8000

## Step 2: Database Schema and Eloquent Models (~10 min)

### Goal

Design and implement the database schema for all AI features: conversations/messages (chatbot), recommendations, forecasts, images/tags (vision).

### Actions

1. **Create migrations**:

```bash
php artisan make:migration create_conversations_table
php artisan make:migration create_messages_table
php artisan make:migration create_recommendations_table
php artisan make:migration create_forecasts_table
php artisan make:migration create_images_table
php artisan make:migration create_image_tags_table
```

2. **Implement conversations migration**:

```php
# filename: database/migrations/YYYY_MM_DD_create_conversations_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('status')->default('active'); // active, completed, archived
            $table->integer('total_tokens')->default(0);
            $table->integer('message_count')->default(0);
            $table->decimal('estimated_cost', 10, 4)->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
```

3. **Implement messages migration**:

```php
# filename: database/migrations/YYYY_MM_DD_create_messages_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['system', 'user', 'assistant', 'function']);
            $table->text('content');
            $table->integer('tokens')->nullable();
            $table->boolean('cached')->default(false);
            $table->timestamps();

            $table->index('conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
```

4. **Implement recommendations migration**:

```php
# filename: database/migrations/YYYY_MM_DD_create_recommendations_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('item_type')->default('product'); // product, article, user, etc.
            $table->integer('item_id');
            $table->decimal('score', 5, 4); // 0.0000 to 9.9999
            $table->string('algorithm')->default('collaborative_filtering');
            $table->json('metadata')->nullable(); // reasoning, features, etc.
            $table->timestamps();

            $table->index(['user_id', 'item_type']);
            $table->index('score');
        });

        // Interaction tracking for training the recommender
        Schema::create('user_interactions', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('item_type');
            $table->integer('item_id');
            $table->string('interaction_type'); // view, click, purchase, rating
            $table->decimal('value', 5, 2)->nullable(); // rating value if applicable
            $table->timestamp('interacted_at');
            $table->timestamps();

            $table->index(['user_id', 'item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_interactions');
        Schema::dropIfExists('recommendations');
    }
};
```

5. **Implement forecasts migration**:

```php
# filename: database/migrations/YYYY_MM_DD_create_forecasts_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->string('metric_name'); // daily_sales, user_signups, etc.
            $table->date('forecast_date');
            $table->decimal('value', 12, 2);
            $table->decimal('lower_bound', 12, 2)->nullable();
            $table->decimal('upper_bound', 12, 2)->nullable();
            $table->decimal('actual_value', 12, 2)->nullable(); // filled in later
            $table->string('method')->default('moving_average'); // algorithm used
            $table->timestamps();

            $table->index(['metric_name', 'forecast_date']);
            $table->unique(['metric_name', 'forecast_date', 'method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecasts');
    }
};
```

6. **Implement images and tags migrations**:

```php
# filename: database/migrations/YYYY_MM_DD_create_images_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('filename');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size'); // bytes
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('status')->default('pending'); // pending, processing, processed, failed
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
```

```php
# filename: database/migrations/YYYY_MM_DD_create_image_tags_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->decimal('confidence', 5, 4); // 0.0000 to 9.9999
            $table->string('source')->default('cloud'); // cloud, local, manual
            $table->timestamps();

            $table->index(['image_id', 'confidence']);
            $table->index('label');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_tags');
    }
};
```

7. **Run migrations**:

```bash
php artisan migrate
```

8. **Create Eloquent models**:

```bash
php artisan make:model Conversation
php artisan make:model Message
php artisan make:model Recommendation
php artisan make:model UserInteraction
php artisan make:model Forecast
php artisan make:model Image
php artisan make:model ImageTag
```

9. **Define Conversation model relationships**:

```php
# filename: app/Models/Conversation.php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'status',
        'total_tokens',
        'message_count',
        'estimated_cost',
    ];

    protected $casts = [
        'total_tokens' => 'integer',
        'message_count' => 'integer',
        'estimated_cost' => 'decimal:4',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

10. **Define Image model relationships**:

```php
# filename: app/Models/Image.php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'path',
        'mime_type',
        'size',
        'width',
        'height',
        'status',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function tags(): HasMany
    {
        return $this->hasMany(ImageTag::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### Expected Result

```bash
# After migrations
php artisan migrate

# Output shows:
# Migration table created successfully.
# Migrating: YYYY_MM_DD_create_conversations_table
# Migrated:  YYYY_MM_DD_create_conversations_table (XX.XXms)
# [... all 6 migrations ...]

# Verify tables exist
php artisan tinker
>>> DB::table('conversations')->count();
# 0

>>> Schema::hasTable('image_tags');
# true
```

### Why It Works

The database schema reflects the relationships between AI features: conversations contain many messages (one-to-many), images have many tags, recommendations link users to items. Indexes on frequently queried columns (`user_id`, `status`, `metric_name`) improve query performance. Using Laravel's migrations ensures the schema is version-controlled and deployable across environments. Eloquent models provide an object-oriented interface to the database, automatically handling timestamps, type casting, and relationship loading. The `foreignId()` and `constrained()` methods create foreign key constraints, maintaining referential integrity.

### Troubleshooting

- **Migration error: "Table already exists"** — Run `php artisan migrate:fresh` (⚠️ deletes all data) or manually drop tables
- **Foreign key constraint fails** — Ensure parent tables are created before child tables (conversations before messages)
- **Column type errors** — Check your database version supports `json` columns (MySQL 5.7.8+, PostgreSQL 9.4+)

## Step 3: AI Chatbot Integration (~20 min)

### Goal

Implement a ChatbotService that manages conversations with OpenAI's GPT-4, tracks token usage, caches responses, and stores conversation history.

### Actions

1. **Create ChatbotService**:

```bash
mkdir -p app/Services
php artisan make:class Services/ChatbotService
```

2. **Implement ChatbotService**:

```php
# filename: app/Services/ChatbotService.php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

final class ChatbotService
{
    private const MODEL = 'gpt-4';
    private const MAX_TOKENS = 500;
    private const TEMPERATURE = 0.7;
    private const CACHE_TTL = 3600; // 1 hour

    private const TOKEN_COSTS = [
        'gpt-4' => ['prompt' => 0.03 / 1000, 'completion' => 0.06 / 1000],
        'gpt-3.5-turbo' => ['prompt' => 0.0015 / 1000, 'completion' => 0.002 / 1000],
    ];

    /**
     * Get or create a conversation by session ID.
     */
    public function getOrCreateConversation(string $sessionId, ?int $userId = null): Conversation
    {
        return Conversation::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'user_id' => $userId,
                'status' => 'active',
            ]
        );
    }

    /**
     * Send a message and get AI response.
     */
    public function sendMessage(Conversation $conversation, string $userMessage): array
    {
        // Store user message
        $userMsg = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $userMessage,
        ]);

        $conversation->increment('message_count');

        // Build message history for context
        $messages = $this->buildMessageHistory($conversation);

        // Check cache
        $cacheKey = $this->getCacheKey($messages);
        $cached = Cache::get($cacheKey);

        if ($cached) {
            Log::info('ChatbotService: Cache hit', ['conversation_id' => $conversation->id]);

            $assistantMsg = Message::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $cached['content'],
                'tokens' => $cached['tokens'],
                'cached' => true,
            ]);

            return [
                'message' => $assistantMsg,
                'cached' => true,
            ];
        }

        // Call OpenAI API
        try {
            $response = OpenAI::chat()->create([
                'model' => self::MODEL,
                'messages' => $messages,
                'max_tokens' => self::MAX_TOKENS,
                'temperature' => self::TEMPERATURE,
            ]);

            $content = $response->choices[0]->message->content;
            $tokensUsed = $response->usage->totalTokens;

            // Store assistant message
            $assistantMsg = Message::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $content,
                'tokens' => $tokensUsed,
                'cached' => false,
            ]);

            // Update conversation stats
            $conversation->increment('total_tokens', $tokensUsed);
            $conversation->increment('message_count');

            $cost = $this->calculateCost(
                $response->usage->promptTokens,
                $response->usage->completionTokens,
                self::MODEL
            );
            $conversation->increment('estimated_cost', $cost);

            // Cache the response
            Cache::put($cacheKey, [
                'content' => $content,
                'tokens' => $tokensUsed,
            ], self::CACHE_TTL);

            Log::info('ChatbotService: Message sent successfully', [
                'conversation_id' => $conversation->id,
                'tokens' => $tokensUsed,
                'cost' => $cost,
            ]);

            return [
                'message' => $assistantMsg,
                'tokens' => $tokensUsed,
                'cached' => false,
            ];

        } catch (\Exception $e) {
            Log::error('ChatbotService: API call failed', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Failed to get AI response: ' . $e->getMessage());
        }
    }

    /**
     * Get conversation history.
     */
    public function getHistory(Conversation $conversation): array
    {
        return $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
                'created_at' => $msg->created_at->toIso8601String(),
                'tokens' => $msg->tokens,
                'cached' => $msg->cached,
            ])
            ->toArray();
    }

    /**
     * Build message array for OpenAI API.
     */
    private function buildMessageHistory(Conversation $conversation): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a helpful AI assistant for SmartDash, an analytics platform. Provide clear, concise, and actionable answers.',
            ],
        ];

        $history = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->take(20) // Limit context window
            ->get();

        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }

        return $messages;
    }

    /**
     * Generate cache key from messages.
     */
    private function getCacheKey(array $messages): string
    {
        return 'chatbot:' . md5(json_encode($messages));
    }

    /**
     * Calculate API cost.
     */
    private function calculateCost(int $promptTokens, int $completionTokens, string $model): float
    {
        $costs = self::TOKEN_COSTS[$model] ?? self::TOKEN_COSTS['gpt-3.5-turbo'];

        return ($promptTokens * $costs['prompt']) + ($completionTokens * $costs['completion']);
    }

    /**
     * Estimate total cost for a conversation.
     */
    public function estimateCost(Conversation $conversation): array
    {
        return [
            'total_tokens' => $conversation->total_tokens,
            'estimated_cost_usd' => (float) $conversation->estimated_cost,
            'message_count' => $conversation->message_count,
        ];
    }
}
```

3. **Create test script**:

```php
# filename: 02-test-chatbot.php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Services\ChatbotService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🤖 SmartDash Chatbot Test\n";
echo str_repeat('=', 50) . "\n\n";

try {
    $chatbot = app(ChatbotService::class);

    // Create conversation
    $sessionId = 'test-' . time();
    $conversation = $chatbot->getOrCreateConversation($sessionId);
    echo "✓ Conversation created (ID: {$conversation->id})\n\n";

    // Send first message
    echo "User: Hello! What is SmartDash?\n\n";
    $response1 = $chatbot->sendMessage($conversation, 'Hello! What is SmartDash?');

    echo "Assistant: {$response1['message']->content}\n\n";
    echo "  Tokens: {$response1['tokens']}\n";
    echo "  Cached: " . ($response1['cached'] ? 'Yes' : 'No') . "\n\n";

    // Send second message
    echo "User: What features does it have?\n\n";
    $response2 = $chatbot->sendMessage($conversation, 'What features does it have?');

    echo "Assistant: {$response2['message']->content}\n\n";

    // Get cost estimate
    $cost = $chatbot->estimateCost($conversation);
    echo "Conversation Stats:\n";
    echo "  Messages: {$cost['message_count']}\n";
    echo "  Total tokens: {$cost['total_tokens']}\n";
    echo "  Estimated cost: \${$cost['estimated_cost_usd']}\n\n";

    echo "✅ Chatbot test completed successfully!\n";

} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}
```

### Expected Result

```bash
php 02-test-chatbot.php

# Output:
# 🤖 SmartDash Chatbot Test
# ==================================================
#
# ✓ Conversation created (ID: 1)
#
# User: Hello! What is SmartDash?
#
# Assistant: SmartDash is an AI-powered analytics platform that combines multiple intelligent features including conversational AI, product recommendations, sales forecasting, and automated image tagging. It demonstrates how different AI technologies can work together in a production web application.
#
#   Tokens: 145
#   Cached: No
#
# User: What features does it have?
#
# Assistant: SmartDash includes four main AI features: 1) An intelligent chatbot for customer support, 2) A recommendation engine using collaborative filtering, 3) Sales forecasting with time series analysis, and 4) Automatic image classification and tagging.
#
# Conversation Stats:
#   Messages: 4
#   Total tokens: 287
#   Estimated cost: $0.0172
#
# ✅ Chatbot test completed successfully!
```

### Why It Works

The ChatbotService encapsulates all chatbot logic in a reusable, testable service class. It maintains conversation context by loading recent messages and including them in each API call, allowing the AI to reference previous exchanges. Caching responses by message hash prevents redundant API calls for identical questions, reducing costs significantly. Token tracking and cost calculation provide transparency about AI expenses. Storing messages in the database enables conversation history, analytics, and debugging. The service uses Laravel's dependency injection, making it easy to inject into controllers, jobs, or tests.

### Troubleshooting

- **Error: "API key not configured"** — Verify `OPENAI_API_KEY` in `.env` is set correctly
- **Error: "Insufficient quota"** — Add credits to your OpenAI account at platform.openai.com/account/billing
- **Timeout errors** — Increase `max_tokens` or use `gpt-3.5-turbo` for faster responses
- **Empty responses** — Check Laravel logs: `tail -f storage/logs/laravel.log`

::: tip Cost Optimization
Use `gpt-3.5-turbo` instead of `gpt-4` for 10x cost savings (~$0.002 vs ~$0.03 per conversation). GPT-3.5 is sufficient for most customer support scenarios.
:::

## Step 4: Recommendation Engine (~25 min)

### Goal

Build a RecommenderService that generates personalized product recommendations using collaborative filtering based on user interaction history.

### Actions

1. **Create RecommenderService**:

```php
# filename: app/Services/RecommenderService.php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Recommendation;
use App\Models\UserInteraction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class RecommenderService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const MIN_INTERACTIONS = 3;
    private const TOP_N = 10;

    /**
     * Generate recommendations for a user.
     */
    public function generateRecommendations(
        int $userId,
        string $itemType = 'product',
        int $limit = self::TOP_N
    ): array {
        $cacheKey = "recommendations:{$userId}:{$itemType}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $itemType, $limit) {
            Log::info('RecommenderService: Generating recommendations', [
                'user_id' => $userId,
                'item_type' => $itemType,
            ]);

            // Get user's interaction history
            $userInteractions = $this->getUserInteractions($userId, $itemType);

            if ($userInteractions->count() < self::MIN_INTERACTIONS) {
                // Cold start: return popular items
                return $this->getPopularItems($itemType, $limit);
            }

            // Find similar users (collaborative filtering)
            $similarUsers = $this->findSimilarUsers($userId, $itemType);

            // Get items liked by similar users but not by this user
            $recommendations = $this->getCollaborativeRecommendations(
                $userId,
                $similarUsers,
                $itemType,
                $limit
            );

            // Store recommendations
            foreach ($recommendations as $rec) {
                Recommendation::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'item_type' => $itemType,
                        'item_id' => $rec['item_id'],
                    ],
                    [
                        'score' => $rec['score'],
                        'algorithm' => 'collaborative_filtering',
                        'metadata' => json_encode($rec['metadata'] ?? []),
                    ]
                );
            }

            return $recommendations;
        });
    }

    /**
     * Record user interaction for training.
     */
    public function recordInteraction(
        int $userId,
        string $itemType,
        int $itemId,
        string $interactionType,
        ?float $value = null
    ): void {
        UserInteraction::create([
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'interaction_type' => $interactionType,
            'value' => $value,
            'interacted_at' => now(),
        ]);

        // Invalidate cache
        Cache::forget("recommendations:{$userId}:{$itemType}");

        Log::info('RecommenderService: Interaction recorded', [
            'user_id' => $userId,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'interaction_type' => $interactionType,
        ]);
    }

    /**
     * Get stored recommendations for a user.
     */
    public function getRecommendations(int $userId, string $itemType = 'product'): array
    {
        return Recommendation::where('user_id', $userId)
            ->where('item_type', $itemType)
            ->orderBy('score', 'desc')
            ->get()
            ->map(fn($rec) => [
                'item_id' => $rec->item_id,
                'score' => (float) $rec->score,
                'algorithm' => $rec->algorithm,
                'created_at' => $rec->created_at->toIso8601String(),
            ])
            ->toArray();
    }

    /**
     * Get user's interaction history.
     */
    private function getUserInteractions(int $userId, string $itemType)
    {
        return UserInteraction::where('user_id', $userId)
            ->where('item_type', $itemType)
            ->orderBy('interacted_at', 'desc')
            ->get();
    }

    /**
     * Find users with similar interaction patterns.
     */
    private function findSimilarUsers(int $userId, string $itemType, int $limit = 10): array
    {
        // Get items this user has interacted with
        $userItems = UserInteraction::where('user_id', $userId)
            ->where('item_type', $itemType)
            ->pluck('item_id')
            ->unique()
            ->toArray();

        if (empty($userItems)) {
            return [];
        }

        // Find users who interacted with similar items
        $similarUsers = UserInteraction::where('item_type', $itemType)
            ->whereIn('item_id', $userItems)
            ->where('user_id', '!=', $userId)
            ->select('user_id', DB::raw('COUNT(DISTINCT item_id) as overlap'))
            ->groupBy('user_id')
            ->orderBy('overlap', 'desc')
            ->limit($limit)
            ->get();

        return $similarUsers->pluck('user_id')->toArray();
    }

    /**
     * Get items liked by similar users.
     */
    private function getCollaborativeRecommendations(
        int $userId,
        array $similarUsers,
        string $itemType,
        int $limit
    ): array {
        if (empty($similarUsers)) {
            return $this->getPopularItems($itemType, $limit);
        }

        // Get items already interacted with by target user
        $userItems = UserInteraction::where('user_id', $userId)
            ->where('item_type', $itemType)
            ->pluck('item_id')
            ->toArray();

        // Get items liked by similar users
        $recommendations = UserInteraction::whereIn('user_id', $similarUsers)
            ->where('item_type', $itemType)
            ->whereNotIn('item_id', $userItems) // Exclude items user already knows
            ->select('item_id', DB::raw('COUNT(*) as frequency'), DB::raw('AVG(COALESCE(value, 5)) as avg_rating'))
            ->groupBy('item_id')
            ->orderBy('frequency', 'desc')
            ->limit($limit)
            ->get();

        return $recommendations->map(fn($rec) => [
            'item_id' => $rec->item_id,
            'score' => min($rec->frequency / count($similarUsers), 1.0), // Normalize to 0-1
            'metadata' => [
                'frequency' => $rec->frequency,
                'avg_rating' => round($rec->avg_rating, 2),
                'similar_users' => count($similarUsers),
            ],
        ])->toArray();
    }

    /**
     * Get popular items (fallback for cold start).
     */
    private function getPopularItems(string $itemType, int $limit): array
    {
        $popular = UserInteraction::where('item_type', $itemType)
            ->select('item_id', DB::raw('COUNT(*) as interaction_count'))
            ->groupBy('item_id')
            ->orderBy('interaction_count', 'desc')
            ->limit($limit)
            ->get();

        return $popular->map(fn($item) => [
            'item_id' => $item->item_id,
            'score' => 0.5, // Lower score for popular items
            'metadata' => [
                'reason' => 'popular',
                'interaction_count' => $item->interaction_count,
            ],
        ])->toArray();
    }
}
```

2. **Create test script**:

```php
# filename: 03-test-recommendations.php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Services\RecommenderService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🎯 SmartDash Recommendation Engine Test\n";
echo str_repeat('=', 50) . "\n\n";

try {
    $recommender = app(RecommenderService::class);

    // Simulate user interactions
    echo "Recording sample interactions...\n";

    // User 1 likes products 1, 2, 3
    $recommender->recordInteraction(1, 'product', 1, 'purchase', 5.0);
    $recommender->recordInteraction(1, 'product', 2, 'purchase', 4.5);
    $recommender->recordInteraction(1, 'product', 3, 'view', null);

    // User 2 likes products 1, 2, 4 (similar to User 1)
    $recommender->recordInteraction(2, 'product', 1, 'purchase', 5.0);
    $recommender->recordInteraction(2, 'product', 2, 'view', null);
    $recommender->recordInteraction(2, 'product', 4, 'purchase', 4.8);

    // User 3 likes products 5, 6 (different taste)
    $recommender->recordInteraction(3, 'product', 5, 'purchase', 4.0);
    $recommender->recordInteraction(3, 'product', 6, 'purchase', 4.2);

    echo "✓ Interactions recorded\n\n";

    // Generate recommendations for User 1
    echo "Generating recommendations for User 1...\n";
    $recs = $recommender->generateRecommendations(1, 'product', 5);

    if (!empty($recs)) {
        echo "✓ Generated " . count($recs) . " recommendations\n\n";
        echo "Top recommendations:\n";
        foreach ($recs as $i => $rec) {
            echo sprintf(
                "  %d. Product #%d (score: %.2f)\n",
                $i + 1,
                $rec['item_id'],
                $rec['score']
            );
            if (isset($rec['metadata']['reason'])) {
                echo "     Reason: {$rec['metadata']['reason']}\n";
            }
        }
    } else {
        echo "⚠️  No recommendations generated (need more interactions)\n";
    }

    echo "\n✅ Recommendation engine test completed!\n";

} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}
```

### Expected Result

```bash
php 03-test-recommendations.php

# Output:
# 🎯 SmartDash Recommendation Engine Test
# ==================================================
#
# Recording sample interactions...
# ✓ Interactions recorded
#
# Generating recommendations for User 1...
# ✓ Generated 2 recommendations
#
# Top recommendations:
#   1. Product #4 (score: 0.50)
#      Reason: Similar users also liked this
#   2. Product #5 (score: 0.33)
#      Reason: popular
#
# ✅ Recommendation engine test completed!
```

### Why It Works

Collaborative filtering finds users with similar interaction patterns (purchased/viewed similar items) and recommends items those users liked that the target user hasn't seen yet. The algorithm calculates overlap by counting how many items two users have in common, then ranks candidates by how many similar users interacted with them. Caching prevents expensive database queries on every request—recommendations only regenerate when new interactions occur. For cold start users (< 3 interactions), the system falls back to popular items, ensuring everyone gets recommendations. This approach scales well because calculations happen asynchronously, and results are cached.

### Troubleshooting

- **Empty recommendations** — Add more sample interactions or lower `MIN_INTERACTIONS` threshold
- **Slow performance** — Add database indexes on `user_interactions(user_id, item_type, item_id)`
- **Cache not clearing** — Verify `CACHE_DRIVER` in `.env`, try `php artisan cache:clear`

## Step 5: Sales Forecasting (~20 min)

### Goal

Implement a ForecastService that predicts future sales using time series analysis with moving average and linear regression methods.

### Actions

1. **Create ForecastService**:

```php
# filename: app/Services/ForecastService.php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Forecast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rubix\ML\Regressors\Ridge;
use Rubix\ML\Datasets\Labeled;

final class ForecastService
{
    private const CACHE_TTL = 1800; // 30 minutes
    private const MOVING_AVERAGE_WINDOW = 7;

    /**
     * Generate forecast for a metric.
     */
    public function generateForecast(
        string $metricName,
        int $daysAhead = 7,
        string $method = 'moving_average'
    ): array {
        Log::info('ForecastService: Generating forecast', [
            'metric' => $metricName,
            'days_ahead' => $daysAhead,
            'method' => $method,
        ]);

        // Get historical data
        $historical = $this->getHistoricalData($metricName);

        if ($historical->count() < self::MOVING_AVERAGE_WINDOW) {
            throw new \RuntimeException("Insufficient historical data. Need at least " . self::MOVING_AVERAGE_WINDOW . " data points.");
        }

        // Generate forecasts based on method
        $forecasts = match($method) {
            'moving_average' => $this->forecastMovingAverage($historical, $daysAhead),
            'linear_regression' => $this->forecastLinearRegression($historical, $daysAhead),
            default => throw new \InvalidArgumentException("Unknown forecast method: {$method}"),
        };

        // Store forecasts in database
        foreach ($forecasts as $forecast) {
            Forecast::updateOrCreate(
                [
                    'metric_name' => $metricName,
                    'forecast_date' => $forecast['date'],
                    'method' => $method,
                ],
                [
                    'value' => $forecast['value'],
                    'lower_bound' => $forecast['lower_bound'],
                    'upper_bound' => $forecast['upper_bound'],
                ]
            );
        }

        Log::info('ForecastService: Forecast generated successfully', [
            'metric' => $metricName,
            'forecasts_count' => count($forecasts),
        ]);

        return $forecasts;
    }

    /**
     * Get stored forecasts for a metric.
     */
    public function getForecasts(string $metricName, string $startDate, string $endDate): array
    {
        return Forecast::where('metric_name', $metricName)
            ->whereBetween('forecast_date', [$startDate, $endDate])
            ->orderBy('forecast_date', 'asc')
            ->get()
            ->map(fn($f) => [
                'date' => $f->forecast_date,
                'value' => (float) $f->value,
                'lower_bound' => (float) $f->lower_bound,
                'upper_bound' => (float) $f->upper_bound,
                'actual' => $f->actual_value ? (float) $f->actual_value : null,
                'method' => $f->method,
            ])
            ->toArray();
    }

    /**
     * Record actual value for accuracy tracking.
     */
    public function recordActual(string $metricName, string $date, float $actualValue): void
    {
        Forecast::where('metric_name', $metricName)
            ->where('forecast_date', $date)
            ->update(['actual_value' => $actualValue]);

        Log::info('ForecastService: Actual value recorded', [
            'metric' => $metricName,
            'date' => $date,
            'value' => $actualValue,
        ]);
    }

    /**
     * Get historical data (mock implementation).
     */
    private function getHistoricalData(string $metricName)
    {
        // In production, this would query your actual sales/metrics table
        // For demo, generate synthetic data
        $data = collect();
        $baseValue = 1000;

        for ($i = 30; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $value = $baseValue + rand(-100, 200) + ($i * 5); // Upward trend + noise

            $data->push([
                'date' => $date,
                'value' => $value,
            ]);
        }

        return $data;
    }

    /**
     * Moving average forecast.
     */
    private function forecastMovingAverage($historical, int $daysAhead): array
    {
        $values = $historical->pluck('value')->toArray();
        $window = self::MOVING_AVERAGE_WINDOW;

        // Calculate initial moving average
        $recentValues = array_slice($values, -$window);
        $ma = array_sum($recentValues) / $window;

        // Calculate standard deviation for confidence intervals
        $variance = array_sum(array_map(fn($v) => ($v - $ma) ** 2, $recentValues)) / $window;
        $stdDev = sqrt($variance);

        $forecasts = [];
        $lastDate = $historical->last()['date'];

        for ($i = 1; $i <= $daysAhead; $i++) {
            $forecastDate = date('Y-m-d', strtotime($lastDate . " +{$i} days"));

            $forecasts[] = [
                'date' => $forecastDate,
                'value' => round($ma, 2),
                'lower_bound' => round($ma - (1.96 * $stdDev), 2), // 95% confidence
                'upper_bound' => round($ma + (1.96 * $stdDev), 2),
            ];
        }

        return $forecasts;
    }

    /**
     * Linear regression forecast.
     */
    private function forecastLinearRegression($historical, int $daysAhead): array
    {
        // Prepare training data
        $samples = [];
        $labels = [];

        foreach ($historical as $i => $point) {
            $samples[] = [$i]; // Day number as feature
            $labels[] = $point['value'];
        }

        // Train model
        $dataset = new Labeled($samples, $labels);
        $estimator = new Ridge();
        $estimator->train($dataset);

        // Generate forecasts
        $forecasts = [];
        $lastDate = $historical->last()['date'];
        $n = $historical->count();

        // Calculate standard error for confidence intervals
        $predictions = [];
        foreach ($samples as $sample) {
            $predictions[] = $estimator->predict([$sample])[0];
        }
        $residuals = array_map(fn($i) => $labels[$i] - $predictions[$i], range(0, count($labels) - 1));
        $mse = array_sum(array_map(fn($r) => $r ** 2, $residuals)) / count($residuals);
        $stdError = sqrt($mse);

        for ($i = 1; $i <= $daysAhead; $i++) {
            $forecastDate = date('Y-m-d', strtotime($lastDate . " +{$i} days"));
            $prediction = $estimator->predict([[$n + $i - 1]])[0];

            $forecasts[] = [
                'date' => $forecastDate,
                'value' => round($prediction, 2),
                'lower_bound' => round($prediction - (1.96 * $stdError), 2),
                'upper_bound' => round($prediction + (1.96 * $stdError), 2),
            ];
        }

        return $forecasts;
    }
}
```

2. **Create test script**:

```php
# filename: 04-test-forecast.php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Services\ForecastService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "📈 SmartDash Forecast Service Test\n";
echo str_repeat('=', 50) . "\n\n";

try {
    $forecaster = app(ForecastService::class);

    // Generate moving average forecast
    echo "Generating 7-day moving average forecast...\n";
    $maForecasts = $forecaster->generateForecast('daily_sales', 7, 'moving_average');

    echo "✓ Generated " . count($maForecasts) . " forecasts\n\n";
    echo "Moving Average Predictions:\n";
    foreach (array_slice($maForecasts, 0, 3) as $forecast) {
        echo sprintf(
            "  %s: $%.2f (range: $%.2f - $%.2f)\n",
            $forecast['date'],
            $forecast['value'],
            $forecast['lower_bound'],
            $forecast['upper_bound']
        );
    }
    echo "\n";

    // Generate linear regression forecast
    echo "Generating 7-day linear regression forecast...\n";
    $lrForecasts = $forecaster->generateForecast('daily_sales', 7, 'linear_regression');

    echo "✓ Generated " . count($lrForecasts) . " forecasts\n\n";
    echo "Linear Regression Predictions:\n";
    foreach (array_slice($lrForecasts, 0, 3) as $forecast) {
        echo sprintf(
            "  %s: $%.2f (range: $%.2f - $%.2f)\n",
            $forecast['date'],
            $forecast['value'],
            $forecast['lower_bound'],
            $forecast['upper_bound']
        );
    }

    echo "\n✅ Forecast service test completed!\n";

} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}
```

### Expected Result

```bash
php 04-test-forecast.php

# Output:
# 📈 SmartDash Forecast Service Test
# ==================================================
#
# Generating 7-day moving average forecast...
# ✓ Generated 7 forecasts
#
# Moving Average Predictions:
#   2024-10-31: $1145.00 (range: $1045.32 - $1244.68)
#   2024-11-01: $1145.00 (range: $1045.32 - $1244.68)
#   2024-11-02: $1145.00 (range: $1045.32 - $1244.68)
#
# Generating 7-day linear regression forecast...
# ✓ Generated 7 forecasts
#
# Linear Regression Predictions:
#   2024-10-31: $1167.23 (range: $1089.45 - $1245.01)
#   2024-11-01: $1172.18 (range: $1094.40 - $1249.96)
#   2024-11-02: $1177.13 (range: $1099.35 - $1254.91)
#
# ✅ Forecast service test completed!
```

### Why It Works

Time series forecasting predicts future values based on historical patterns. Moving average smooths out short-term fluctuations by averaging recent values, providing a stable baseline forecast. Linear regression fits a trend line to the data, capturing upward or downward momentum. Confidence intervals (±1.96 standard deviations) represent 95% certainty ranges—the actual value will likely fall within these bounds. Storing forecasts in the database enables comparing predictions to actual values later, measuring accuracy. In production, you'd replace the synthetic data generation with queries to your actual sales/metrics tables.

### Troubleshooting

- **Error: "Insufficient historical data"** — Need at least 7 data points. Add more historical records.
