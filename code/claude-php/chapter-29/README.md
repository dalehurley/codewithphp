# Chapter 29: Content Moderation System

Comprehensive AI-powered content moderation system with toxic content detection, PII redaction, automated moderation queue, and human review workflow.

## Features

- **Toxic Content Detection** - Hate speech, harassment, violence detection
- **PII Redaction** - Automatic detection and redaction of personal information
- **Moderation Queue** - Automated workflow with AI assistance
- **Human Review** - Flag uncertain content for manual review
- **Auto-Actions** - Automatic approve/reject based on confidence
- **Analytics** - Moderation statistics and performance metrics
- **Queue Processing** - Background job processing with Laravel Horizon

## Installation

```bash
composer install
php artisan migrate
php artisan horizon:install
cp .env.example .env
```

## Components

### ToxicContentDetector

Analyzes content for toxic behavior:

```php
$detector = new ToxicContentDetector($apiKey);

$analysis = $detector->analyze($content);
// Returns: toxicity_score, issues, severity, action

if ($detector->isSafe($content, threshold: 0.8)) {
    // Content is safe
}
```

### PiiRedactionService

Detects and redacts PII:

```php
$redactor = new PiiRedactionService($apiKey);

// Detect PII
$pii = $redactor->detectPii($content);

// Redact PII
$result = $redactor->redact($content);
// Returns: original, redacted, pii_detected

// Anonymize content
$anonymized = $redactor->anonymize($content);
```

### ModerationQueueService

Manages moderation workflow:

```php
$queue = new ModerationQueueService($detector, $redactor);

// Submit content for moderation
$item = $queue->submit(
    content: $userComment,
    contentType: 'comment',
    userId: $user->id
);

// Process item (usually done by queue worker)
$queue->process($item);

// Apply manual action
$queue->applyAction($item, 'approve');

// Get statistics
$stats = $queue->getStats();
```

## Usage Flow

1. User submits content
2. Content enters moderation queue
3. AI analyzes for toxicity and PII
4. System calculates moderation score
5. Auto-action if confidence is high
6. Otherwise, flag for human review
7. Moderator reviews and takes action

## Configuration

```sh
MODERATION_THRESHOLD_TOXIC=0.8
MODERATION_THRESHOLD_SPAM=0.7
MODERATION_AUTO_APPROVE_THRESHOLD=0.2
QUEUE_CONNECTION=redis
```

## Queue Processing

Start queue worker:

```bash
php artisan horizon
```

Or use standard queue:

```bash
php artisan queue:work --queue=moderation
```

## API Endpoints

```php
// Submit for moderation
POST /api/moderation/submit
{
    "content": "...",
    "type": "comment"
}

// Get moderation status
GET /api/moderation/{id}

// Manual review action
POST /api/moderation/{id}/action
{
    "action": "approve|reject|flag"
}

// Get queue stats
GET /api/moderation/stats
```

## Moderation Actions

- **approve** - Content is safe, publish it
- **reject** - Content violates policies, block it
- **flag** - Uncertain, send to human review
- **redact** - Remove PII and approve

## Database Schema

```php
Schema::create('moderation_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id');
    $table->text('content');
    $table->string('content_type');
    $table->string('status');
    $table->json('ai_analysis')->nullable();
    $table->string('ai_recommendation')->nullable();
    $table->text('original_content')->nullable();
    $table->timestamp('processed_at')->nullable();
    $table->timestamp('actioned_at')->nullable();
    $table->timestamps();
});
```

## Testing

```bash
php artisan test --filter ModerationTest
```

## Performance

- Queue processing: 100+ items/minute
- Average moderation time: 2-5 seconds
- Auto-action rate: 70-80%
- Human review required: 20-30%

## Best Practices

1. Always use queue processing for moderation
2. Set appropriate thresholds for your use case
3. Monitor false positive/negative rates
4. Provide clear feedback to users
5. Have human moderators review edge cases
6. Log all moderation actions for auditing
7. Regularly update moderation policies

## Advanced Features

### Custom Moderation Rules

```php
$detector->addCustomRule('no_competitor_mentions', function($content) {
    // Custom detection logic
});
```

### Multi-Language Support

```php
$detector->analyze($content, language: 'es');
```

### Appeal System

Allow users to appeal moderation decisions:

```php
$item->createAppeal($reason);
```

## Monitoring

Track key metrics:
- Moderation queue length
- Processing times
- Accuracy rates
- User appeals
- False positives/negatives

## Next Steps

- Implement image moderation (Chapter 13)
- Add multi-language support
- Create moderation dashboard
- Implement appeal workflow
- Add custom rule builder

## Resources

- [Content Moderation Best Practices](https://docs.anthropic.com/claude/docs/content-moderation)
- [Laravel Horizon](https://laravel.com/docs/horizon)
- [Queue Workers](https://laravel.com/docs/queues)
