---
title: "23: Integrating AI Models into Web Applications"
description: "Build production-ready Laravel applications with ML integration: create model service layers, implement caching strategies, process predictions in background jobs, and deploy intelligent features with proper error handling and performance optimization"
series: "ai-ml-php-developers"
chapter: 23
order: 23
difficulty: "Intermediate"
prerequisites:
  - "/series/ai-ml-php-developers/chapters/22-building-a-recommendation-engine-in-php"
  - "/series/ai-ml-php-developers/chapters/15-language-models-and-text-generation-with-openai-apis"
  - "/series/ai-ml-php-developers/chapters/14-nlp-project-text-classification-in-php"
---

![Integrating AI Models into Web Applications](/images/ai-ml-php-developers/chapter-23-ai-web-integration-hero-full.webp)

# Chapter 23: Integrating AI Models into Web Applications

## Overview

Throughout this series, you've built impressive AI capabilities—text classifiers, language model integrations, image recognition systems, and recommendation engines. But these have been primarily standalone scripts and CLI tools. Now it's time to bring everything together and deploy ML models into production web applications where real users can interact with them.

Integrating AI into web apps is fundamentally different from running batch scripts. You're dealing with real-time user expectations (sub-second response times), concurrent requests, limited server resources, intermittent failures, and the need for graceful degradation when models are unavailable. A sentiment analyzer that works perfectly in isolation can bring your entire application to a halt if it blocks web requests for 5 seconds. A recommendation engine that loads a 500MB model on every request will exhaust your server memory. These production realities require architectural patterns specifically designed for ML workloads in web contexts.

This chapter focuses on **Laravel**, PHP's most popular framework, to demonstrate professional-grade ML integration patterns. You'll build a complete e-commerce application with three intelligent features: sentiment analysis on product reviews, personalized product recommendations, and AI-powered customer support responses. Along the way, you'll implement the critical infrastructure every production ML system needs: model service layers that load models efficiently, Redis caching to avoid redundant predictions, background job queues for long-running inference, comprehensive error handling with fallback strategies, and monitoring to track ML system health.

