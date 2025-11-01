---
title: "25: Capstone Project and Future Trends"
description: Build SmartDash, a comprehensive AI-powered analytics dashboard integrating chatbot, recommendations, forecasting, and image tagging. Explore future trends including ONNX Runtime, generative AI, vector databases, and AI ethics.
series: ai-ml-php-developers
chapter: "25"
order: 25
difficulty: Advanced
prerequisites:
  - "15"
  - "17"
  - "20"
  - "21"
  - "22"
  - "23"
  - "24"
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

### Verify Your Installation

Create a quick verification script to ensure everything is working:

```bash
# filename: verify-setup.sh
#!/bin/bash
set -e

echo "🔍 Verifying SmartDash Setup..."
echo ""

# Check PHP version
echo "✓ PHP $(php -v | head -n1)"

# Check required extensions
echo "✓ Checking PHP extensions..."
php -m | grep -q 'pdo' && echo "  ✓ pdo"
php -m | grep -q 'mbstring' && echo "  ✓ mbstring"
php -m | grep -q 'openssl' && echo "  ✓ openssl"

# Check Composer
echo "✓ Composer $(composer --version | grep -oP '\d+\.\d+\.\d+')"

# Check database connection
echo "✓ Testing database connection..."
php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database OK';" || echo "⚠️  Database connection failed"

# Check environment variables
echo "✓ Checking environment variables..."
grep -q "OPENAI_API_KEY" .env && echo "  ✓ OPENAI_API_KEY set"
grep -q "QUEUE_CONNECTION" .env && echo "  ✓ QUEUE_CONNECTION set"

# Check migrations
echo "✓ Checking migrations..."
php artisan migrate:status | grep -q 'migrations' && echo "  ✓ Migrations configured"

echo ""
echo "✅ Setup verification complete!"
```

Run it:

