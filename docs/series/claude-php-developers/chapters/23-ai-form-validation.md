---
title: "23: Claude-Powered Form Validation"
description: "Enhance Laravel form validation with AI-powered rules. Implement content quality checks, spam detection, offensive language filtering, and intelligent business logic validation using Claude."
series: "claude-php-developers"
chapter: 23
order: 23
difficulty: "Intermediate"
prerequisites:
  - "Laravel 11+ with validation knowledge"
  - "Understanding of Laravel custom rules"
  - "Completion of Chapter 21"
---

![23: Claude-Powered Form Validation](/images/claude-php/chapter-23-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 23</span>
</div>

# Chapter 23: Claude-Powered Form Validation

## Overview

Traditional form validation checks syntax and format, but it can't understand context, intent, or quality. In this chapter, you'll learn to enhance Laravel's validation system with Claude's intelligence to validate content quality, detect spam, filter offensive language, and enforce complex business rules that traditional regex can't handle.

You'll build custom validation rules that leverage Claude's language understanding to create smarter, more sophisticated validation that improves data quality and user experience while preventing abuse.

**What You'll Learn:**

- Creating custom validation rules with Claude
- Content quality and coherence validation
- Spam and promotional content detection
- Offensive language and toxicity filtering
- Business logic validation with AI
- Contextual validation based on other fields
- Performance optimization and caching
- Fallback strategies for API failures
- Cost-effective validation patterns
- Testing AI-powered validation

**Estimated Time**: 90-120 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Laravel 11+** installed
- ✓ **Claude SDK** installed (`composer require claude-php/claude-php-sdk`)
- ✓ **Understanding of Laravel validation**
- ✓ **Redis** for caching (recommended)
- ✓ **Basic knowledge of custom validation rules**
- ✓ **Anthropic API key** configured in `config/services.php`

## Configuration Setup

Before starting, configure your Anthropic API key in Laravel:

**Add to `config/services.php`:**

```php
# filename: config/services.php
return [
    // ... existing config

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
    ],
];
```

**Add to `.env`:**

```bash
ANTHROPIC_API_KEY=your_api_key_here
```

## What You'll Build

By the end of this chapter, you will have created:

- **ContentQuality** validation rule that assesses text quality using AI
- **NotSpam** rule that detects spam and promotional content
- **NoOffensiveLanguage** rule that filters inappropriate content
- **ValidBusinessDescription** rule for business logic validation
- **AppropriateEmailContent** rule for contextual validation
- **AiValidationService** class for reusable validation logic
- **Form Request classes** with AI-powered validation rules
- **Async and batch validation** services for performance optimization
- **Middleware** for automatic content validation
- **Comprehensive tests** for AI validation rules

You'll have a complete understanding of how to enhance Laravel's validation system with Claude's intelligence to create smarter, more sophisticated validation that improves data quality and user experience.

## Objectives

By completing this chapter, you will:

- **Understand** how to create custom Laravel validation rules with Claude
- **Implement** content quality assessment using AI
- **Build** spam detection and offensive language filtering systems
- **Create** contextual validation rules based on business logic
- **Optimize** validation performance with caching and async processing
- **Design** batch validation systems for multiple items
- **Test** AI-powered validation with proper mocking strategies
- **Apply** graceful degradation patterns for API failures

## Custom Validation Rules

### Content Quality Rule

```php
<?php
# filename: app/Rules/ContentQuality.php
declare(strict_types=1);

namespace App\Rules;

use ClaudePhp\ClaudePhp;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ContentQuality implements ValidationRule
{
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct(
        private readonly int $minQualityScore = 7,
        private readonly array $criteria = [
            'clarity',
            'coherence',
            'relevance',
            'grammar',
        ]
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || strlen($value) < 10) {
            return; // Skip AI validation for very short content
        }

        try {
            $score = $this->assessQuality($value);

            if ($score < $this->minQualityScore) {
                $fail("The {$attribute} does not meet minimum quality standards. Please provide more detailed, clear, and relevant content.");
            }
        } catch (\Exception $e) {
            // Log error but don't fail validation on API errors
            Log::warning('ContentQuality validation failed', [
                'attribute' => $attribute,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function assessQuality(string $content): int
    {
        $cacheKey = 'quality:' . md5($content);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($content) {
            $criteria = implode(', ', $this->criteria);

            $prompt = <<<PROMPT
Assess the quality of the following text based on these criteria: {$criteria}.

Rate the text from 1-10 where:
- 1-3: Poor quality (incoherent, unclear, irrelevant)
- 4-6: Below average quality (some issues with clarity or relevance)
- 7-8: Good quality (clear, coherent, relevant)
- 9-10: Excellent quality (very clear, well-written, highly relevant)

Text:
{$content}

Return ONLY a single number from 1-10, nothing else.
PROMPT;

            // Create Claude client
            $client = new ClaudePhp(apiKey: config('services.anthropic.api_key'));

            $response = $client->messages()->create(
                'model' => 'claude-haiku-4-5-20251001',
                'max_tokens' => 10,
                'temperature' => 0.0,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            );

            $text = $response->content[0]->text ?? '';
            preg_match('/\d+/', $text, $matches);

            return isset($matches[0]) ? (int) $matches[0] : 5;
        });
    }
}
```

### Spam Detection Rule

```php
<?php
# filename: app/Rules/NotSpam.php
declare(strict_types=1);

namespace App\Rules;

use ClaudePhp\ClaudePhp;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Cache;

class NotSpam implements ValidationRule
{
    private const CACHE_TTL = 7200; // 2 hours

    public function __construct(
        private readonly float $threshold = 0.7
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || strlen($value) < 20) {
            return;
        }

        try {
            $isSpam = $this->detectSpam($value);

            if ($isSpam) {
                $fail("The {$attribute} appears to be spam or promotional content.");
            }
        } catch (\Exception $e) {
            // Fail silently on API errors
            \Log::warning('Spam detection failed', ['error' => $e->getMessage()]);
        }
    }

    private function detectSpam(string $content): bool
    {
        $cacheKey = 'spam:' . md5($content);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($content) {
            $prompt = <<<PROMPT
Analyze this text for spam characteristics:
- Excessive promotional language
- Repeated links or contact information
- Irrelevant commercial content
- Suspicious patterns or requests
- Cryptocurrency/get-rich-quick schemes

Text:
{$content}

Is this spam? Respond with ONLY 'YES' or 'NO'.
PROMPT;

            $client = new ClaudePhp(apiKey: config('services.anthropic.api_key'));

            $response = $client->messages()->create(
                'model' => 'claude-haiku-4-5-20251001',
                'max_tokens' => 10,
                'temperature' => 0.0,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            );

            $text = $response->content[0]->text ?? '';

            return stripos($text, 'YES') !== false;
        });
    }
}
```

### Offensive Language Rule

```php
<?php
# filename: app/Rules/NoOffensiveLanguage.php
declare(strict_types=1);

namespace App\Rules;

use ClaudePhp\ClaudePhp;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Cache;

class NoOffensiveLanguage implements ValidationRule
{
    public function __construct(
        private readonly array $categories = [
            'profanity',
            'hate_speech',
            'harassment',
            'threats',
            'sexual_content'
        ]
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        try {
            $violations = $this->detectOffensiveContent($value);

            if (!empty($violations)) {
                $categories = implode(', ', $violations);
                $fail("The {$attribute} contains inappropriate content ({$categories}). Please revise your input.");
            }
        } catch (\Exception $e) {
            \Log::warning('Offensive language detection failed', ['error' => $e->getMessage()]);
        }
    }

    private function detectOffensiveContent(string $content): array
    {
        $cacheKey = 'offensive:' . md5($content);

        return Cache::remember($cacheKey, 3600, function () use ($content) {
            $categories = implode(', ', $this->categories);

            $prompt = <<<PROMPT
Analyze this text for offensive content in these categories: {$categories}.

Text:
{$content}

List any categories that apply, separated by commas. If the text is clean, respond with 'NONE'.
Examples:
- "profanity, harassment"
- "NONE"

Response:
PROMPT;

            $client = new ClaudePhp(apiKey: config('services.anthropic.api_key'));

            $response = $client->messages()->create(
                'model' => 'claude-haiku-4-5-20251001',
                'max_tokens' => 50,
                'temperature' => 0.0,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            );

            $text = $response->content[0]->text ?? 'NONE';

            if (stripos($text, 'NONE') !== false) {
                return [];
            }

            // Parse comma-separated violations
            $violations = array_map('trim', explode(',', strtolower($text)));

            return array_intersect($violations, $this->categories);
        });
    }
}
```

### Business Logic Validation

```php
<?php
# filename: app/Rules/ValidBusinessDescription.php
declare(strict_types=1);

namespace App\Rules;

use ClaudePhp\ClaudePhp;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBusinessDescription implements ValidationRule
{
    public function __construct(
        private readonly array $requiredElements = [
            'what the business does',
            'target market or customers',
            'key products or services'
        ]
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || strlen($value) < 50) {
            $fail("The {$attribute} must be at least 50 characters and describe your business comprehensively.");
            return;
        }

        try {
            $missingElements = $this->validateDescription($value);

            if (!empty($missingElements)) {
                $elements = implode(', ', $missingElements);
                $fail("The {$attribute} is missing important information: {$elements}. Please provide a more complete description.");
            }
        } catch (\Exception $e) {
            \Log::warning('Business description validation failed', ['error' => $e->getMessage()]);
        }
    }

    private function validateDescription(string $description): array
    {
        $elements = implode("\n- ", $this->requiredElements);

        $prompt = <<<PROMPT
Analyze this business description and determine which of these required elements are missing:
- {$elements}

Business Description:
{$description}

List ONLY the missing elements, one per line. If all elements are present, respond with 'COMPLETE'.
PROMPT;

        $client = app(ClaudePhp::class);

        $response = $client->messages()->create(
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 100,
            'temperature' => 0.0,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        );

        $text = $response->content[0]->text ?? 'COMPLETE';

        if (stripos($text, 'COMPLETE') !== false) {
            return [];
        }

        // Parse missing elements
        $lines = explode("\n", trim($text));
        $missing = [];

        foreach ($lines as $line) {
            $line = trim($line, "- \t\n\r\0\x0B");
            if (!empty($line) && $line !== 'COMPLETE') {
                $missing[] = $line;
            }
        }

        return $missing;
    }
}
```

## Contextual Validation

### Email Content Appropriateness

```php
<?php
# filename: app/Rules/AppropriateEmailContent.php
declare(strict_types=1);

namespace App\Rules;

use ClaudePhp\ClaudePhp;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AppropriateEmailContent implements ValidationRule
{
    public function __construct(
        private readonly string $context,
        private readonly string $recipientType = 'customer'
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        try {
            $issues = $this->analyzeEmailAppropriateness($value);

            if (!empty($issues)) {
                $fail("The {$attribute} has tone or content issues: " . implode('; ', $issues));
            }
        } catch (\Exception $e) {
            \Log::warning('Email content validation failed', ['error' => $e->getMessage()]);
        }
    }

    private function analyzeEmailAppropriateness(string $content): array
    {
        $prompt = <<<PROMPT
Analyze this email content for appropriateness in a {$this->recipientType} communication context: {$this->context}

Check for:
1. Professional tone
2. Clear and respectful language
3. Appropriate level of formality
4. No inappropriate requests or assumptions
5. Grammar and clarity

Email Content:
{$content}

List any issues found, one per line. If the email is appropriate, respond with 'APPROPRIATE'.
PROMPT;

        $client = app(ClaudePhp::class);

        $response = $client->messages()->create(
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 200,
            'temperature' => 0.0,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        );

        $text = $response->content[0]->text ?? 'APPROPRIATE';

        if (stripos($text, 'APPROPRIATE') !== false) {
            return [];
        }

        return array_filter(explode("\n", trim($text)));
    }
}
```

## Validation Service

```php
<?php
# filename: app/Services/AiValidationService.php
declare(strict_types=1);

namespace App\Services;

use ClaudePhp\ClaudePhp;
use Illuminate\Support\Facades\Cache;

class AiValidationService
{
    private function getClient(): ClaudePhp
    {
        return new ClaudePhp(apiKey: config('services.anthropic.api_key'));
    }

    /**
     * Validate content quality score (1-10)
     */
    public function getQualityScore(string $content, array $criteria = []): int
    {
        $cacheKey = $this->getCacheKey('quality', $content, $criteria);

        return Cache::remember($cacheKey, 3600, function () use ($content, $criteria) {
            $criteriaText = empty($criteria)
                ? 'clarity, coherence, and relevance'
                : implode(', ', $criteria);

            $prompt = "Rate this text from 1-10 based on {$criteriaText}. Return only the number.\n\n{$content}";

            $response = $this->getClient()->messages()->create(
                'model' => 'claude-haiku-4-5-20251001',
                'max_tokens' => 10,
                'temperature' => 0.0,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            );

            $text = $response->content[0]->text ?? '';
            preg_match('/\d+/', $text, $matches);

            return isset($matches[0]) ? min(10, max(1, (int) $matches[0])) : 5;
        });
    }

    /**
     * Detect if content is spam
     */
    public function isSpam(string $content): bool
    {
        $cacheKey = $this->getCacheKey('spam', $content);

        return Cache::remember($cacheKey, 7200, function () use ($content) {
            $prompt = <<<PROMPT
Is this spam or promotional content? Consider:
- Excessive links or contact info
- Repeated marketing language
- Suspicious requests
- Crypto/MLM schemes

Text: {$content}

Answer: YES or NO
PROMPT;

            $response = $this->getClient()->messages()->create(
                'model' => 'claude-haiku-4-5-20251001',
                'max_tokens' => 5,
                'temperature' => 0.0,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            );

            $text = $response->content[0]->text ?? '';

            return stripos($text, 'YES') !== false;
        });
    }

    /**
     * Detect offensive language categories
     */
    public function detectOffensiveContent(string $content): array
    {
        $cacheKey = $this->getCacheKey('offensive', $content);

        return Cache::remember($cacheKey, 3600, function () use ($content) {
            $prompt = <<<PROMPT
Detect offensive content categories in this text:
- profanity
- hate_speech
- harassment
- threats
- sexual_content

Text: {$content}

List detected categories comma-separated, or 'NONE' if clean.
PROMPT;

            $response = $this->getClient()->messages()->create(
                'model' => 'claude-haiku-4-5-20251001',
                'max_tokens' => 50,
                'temperature' => 0.0,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            );

            $text = $response->content[0]->text ?? 'NONE';

            if (stripos($text, 'NONE') !== false) {
                return [];
            }

            return array_map('trim', explode(',', strtolower($text)));
        });
    }

    /**
     * Validate against custom business rules
     */
    public function validateBusinessRules(string $content, array $rules): array
    {
        $rulesText = implode("\n", array_map(fn($r) => "- {$r}", $rules));

        $prompt = <<<PROMPT
Check if this content meets these business rules:
{$rulesText}

Content: {$content}

List any violated rules, or 'VALID' if all rules are met.
PROMPT;

        $response = $this->client->messages()->create(
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 200,
            'temperature' => 0.0,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        );

        $text = $response->content[0]->text ?? 'VALID';

        if (stripos($text, 'VALID') !== false) {
            return [];
        }

        return array_filter(explode("\n", trim($text)));
    }

    /**
     * Generate cache key
     */
    private function getCacheKey(string $type, string $content, array $context = []): string
    {
        $hash = md5($content . json_encode($context));
        return "ai_validation:{$type}:{$hash}";
    }
}
```

## Form Request with AI Validation

```php
<?php
# filename: app/Http/Requests/CreateBlogPostRequest.php
declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\ContentQuality;
use App\Rules\NoOffensiveLanguage;
use App\Rules\NotSpam;
use Illuminate\Foundation\Http\FormRequest;

class CreateBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:200',
                new ContentQuality(minQualityScore: 6),
                new NoOffensiveLanguage(),
            ],
            'content' => [
                'required',
                'string',
                'min:100',
                new ContentQuality(minQualityScore: 7, criteria: [
                    'clarity',
                    'coherence',
                    'grammar',
                    'structure',
                ]),
                new NotSpam(),
                new NoOffensiveLanguage(),
            ],
            'excerpt' => [
                'required',
                'string',
                'max:500',
                new ContentQuality(minQualityScore: 6),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for your blog post.',
            'content.required' => 'Blog post content is required.',
            'content.min' => 'Your blog post must be at least 100 characters long.',
        ];
    }
}
```

### Product Review Validation

```php
<?php
# filename: app/Http/Requests/CreateProductReviewRequest.php
declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\ContentQuality;
use App\Rules\NoOffensiveLanguage;
use App\Rules\NotSpam;
use Illuminate\Foundation\Http\FormRequest;

class CreateProductReviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'title' => [
                'required',
                'string',
                'max:100',
                new NoOffensiveLanguage(),
            ],
            'review' => [
                'required',
                'string',
                'min:50',
                'max:2000',
                new ContentQuality(minQualityScore: 6, criteria: [
                    'clarity',
                    'helpfulness',
                    'specificity',
                ]),
                new NotSpam(),
                new NoOffensiveLanguage(),
            ],
        ];
    }
}
```

## Performance Optimization

### Async Validation

```php
<?php
# filename: app/Services/AsyncAiValidation.php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ValidateContentJob;

class AsyncAiValidation
{
    /**
     * Queue validation for async processing
     */
    public function queueValidation(
        string $content,
        string $type,
        array $options = []
    ): string {
        $validationId = uniqid('val_', true);

        Queue::push(new ValidateContentJob(
            $validationId,
            $content,
            $type,
            $options
        ));

        return $validationId;
    }

    /**
     * Check validation result
     */
    public function getResult(string $validationId): ?array
    {
        return Cache::get("validation_result:{$validationId}");
    }
}
```

### Batch Validation

```php
<?php
# filename: app/Services/BatchValidationService.php
declare(strict_types=1);

namespace App\Services;

use ClaudePhp\ClaudePhp;
use Illuminate\Support\Collection;

class BatchValidationService
{
    public function __construct(
        private readonly ClaudePhp $client
    ) {}

    /**
     * Validate multiple items in a single request
     */
    public function validateBatch(array $items, string $validationType): array
    {
        $itemsList = collect($items)
            ->map(fn($item, $index) => ($index + 1) . ". {$item}")
            ->implode("\n");

        $prompt = $this->getBatchPrompt($validationType, $itemsList);

        $response = $this->getClient()->messages()->create(
            'model' => 'claude-haiku-4-5-20251001',
            'temperature' => 0.0,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        );

        return $this->parseBatchResults($response->content[0]->text ?? '', count($items));
    }

    private function getBatchPrompt(string $type, string $itemsList): string
    {
        return match($type) {
            'spam' => <<<PROMPT
Analyze each item for spam. Format: "number. YES/NO"

Items:
{$itemsList}
PROMPT,
            'quality' => <<<PROMPT
Rate each item 1-10 for quality. Format: "number. score"

Items:
{$itemsList}
PROMPT,
            default => throw new \InvalidArgumentException("Unknown validation type: {$type}"),
        };
    }

    private function parseBatchResults(string $response, int $expectedCount): array
    {
        $lines = explode("\n", trim($response));
        $results = [];

        foreach ($lines as $line) {
            if (preg_match('/^(\d+)\.\s*(.+)$/', trim($line), $matches)) {
                $index = (int) $matches[1] - 1;
                $results[$index] = trim($matches[2]);
            }
        }

        return $results;
    }
}
```

## Middleware for Auto-Validation

```php
<?php
# filename: app/Http/Middleware/ValidateUserContent.php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AiValidationService;
use Closure;
use Illuminate\Http\Request;

class ValidateUserContent
{
    public function __construct(
        private readonly AiValidationService $validator
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $contentFields = ['content', 'message', 'comment', 'description'];

        foreach ($contentFields as $field) {
            if ($request->has($field)) {
                $content = $request->input($field);

                // Check for spam
                if ($this->validator->isSpam($content)) {
                    return response()->json([
                        'error' => 'Your content was flagged as spam.',
                    ], 422);
                }

                // Check for offensive content
                $offensive = $this->validator->detectOffensiveContent($content);
                if (!empty($offensive)) {
                    return response()->json([
                        'error' => 'Your content contains inappropriate language.',
                        'categories' => $offensive,
                    ], 422);
                }
            }
        }

        return $next($request);
    }
}
```

## Testing AI Validation

```php
<?php
# filename: tests/Feature/AiValidationTest.php
declare(strict_types=1);

namespace Tests\Feature;

use App\Rules\ContentQuality;
use App\Rules\NotSpam;
use App\Rules\NoOffensiveLanguage;
use ClaudePhp\ClaudePhp;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Tests\TestCase;

class AiValidationTest extends TestCase
{
    private $mockClient;
    private $mockMessages;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock ClaudePhp globally for all tests
        $this->mockClient = Mockery::mock('overload:ClaudePhp\ClaudePhp');
        $this->mockMessages = Mockery::mock();
        $this->mockClient->shouldReceive('messages')->andReturn($this->mockMessages);
    }

    public function test_content_quality_passes_for_good_content(): void
    {
        $mockResponse = (object) [
            'content' => [
                ['text' => '8']
            ]
        ];

        $this->mockMessages->shouldReceive('create')
            ->once()
            ->andReturn($mockResponse);

        $validator = Validator::make(
            ['content' => 'This is a well-written, clear, and coherent piece of content.'],
            ['content' => new ContentQuality(minQualityScore: 7)]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_spam_detection_fails_for_spam_content(): void
    {
        $mockResponse = (object) [
            'content' => [
                ['text' => 'YES']
            ]
        ];

        $this->mockMessages->shouldReceive('create')
            ->once()
            ->andReturn($mockResponse);

        $validator = Validator::make(
            ['content' => 'BUY NOW! LIMITED TIME OFFER! Click here!!!'],
            ['content' => new NotSpam()]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_offensive_language_detection(): void
    {
        $mockResponse = (object) [
            'content' => [
                ['text' => 'profanity, hate_speech']
            ]
        ];

        $this->mockMessages->shouldReceive('create')
            ->once()
            ->andReturn($mockResponse);

        $validator = Validator::make(
            ['content' => 'Offensive content here'],
            ['content' => new NoOffensiveLanguage()]
        );

        $this->assertTrue($validator->fails());
    }
}
```

## Exercises

### Exercise 1: Sentiment Analysis Rule

Create a rule that validates content sentiment:

```php
<?php
class RequiredSentiment implements ValidationRule
{
    public function __construct(
        private readonly string $requiredSentiment // 'positive', 'neutral', 'negative'
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // TODO: Detect sentiment with Claude
        // TODO: Fail if sentiment doesn't match requirement
    }
}
```

### Exercise 2: Readability Validator

Create a rule for readability level:

```php
<?php
class ReadabilityLevel implements ValidationRule
{
    public function __construct(
        private readonly string $targetAudience // 'general', 'technical', 'academic'
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // TODO: Assess readability with Claude
        // TODO: Validate appropriate for audience
    }
}
```

### Exercise 3: Fact-Checking Rule

Create a rule that validates factual claims:

```php
<?php
class FactualAccuracy implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // TODO: Identify factual claims
        // TODO: Flag unsupported or questionable claims
        // TODO: Suggest verification needed
    }
}
```

<details>
<summary>Solution Hints</summary>

**Exercise 1**: Use Claude with prompt asking for sentiment classification. Return 'positive', 'neutral', or 'negative'. Compare with required sentiment. Cache results.

**Exercise 2**: Prompt Claude to assess readability using Flesch-Kincaid or similar metrics. Map to audience appropriateness. Provide helpful feedback.

**Exercise 3**: Use Claude to identify factual claims in text. Flag claims without sources or that seem dubious. Don't verify facts (beyond Claude's scope), but suggest verification.

