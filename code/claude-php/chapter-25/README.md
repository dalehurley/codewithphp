# Chapter 25: Admin Panel with AI Features - Code Samples

This directory contains all the code samples from Chapter 25 of the Claude for PHP Developers series, demonstrating how to integrate Claude AI into Laravel Filament admin panels.

## Compatibility

✅ **Laravel 12** - All code samples are compatible with Laravel 12
✅ **Filament 4** - Updated for Filament 4 syntax and features
✅ **Claude-PHP-SDK v0.2** - Uses the latest Claude-PHP-SDK with proper API calls

## Requirements

- PHP 8.4+
- Laravel 12+
- Filament 4+
- Claude-PHP-SDK v0.2 (`composer require claude-php/claude-php-sdk`)
- ANTHROPIC_API_KEY environment variable

## Installation

```bash
# Install dependencies
composer require filament/filament:"^4.0" claude-php/claude-php-sdk

# Install Filament
php artisan filament:install --panels
php artisan make:filament-user

# Set up environment
cp .env.example .env
# Add your ANTHROPIC_API_KEY to .env

# Run migrations
php artisan migrate

# Run the compatibility test
php test-sdk-compatibility.php
```

## Files Overview

### Core AI Integration
- `app/Services/ClaudeService.php` - Main service class for Claude API
- `app/Facades/Claude.php` - Laravel facade for easy access
- `app/Providers/ClaudeServiceProvider.php` - Service provider registration

### Filament Actions
- `app/Filament/Actions/SummarizeContentAction.php` - AI content summarization
- `app/Filament/Actions/GenerateSeoMetaAction.php` - SEO metadata generation
- `app/Filament/Actions/BulkGenerateDescriptionsAction.php` - Bulk operations

### Admin Resources
- `app/Filament/Resources/BlogPostResource.php` - Complete Filament resource with AI features
- `app/Models/BlogPost.php` - Blog post model with AI fields

### Advanced Features
- `app/Filament/Widgets/AiInsightsWidget.php` - AI-powered dashboard insights
- `app/Services/SemanticSearchService.php` - Intelligent search capabilities
- `app/Filament/Pages/ContentQualityReport.php` - Content analysis page

### Background Processing
- `app/Jobs/ProcessAiBulkOperation.php` - Queue job for bulk operations
- `app/Console/Commands/BatchGenerateSummaries.php` - Artisan command
- `app/Services/ResumableBatchProcessor.php` - Resumable batch processing

### Security & Monitoring
- `app/Http/Middleware/CanUseAiFeatures.php` - Authorization middleware
- `app/Services/AiCostTracker.php` - Cost tracking and budget management
- `app/Models/AiAuditLog.php` - Audit logging
- `app/Models/AiBatchOperation.php` - Batch operation tracking

## Testing

Run the compatibility test:

```bash
php test-sdk-compatibility.php
```

This test verifies:
- SDK basic usage works
- Service class structure is correct
- Facade compatibility
- Action class structure
- Configuration validity

## Key Features Demonstrated

### 🤖 AI-Powered Actions
- Content summarization with one click
- SEO metadata generation
- Bulk operations for multiple records

### 📊 Intelligent Insights
- AI-powered dashboard widgets
- Content quality analysis
- Semantic search capabilities

### ⚡ Performance Optimization
- Background job processing
- Caching strategies
- Cost tracking and budget limits

### 🔒 Security & Compliance
- Role-based access control
- Audit logging
- Input validation and sanitization

## API Usage Examples

### Basic Claude Call
```php
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: env('ANTHROPIC_API_KEY'));

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Hello Claude!']
    ]
]);

echo $response->content[0]->text;
```

### Using the Facade
```php
use App\Facades\Claude;

$response = Claude::generate('Summarize this content...', null, [
    'temperature' => 0.3,
    'max_tokens' => 300
]);

echo $response->content[0]->text;
```

### Bulk Operations
```php
use App\Services\ResumableBatchProcessor;

$processor = new ResumableBatchProcessor();
$operation = $processor->processBatch('bulk_summarize', $recordIds, function($id) {
    $post = BlogPost::find($id);
    $summary = Claude::generate("Summarize: {$post->content}");
    $post->update(['summary' => $summary->content[0]->text]);
});
```

## Configuration

Update your `.env` file:

```bash
ANTHROPIC_API_KEY=your_api_key_here
AI_DEFAULT_MODEL=claude-sonnet-4-5-20250929
AI_FAST_MODEL=claude-haiku-4-5-20251001
AI_SMART_MODEL=claude-opus-4-1-20250805
AI_DAILY_BUDGET_LIMIT=100.0
```

## Troubleshooting

### Common Issues

1. **"Class 'App\Facades\Claude' not found"**
   - Register the facade in `config/app.php`
   - Ensure the service provider is loaded

2. **"Action not appearing in Filament table"**
   - Import the action class in your resource
   - Use `ActionClass::make()` in the actions array

3. **API rate limits or cost concerns**
   - Implement caching
   - Use cheaper models for bulk operations
   - Set budget limits

4. **Slow admin panel performance**
   - Use background jobs for heavy operations
   - Implement caching for AI responses
   - Use lazy loading for widgets

## Next Steps

After completing Chapter 25, continue with:
- Chapter 26: Code Review Assistant
- Chapter 27: Documentation Generator
- Chapter 28: Customer Support Bot

## Contributing

Found an issue? Create a GitHub issue or submit a pull request. All code samples should be production-ready and follow PSR-12 standards.