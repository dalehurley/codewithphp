---
title: "25: Admin Panel with AI Features"
description: "Transform Laravel admin panels with AI superpowers. Integrate Claude with Filament PHP for intelligent content summarization, smart search, bulk operations, data cleanup, and automated insights."
series: "claude-php-developers"
chapter: 25
order: 25
difficulty: "Advanced"
prerequisites:
  - "Laravel 11+ with Filament 3"
  - "Understanding of Filament resources"
  - "Completion of Chapter 21"
---

![25: Admin Panel with AI Features](/images/claude-php/chapter-25-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 25</span>
</div>

# Chapter 25: Admin Panel with AI Features

## Overview

Admin panels are powerful tools for managing applications, but they can be even more effective with AI integration. In this chapter, you'll enhance a Filament admin panel with Claude-powered features: automated content summarization, intelligent search, bulk content generation, data quality analysis, and AI-assisted decision making.

You'll build custom Filament actions, widgets, and pages that leverage Claude to save time, improve data quality, and provide intelligent insights that would be impossible with traditional tools.

**What You'll Learn:**
- Integrating Claude with Filament PHP
- Custom actions for AI operations
- Bulk content generation and updates
- Intelligent semantic search
- Automated content summarization
- Data quality analysis and cleanup
- AI-powered form helpers
- Dashboard widgets with insights
- Audit log summarization
- Trend analysis and predictions
- Performance optimization for admin operations

**Estimated Time**: 120-150 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Laravel 11+** with Filament 3 installed
- ✓ **Claude service** from Chapter 21
- ✓ **Database** with sample data
- ✓ **Filament admin panel** configured
- ✓ **Understanding of Filament resources and actions**

## Setup Filament

If you haven't installed Filament yet:

```bash
composer require filament/filament:"^3.0"
php artisan filament:install --panels
php artisan make:filament-user
```

## Custom Filament Actions

### Summarize Content Action

```php
<?php
# filename: app/Filament/Actions/SummarizeContentAction.php
declare(strict_types=1);

namespace App\Filament\Actions;

use App\Facades\Claude;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class SummarizeContentAction
{
    public static function make(): Action
    {
        return Action::make('summarize')
            ->label('AI Summarize')
            ->icon('heroicon-o-sparkles')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Generate AI Summary')
            ->modalDescription('Claude will generate a concise summary of this content.')
            ->modalSubmitActionLabel('Generate Summary')
            ->action(function ($record) {
                try {
                    $content = $record->content ?? $record->body ?? $record->description;

                    if (empty($content)) {
                        Notification::make()
                            ->title('No content to summarize')
                            ->danger()
                            ->send();
                        return;
                    }

                    $summary = Claude::generate(
                        "Summarize this content in 2-3 sentences, focusing on key points:\n\n{$content}",
                        null,
                        ['temperature' => 0.3, 'max_tokens' => 300]
                    );

                    $record->update(['summary' => $summary]);

                    Notification::make()
                        ->title('Summary generated successfully')
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Failed to generate summary')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
```

### Generate SEO Meta Action

```php
<?php
# filename: app/Filament/Actions/GenerateSeoMetaAction.php
declare(strict_types=1);

namespace App\Filament\Actions;

use App\Facades\Claude;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class GenerateSeoMetaAction
{
    public static function make(): Action
    {
        return Action::make('generate_seo')
            ->label('Generate SEO Meta')
            ->icon('heroicon-o-magnifying-glass')
            ->color('success')
            ->action(function ($record) {
                try {
                    $content = $record->content ?? $record->body;
                    $title = $record->title ?? $record->name;

                    $prompt = <<<PROMPT
Generate SEO metadata for this content:

Title: {$title}
Content: {$content}

Provide:
1. Meta description (150-160 characters)
2. 5-7 relevant keywords
3. Suggested slug (URL-friendly)

Format as JSON with keys: meta_description, keywords (array), slug
PROMPT;

                    $response = Claude::generate($prompt, null, [
                        'temperature' => 0.5,
                        'max_tokens' => 300
                    ]);

                    // Extract JSON
                    if (preg_match('/\{.*\}/s', $response, $matches)) {
                        $seoData = json_decode($matches[0], true);

                        $record->update([
                            'meta_description' => $seoData['meta_description'] ?? null,
                            'keywords' => $seoData['keywords'] ?? [],
                            'slug' => $seoData['slug'] ?? null,
                        ]);

                        Notification::make()
                            ->title('SEO metadata generated')
                            ->success()
                            ->send();
                    }

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Failed to generate SEO metadata')
                        ->danger()
                        ->send();
                }
            });
    }
}
```

### Bulk Generate Descriptions

```php
<?php
# filename: app/Filament/Actions/BulkGenerateDescriptionsAction.php
declare(strict_types=1);

namespace App\Filament\Actions;

use App\Facades\Claude;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class BulkGenerateDescriptionsAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('bulk_generate_descriptions')
            ->label('AI Generate Descriptions')
            ->icon('heroicon-o-sparkles')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Generate AI Descriptions')
            ->modalDescription('Generate descriptions for all selected items using AI.')
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records) {
                $count = 0;
                $failed = 0;

                foreach ($records as $record) {
                    try {
                        // Build prompt based on available data
                        $name = $record->name ?? $record->title ?? 'this item';
                        $category = $record->category?->name ?? '';
                        $features = $record->features ?? [];

                        $prompt = "Write a compelling 2-3 sentence description for: {$name}";
                        if ($category) {
                            $prompt .= " (Category: {$category})";
                        }
                        if (!empty($features)) {
                            $featureList = implode(', ', $features);
                            $prompt .= "\nKey features: {$featureList}";
                        }

                        $description = Claude::withModel('claude-haiku-4-20250514')
                            ->generate($prompt, null, [
                                'temperature' => 0.7,
                                'max_tokens' => 200
                            ]);

                        $record->update(['description' => trim($description)]);
                        $count++;

                    } catch (\Exception $e) {
                        $failed++;
                    }
                }

                Notification::make()
                    ->title("Generated {$count} descriptions" . ($failed > 0 ? ", {$failed} failed" : ''))
                    ->success()
                    ->send();
            });
    }
}
```

## Filament Resource with AI Features

### BlogPost Resource

```php
<?php
# filename: app/Filament/Resources/BlogPostResource.php
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Actions\GenerateSeoMetaAction;
use App\Filament\Actions\SummarizeContentAction;
use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Content')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->maxLength(200)
                        ->live(onBlur: true),

                    Forms\Components\RichEditor::make('content')
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('summary')
                        ->rows(3)
                        ->helperText('Leave empty to auto-generate')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('SEO')
                ->schema([
                    Forms\Components\TextInput::make('slug')
                        ->required(),

                    Forms\Components\Textarea::make('meta_description')
                        ->maxLength(160)
                        ->rows(2),

                    Forms\Components\TagsInput::make('keywords')
                        ->separator(','),
                ]),

            Forms\Components\Section::make('AI Assistant')
                ->schema([
                    Forms\Components\Placeholder::make('ai_helper')
                        ->content(fn() => view('filament.components.ai-helper'))
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('summary')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\IconColumn::make('has_seo')
                    ->label('SEO')
                    ->boolean()
                    ->getStateUsing(fn($record) => !empty($record->meta_description)),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('missing_summary')
                    ->query(fn($query) => $query->whereNull('summary'))
                    ->label('Missing Summary'),

                Tables\Filters\Filter::make('missing_seo')
                    ->query(fn($query) => $query->whereNull('meta_description'))
                    ->label('Missing SEO'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                SummarizeContentAction::make(),
                GenerateSeoMetaAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    \App\Filament\Actions\BulkGenerateDescriptionsAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
```

## Custom Filament Widgets

### AI Insights Widget

```php
<?php
# filename: app/Filament/Widgets/AiInsightsWidget.php
declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Facades\Claude;
use App\Models\BlogPost;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;

class AiInsightsWidget extends Widget
{
    protected static string $view = 'filament.widgets.ai-insights';
    protected int | string | array $columnSpan = 'full';

    public function getInsights(): array
    {
        return Cache::remember('admin_ai_insights', 3600, function () {
            $stats = [
                'total_posts' => BlogPost::count(),
                'posts_without_summary' => BlogPost::whereNull('summary')->count(),
                'posts_without_seo' => BlogPost::whereNull('meta_description')->count(),
                'recent_signups' => User::where('created_at', '>=', now()->subDays(7))->count(),
            ];

            $prompt = <<<PROMPT
Analyze these admin statistics and provide 3 actionable insights:

- Total blog posts: {$stats['total_posts']}
- Posts missing summaries: {$stats['posts_without_summary']}
- Posts missing SEO metadata: {$stats['posts_without_seo']}
- New user signups (last 7 days): {$stats['recent_signups']}

Provide insights as a numbered list (1-3 insights), each with:
- The insight
- Why it matters
- Recommended action

Keep it concise and actionable.
PROMPT;

            try {
                $insights = Claude::withModel('claude-haiku-4-20250514')
                    ->generate($prompt, null, ['temperature' => 0.5, 'max_tokens' => 500]);

                return [
                    'stats' => $stats,
                    'insights' => $insights,
                    'generated_at' => now(),
                ];
            } catch (\Exception $e) {
                return [
                    'stats' => $stats,
                    'insights' => 'AI insights temporarily unavailable.',
                    'generated_at' => now(),
                ];
            }
        });
    }
}
```

### Widget View

```blade
{{-- filename: resources/views/filament/widgets/ai-insights.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">AI-Powered Insights</h3>
                <span class="text-xs text-gray-500">
                    Updated: {{ $this->getInsights()['generated_at']->diffForHumans() }}
                </span>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-4 gap-4">
                @foreach($this->getInsights()['stats'] as $label => $value)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="text-2xl font-bold text-gray-900">{{ $value }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ Str::headline($label) }}</div>
                    </div>
                @endforeach
            </div>

            <!-- AI Insights -->
            <div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg p-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-purple-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <div class="flex-1 prose prose-sm max-w-none">
                        {!! Str::markdown($this->getInsights()['insights']) !!}
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
```

## AI-Powered Search

### Semantic Search Service

```php
<?php
# filename: app/Services/SemanticSearchService.php
declare(strict_types=1);

namespace App\Services;

use App\Facades\Claude;
use Illuminate\Database\Eloquent\Builder;

class SemanticSearchService
{
    /**
     * Enhance search query with AI understanding
     */
    public function enhanceQuery(string $query): array
    {
        $prompt = <<<PROMPT
Analyze this search query and extract:
1. Main keywords
2. Synonyms and related terms
3. Intent (what the user is looking for)

Query: "{$query}"

Return as JSON with keys: keywords (array), synonyms (array), intent (string)
PROMPT;

        try {
            $response = Claude::withModel('claude-haiku-4-20250514')
                ->generate($prompt, null, ['temperature' => 0.3, 'max_tokens' => 200]);

            if (preg_match('/\{.*\}/s', $response, $matches)) {
                return json_decode($matches[0], true) ?? ['keywords' => [$query]];
            }
        } catch (\Exception $e) {
            // Fallback to original query
        }

        return ['keywords' => [$query]];
    }

    /**
     * Apply semantic search to query builder
     */
    public function applyToQuery(Builder $query, string $searchTerm, array $columns): Builder
    {
        $enhanced = $this->enhanceQuery($searchTerm);
        $allTerms = array_merge(
            $enhanced['keywords'] ?? [],
            $enhanced['synonyms'] ?? []
        );

        return $query->where(function (Builder $q) use ($allTerms, $columns) {
            foreach ($allTerms as $term) {
                $q->orWhere(function (Builder $subQuery) use ($term, $columns) {
                    foreach ($columns as $column) {
                        $subQuery->orWhere($column, 'LIKE', "%{$term}%");
                    }
                });
            }
        });
    }
}
```

## Data Quality Analysis

### Content Quality Checker

```php
<?php
# filename: app/Filament/Pages/ContentQualityReport.php
declare(strict_types=1);

namespace App\Filament\Pages;

use App\Facades\Claude;
use App\Models\BlogPost;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class ContentQualityReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Reports';
    protected static string $view = 'filament.pages.content-quality-report';

    public function getQualityReport(): array
    {
        return Cache::remember('content_quality_report', 1800, function () {
            $posts = BlogPost::select('id', 'title', 'content', 'summary')
                ->whereNotNull('content')
                ->latest()
                ->limit(10)
                ->get();

            $issues = [];

            foreach ($posts as $post) {
                try {
                    $analysis = $this->analyzeContent($post);
                    if (!empty($analysis['issues'])) {
                        $issues[] = [
                            'post_id' => $post->id,
                            'title' => $post->title,
                            'issues' => $analysis['issues'],
                            'score' => $analysis['score'],
                        ];
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            return $issues;
        });
    }

    private function analyzeContent(BlogPost $post): array
    {
        $prompt = <<<PROMPT
Analyze this blog post for quality issues:

Title: {$post->title}
Content: {$post->content}

Check for:
- Grammar and spelling errors
- Readability issues
- Structural problems
- Missing elements (intro, conclusion, etc.)
- SEO issues

Rate quality 1-10 and list specific issues.

Format as JSON: {"score": 8, "issues": ["issue 1", "issue 2"]}
PROMPT;

        $response = Claude::withModel('claude-haiku-4-20250514')
            ->generate($prompt, null, ['temperature' => 0.3, 'max_tokens' => 300]);

        if (preg_match('/\{.*\}/s', $response, $matches)) {
            return json_decode($matches[0], true) ?? ['score' => 5, 'issues' => []];
        }

        return ['score' => 5, 'issues' => []];
    }
}
```

### Quality Report View

```blade
{{-- filename: resources/views/filament/pages/content-quality-report.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Content Quality Analysis
            </x-slot>

            <x-slot name="description">
                AI-powered analysis of recent content for quality issues
            </x-slot>

            <div class="space-y-4">
                @forelse($this->getQualityReport() as $item)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">
                                    {{ $item['title'] }}
                                </h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    Post #{{ $item['post_id'] }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold {{ $item['score'] >= 7 ? 'text-green-600' : ($item['score'] >= 5 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $item['score'] }}/10
                                </div>
                                <p class="text-xs text-gray-500">Quality Score</p>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded p-3">
                            <p class="text-sm font-medium text-gray-700 mb-2">Issues Found:</p>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($item['issues'] as $issue)
                                    <li class="text-sm text-gray-600">{{ $issue }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="mt-3 flex gap-2">
                            <x-filament::button
                                tag="a"
                                href="{{ route('filament.admin.resources.blog-posts.edit', $item['post_id']) }}"
                                size="sm"
                            >
                                Edit Post
                            </x-filament::button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-4 text-sm text-gray-500">No quality issues detected!</p>
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
```

## Form Helper with AI Suggestions

### AI Writing Assistant Component

```php
<?php
# filename: app/Filament/Forms/Components/AiWritingAssistant.php
declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class AiWritingAssistant extends Field
{
    protected string $view = 'filament.forms.components.ai-writing-assistant';

    public function getSuggestions(string $context): array
    {
        // This would be called via Livewire from the frontend
        return [
            'Expand this section with more details',
            'Add examples to illustrate your point',
            'Include relevant statistics or data',
            'Conclude with a call-to-action',
        ];
    }
}
```

## Performance Optimization

### Batch AI Operations

```php
<?php
# filename: app/Console/Commands/BatchGenerateSummaries.php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Facades\Claude;
use App\Models\BlogPost;
use Illuminate\Console\Command;

class BatchGenerateSummaries extends Command
{
    protected $signature = 'ai:generate-summaries {--limit=10}';
    protected $description = 'Generate summaries for posts missing them';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $posts = BlogPost::whereNull('summary')
            ->whereNotNull('content')
            ->limit($limit)
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No posts need summaries.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($posts->count());
        $bar->start();

        foreach ($posts as $post) {
            try {
                $summary = Claude::withModel('claude-haiku-4-20250514')
                    ->generate(
                        "Summarize in 2-3 sentences:\n\n{$post->content}",
                        null,
                        ['temperature' => 0.3, 'max_tokens' => 200]
                    );

                $post->update(['summary' => $summary]);
                $bar->advance();

            } catch (\Exception $e) {
                $this->error("Failed for post {$post->id}: {$e->getMessage()}");
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Summary generation complete!');

        return self::SUCCESS;
    }
}
```

## Exercises

### Exercise 1: Duplicate Detection

Create an action to find similar content:

```php
<?php
class DetectDuplicatesAction extends Action
{
    public function action(callable $callback): void
    {
        // TODO: Use Claude to find semantically similar posts
        // TODO: Compare content and flag potential duplicates
        // TODO: Show similarity scores
    }
}
```

### Exercise 2: Content Trend Analysis

Build a widget showing content trends:

```php
<?php
class ContentTrendsWidget extends Widget
{
    public function getTrends(): array
    {
        // TODO: Analyze recent posts for trending topics
        // TODO: Identify emerging themes
        // TODO: Suggest content ideas
    }
}
```

### Exercise 3: Auto-Tagging System

Implement intelligent auto-tagging:

```php
<?php
class AutoTagContent extends Action
{
    public function action($record): void
    {
        // TODO: Analyze content with Claude
        // TODO: Extract relevant tags
        // TODO: Apply to post
    }
}
```

<details>
<summary>Solution Hints</summary>

**Exercise 1**: Loop through posts, generate embeddings or use Claude to compare content similarity. Use prompt: "Rate similarity 0-100 between these texts". Flag pairs above threshold.

**Exercise 2**: Use Claude to analyze titles and content from recent posts. Prompt: "Identify 5 trending topics and suggest 3 new content ideas". Cache results hourly.

**Exercise 3**: Prompt Claude: "Extract 5-10 relevant tags for this content. Return as comma-separated list." Parse response and save to post tags relationship.

</details>

## Troubleshooting

**Slow admin panel performance?**
- Cache AI results aggressively
- Use async jobs for bulk operations
- Implement lazy loading for widgets
- Use Haiku model for faster responses

**Widget refresh consuming too many API calls?**
- Increase cache TTL for widgets
- Add manual refresh button instead of auto-refresh
- Only load widgets when viewed

**Bulk operations timing out?**
- Process in smaller batches
- Use queue jobs for large operations
- Add progress tracking
- Implement resumable operations

**Inconsistent AI results?**
- Use lower temperature (0.3-0.5)
- Provide more specific prompts
- Add examples in system prompts
- Validate outputs before saving

## Key Takeaways

- ✓ **Custom Actions** integrate AI into existing workflows
- ✓ **Bulk Operations** save massive amounts of time
- ✓ **Widgets** provide intelligent insights
- ✓ **Semantic Search** understands user intent
- ✓ **Quality Analysis** maintains content standards
- ✓ **Caching** is essential for performance
- ✓ **Async Processing** handles large operations
- ✓ **AI Assistant** enhances admin productivity

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="25"
  label="You've supercharged your admin panel with AI!"
/>

---

Continue to [Chapter 26: Code Review Assistant](/series/claude-php-developers/chapters/26-code-review-assistant) to build automated code review systems.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 25 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-25)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-25
composer install
cp .env.example .env
# Add your ANTHROPIC_API_KEY to .env
php artisan migrate --seed
php artisan make:filament-user
php artisan serve
```