</details>

## Troubleshooting

**Validation is too slow?**

- Implement caching with longer TTL for common patterns
- Use Haiku model for faster responses
- Consider async validation for non-critical fields
- Batch multiple validations together

**False positives in spam detection?**

- Adjust detection threshold
- Provide more context in prompt
- Allow users to appeal/report false positives
- Combine AI with traditional heuristics

**API costs too high?**

- Cache aggressively (same content = same validation)
- Only validate longer content (skip short strings)
- Use batch validation when possible
- Implement rate limiting per user

**Validation failing on API errors?**

- Always catch exceptions in validation rules
- Implement fallback to basic validation
- Log errors for monitoring
- Don't block user on temporary API issues

## Further Reading

- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community SDK on GitHub
- **[Claude-PHP-SDK Packagist](https://packagist.org/packages/claude-php/claude-3-api)** — Package details and versions
- **[Anthropic API Documentation](https://docs.anthropic.com)** — Complete API reference and guides

## Wrap-up

Congratulations! You've enhanced Laravel's validation system with AI-powered intelligence. Here's what you've accomplished:

- ✓ **Custom Validation Rules**: Created reusable validation rules that leverage Claude's language understanding
- ✓ **Content Quality Assessment**: Built a system that evaluates text quality based on clarity, coherence, and relevance
- ✓ **Spam Detection**: Implemented intelligent spam detection that goes beyond simple keyword matching
- ✓ **Offensive Language Filtering**: Created a multi-category content moderation system
- ✓ **Business Logic Validation**: Built contextual validation rules that understand domain-specific requirements
- ✓ **Performance Optimization**: Implemented caching, async processing, and batch validation for efficiency
- ✓ **Graceful Degradation**: Designed validation rules that handle API failures without blocking users
- ✓ **Comprehensive Testing**: Created test suites with proper mocking strategies for AI validation

You now have the knowledge and tools to build sophisticated validation systems that improve data quality, enhance user experience, and prevent abuse—all while maintaining performance and reliability.

## Further Reading

- [Laravel Validation Documentation](https://laravel.com/docs/validation) — Official guide to Laravel's validation system
- [Laravel Custom Validation Rules](https://laravel.com/docs/validation#custom-validation-rules) — Creating custom validation rules
- [Anthropic Claude API Documentation](https://docs.claude.com) — Official Claude API reference and best practices
- [Redis Caching Strategies](https://redis.io/docs/manual/patterns/) — Performance optimization patterns for caching
- [Laravel Queues](https://laravel.com/docs/queues) — Asynchronous job processing for validation
- [Chapter 21: Laravel Integration Patterns](/series/claude-php-developers/chapters/21-laravel-integration) — Foundation for Laravel integration
- [Chapter 18: Caching Strategies](/series/claude-php-developers/chapters/18-caching-strategies) — Advanced caching techniques
- [Chapter 24: Content Generation API](/series/claude-php-developers/chapters/24-content-generation-api) — Next chapter on building content APIs

## Key Takeaways

- ✓ **AI Validation** catches issues traditional regex can't detect
- ✓ **Content Quality** improves data quality and user experience
- ✓ **Spam Detection** reduces abuse without complex rules
- ✓ **Offensive Language** filtering protects community standards
- ✓ **Caching** is essential for performance and cost
- ✓ **Graceful Degradation** handles API failures smoothly
- ✓ **Contextual Rules** validate based on business logic
- ✓ **Testing** requires mocking Claude responses

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="23"
  label="You've mastered AI-powered form validation!"
/>

---

Continue to [Chapter 24: Content Generation API](/series/claude-php-developers/chapters/24-content-generation-api) to build a RESTful API for content generation.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 23 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-23)**

Clone and run locally:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-23
composer install
cp .env.example .env
# Add your ANTHROPIC_API_KEY to .env
php artisan serve
```