The patterns you learn here apply to any PHP framework (Symfony, Slim, or even vanilla PHP). By the end of this chapter, you'll understand not just _how_ to integrate ML models, but _when_ to use synchronous vs asynchronous processing, how to cache intelligently, when to call external APIs vs run models locally, and how to build systems that degrade gracefully when AI components fail. These are the skills that separate toy demos from production AI applications serving thousands of users.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 22](/series/ai-ml-php-developers/chapters/22-building-a-recommendation-engine-in-php) or understand how recommendation engines work
- Completed [Chapter 14](/series/ai-ml-php-developers/chapters/14-nlp-project-text-classification-in-php) or know how to build text classifiers
- Completed [Chapter 15](/series/ai-ml-php-developers/chapters/15-language-models-and-text-generation-with-openai-apis) or have experience with LLM APIs
- PHP 8.4+ installed and working, confirmed with `php --version`
- **Laravel 12.x installed** (we'll create a fresh project in Step 1)
- Composer for dependency management
- Redis or Memcached for caching (Redis recommended)
- Basic Laravel knowledge: routes, controllers, models, middleware, jobs
- Understanding of queues, background processing, and database migrations
- Optional: OpenAI API key for chatbot features (free tier works)
- Text editor or IDE with PHP and Laravel support

**Estimated Time**: ~115-145 minutes (including Laravel setup, reading, coding, and exercises)

**Verify your environment:**

```bash
# Check PHP version (need 8.4+)
php --version

# Check Laravel installer
laravel --version

# Or check Composer for Laravel creation
composer --version

# Check Redis is running (if using)
redis-cli ping
# Should return: PONG

# Check required PHP extensions
php -m | grep -E 'redis|pdo|mbstring|openssl|tokenizer|xml|ctype|json'
```

## What You'll Build

By the end of this chapter, you will have created:

- A **complete Laravel 12.x e-commerce application** with ML-powered features serving as a realistic integration example
- A **ModelService base class** providing the foundation for loading, caching, and managing ML models in Laravel's service container
- A **SentimentAnalysisService** that analyzes product review text and returns sentiment scores using the classifier from Chapter 14
- A **ProductRecommendationService** integrating the recommendation engine from Chapter 22 to suggest personalized products
- A **ChatbotService** using OpenAI's API (from Chapter 15) to provide intelligent customer support responses
- A **model caching strategy** using Redis to store predictions and avoid redundant inference, with TTL management and cache invalidation
- A **ModelServiceProvider** binding ML services as singletons in Laravel's container for efficient model loading once per request cycle
- A **RESTful API endpoints** for ML predictions: `/api/ml/sentiment`, `/api/ml/recommendations`, `/api/ml/chat` with proper validation and error responses
- A **ProcessPredictionJob** background queue job for handling long-running ML inference without blocking HTTP requests
- A **BatchPredictionJob** for processing multiple predictions efficiently in a single batch operation
- A **ValidateMLInput middleware** that sanitizes and validates user input before passing to ML models
- A **rate limiter configuration** preventing abuse of computationally expensive ML endpoints
- A **Prediction model and migration** for storing ML results in the database with metadata for monitoring and debugging
- A **comprehensive error handling layer** with fallback strategies when models fail, timeout management, and retry logic
- A **health check endpoint** `/api/ml/health` monitoring ML service availability and performance metrics
- A **Blade component** for displaying AI-generated content in views with loading states and error messages
- A **Vue.js widget** for real-time sentiment analysis as users type reviews
- A **production-ready logging system** tracking prediction latency, cache hit rates, and error frequencies
- Feature tests demonstrating how to test ML integrations without actually running expensive inference
- **Environment configuration** managing API keys, model paths, and service toggles through `.env`

All code follows Laravel 12.x conventions, uses PHP 8.4 features, includes comprehensive error handling, and is production-ready.

::: info Code Examples
Complete, runnable examples for this chapter are available in the code directory:

**Setup & Configuration:**

- [`composer.json`](../code/chapter-23/composer.json) — Laravel + ML dependencies
- [`env.example`](../code/chapter-23/env.example) — Environment configuration template
- [`README.md`](../code/chapter-23/README.md) — Setup and installation instructions

**Core Services:**

- [`app/Services/ML/ModelService.php`](../code/chapter-23/app/Services/ML/ModelService.php) — Base ML service class
- [`app/Services/ML/SentimentAnalysisService.php`](../code/chapter-23/app/Services/ML/SentimentAnalysisService.php) — Sentiment analysis implementation
- [`app/Services/ML/ProductRecommendationService.php`](../code/chapter-23/app/Services/ML/ProductRecommendationService.php) — Recommendation engine integration
- [`app/Services/ML/ChatbotService.php`](../code/chapter-23/app/Services/ML/ChatbotService.php) — OpenAI chatbot service

**Controllers & Routes:**

- [`app/Http/Controllers/MLController.php`](../code/chapter-23/app/Http/Controllers/MLController.php) — ML API endpoints
- [`routes/api.php`](../code/chapter-23/routes/api.php) — API route definitions
- [`routes/web.php`](../code/chapter-23/routes/web.php) — Web route definitions

**Middleware & Validation:**

- [`app/Http/Middleware/ValidateMLInput.php`](../code/chapter-23/app/Http/Middleware/ValidateMLInput.php) — Input validation middleware
- [`app/Http/Requests/SentimentAnalysisRequest.php`](../code/chapter-23/app/Http/Requests/SentimentAnalysisRequest.php) — Sentiment request validation

**Background Jobs:**

- [`app/Jobs/ProcessPredictionJob.php`](../code/chapter-23/app/Jobs/ProcessPredictionJob.php) — Async prediction processing
- [`app/Jobs/BatchPredictionJob.php`](../code/chapter-23/app/Jobs/BatchPredictionJob.php) — Batch processing

**Models & Migrations:**

- [`app/Models/Prediction.php`](../code/chapter-23/app/Models/Prediction.php) — Prediction result model
- [`database/migrations/create_predictions_table.php`](../code/chapter-23/database/migrations/create_predictions_table.php) — Predictions table schema

**Service Provider:**

- [`app/Providers/MLServiceProvider.php`](../code/chapter-23/app/Providers/MLServiceProvider.php) — ML services registration
- [`bootstrap/providers.php`](../code/chapter-23/bootstrap/providers.php) — Provider registration

**Frontend:**

- [`resources/views/ml-demo.blade.php`](../code/chapter-23/resources/views/ml-demo.blade.php) — Demo page
- [`resources/views/components/sentiment-widget.blade.php`](../code/chapter-23/resources/views/components/sentiment-widget.blade.php) — Sentiment Blade component
- [`resources/js/components/SentimentAnalyzer.vue`](../code/chapter-23/resources/js/components/SentimentAnalyzer.vue) — Vue sentiment widget

**Tests:**

- [`tests/Feature/MLIntegrationTest.php`](../code/chapter-23/tests/Feature/MLIntegrationTest.php) — Feature tests
- [`tests/Unit/SentimentServiceTest.php`](../code/chapter-23/tests/Unit/SentimentServiceTest.php) — Unit tests

**Configuration:**

- [`config/ml.php`](../code/chapter-23/config/ml.php) — ML configuration file

All files are in [`docs/series/ai-ml-php-developers/code/chapter-23/`](../code/chapter-23/)
:::

## Quick Start

Want to see ML integration in Laravel right now? Here's a 5-minute example showing sentiment analysis in a web route:

```php
# filename: routes/web.php (Quick Demo)
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Simple sentiment analysis endpoint
Route::post('/analyze-sentiment', function () {
    $text = request('text');

    if (!$text || strlen($text) < 10) {
        return response()->json(['error' => 'Text must be at least 10 characters'], 422);
    }

    // Simple sentiment scoring based on positive/negative words
    $positiveWords = ['great', 'excellent', 'amazing', 'love', 'perfect',
                      'wonderful', 'fantastic', 'best', 'awesome', 'brilliant'];
    $negativeWords = ['terrible', 'awful', 'hate', 'worst', 'horrible',
                      'bad', 'poor', 'disappointing', 'useless', 'garbage'];

    $lower = strtolower($text);
    $positiveCount = 0;
    $negativeCount = 0;

    foreach ($positiveWords as $word) {
        $positiveCount += substr_count($lower, $word);
    }

    foreach ($negativeWords as $word) {
        $negativeCount += substr_count($lower, $word);
    }

    $score = ($positiveCount - $negativeCount) / (strlen($text) / 100);

    if ($score > 0.5) {
        $sentiment = 'positive';
        $emoji = '😊';
    } elseif ($score < -0.5) {
        $sentiment = 'negative';
        $emoji = '😞';
    } else {
        $sentiment = 'neutral';
        $emoji = '😐';
    }

    return response()->json([
        'text' => $text,
        'sentiment' => $sentiment,
        'score' => round($score, 2),
        'emoji' => $emoji,
        'positive_words' => $positiveCount,
        'negative_words' => $negativeCount,
    ]);
});

// Simple HTML form to test it
Route::get('/sentiment-demo', function () {
    return <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>Sentiment Analysis Demo</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            body { font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
            textarea { width: 100%; height: 150px; padding: 10px; font-size: 16px; }
            button { background: #3b82f6; color: white; padding: 12px 24px; border: none;
                     font-size: 16px; cursor: pointer; border-radius: 6px; }
            button:hover { background: #2563eb; }
            #result { margin-top: 20px; padding: 20px; border-radius: 8px; display: none; }
            .positive { background: #dcfce7; border: 2px solid #16a34a; }
            .negative { background: #fee2e2; border: 2px solid #dc2626; }
            .neutral { background: #f3f4f6; border: 2px solid #6b7280; }
        </style>
    </head>
    <body>
        <h1>Sentiment Analysis Demo</h1>
        <textarea id="text" placeholder="Enter some text to analyze (e.g., product review)...">This product is absolutely fantastic! I love it and would recommend it to everyone.</textarea>
        <br><br>
        <button onclick="analyze()">Analyze Sentiment</button>

        <div id="result"></div>

        <script>
        async function analyze() {
            const text = document.getElementById('text').value;
            const result = document.getElementById('result');

            result.innerHTML = 'Analyzing...';
            result.style.display = 'block';
            result.className = '';

            try {
                const response = await fetch('/analyze-sentiment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ text })
                });

                const data = await response.json();

                if (data.error) {
                    result.innerHTML = '<strong>Error:</strong> ' + data.error;
                    return;
                }

                result.className = data.sentiment;
                result.innerHTML = `
                    <h2>${data.emoji} Sentiment: ${data.sentiment.toUpperCase()}</h2>
                    <p><strong>Score:</strong> ${data.score}</p>
                    <p><strong>Positive words found:</strong> ${data.positive_words}</p>
                    <p><strong>Negative words found:</strong> ${data.negative_words}</p>
                `;
            } catch (error) {
                result.innerHTML = '<strong>Error:</strong> ' + error.message;
            }
        }
        </script>
    </body>
    </html>
    HTML;
});
```

**Test it:**

```bash
# Start Laravel development server
cd your-laravel-app
php artisan serve

# Open browser to:
# http://localhost:8000/sentiment-demo
```

Try different texts:

- Positive: "This product is absolutely fantastic! I love it and would recommend it to everyone."
- Negative: "Terrible quality, waste of money. Very disappointed with this purchase."
- Neutral: "The product arrived on time and matches the description."

This simple example shows the basic concept, but in this chapter you'll build a production-ready system with proper ML models, caching, background processing, and comprehensive error handling!

## Objectives

By completing this chapter, you will:

- **Understand** the architectural patterns for integrating ML models into web applications, including synchronous vs asynchronous processing, caching strategies, and performance optimization techniques
- **Implement** a service layer architecture that cleanly separates ML logic from application code using Laravel's service container and dependency injection
- **Build** a sentiment analysis API endpoint that processes product reviews in real-time with input validation, caching, and error handling
- **Create** background job queues for processing long-running ML predictions without blocking HTTP requests or degrading user experience
- **Deploy** Redis caching to store prediction results and avoid redundant inference, implementing TTL strategies and cache invalidation patterns
- **Integrate** multiple ML services (sentiment analysis, recommendations, chatbot) into a cohesive application architecture with unified error handling
- **Master** production concerns including rate limiting, monitoring, logging, graceful degradation, timeout management, and health checks for ML services
- **Secure** ML endpoints against injection attacks, DoS attempts, and data leakage using input validation, sanitization, and privacy controls
- **Test** ML integrations using mocks and stubs to avoid expensive inference during development and CI/CD pipelines

## Step 1: Set Up Your Laravel Project

Before integrating ML models, you need a clean Laravel 12 installation with the necessary dependencies.

### Create a Fresh Laravel Project

```bash
# Create a new Laravel project
composer create-project laravel/laravel ml-shop "12.*"
cd ml-shop

# Verify PHP version
php --version
# Output should be PHP 8.4+
```

### Install Required Dependencies

```bash
# ML libraries
composer require php-ai/php-ml

# LLM API client (for chatbot)
composer require openai-php/client

# Redis for caching
composer require predis/predis

# Testing dependencies (optional but recommended)
composer require --dev phpunit/phpunit
```

### Verify Installation

```bash
# Check vendor directory was created
ls -la vendor/ | head -20

# Check php-ml is installed
php -r "require 'vendor/autoload.php'; echo 'Dependencies loaded successfully';"
```

## Step 2: Understanding the Service Layer Architecture

The core architectural pattern for production ML integration is the **Service Layer**. This pattern provides several critical benefits:

- **Encapsulation**: All ML logic lives in isolated service classes
- **Testability**: Services can be mocked for unit tests
- **Reusability**: The same service works in routes, commands, jobs, and schedules
- **Caching**: Predictions are cached at the service level
- **Error Handling**: Centralized error management and fallback strategies
- **Monitoring**: Performance metrics are collected consistently

### The Base ModelService Class

Every ML model in your application should extend the `ModelService` abstract base class. This provides:

```php
# filename: app/Services/ML/ModelService.php
<?php

declare(strict_types=1);

namespace App\Services\ML;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;

abstract class ModelService
{
    protected mixed $model = null;
    protected bool $modelLoaded = false;

    public function __construct(
        protected readonly string $modelName,
    ) {}

    /**
     * Load the ML model (implemented by child classes).
     * This is called once and cached by Laravel's service container.
     */
    abstract protected function loadModel(): mixed;

    /**
     * Make a prediction (implemented by child classes).
     */
    abstract public function predict(mixed $input): mixed;

    /**
     * Get a unique cache key for the given input.
     * Uses MD5 hash to handle complex input types.
     */
    protected function getCacheKey(mixed $input): string
    {
        $prefix = config('ml.cache.prefix', 'ml:');
        $hash = md5(serialize($input));
        return "{$prefix}{$this->modelName}:{$hash}";
    }

    /**
     * Get cached prediction or compute a new one.
     * Implements intelligent caching with hit rate logging.
     */
    protected function cachedPredict(mixed $input, callable $predictor): mixed
    {
        // If caching is disabled, skip cache layer
        if (!config('ml.cache.enabled', true)) {
            $startTime = microtime(true);
            $result = $predictor($input);
            $latency = (int) round((microtime(true) - $startTime) * 1000);
            $this->logPrediction($input, $result, $latency, false);
            return $result;
        }

        $cacheKey = $this->getCacheKey($input);
        $ttl = config('ml.cache.ttl', 3600);

        // Check cache first
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            Log::info("ML cache HIT for {$this->modelName}");
            $this->logPrediction($input, $cached, 0, true);
            return $cached;
        }

        // Cache miss - compute prediction
        Log::info("ML cache MISS for {$this->modelName}");
        $startTime = microtime(true);
        $result = $predictor($input);
        $latency = (int) round((microtime(true) - $startTime) * 1000);

        // Store in cache for future requests
        Cache::put($cacheKey, $result, $ttl);
        $this->logPrediction($input, $result, $latency, false);

        return $result;
    }

    /**
     * Ensure model is loaded before making predictions.
     * The model is loaded only once thanks to lazy loading.
     */
    protected function ensureModelLoaded(): void
    {
        if ($this->modelLoaded) {
            return; // Already loaded
        }

        try {
            $this->model = $this->loadModel();
            $this->modelLoaded = true;
            Log::info("Model loaded: {$this->modelName}");
        } catch (\Exception $e) {
            Log::error("Failed to load model: {$this->modelName}", [
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException(
                "Failed to load {$this->modelName} model: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    /**
     * Log prediction for monitoring and debugging.
     * Tracks latency, cache hits, and errors.
     */
    protected function logPrediction(
        mixed $input,
        mixed $result,
        int $latencyMs,
        bool $fromCache
    ): void {
        Log::channel('ml')->info("Prediction logged", [
            'model' => $this->modelName,
            'input_type' => gettype($input),
            'result_type' => gettype($result),
            'latency_ms' => $latencyMs,
            'from_cache' => $fromCache,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
```

**Key Concepts:**

- **Abstract base class**: Defines the contract all ML services must follow
- **Lazy loading**: Model is only loaded when first prediction is needed
- **Caching layer**: Predictions are cached with configurable TTL
- **Latency tracking**: Performance metrics logged for monitoring
- **Error handling**: Exceptions are caught and logged with context

## Step 3: Implementing the Sentiment Analysis Service

Now let's implement a concrete service that analyzes product review sentiment. This service will integrate with the classifier from Chapter 14.

### The SentimentAnalysisService

```php
# filename: app/Services/ML/SentimentAnalysisService.php
<?php

declare(strict_types=1);

namespace App\Services\ML;

use Illuminate\Support\Facades\Log;

class SentimentAnalysisService extends ModelService
{
    // Sentiment thresholds
    private const POSITIVE_THRESHOLD = 0.6;
    private const NEGATIVE_THRESHOLD = 0.4;

    public function __construct()
    {
        parent::__construct('sentiment-classifier');
    }

    /**
     * Analyze sentiment of given text.
     * Returns ['sentiment' => 'positive'|'negative'|'neutral', 'confidence' => 0.0-1.0, 'details' => [...]]
     */
    public function predict(mixed $input): mixed
    {
        // Validate input
        if (!is_string($input)) {
            throw new \InvalidArgumentException('Input must be a string');
        }

        if (strlen(trim($input)) === 0) {
            throw new \InvalidArgumentException('Text cannot be empty');
        }

        if (strlen($input) > 5000) {
            throw new \InvalidArgumentException('Text cannot exceed 5000 characters');
        }

        // Use cached prediction
        return $this->cachedPredict($input, function ($text) {
            $this->ensureModelLoaded();

            // Simple word-based sentiment scoring
            // In production, you'd load an actual trained classifier
            $tokens = $this->tokenize($text);
            $score = $this->calculateSentimentScore($tokens);
            $confidence = abs($score); // Confidence increases with extreme scores

            if ($score > self::POSITIVE_THRESHOLD) {
                $sentiment = 'positive';
            } elseif ($score < -self::NEGATIVE_THRESHOLD) {
                $sentiment = 'negative';
            } else {
                $sentiment = 'neutral';
            }

            return [
                'sentiment' => $sentiment,
                'confidence' => min($confidence, 1.0),
                'score' => round($score, 2),
                'details' => [
                    'word_count' => count($tokens),
                    'positive_words' => $this->countPositiveWords($text),
                    'negative_words' => $this->countNegativeWords($text),
                ],
            ];
        });
    }

    /**
     * Load the sentiment model (in this case, just initialize resources).
     */
    protected function loadModel(): mixed
    {
        // In a real implementation, this would load a trained model
        // For now, we'll use a simple word-based approach
        return [
            'positive_words' => $this->getPositiveWords(),
            'negative_words' => $this->getNegativeWords(),
        ];
    }

    /**
     * Tokenize text into words.
     */
    private function tokenize(string $text): array
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        return array_filter(explode(' ', $text));
    }

    /**
     * Calculate sentiment score from tokens.
     * Range: -1.0 (negative) to 1.0 (positive)
     */
    private function calculateSentimentScore(array $tokens): float
    {
        $positiveWords = $this->model['positive_words'];
        $negativeWords = $this->model['negative_words'];

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($tokens as $token) {
            if (isset($positiveWords[$token])) {
                $positiveCount++;
            }
            if (isset($negativeWords[$token])) {
                $negativeCount++;
            }
        }

        if (count($tokens) === 0) {
            return 0.0;
        }

        $total = $positiveCount + $negativeCount;
        if ($total === 0) {
            return 0.0;
        }

        return ($positiveCount - $negativeCount) / $total;
    }

    /**
     * Count positive words in text.
     */
    private function countPositiveWords(string $text): int
    {
        $positiveWords = array_keys($this->model['positive_words']);
        $count = 0;
        $lowerText = strtolower($text);

        foreach ($positiveWords as $word) {
            $count += substr_count($lowerText, $word);
        }

        return $count;
    }

    /**
     * Count negative words in text.
     */
    private function countNegativeWords(string $text): int
    {
        $negativeWords = array_keys($this->model['negative_words']);
        $count = 0;
        $lowerText = strtolower($text);

        foreach ($negativeWords as $word) {
            $count += substr_count($lowerText, $word);
        }

        return $count;
    }

    /**
     * Get list of positive sentiment words.
     */
    private function getPositiveWords(): array
    {
        return array_flip([
            'excellent', 'amazing', 'fantastic', 'wonderful', 'brilliant',
            'love', 'perfect', 'great', 'awesome', 'outstanding',
            'beautiful', 'incredible', 'superb', 'magnificent', 'delightful',
            'pleasant', 'enjoyable', 'nice', 'good', 'best',
            'clever', 'impressive', 'remarkable', 'splendid', 'terrific',
        ]);
    }

    /**
     * Get list of negative sentiment words.
     */
    private function getNegativeWords(): array
    {
        return array_flip([
            'terrible', 'awful', 'horrible', 'dreadful', 'pathetic',
            'hate', 'worst', 'bad', 'poor', 'disappointing',
            'useless', 'garbage', 'disgusting', 'ugly', 'nasty',
            'unpleasant', 'painful', 'annoying', 'frustrating', 'broken',
            'mediocre', 'inadequate', 'insufficient', 'waste', 'rubbish',
        ]);
    }
}
```

**Key Implementation Details:**

- **Input validation**: Checks for empty strings and overly long text (DoS prevention)
- **Tokenization**: Converts text to lowercase and removes punctuation
- **Scoring algorithm**: Calculates ratio of positive to negative words
- **Caching**: Results are cached to avoid redundant processing
- **Monitoring**: Logs latency and cache hits for performance tracking

## Step 4: Creating the ML Service Provider

Register all ML services in Laravel's service container using a dedicated provider:

```php
# filename: app/Providers/MLServiceProvider.php
<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\ML\SentimentAnalysisService;
use Illuminate\Support\ServiceProvider;

class MLServiceProvider extends ServiceProvider
{
    /**
     * Register ML services as singletons.
     * This ensures the model is loaded only once per request cycle.
     */
    public function register(): void
    {
        // Sentiment analysis service
        $this->app->singleton(SentimentAnalysisService::class, function ($app) {
            return new SentimentAnalysisService();
        });

        // Additional ML services can be registered here:
        // $this->app->singleton(ChatbotService::class, ...);
        // $this->app->singleton(ProductRecommendationService::class, ...);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configuration, logging channels, etc.
        $this->app->make('config')->set('ml', require config_path('ml.php'));
    }
}
```

Register the provider in `bootstrap/providers.php`:

```php
# filename: bootstrap/providers.php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\MLServiceProvider::class,
];
```

## Step 5: Building the ML API Controller

Create REST endpoints for ML predictions with proper error handling:

```php
# filename: app/Http/Controllers/MLController.php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ML\SentimentAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MLController extends Controller
{
    public function __construct(
        private readonly SentimentAnalysisService $sentimentService,
    ) {}

    /**
     * Analyze sentiment of given text.
     * POST /api/ml/sentiment
     */
    public function sentiment(Request $request): JsonResponse
    {
        try {
            // Validate input
            $validated = $request->validate([
                'text' => 'required|string|min:5|max:5000',
            ], [
                'text.required' => 'Text field is required',
                'text.min' => 'Text must be at least 5 characters',
                'text.max' => 'Text cannot exceed 5000 characters',
            ]);

            // Get prediction
            $result = $this->sentimentService->predict($validated['text']);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Throwable $e) {
            Log::error('Sentiment analysis failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to analyze sentiment',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Health check endpoint for ML services.
     * GET /api/ml/health
     */
    public function health(): JsonResponse
    {
        try {
            // Try to make a test prediction
            $testText = 'This is a test';
            $this->sentimentService->predict($testText);

            return response()->json([
                'status' => 'healthy',
                'services' => [
                    'sentiment' => 'operational',
                ],
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (Throwable $e) {
            Log::error('ML health check failed', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'degraded',
                'services' => [
                    'sentiment' => 'error',
                ],
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String(),
            ], 503);
        }
    }
}
```

## Step 6: Defining Routes

Set up API routes for ML predictions:

```php
# filename: routes/api.php
<?php

declare(strict_types=1);

use App\Http\Controllers\MLController;
use Illuminate\Support\Facades\Route;

Route::prefix('ml')->group(function () {
    // Health check (public)
    Route::get('health', [MLController::class, 'health']);

    // ML predictions (can add rate limiting here)
    Route::post('sentiment', [MLController::class, 'sentiment'])
        ->middleware('throttle:60,1'); // 60 requests per minute
});
```

## Step 7: Configuration File

Create a configuration file for ML settings:

```php
# filename: config/ml.php
<?php

declare(strict_types=1);

return [
    /**
     * Cache settings for ML predictions.
     */
    'cache' => [
        'enabled' => env('ML_CACHE_ENABLED', true),
        'ttl' => (int) env('ML_CACHE_TTL', 3600), // 1 hour
        'prefix' => env('ML_CACHE_PREFIX', 'ml:'),
    ],

    /**
     * Model settings.
     */
    'models' => [
        'sentiment' => [
            'enabled' => env('ML_SENTIMENT_ENABLED', true),
            'timeout' => (int) env('ML_SENTIMENT_TIMEOUT', 30),
        ],
    ],

    /**
     * Logging configuration.
     */
    'logging' => [
        'channel' => env('ML_LOG_CHANNEL', 'ml'),
        'track_latency' => env('ML_TRACK_LATENCY', true),
    ],
];
```

Update `.env` file:

```bash
# filename: .env
ML_CACHE_ENABLED=true
ML_CACHE_TTL=3600
ML_CACHE_PREFIX=ml:
ML_SENTIMENT_ENABLED=true
ML_SENTIMENT_TIMEOUT=30
CACHE_DRIVER=redis
```

## Step 8: Testing Your Implementation

### Manual Testing with cURL

```bash
# Start the Laravel development server
php artisan serve

# In another terminal, test the sentiment endpoint
curl -X POST http://localhost:8000/api/ml/sentiment \
  -H "Content-Type: application/json" \
  -d '{"text":"This product is absolutely amazing! I love it so much!"}'

# Expected output:
# {
#   "success": true,
#   "data": {
#     "sentiment": "positive",
#     "confidence": 1.0,
#     "score": 1.0,
#     "details": {
#       "word_count": 7,
#       "positive_words": 3,
#       "negative_words": 0
#     }
#   }
# }

# Test health endpoint
curl http://localhost:8000/api/ml/health

# Expected output:
# {
#   "status": "healthy",
#   "services": {
#     "sentiment": "operational"
#   },
#   "timestamp": "2024-10-31T12:34:56+00:00"
# }
```

### Automated Tests

```php
# filename: tests/Feature/MLIntegrationTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class MLIntegrationTest extends TestCase
{
    public function test_positive_sentiment_analysis(): void
    {
        $response = $this->postJson('/api/ml/sentiment', [
            'text' => 'This product is absolutely amazing and fantastic!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'sentiment' => 'positive',
                ],
            ]);

        $this->assertGreaterThan(0.5, $response['data']['confidence']);
    }

    public function test_negative_sentiment_analysis(): void
    {
        $response = $this->postJson('/api/ml/sentiment', [
            'text' => 'Terrible product, complete waste of money!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'sentiment' => 'negative',
                ],
            ]);
    }

    public function test_invalid_input_rejected(): void
    {
        $response = $this->postJson('/api/ml/sentiment', [
            'text' => 'short',
        ]);

        $response->assertStatus(422);
    }

    public function test_health_check(): void
    {
        $response = $this->getJson('/api/ml/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'healthy',
            ]);
    }
}
```

Run tests:

```bash
php artisan test --filter=MLIntegrationTest
```

## Production Considerations

### Caching Strategy

ML predictions should be cached aggressively:

- **Query-based caching**: Same text input = same output, cache for hours
- **Cache invalidation**: Clear cache when model is retrained
- **TTL management**: Balance freshness with performance (typically 1-24 hours)

### Error Handling & Fallbacks

```php
try {
    $sentiment = $this->sentimentService->predict($text);
} catch (\RuntimeException $e) {
    // Model failed to load
    Log::error('Sentiment model unavailable', ['error' => $e->getMessage()]);
    
    // Fallback: return neutral sentiment
    return [
        'sentiment' => 'neutral',
        'confidence' => 0.0,
        'fallback' => true,
    ];
}
```

### Rate Limiting

Protect ML endpoints from abuse:

```php
Route::post('sentiment', [MLController::class, 'sentiment'])
    ->middleware('throttle:60,1'); // 60 requests/minute per user
```

### Monitoring

Track ML system health:

```bash
# Check cache hit rates
redis-cli info stats

# Monitor logs
tail -f storage/logs/ml.log

# Track API response times
# Use Laravel Telescope or similar APM tools
```

## Troubleshooting

### Common Issues and Solutions

| Problem | Cause | Solution |
|---------|-------|----------|
| "Model failed to load" | Missing file or permission issue | Verify model file path in `.env` and check file permissions |
| High latency (>5s) | Model not cached | Check Redis connection: `redis-cli ping` should return PONG |
| "Cache driver not working" | Redis not running | Start Redis: `redis-server` or configure Memcached alternative |
| Memory exhaustion | Large model not freed | Implement model caching in service provider (singleton pattern) |
| API returns 422 errors | Invalid input validation | Check that text is 5-5000 characters without special SQL characters |

### Debugging Tips

```bash
# Check if Redis is running
redis-cli ping
# Expected: PONG

# Monitor Redis keys
redis-cli MONITOR

# Check Laravel logs
tail -f storage/logs/laravel.log

# Test cache directly
php artisan tinker
Cache::put('test', 'value');
Cache::get('test');
```

## Exercises

### Exercise 1: Extend to Product Recommendation Service

Create a `ProductRecommendationService` following the same pattern as `SentimentAnalysisService`. Include:
- Load a product similarity matrix (or generate a simple one)
- Accept a product ID and return recommended products
- Implement caching for recommendations
- Add an API endpoint at `POST /api/ml/recommendations`

**Expected output:**
```json
{
  "success": true,
  "data": {
    "product_id": 42,
    "recommendations": [
      {"id": 15, "score": 0.95},
      {"id": 28, "score": 0.87}
    ]
  }
}
```

### Exercise 2: Implement Background Job Processing

Create a `ProcessPredictionJob` that handles long-running predictions asynchronously:

```php
dispatch(new ProcessPredictionJob($review->id, $review->text));
```

This should:
- Accept a review ID and text
- Call the sentiment service
- Store results in database
- Send notification when complete

### Exercise 3: Add Rate Limiting with Custom Responses

Enhance the rate limiting middleware to return informative error messages:

```json
{
  "success": false,
  "error": "Too many requests",
  "retry_after": 45,
  "limit": 60,
  "window": "1 minute"
}
```

### Exercise 4: Implement Model Versioning

Add support for multiple model versions:

```php
$service = $this->sentimentService->useVersion('v2');
$result = $service->predict($text);
```

Include:
- Version selection in configuration
- A/B testing support
- Gradual rollout mechanism

## Key Takeaways

1. **Service Layer Pattern**: Encapsulates ML logic in reusable, testable services
2. **Caching is Critical**: Cache predictions aggressively to ensure sub-100ms response times
3. **Error Handling**: Always have fallback strategies for when models fail
4. **Monitoring**: Track cache hit rates, latency, and error frequencies
5. **Validation**: Validate and sanitize all user input before ML processing
6. **Lazy Loading**: Load models only once per request cycle using service container
7. **Production Ready**: Implement rate limiting, health checks, and graceful degradation

## Next Steps

- Implement the ProductRecommendationService (Exercise 1)
- Set up background job queues for long-running predictions (Exercise 2)
- Deploy to staging environment and monitor real-world performance
- Read Chapter 24 for advanced topics: distributed ML inference, model serving frameworks, and scaling strategies

## Resources

- [Laravel Service Container](https://laravel.com/docs/12.x/container)
- [Laravel Caching](https://laravel.com/docs/12.x/cache)
- [PHP-ML Library](https://php-ml.readthedocs.io/)
- [OpenAI PHP Client](https://github.com/openai-php/client)
- [Redis for PHP](https://github.com/predis/predis)

---

**Code Examples**: All complete, runnable code for this chapter is available in [`docs/series/ai-ml-php-developers/code/chapter-23/`](../code/chapter-23/)
