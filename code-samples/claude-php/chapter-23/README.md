# Chapter 23: AI Form Validation

Advanced form validation using Claude AI to validate content quality, detect spam, analyze sentiment, and ensure professional submissions.

## Features

- **Content Quality** validation
- **Spam Detection** with configurable threshold
- **Sentiment Analysis** validation
- **Professional Email** verification
- **Realistic Name** checking
- **Custom Validation** rules
- **Caching** for performance

## Installation

```bash
composer install
cp .env.example .env
```

## Usage

### Basic Validation

```php
use App\Rules\ClaudeValidation;

$request->validate([
    'email' => [
        'required',
        'email',
        new ClaudeValidation('professional_email'),
    ],
]);
```

### Content Quality

```php
use App\Rules\ContentQuality;

$request->validate([
    'message' => [
        'required',
        new ContentQuality(minQualityScore: 7),
    ],
]);
```

### Spam Detection

```php
use App\Rules\SpamDetection;

$request->validate([
    'comment' => [
        'required',
        new SpamDetection(threshold: 0.7),
    ],
]);
```

### Custom Validation

```php
new ClaudeValidation(
    'custom',
    'Is this a valid business address? {value}. Respond with valid or invalid.'
)
```

## Available Rules

- `ClaudeValidation` - General AI validation
- `ContentQuality` - Quality score validation
- `SpamDetection` - Spam probability detection
- `PositiveSentiment` - Sentiment validation

## Examples

See `examples/form-validation-example.php` for complete implementations.

## Performance

All validations are cached by default (1 hour TTL). Configure in `.env`:

```
VALIDATION_CACHE_TTL=3600
```

## Next Steps

- Content generation API (Chapter 24)
- Admin moderation panel (Chapter 25)
- Advanced content moderation (Chapter 29)