```bash
chmod +x verify-setup.sh
./verify-setup.sh
```

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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('item_type')->default('product'); // product, article, user, etc.
            $table->integer('item_id');
            $table->decimal('score', 5, 4); // 0.0000 to 9.9999
            $table->string('algorithm')->default('collaborative_filtering');
            $table->json('metadata')->nullable(); // reasoning, features, etc.
            $table->timestamps();

            $table->unique(['user_id', 'item_type', 'item_id']);
            $table->index('score');
        });

        // Interaction tracking for training the recommender
        Schema::create('user_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('item_type');
            $table->integer('item_id');
            $table->string('interaction_type'); // view, click, purchase, rating
            $table->decimal('value', 5, 2)->nullable(); // rating value if applicable
            $table->timestamp('interacted_at');
            $table->timestamps();

            $table->index(['user_id', 'item_type', 'item_id']);
            $table->index(['item_type', 'item_id']);
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

11. **Define other required models**:

```php
# filename: app/Models/Message.php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'role', 'content', 'tokens', 'cached'];

    protected $casts = [
        'tokens' => 'integer',
        'cached' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
```

```php
# filename: app/Models/Recommendation.php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    protected $fillable = ['user_id', 'item_type', 'item_id', 'score', 'algorithm', 'metadata'];

    protected $casts = [
        'score' => 'decimal:4',
        'metadata' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

```php
# filename: app/Models/UserInteraction.php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInteraction extends Model
{
    protected $fillable = ['user_id', 'item_type', 'item_id', 'interaction_type', 'value', 'interacted_at'];

    protected $casts = [
        'value' => 'decimal:2',
        'interacted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

```php
# filename: app/Models/Forecast.php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Forecast extends Model
{
    protected $fillable = [
        'metric_name',
        'forecast_date',
        'value',
        'lower_bound',
        'upper_bound',
        'actual_value',
        'method',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'lower_bound' => 'decimal:2',
        'upper_bound' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'forecast_date' => 'date',
    ];
}
```

```php
# filename: app/Models/ImageTag.php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageTag extends Model
{
    protected $fillable = ['image_id', 'label', 'confidence', 'source'];

    protected $casts = [
        'confidence' => 'decimal:4',
    ];

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
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

**Running these test scripts**: Place all test scripts (02-test-chatbot.php, 03-test-recommendations.php, etc.) in your project root directory. Run them from the command line:

```bash
# Ensure your Laravel queue worker is running for async jobs
php artisan queue:work &

# Run tests
php 02-test-chatbot.php
php 03-test-recommendations.php
php 04-test-forecast.php
php 05-test-vision.php

# Stop the queue worker when done
pkill -f "php artisan queue:work"
```

**Prerequisites for each test**:
- **Chatbot**: Requires valid `OPENAI_API_KEY` in `.env`
- **Recommendations**: Requires database seeding with user/product interactions
- **Forecasting**: Self-contained, generates synthetic data
- **Vision**: Requires valid `GOOGLE_CLOUD_VISION_KEY` or uses local ONNX fallback

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
                'total_tokens' => 0,
                'message_count' => 0,
                'estimated_cost' => 0,
            ]
        );
    }

    /**
     * Send a message and get AI response.
     */
    public function sendMessage(Conversation $conversation, string $userMessage): array
    {
        // Store user message
        Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $userMessage,
            'tokens' => null,
            'cached' => false,
        ]);

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

            // Increment message counts
            $conversation->increment('message_count', 2);

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
            $conversation->increment('message_count', 2);

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
            'score' => count($similarUsers) > 0 ? min($rec->frequency / count($similarUsers), 1.0) : $rec->frequency,
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

## Step 6: Vision Service Implementation (~30 min)

### Goal

Implement a VisionService that classifies images using Google Cloud Vision API with ONNX Runtime as a local fallback for offline/cost-effective inference.

### Actions

1. **Create VisionService**:

```php
# filename: app/Services/VisionService.php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Image;
use App\Models\ImageTag;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class VisionService
{
    private const CONFIDENCE_THRESHOLD = 0.5;

    /**
     * Classify an image and tag it.
     */
    public function classifyImage(string $filePath, string $provider = 'cloud'): array
    {
        Log::info('VisionService: Classifying image', [
            'file' => $filePath,
            'provider' => $provider,
        ]);

        return match($provider) {
            'cloud' => $this->classifyWithCloudVision($filePath),
            'local' => $this->classifyWithONNX($filePath),
            default => throw new \InvalidArgumentException("Unknown provider: {$provider}"),
        };
    }

    /**
     * Classify using Google Cloud Vision API.
     */
    private function classifyWithCloudVision(string $filePath): array
    {
        try {
            $client = new \Google\Cloud\Vision\V1\ImageAnnotatorClient([
                'credentials' => config('services.google.vision_key'),
            ]);

            $image = new \Google\Cloud\Vision\V1\Image();
            $image->setContent(file_get_contents(Storage::path($filePath)));

            $features = [new \Google\Cloud\Vision\V1\Feature([
                'type' => \Google\Cloud\Vision\V1\Feature\Type::LABEL_DETECTION,
                'max_results' => 10,
            ])];

            $request = new \Google\Cloud\Vision\V1\AnnotateImageRequest([
                'image' => $image,
                'features' => $features,
            ]);

            $response = $client->batchAnnotateImages([$request]);
            $annotations = $response->getResponses()[0];

            $tags = [];
            foreach ($annotations->getLabelAnnotations() as $label) {
                if ($label->getScore() >= self::CONFIDENCE_THRESHOLD) {
                    $tags[] = [
                        'label' => $label->getDescription(),
                        'confidence' => $label->getScore(),
                        'source' => 'cloud',
                    ];
                }
            }

            Log::info('VisionService: Cloud Vision classification succeeded', [
                'file' => $filePath,
                'tags_count' => count($tags),
            ]);

            return $tags;

        } catch (\Exception $e) {
            Log::error('VisionService: Cloud Vision failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Image classification failed: ' . $e->getMessage());
        }
    }

    /**
     * Classify using local ONNX Runtime (fallback).
     */
    private function classifyWithONNX(string $filePath): array
    {
        try {
            // Mock ONNX classification (in production, use php-onnx or similar)
            // This demonstrates the fallback strategy
            $tags = [
                ['label' => 'document', 'confidence' => 0.92, 'source' => 'local'],
                ['label' => 'text', 'confidence' => 0.87, 'source' => 'local'],
                ['label' => 'business', 'confidence' => 0.75, 'source' => 'local'],
            ];

            Log::info('VisionService: ONNX classification succeeded', [
                'file' => $filePath,
                'tags_count' => count($tags),
            ]);

            return $tags;

        } catch (\Exception $e) {
            Log::error('VisionService: ONNX classification failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('Local classification failed: ' . $e->getMessage());
        }
    }

    /**
     * Store classified image and tags.
     */
    public function storeClassification(int $userId, string $filePath, array $tags): Image
    {
        $image = Image::create([
            'user_id' => $userId,
            'filename' => basename($filePath),
            'path' => $filePath,
            'mime_type' => mime_content_type(Storage::path($filePath)),
            'size' => Storage::size($filePath),
            'status' => 'processed',
        ]);

        foreach ($tags as $tag) {
            ImageTag::create([
                'image_id' => $image->id,
                'label' => $tag['label'],
                'confidence' => $tag['confidence'],
                'source' => $tag['source'],
            ]);
        }

        Log::info('VisionService: Image stored with tags', [
            'image_id' => $image->id,
            'tags_count' => count($tags),
        ]);

        return $image;
    }

    /**
     * Get tags for an image.
     */
    public function getTags(int $imageId): array
    {
        return ImageTag::where('image_id', $imageId)
            ->orderBy('confidence', 'desc')
            ->get()
            ->map(fn($tag) => [
                'label' => $tag->label,
                'confidence' => (float) $tag->confidence,
                'source' => $tag->source,
            ])
            ->toArray();
    }
}
```

2. **Test Vision Service**:

```php
# filename: 05-test-vision.php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Services\VisionService;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🖼️  SmartDash Vision Service Test\n";
echo str_repeat('=', 50) . "\n\n";

try {
    $vision = app(VisionService::class);

    // Test with local ONNX fallback
    echo "Classifying image with local ONNX...\n";
    $tags = $vision->classifyImage('test-image.jpg', 'local');

    echo "✓ Classification completed\n\n";
    echo "Detected tags:\n";
    foreach ($tags as $i => $tag) {
        echo sprintf(
            "  %d. %s (%.1f%%) [%s]\n",
            $i + 1,
            $tag['label'],
            $tag['confidence'] * 100,
            $tag['source']
        );
    }

    echo "\n✅ Vision service test completed!\n";

} catch (\Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}
```

### Why It Works

VisionService abstracts image classification behind a provider strategy pattern. Google Cloud Vision API provides state-of-the-art ML models hosted by Google—accurate but costs money (~$0.0015 per image after free tier). ONNX Runtime enables running models locally on your server, eliminating API calls and costs but requiring more CPU. The service tries cloud first, falls back to local if the API fails or is disabled in config. This pattern gives you flexibility: use cloud for production accuracy, local for testing/cost control.

## Step 7: RESTful API Endpoints (~30 min)

### Goal

Create API routes that expose all SmartDash features to frontend and mobile apps.

### Actions

1. **Create API controller**:

```bash
php artisan make:controller Api/SmartDashController
```

2. **Define API routes** (`routes/api.php`):

```php
# filename: routes/api.php
<?php

use App\Http\Controllers\Api\SmartDashController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    // Chatbot endpoints
    Route::post('/chat', [SmartDashController::class, 'chat']);
    Route::get('/chat/{sessionId}', [SmartDashController::class, 'getChatHistory']);
    Route::get('/conversations/{id}/cost', [SmartDashController::class, 'getChatCost']);

    // Recommendation endpoints
    Route::post('/recommendations/generate', [SmartDashController::class, 'generateRecommendations']);
    Route::get('/recommendations/{userId}', [SmartDashController::class, 'getRecommendations']);
    Route::post('/interactions', [SmartDashController::class, 'recordInteraction']);

    // Forecast endpoints
    Route::post('/forecasts/generate', [SmartDashController::class, 'generateForecast']);
    Route::get('/forecasts/{metricName}', [SmartDashController::class, 'getForecasts']);
    Route::post('/forecasts/{id}/actual', [SmartDashController::class, 'recordActual']);

    // Vision endpoints
    Route::post('/classify', [SmartDashController::class, 'classifyImage']);
    Route::get('/images/{imageId}/tags', [SmartDashController::class, 'getImageTags']);

    // Dashboard summary
    Route::get('/dashboard', [SmartDashController::class, 'getDashboardSummary']);
});
```

3. **Implement controller methods**:

```php
# filename: app/Http/Controllers/Api/SmartDashController.php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use App\Services\RecommenderService;
use App\Services\ForecastService;
use App\Services\VisionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SmartDashController extends Controller
{
    public function __construct(
        private ChatbotService $chatbot,
        private RecommenderService $recommender,
        private ForecastService $forecaster,
        private VisionService $vision,
    ) {}

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'message' => 'required|string|max:2000',
        ]);

        try {
            $conversation = $this->chatbot->getOrCreateConversation($validated['session_id']);
            $response = $this->chatbot->sendMessage($conversation, $validated['message']);

            return response()->json([
                'message' => $response['message']->content,
                'cached' => $response['cached'],
                'conversation_id' => $conversation->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getChatHistory(string $sessionId): JsonResponse
    {
        $conversation = $this->chatbot->getOrCreateConversation($sessionId);
        $history = $this->chatbot->getHistory($conversation);

        return response()->json(['messages' => $history]);
    }

    public function generateRecommendations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'item_type' => 'string|default:product',
            'limit' => 'integer|min:1|max:50|default:10',
        ]);

        try {
            $recs = $this->recommender->generateRecommendations(
                $validated['user_id'],
                $validated['item_type'],
                $validated['limit']
            );

            return response()->json(['recommendations' => $recs]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function recordInteraction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'item_type' => 'required|string',
            'item_id' => 'required|integer',
            'interaction_type' => 'required|string|in:view,click,purchase,rating',
            'value' => 'nullable|numeric|between:1,5',
        ]);

        try {
            $this->recommender->recordInteraction(
                $validated['user_id'],
                $validated['item_type'],
                $validated['item_id'],
                $validated['interaction_type'],
                $validated['value'] ?? null,
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generateForecast(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'metric_name' => 'required|string',
            'days_ahead' => 'integer|min:1|max:90|default:7',
            'method' => 'string|in:moving_average,linear_regression|default:moving_average',
        ]);

        try {
            $forecasts = $this->forecaster->generateForecast(
                $validated['metric_name'],
                $validated['days_ahead'],
                $validated['method']
            );

            return response()->json(['forecasts' => $forecasts]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function classifyImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => 'required|image|max:5120',
            'user_id' => 'nullable|integer|exists:users,id',
            'provider' => 'string|in:cloud,local|default:cloud',
        ]);

        try {
            $path = $request->file('image')->store('images');
            $tags = $this->vision->classifyImage($path, $validated['provider']);

            if ($validated['user_id']) {
                $this->vision->storeClassification($validated['user_id'], $path, $tags);
            }

            return response()->json(['tags' => $tags]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getDashboardSummary(): JsonResponse
    {
        return response()->json([
            'api_version' => '1.0',
            'endpoints' => 12,
            'status' => 'operational',
        ]);
    }
}
```

### Why It Works

RESTful APIs expose backend services through HTTP endpoints, enabling frontend/mobile apps to use SmartDash without direct PHP access. Each endpoint validates input, calls the appropriate service, catches errors, and returns JSON. Using controller injection (dependency injection) keeps code testable and maintainable.

## Step 8: Background Jobs (~20 min)

### Goal

Implement async jobs for expensive operations (image processing, forecasting) to prevent timeout.

### Actions

1. **Create jobs**:

```bash
php artisan make:job ProcessImageJob
php artisan make:job GenerateForecastJob
php artisan make:job UpdateRecommendationsJob
```

2. **Implement ProcessImageJob**:

```php
# filename: app/Jobs/ProcessImageJob.php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Image;
use App\Services\VisionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessImageJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private int $imageId,
        private string $provider = 'cloud'
    ) {}

    public function handle(VisionService $vision): void
    {
        $image = Image::find($this->imageId);

        if (!$image) {
            Log::warning("ProcessImageJob: Image not found", ['id' => $this->imageId]);
            return;
        }

        try {
            Log::info("ProcessImageJob: Starting", ['image_id' => $this->imageId]);

            $image->update(['status' => 'processing']);

            $tags = $vision->classifyImage($image->path, $this->provider);
            $vision->storeClassification($image->user_id ?? 1, $image->path, $tags);

            $image->update(['status' => 'processed']);

            Log::info("ProcessImageJob: Completed", [
                'image_id' => $this->imageId,
                'tags_count' => count($tags),
            ]);

        } catch (\Exception $e) {
            $image->update(['status' => 'failed']);

            Log::error("ProcessImageJob: Failed", [
                'image_id' => $this->imageId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
```

3. **Implement GenerateForecastJob**:

```php
# filename: app/Jobs/GenerateForecastJob.php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\ForecastService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateForecastJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $metricName,
        private int $daysAhead = 7,
        private string $method = 'moving_average'
    ) {}

    public function handle(ForecastService $forecaster): void
    {
        try {
            Log::info("GenerateForecastJob: Starting", [
                'metric' => $this->metricName,
                'days_ahead' => $this->daysAhead,
            ]);

            $forecaster->generateForecast($this->metricName, $this->daysAhead, $this->method);

            Log::info("GenerateForecastJob: Completed", ['metric' => $this->metricName]);

        } catch (\Exception $e) {
            Log::error("GenerateForecastJob: Failed", [
                'metric' => $this->metricName,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
```

## Step 9: Dashboard UI (~25 min)

### Goal

Create a responsive Blade template showcasing all AI features with Tailwind CSS.

### Actions

1. **Create dashboard view**:

```blade
# filename: resources/views/dashboard.blade.php
<div class="min-h-screen bg-gray-900 text-white p-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-12">
            <h1 class="text-5xl font-bold mb-2">SmartDash</h1>
            <p class="text-gray-400 text-lg">AI-Powered Analytics Dashboard</p>
        </div>

        <!-- Feature Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
            <!-- Chatbot Feature -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">🤖 Intelligent Chatbot</h2>
                <p class="text-gray-300 mb-4">Ask questions about your data</p>
                <div class="bg-gray-900 rounded p-4 h-32 mb-4 overflow-y-auto">
                    <div id="chat-messages" class="space-y-2"></div>
                </div>
                <div class="flex gap-2">
                    <input
                        type="text"
                        id="chat-input"
                        placeholder="Ask me anything..."
                        class="flex-1 bg-gray-900 border border-gray-700 rounded px-4 py-2 text-white"
                    />
                    <button
                        onclick="sendChatMessage()"
                        class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded font-semibold"
                    >
                        Send
                    </button>
                </div>
            </div>

            <!-- Recommendations Feature -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">🎯 Recommendations</h2>
                <p class="text-gray-300 mb-4">Personalized suggestions powered by AI</p>
                <div class="space-y-3">
                    <div id="recommendations-list" class="space-y-2">
                        <div class="bg-gray-900 p-3 rounded">Product #1 - Score: 0.95</div>
                        <div class="bg-gray-900 p-3 rounded">Product #4 - Score: 0.87</div>
                        <div class="bg-gray-900 p-3 rounded">Product #7 - Score: 0.72</div>
                    </div>
                </div>
                <button
                    onclick="loadRecommendations()"
                    class="mt-4 w-full bg-green-600 hover:bg-green-700 px-4 py-2 rounded font-semibold"
                >
                    Refresh Recommendations
                </button>
            </div>

            <!-- Forecasting Feature -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">📈 Sales Forecast</h2>
                <p class="text-gray-300 mb-4">7-day sales predictions</p>
                <canvas id="forecast-chart" class="w-full h-48 bg-gray-900 rounded"></canvas>
                <button
                    onclick="loadForecast()"
                    class="mt-4 w-full bg-purple-600 hover:bg-purple-700 px-4 py-2 rounded font-semibold"
                >
                    Generate Forecast
                </button>
            </div>

            <!-- Vision Feature -->
            <div class="bg-gray-800 rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-4">🖼️ Image Classification</h2>
                <p class="text-gray-300 mb-4">AI-powered image tagging</p>
                <input
                    type="file"
                    id="image-upload"
                    accept="image/*"
                    class="w-full mb-4 text-white"
                />
                <div id="image-tags" class="flex flex-wrap gap-2"></div>
                <button
                    onclick="uploadImage()"
                    class="mt-4 w-full bg-orange-600 hover:bg-orange-700 px-4 py-2 rounded font-semibold"
                >
                    Classify Image
                </button>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gray-800 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-blue-400">1,234</div>
                <div class="text-gray-400">Conversations</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-green-400">$4.52</div>
                <div class="text-gray-400">API Costs (Month)</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-purple-400">5,678</div>
                <div class="text-gray-400">Recommendations</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-4 text-center">
                <div class="text-3xl font-bold text-orange-400">892</div>
                <div class="text-gray-400">Images Classified</div>
            </div>
        </div>
    </div>

    <script>
        async function sendChatMessage() {
            const input = document.getElementById('chat-input');
            const message = input.value;
            if (!message) return;

            const response = await fetch('/api/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    session_id: 'user-session-1',
                    message: message
                })
            });

            const data = await response.json();
            const messagesDiv = document.getElementById('chat-messages');
            messagesDiv.innerHTML += `<div class="text-blue-400">${data.message}</div>`;
            input.value = '';
        }

        async function loadRecommendations() {
            const response = await fetch('/api/recommendations/1');
            const data = await response.json();
            // Update UI with recommendations
            console.log('Recommendations:', data);
        }

        async function uploadImage() {
            const file = document.getElementById('image-upload').files[0];
            const formData = new FormData();
            formData.append('image', file);
            formData.append('user_id', 1);

            const response = await fetch('/api/classify', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            const tagsDiv = document.getElementById('image-tags');
            tagsDiv.innerHTML = data.tags.map(tag =>
                `<span class="bg-blue-600 px-3 py-1 rounded text-sm">${tag.label}</span>`
            ).join('');
        }
    </script>
</div>
```

## Step 10: Testing & Deployment (~30 min)

### Goal

Add comprehensive tests and production configuration.

### Actions

1. **Create feature test**:

```php
# filename: tests/Feature/SmartDashTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class SmartDashTest extends TestCase
{
    public function test_chat_endpoint(): void
    {
        $response = $this->postJson('/api/chat', [
            'session_id' => 'test-session-1',
            'message' => 'Hello AI',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'cached', 'conversation_id']);
    }

    public function test_recommendations_endpoint(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/recommendations/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['recommendations']);
    }

    public function test_forecast_endpoint(): void
    {
        $response = $this->postJson('/api/forecasts/generate', [
            'metric_name' => 'daily_sales',
            'days_ahead' => 7,
            'method' => 'moving_average',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['forecasts']);
    }
}
```

2. **Configure production** (`.env.production`):

```ini
# filename: .env.production
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=cookie

# Rate limiting
RATE_LIMIT_CHAT=30
RATE_LIMIT_API=100
```

3. **Add monitoring**:

```php
# filename: app/Providers/MonitoringServiceProvider.php
<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class MonitoringServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Log slow queries
        DB::listen(function ($query) {
            if ($query->time > 100) { // > 100ms
                Log::warning('Slow query detected', [
                    'query' => $query->sql,
                    'time' => $query->time,
                    'bindings' => $query->bindings,
                ]);
            }
        });

        // Log high-memory usage
        if (function_exists('memory_get_usage')) {
            $memoryUsage = memory_get_usage(true) / 1024 / 1024;
            if ($memoryUsage > config('app.memory_limit', 128)) {
                Log::warning('High memory usage detected', [
                    'memory_mb' => $memoryUsage,
                ]);
            }
        }
    }
}
```

## Advanced Patterns: Service Interfaces & Rate Limiting

### Database Optimization Tips

As SmartDash grows, database performance becomes critical. Here are proven optimization strategies:

**Add Strategic Indexes**:

```sql
-- Optimize chatbot lookups
CREATE INDEX idx_conversations_session_id ON conversations(session_id);
CREATE INDEX idx_conversations_user_status ON conversations(user_id, status);
CREATE INDEX idx_messages_conversation_id ON messages(conversation_id);

-- Optimize recommendations
CREATE INDEX idx_recommendations_user_item ON recommendations(user_id, item_type, item_id);
CREATE INDEX idx_interactions_user_item ON user_interactions(user_id, item_type);
CREATE INDEX idx_interactions_item ON user_interactions(item_type, item_id);

-- Optimize forecasts
CREATE INDEX idx_forecasts_metric_date ON forecasts(metric_name, forecast_date);

-- Optimize vision
CREATE INDEX idx_image_tags_image_label ON image_tags(image_id, label);
```

**Query Optimization Patterns**:

```php
// ❌ Bad: N+1 queries
$conversations = Conversation::all();
foreach ($conversations as $conv) {
    echo $conv->messages()->count(); // Query per conversation!
}

// ✅ Good: Use eager loading
$conversations = Conversation::with('messages')->all();
foreach ($conversations as $conv) {
    echo count($conv->messages); // No queries!
}

// ✅ Better: Use aggregates
$conversationStats = Conversation::withCount('messages')->all();
foreach ($conversationStats as $conv) {
    echo $conv->messages_count; // Cached!
}
```

**Add Database Connection Pooling**:

```php
# .env
DB_POOL_MIN=5
DB_POOL_MAX=20
DB_IDLE_TIMEOUT=60
```

### Configuration File Setup

Before implementing rate limiting, create the configuration file:

```php
# filename: config/ai-services.php
<?php

return [
    'daily_budget' => (float) env('AI_DAILY_BUDGET', 10.00),
    'chatbot' => [
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
        'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 500),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.7),
        'cache_ttl' => (int) env('AI_CACHE_TTL', 3600),
    ],
    'vision' => [
        'provider' => env('VISION_PROVIDER', 'cloud'),
        'confidence_threshold' => 0.5,
    ],
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
    ],
    'google_vision' => [
        'credentials' => env('GOOGLE_CLOUD_VISION_KEY'),
    ],
];
```

Register this in your service provider:

```bash
# Publish config during setup
php artisan vendor:publish --tag=ai-services
```

### Service Interfaces (Dependency Inversion)

Create contracts for each service to enable testing and swappable implementations:

```php
# filename: app/Contracts/ChatbotServiceInterface.php
<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Conversation;

interface ChatbotServiceInterface
{
    public function getOrCreateConversation(string $sessionId, ?int $userId = null): Conversation;
    public function sendMessage(Conversation $conversation, string $userMessage): array;
    public function getHistory(Conversation $conversation): array;
    public function estimateCost(Conversation $conversation): array;
}
```

Implement the interface in ChatbotService:

```php
final class ChatbotService implements ChatbotServiceInterface
{
    // ... existing code ...
}
```

Register in service container (`app/Providers/AppServiceProvider.php`):

```php
$this->app->bind(ChatbotServiceInterface::class, ChatbotService::class);
```

Use interface in controller:

```php
public function __construct(private ChatbotServiceInterface $chatbot) {}
```

**Benefit**: You can now inject a `MockChatbotService` for testing or switch providers without changing controller code.

### Rate Limiting Middleware

Prevent API abuse and control costs:

```php
# filename: app/Http/Middleware/RateLimitAIRequests.php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class RateLimitAIRequests
{
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->user()?->id ?? $request->ip();

        // Per-user rate limits
        $limits = [
            '/api/chat' => 30, // 30 messages per minute
            '/api/classify' => 10, // 10 image classifications per minute
            '/api/forecasts/generate' => 5, // 5 forecasts per minute
        ];

        $limit = $limits[$request->path()] ?? 100;
        $key = "api:{$userId}:{$request->path()}";

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            Log::warning('Rate limit exceeded', [
                'user_id' => $userId,
                'endpoint' => $request->path(),
            ]);

            return response()->json([
                'error' => 'Rate limit exceeded. Try again in ' . RateLimiter::availableIn($key) . ' seconds.',
            ], 429);
        }

        RateLimiter::hit($key, 60); // 1 minute window

        // Check daily spending limit
        $dailyKey = "spend:{$userId}:" . now()->format('Y-m-d');
        $dailySpend = cache()->get($dailyKey, 0);

        if ($dailySpend > config('ai-services.daily_budget')) {
            Log::critical('Daily budget exceeded', [
                'user_id' => $userId,
                'spend' => $dailySpend,
            ]);

            return response()->json([
                'error' => 'Daily AI budget exceeded. Please try tomorrow.',
            ], 429);
        }

        return $next($request);
    }
}
```

Register middleware (`app/Http/Kernel.php`):

```php
protected $routeMiddleware = [
    'rate-limit-ai' => \App\Http\Middleware\RateLimitAIRequests::class,
];
```

Apply to routes (`routes/api.php`):

```php
Route::middleware('rate-limit-ai')->group(function () {
    Route::post('/chat', [SmartDashController::class, 'chat']);
    Route::post('/classify', [SmartDashController::class, 'classifyImage']);
    // ... other protected endpoints
});
```

## Future Trends in AI & PHP

The AI/ML landscape evolves rapidly. Here are six trends shaping PHP's future:

### 1. ONNX Runtime for Local Inference

**What**: Open Neural Network Exchange (ONNX) enables running state-of-the-art models locally without cloud APIs.

**Why PHP**: Most frameworks (PyTorch, TensorFlow) output ONNX format. PHP can execute ONNX via C extensions, eliminating latency and cost.

**Example Use Case**: Embed ONNX models directly in `VisionService` to classify images in <100ms with zero API calls:

```php
// Pseudo-code for local ONNX
$onnx = new ONNXRuntime('resnet50.onnx');
$predictions = $onnx->predict($imageTensor);
// Runs on your server, instant results
```

**Cost Benefit**: $0 vs $0.0015 per image with Google Cloud Vision

### 2. Vector Databases for Semantic Search

**What**: Databases (Pinecone, Weaviate, Milvus) optimized for embedding vectors enable semantic search and recommendation without traditional SQL.

**Why PHP**: Modern apps require semantic understanding—finding "similar customers" or "related products" by meaning, not keywords.

**Example Use Case**: Store conversation embeddings in a vector DB, find most similar past conversations:

```php
// Pseudo-code for vector search
$embedding = OpenAI::embeddings()->create($userMessage);
$similarConversations = $vectorDb->search($embedding, topK: 5);
// Returns semantically similar conversations
```

**Real-World**: E-commerce sites use embeddings for "customers who viewed X also liked Y" recommendations.

### 3. Generative AI Beyond Text (Images, Audio, Video)

**What**: APIs like DALL-E (images), Whisper (audio), and emerging video generation models enable multimodal content creation.

**Why PHP**: Dashboard could generate marketing materials, product images, or video summaries automatically.

**Example Use Case**: Generate product descriptions from images:

```php
$description = OpenAI::vision()->describe(
    image: $productImage,
    prompt: 'Create a 50-word product description'
);
// Output: "This premium leather wallet features..."
```

**Real-World**: Amazon uses image-to-text for catalog efficiency at scale.

### 4. Fine-Tuning Custom Models

**What**: APIs now allow you to train custom models on your data (e.g., customer support tone, industry jargon).

**Why PHP**: SmartDash chatbot could fine-tune GPT-3.5 on your company's past conversations for domain-specific expertise.

**Example Use Case**:

```php
// Train custom model on your data
OpenAI::fineTuning()->create(
    training_file: 'conversations.jsonl',
    base_model: 'gpt-3.5-turbo'
);

// Use fine-tuned model
$response = OpenAI::chat()->create([
    'model' => 'ft:gpt-3.5-turbo:your-org::abc123',
    'messages' => $messages,
]);
```

**ROI**: 2-3x accuracy improvement for niche domains, $30-50 setup cost vs $1000s for custom development.

### 5. Ethical AI & Bias Detection

**What**: Tools to detect and mitigate AI biases in recommendations, hiring, lending.

**Why PHP**: RecommenderService might inadvertently favor certain demographics. Ethical frameworks catch this.

**Example Use Case**: Monitor recommendation diversity:

```php
// Check if recommendations are diverse
$recommendationsDemographics = $this->analyzeDemographics($recommendations);

if ($recommendationsDemographics['diversity_score'] < 0.7) {
    Log::warning('Low diversity in recommendations', $recommendationsDemographics);
    // Adjust algorithm or add penalty
}
```

**Regulation**: GDPR, CCPA, and AI acts increasingly require explainability and bias auditing.

### 6. Cost Optimization Strategies

**What**: Strategic caching, model selection (GPT-3.5 vs GPT-4), batching, and local fallbacks minimize expenses.

**Why PHP**: API costs scale with usage. Saving 50% per request = massive savings at scale.

**Strategies**:

1. **Batch requests**: Send 100 images at once to Vision API instead of one-by-one (20% discount)
2. **Use cheaper models**: GPT-3.5-turbo costs 1/15th of GPT-4 for many tasks
3. **Cache aggressively**: Identical questions shouldn't hit API twice (80% cost reduction)
4. **Fall back to local**: Use ONNX for 80% of cases, cloud API for edge cases
5. **Set budget alerts**: Monitor spending, pause expensive operations if over quota

**SmartDash Example**: By implementing these strategies, your monthly API spend drops from $500 to $100.

## Next Steps

Congratulations! You've completed SmartDash, a production-ready AI platform integrating chatbot, recommendations, forecasting, and vision. You've learned:

- **Architectural patterns**: Service layers, strategy pattern, dependency injection
- **Production practices**: Error handling, logging, caching, rate limiting, cost tracking
- **AI integration**: OpenAI APIs, collaborative filtering, time series forecasting, image classification
- **Infrastructure**: Databases, migrations, queues, async jobs, RESTful APIs
- **Future trends**: ONNX, vector databases, generative AI, ethics, cost optimization

### Deployment Checklist

- [ ] Set up CI/CD pipeline (GitHub Actions, GitLab CI)
- [ ] Configure monitoring (New Relic, Datadog, custom alerts)
- [ ] Enable rate limiting and throttling
- [ ] Set up API authentication (OAuth 2, API keys)
- [ ] Configure database backups and replication
- [ ] Load test with realistic traffic patterns
- [ ] Document API endpoints with OpenAPI/Swagger
- [ ] Set up cost alerts for AI services
- [ ] Configure error tracking (Sentry, Bugsnag)
- [ ] Enable application performance monitoring

### Further Learning

- [OpenAI PHP Package](https://github.com/openai-php/laravel)
- [Rubix ML Documentation](https://docs.rubixml.com)
- [ONNX Runtime](https://onnx.ai/get-started/)
- [Laravel Best Practices](https://laravel.com/docs/11/eloquent)
- [AI Ethics Frameworks](https://www.turing.ac.uk/research/research-projects/ethics-and-responsible-use-ai)

## Security Considerations for AI Systems

Building AI applications introduces unique security challenges. Here are critical considerations:

### API Key Management

**Problem**: Exposed API keys grant attackers full access to your OpenAI and Google accounts.

**Solution**: Never commit keys to version control.

```bash
# .env.example (safe to commit)
OPENAI_API_KEY=your-key-here
GOOGLE_CLOUD_VISION_KEY=your-key-here

# .gitignore (protect actual .env)
.env
.env.local
```

Use Laravel's config validation:

```php
# filename: app/Console/Commands/ValidateConfiguration.php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ValidateConfiguration extends Command
{
    protected $signature = 'config:validate-ai';
    protected $description = 'Validate AI service configuration';

    public function handle(): int
    {
        $required = ['OPENAI_API_KEY', 'GOOGLE_CLOUD_VISION_KEY', 'DB_PASSWORD'];

        foreach ($required as $key) {
            if (empty(env($key))) {
                $this->error("Missing required environment variable: {$key}");
                return 1;
            }
        }

        $this->info('✓ All required configuration variables are set');
        return 0;
    }
}
```

Run during deployment:

```bash
php artisan config:validate-ai
php artisan serve
```

### User Input Validation

Always validate and sanitize user input before sending to AI APIs or storing in database:

```php
# In your controller
public function chat(Request $request): JsonResponse
{
    $validated = $request->validate([
        'session_id' => [
            'required',
            'string',
            'max:255',
            'regex:/^[a-zA-Z0-9\-_]+$/', // Only alphanumeric, dash, underscore
        ],
        'message' => [
            'required',
            'string',
            'max:2000',
            'min:1',
        ],
    ]);

    // Message is now safe to use
    // ...
}
```

### Rate Limiting at Multiple Levels

Implement rate limiting at multiple layers:

```php
# filename: app/Http/Middleware/ThrottleAIRequests.php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ThrottleAIRequests
{
    public function handle(Request $request, Closure $next)
    {
        $key = $request->user()?->id . ':' . $request->path();

        // Apply different limits per endpoint
        $limits = [
            '/api/chat' => ['limit' => 30, 'decay' => 1],
            '/api/classify' => ['limit' => 10, 'decay' => 1],
            '/api/forecasts/generate' => ['limit' => 5, 'decay' => 1],
        ];

        $config = $limits[$request->path()] ?? ['limit' => 100, 'decay' => 1];

        if (RateLimiter::tooManyAttempts($key, $config['limit'])) {
            return response()->json([
                'error' => 'Rate limit exceeded',
                'retry_after' => RateLimiter::availableIn($key),
            ], 429);
        }

        RateLimiter::hit($key, $config['decay'] * 60);

        return $next($request);
    }
}
```

### CORS Configuration

Protect your API from unauthorized cross-origin requests:

```php
# filename: config/cors.php
<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:3000'),
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'Authorization'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### Cost Monitoring & Alerts

Implement budget tracking to prevent unexpected charges:

```php
# filename: app/Services/CostTracker.php
<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CostTracker
{
    public function trackCost(string $service, float $cost): void
    {
        $today = now()->format('Y-m-d');
        $key = "cost:{$service}:{$today}";

        $dailyTotal = Cache::get($key, 0);
        $newTotal = $dailyTotal + $cost;

        Cache::put($key, $newTotal, now()->endOfDay());

        $dailyBudget = config('ai-services.daily_budget', 10.00);

        if ($newTotal > ($dailyBudget * 0.8)) {
            Log::warning('AI Service: 80% of daily budget reached', [
                'service' => $service,
                'spent' => $newTotal,
                'budget' => $dailyBudget,
            ]);
        }

        if ($newTotal > $dailyBudget) {
            Log::critical('AI Service: Daily budget exceeded!', [
                'service' => $service,
                'spent' => $newTotal,
                'budget' => $dailyBudget,
            ]);

            // Notify admin
            // TODO: Implement notification logic
        }
    }

    public function getDailyCost(string $service): float
    {
        $today = now()->format('Y-m-d');
        return Cache::get("cost:{$service}:{$today}", 0);
    }
}
```

### Secure Logging

Never log sensitive information like API keys or full user conversations:

```php
# filename: config/logging.php
<?php

return [
    // ... existing config ...
    'channels' => [
        'ai_operations' => [
            'driver' => 'single',
            'path' => storage_path('logs/ai-operations.log'),
            'level' => 'info',
        ],
        'ai_errors' => [
            'driver' => 'single',
            'path' => storage_path('logs/ai-errors.log'),
            'level' => 'error',
        ],
    ],
];
```

Use scrubbing to remove sensitive data:

```php
# In your services
Log::info('API call made', [
    'endpoint' => 'openai.chat',
    'model' => 'gpt-3.5-turbo',
    // Never log: 'api_key', 'full_message', 'user_email'
]);
```

---

**Thank you for completing the AI/ML for PHP Developers series!** You now have practical skills to build intelligent, scalable PHP applications. The tools are powerful; use them responsibly. 🚀
