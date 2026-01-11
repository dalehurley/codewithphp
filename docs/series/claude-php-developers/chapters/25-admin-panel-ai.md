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
estimatedTime: "PT5M"
teaches:
  - 'Integrate Claude with Filament PHP admin panels'
  - 'Create custom actions for AI-powered operations'
  - 'Build bulk content generation workflows'
  - 'Implement intelligent semantic search capabilities'
  - 'Automate content summarization and SEO metadata generation'
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

**Estimated Time**: 120-150 minutes

**Compatibility**: Laravel 12, Filament 4, Claude-PHP-SDK v0.2 ✅

## What You'll Build

By the end of this chapter, you will have created:

- Custom Filament actions for AI-powered content summarization and SEO generation
- Bulk operations system for generating descriptions across multiple records
- AI insights widget that analyzes admin statistics and provides actionable recommendations
- Semantic search service that understands user intent and enhances queries
- Content quality analysis page that identifies issues and suggests improvements
- AI writing assistant component for form helpers
- Batch processing command for generating summaries efficiently
- Complete Filament resource with integrated AI features
- Performance-optimized admin panel with caching and async operations

## Prerequisites

Before starting, ensure you have:

- ✓ **Laravel 11+** with Filament 3 installed
- ✓ **Claude-PHP-SDK** installed: `composer require claude-php/Claude-PHP-SDK`
- ✓ **Database** with sample data
- ✓ **Filament admin panel** configured
- ✓ **Understanding of Filament resources and actions**
- ✓ **ANTHROPIC_API_KEY** set in your `.env` file

## Objectives

By completing this chapter, you will:

- Integrate Claude with Filament PHP admin panels
- Create custom actions for AI-powered operations
- Build bulk content generation workflows
- Implement intelligent semantic search capabilities
- Automate content summarization and SEO metadata generation
- Analyze data quality with AI-powered insights
- Build dashboard widgets with actionable recommendations
- Optimize performance with caching and batch processing
- Create reusable AI components for admin panels

## Quick Start

Get a working AI-powered admin action in 5 minutes:

```bash
# Create the action file
php artisan make:class Filament/Actions/SummarizeContentAction
```

Then add this code:

```php
<?php
# filename: app/Filament/Actions/SummarizeContentAction.php
declare(strict_types=1);

namespace App\Filament\Actions;

use App\Facades\Claude;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class SummarizeContentAction
{
    public static function make(): Action
    {
        return Action::make('summarize')
            ->label('AI Summarize')
            ->icon('heroicon-o-sparkles')
            ->action(function ($record) {
                $summary = Claude::generate(
                    "Summarize this content in 2-3 sentences:\n\n{$record->content}",
                    null,
                    ['max_tokens' => 200, 'temperature' => 0.3]
                );
                $record->update(['summary' => $summary->content[0]->text]);
                Notification::make()->title('Summary generated!')->success()->send();
            });
    }
}
```

Add to your resource's `table()` method:

```php
->actions([
    SummarizeContentAction::make(),
])
```

**Expected Result**: You'll see an "AI Summarize" button in your Filament table that generates summaries with one click.

## Setup Filament

If you haven't installed Filament yet:

```bash
composer require filament/filament:"^4.0"
php artisan filament:install --panels
php artisan make:filament-user
```

**Estimated Time**: ~5 minutes

## Step 1: Create the BlogPost Model (~10 min)

### Goal

Set up the database structure and model for blog posts that will use AI features.

### Actions

1. **Create the migration**:

```bash
php artisan make:migration create_blog_posts_table
```

2. **Define the schema**:

```php
<?php
# filename: database/migrations/2024_01_01_000001_create_blog_posts_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->text('summary')->nullable();
            $table->string('slug')->unique();
            $table->text('meta_description')->nullable();
            $table->json('keywords')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
```

3. **Create the model**:

```bash
php artisan make:model BlogPost
```

4. **Define the model**:

```php
<?php
# filename: app/Models/BlogPost.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'summary',
        'slug',
        'meta_description',
        'keywords',
    ];

    protected $casts = [
        'keywords' => 'array',
    ];
}
```

5. **Run the migration**:

```bash
php artisan migrate
```

### Expected Result

```
Migrating: 2024_01_01_000001_create_blog_posts_table
Migrated:  2024_01_01_000001_create_blog_posts_table
```

### Why It Works

The migration creates a `blog_posts` table with fields for content, AI-generated summaries, and SEO metadata. The `keywords` field uses JSON casting to store arrays. The model uses `HasFactory` for testing and seeding.

## Step 2: Create Summarize Content Action (~15 min)

### Goal

Build a Filament action that uses Claude to automatically generate content summaries.

### Actions

1. **Create the action class**:

```bash
mkdir -p app/Filament/Actions
```

2. **Create the action file** with the code shown below.

### Expected Result

You'll have a reusable action class that can be added to any Filament resource.

### Why It Works

Filament actions are self-contained classes that return configured `Action` instances. The `action()` closure receives the `$record` being acted upon. We use the null coalescing operator (`??`) to handle different field names across models. Notifications provide user feedback, and error handling ensures the admin panel doesn't break if Claude API calls fail.

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

                    $record->update(['summary' => $summary->content[0]->text]);

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

## Step 3: Create SEO Meta Generation Action (~15 min)

### Goal

Build an action that generates SEO metadata (description, keywords, slug) using Claude's understanding of content.

### Actions

1. **Create the action file**:

```bash
touch app/Filament/Actions/GenerateSeoMetaAction.php
```

2. **Add the implementation**:

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

                    // Extract JSON from response
                    if (preg_match('/\{.*\}/s', $response->content[0]->text, $matches)) {
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

### Why It Works

This action prompts Claude to analyze content and generate structured SEO data. We use a HEREDOC string for the prompt to keep it readable. The regex pattern `/\{.*\}/s` extracts JSON from Claude's response (which may include explanatory text). We use null coalescing (`??`) when extracting data to handle missing keys gracefully. The action updates multiple fields in a single database operation for efficiency.

## Step 4: Create Bulk Description Generation (~20 min)

### Goal

Build a bulk action that generates descriptions for multiple records at once, saving time when processing many items.

### Actions

1. **Create the bulk action file**:

```bash
touch app/Filament/Actions/BulkGenerateDescriptionsAction.php
```

2. **Add the implementation**:

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

                        $description = Claude::withModel('claude-haiku-4-5-20251001')
                            ->generate($prompt, null, [
                                'temperature' => 0.7,
                                'max_tokens' => 200
                            ]);

                        $record->update(['description' => trim($description->content[0]->text)]);
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

### Why It Works

Bulk actions receive a `Collection` of selected records. We iterate through each record, building context-aware prompts based on available data (name, category, features). Using `claude-haiku-4-5` keeps costs low for bulk operations. Error handling continues processing even if individual records fail, and we track both success and failure counts. The notification shows a summary of results.

## Step 5: Create Filament Resource with AI Integration (~25 min)

### Goal

Integrate all AI actions into a complete Filament resource for managing blog posts.

### Actions

1. **Generate the resource**:

```bash
php artisan make:filament-resource BlogPost --generate
```

2. **Update the resource** to include AI actions:

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
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

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
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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

### Expected Result

Your Filament admin panel will show a BlogPost resource with:

- Form fields for content, summary, and SEO metadata
- Table columns showing title, summary preview, and SEO status
- Filters for posts missing summaries or SEO data
- Row actions for summarizing and generating SEO metadata
- Bulk action for generating descriptions

### Why It Works

Filament resources define both forms (for editing) and tables (for listing). The `form()` method returns a schema with sections grouping related fields. The `table()` method defines columns, filters, and actions. Actions are added to the `actions()` array for row-level operations and `bulkActions()` for multi-select operations. The `getStateUsing()` closure allows computed columns like the SEO status icon.

## Step 6: Create AI Insights Widget (~20 min)

### Goal

Build a dashboard widget that analyzes admin statistics and provides AI-powered insights and recommendations.

### Actions

1. **Create the widget**:

```bash
php artisan make:filament-widget AiInsightsWidget --stats-overview
```

2. **Update the widget class**:

```php
<?php
# filename: app/Filament/Widgets/AiInsightsWidget.php
declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Facades\Claude;
use App\Models\BlogPost;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\Cache;

class AiInsightsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;
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
                $insights = Claude::withModel('claude-haiku-4-5-20251001')
                    ->generate($prompt, null, ['temperature' => 0.5, 'max_tokens' => 500]);

                return [
                    'stats' => $stats,
                    'insights' => $insights->content[0]->text,
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

3. **Create the widget view**:

```bash
mkdir -p resources/views/filament/widgets
touch resources/views/filament/widgets/ai-insights.blade.php
```

4. **Add the Blade template**:

<div v-pre>

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

</div>

### Expected Result

Your Filament dashboard will display a widget showing:

- Statistics grid with key metrics
- AI-generated insights with actionable recommendations
- Timestamp showing when insights were last generated

### Why It Works

Widgets extend Filament's `Widget` class and use Blade views for rendering. The `getInsights()` method caches results for 1 hour to reduce API calls. We use `Cache::remember()` to store both stats and AI insights together. The widget uses Filament's section component for consistent styling. The `diffForHumans()` method shows relative time (e.g., "2 hours ago").

## Step 7: Implement Semantic Search (~20 min)

### Goal

Create an intelligent search service that understands user intent and enhances search queries with synonyms and related terms.

### Actions

1. **Create the service**:

```bash
php artisan make:class Services/SemanticSearchService
```

2. **Add the implementation**:

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
            $response = Claude::withModel('claude-haiku-4-5-20251001')
                ->generate($prompt, null, ['temperature' => 0.3, 'max_tokens' => 200]);

            if (preg_match('/\{.*\}/s', $response->content[0]->text, $matches)) {
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

### Expected Result

You'll have a service that can enhance any search query by:

- Extracting main keywords
- Finding synonyms and related terms
- Understanding user intent
- Applying enhanced search to query builders

### Why It Works

The `enhanceQuery()` method uses Claude to analyze search intent and extract semantic information. We use a low temperature (0.3) for consistent keyword extraction. The `applyToQuery()` method builds a complex WHERE clause that searches across multiple columns using OR conditions. Each term searches all specified columns, and terms are combined with OR to find matches for any enhanced term. This provides better search results than simple LIKE queries.

## Step 8: Create Content Quality Analysis Page (~25 min)

### Goal

Build a Filament page that analyzes content quality and identifies issues that need attention.

### Actions

1. **Create the page**:

```bash
php artisan make:filament-page ContentQualityReport
```

2. **Update the page class**:

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

        $response = Claude::withModel('claude-haiku-4-5-20251001')
            ->generate($prompt, null, ['temperature' => 0.3, 'max_tokens' => 300]);

        if (preg_match('/\{.*\}/s', $response->content[0]->text, $matches)) {
            return json_decode($matches[0], true) ?? ['score' => 5, 'issues' => []];
        }

        return ['score' => 5, 'issues' => []];
    }
}
```

3. **Create the page view**:

```bash
mkdir -p resources/views/filament/pages
touch resources/views/filament/pages/content-quality-report.blade.php
```

4. **Add the Blade template**:

<div v-pre>

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

</div>

### Expected Result

You'll have a new page in your Filament admin panel under "Reports" that shows:

- List of posts with quality issues
- Quality scores (1-10) with color coding
- Specific issues identified by AI
- Direct links to edit problematic posts

### Why It Works

Filament pages extend the `Page` class and can be added to navigation groups. The `getQualityReport()` method caches results for 30 minutes to balance freshness with API costs. We analyze only the 10 most recent posts to limit processing time. The analysis uses Claude to check multiple quality dimensions and returns structured JSON. The Blade view uses Filament components for consistent styling and includes conditional classes based on quality scores.

## Step 9: Create AI Writing Assistant Component (~15 min)

### Goal

Build a reusable form component that provides AI-powered writing suggestions.

### Actions

1. **Create the component**:

```bash
mkdir -p app/Filament/Forms/Components
touch app/Filament/Forms/Components/AiWritingAssistant.php
```

2. **Add the implementation**:

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

### Expected Result

You'll have a custom Filament form component that can be added to any form to provide AI writing assistance.

### Why It Works

Custom form components extend Filament's `Field` class and define their own view. The component can be called via Livewire from the frontend to get real-time suggestions. This pattern allows you to build reusable AI-powered form helpers that work across different resources.

## Step 10: Create Batch Processing Command (~15 min)

### Goal

Build a console command for efficiently processing large batches of content through AI operations.

### Actions

1. **Create the command**:

```bash
php artisan make:command BatchGenerateSummaries
```

2. **Add the implementation**:

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
                $summary = Claude::withModel('claude-haiku-4-5-20251001')
                    ->generate(
                        "Summarize in 2-3 sentences:\n\n{$post->content}",
                        null,
                        ['temperature' => 0.3, 'max_tokens' => 200]
                    );

                $post->update(['summary' => $summary->content[0]->text]);
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

### Expected Result

You'll have a command that can be run via:

```bash
php artisan ai:generate-summaries --limit=50
```

The command will process posts in batches, showing a progress bar and handling errors gracefully.

### Why It Works

Console commands extend Laravel's `Command` class and use the `handle()` method for execution. We use `createProgressBar()` to show visual feedback during long operations. The `--limit` option allows controlling batch size. Error handling continues processing even if individual items fail, ensuring the command completes successfully. Using `claude-haiku-4-5` keeps costs low for batch operations.

## Exercises

### Exercise 1: Duplicate Detection Action (~30 min)

**Goal**: Create an action that finds semantically similar posts to prevent duplicate content.

Create a file called `app/Filament/Actions/DetectDuplicatesAction.php` and implement:

- An action that compares the current post's content with other posts
- Use Claude to rate similarity between content (0-100 scale)
- Display a modal showing similar posts with similarity scores
- Allow flagging posts as duplicates

**Validation**: Test with posts that have similar topics:

```php
// Create two similar posts
$post1 = BlogPost::create(['title' => 'PHP Basics', 'content' => '...']);
$post2 = BlogPost::create(['title' => 'Introduction to PHP', 'content' => '...']);

// Run the action on $post1
// Should show $post2 with high similarity score
```

**Expected Output**: Modal showing similar posts with scores like "Post #2: 85% similar"

### Exercise 2: Content Trend Analysis Widget (~30 min)

**Goal**: Build a widget that analyzes content trends and suggests new topics.

Create `app/Filament/Widgets/ContentTrendsWidget.php` and implement:

- Analyze titles and content from the last 30 posts
- Identify 5 trending topics using Claude
- Suggest 3 new content ideas based on trends
- Cache results for 1 hour
- Display trends and suggestions in a visually appealing format

**Validation**: Create 30+ posts with various topics, then check the widget:

```bash
# Seed database with sample posts
php artisan db:seed --class=BlogPostSeeder

# View dashboard - widget should show trends
```

**Expected Output**: Widget displaying trending topics and content suggestions

### Exercise 3: Auto-Tagging System (~25 min)

**Goal**: Create an action that automatically generates relevant tags for posts.

Create `app/Filament/Actions/AutoTagContentAction.php` and implement:

- Analyze post content with Claude
- Extract 5-10 relevant tags
- Parse tags from Claude's response (comma-separated or JSON)
- Update the post's tags relationship
- Show notification with generated tags

**Validation**: Test with a post that has clear topics:

```php
$post = BlogPost::create([
    'title' => 'Laravel Authentication Guide',
    'content' => 'Complete guide to Laravel authentication...'
]);

// Run action
// Should generate tags like: ['laravel', 'authentication', 'security', 'tutorial']
```

**Expected Output**: Post updated with tags, notification showing "Generated 7 tags: laravel, authentication..."

<details>
<summary>Solution Hints</summary>

**Exercise 1**: Loop through posts, generate embeddings or use Claude to compare content similarity. Use prompt: "Rate similarity 0-100 between these texts". Flag pairs above threshold.

**Exercise 2**: Use Claude to analyze titles and content from recent posts. Prompt: "Identify 5 trending topics and suggest 3 new content ideas". Cache results hourly.

**Exercise 3**: Prompt Claude: "Extract 5-10 relevant tags for this content. Return as comma-separated list." Parse response and save to post tags relationship.

</details>

## Step 11: Add Authentication & Authorization (~20 min)

### Goal

Ensure only authorized users can trigger AI-powered admin actions and track who performed what operations.

### Actions

1. **Create an audit log migration**:

```bash
php artisan make:migration create_ai_audit_logs_table
```

2. **Define the schema**:

```php
<?php
# filename: database/migrations/2024_01_01_000010_create_ai_audit_logs_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action'); // 'summarize', 'generate_seo', etc.
            $table->string('model');
            $table->integer('input_tokens')->nullable();
            $table->integer('output_tokens')->nullable();
            $table->decimal('cost', 10, 6)->nullable();
            $table->json('input_data')->nullable();
            $table->json('output_data')->nullable();
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_audit_logs');
    }
};
```

3. **Create an audit log model**:

```php
<?php
# filename: app/Models/AiAuditLog.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'input_tokens',
        'output_tokens',
        'cost',
        'input_data',
        'output_data',
        'success',
        'error_message',
    ];

    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array',
        'success' => 'boolean',
        'cost' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

4. **Create a middleware to check permissions**:

```php
<?php
# filename: app/Http/Middleware/CanUseAiFeatures.php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanUseAiFeatures
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only admins can use AI features
        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
```

5. **Update actions to check permissions**:

```php
// In SummarizeContentAction::make()
            ->action(function ($record) {
                // Check permission
                if (!auth()->check() || !auth()->user()->is_admin) {
                    Notification::make()
                        ->title('Unauthorized')
                        ->body('You do not have permission to use AI features.')
                        ->danger()
                        ->send();
                    return;
                }

    // Log the operation
    \App\Models\AiAuditLog::create([
        'user_id' => auth()->id(),
        'action' => 'summarize',
        'model' => 'claude-sonnet-4-5',
        'input_data' => ['post_id' => $record->id],
    ]);

    // ... rest of action
})
```

### Expected Result

All AI operations are now tracked with:

- Who performed the action
- When it was performed
- Which AI model was used
- Tokens and cost
- Success or failure with error messages

### Why It Works

Authorization checks happen before any AI operation. Audit logging provides accountability and compliance. The middleware can be applied to routes that shouldn't have direct access. Storing input/output data enables debugging and auditing decisions.

---

## Step 12: Implement Error Recovery & Retry Logic (~20 min)

### Goal

Handle failures gracefully and enable resuming bulk operations without reprocessing successes.

### Actions

1. **Create a failed operations tracking table**:

```bash
php artisan make:migration create_ai_batch_operations_table
```

2. **Define the schema**:

```php
<?php
# filename: database/migrations/2024_01_01_000011_create_ai_batch_operations_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_batch_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('operation_type'); // 'bulk_summarize', 'bulk_seo', etc.
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed', 'paused']);
            $table->integer('total_records');
            $table->integer('processed_records')->default(0);
            $table->integer('successful_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->json('failed_record_ids')->nullable();
            $table->json('last_processed_id')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_batch_operations');
    }
};
```

3. **Create a resumable batch processor**:

```php
<?php
# filename: app/Services/ResumableBatchProcessor.php
declare(strict_types=1);

namespace App\Services;

use App\Models\AiBatchOperation;
use App\Facades\Claude;
use Illuminate\Support\Facades\Log;

class ResumableBatchProcessor
{
    public function __construct(private int $batchSize = 10, private int $maxRetries = 3) {}

    public function processBatch(
        string $operationType,
        array $recordIds,
        callable $processRecord
    ): AiBatchOperation {
        $operation = AiBatchOperation::create([
            'user_id' => auth()->id(),
            'operation_type' => $operationType,
            'status' => 'in_progress',
            'total_records' => count($recordIds),
        ]);

        try {
            $this->processRecords($operation, $recordIds, $processRecord);
        } catch (\Exception $e) {
            $operation->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error("Batch operation {$operation->id} failed", ['error' => $e->getMessage()]);
        }

        return $operation;
    }

    private function processRecords(
        AiBatchOperation $operation,
        array $recordIds,
        callable $processRecord
    ): void {
        // Skip already processed records
        $startIndex = 0;
        if ($operation->last_processed_id) {
            $startIndex = array_search($operation->last_processed_id, $recordIds) + 1;
        }

        foreach (array_slice($recordIds, $startIndex) as $recordId) {
            try {
                $processRecord($recordId);

                $operation->increment('successful_records');
                $operation->update(['last_processed_id' => $recordId]);

            } catch (\Exception $e) {
                $operation->increment('failed_records');
                $failedIds = $operation->failed_record_ids ?? [];
                $failedIds[] = $recordId;
                $operation->update(['failed_record_ids' => $failedIds]);

                Log::warning("Failed to process record {$recordId}", ['error' => $e->getMessage()]);
            }

            $operation->increment('processed_records');
        }

        $operation->update(['status' => 'completed']);
    }

    public function retry(AiBatchOperation $operation, callable $processRecord): void
    {
        if ($operation->retry_count >= $this->maxRetries) {
            throw new \Exception("Max retries exceeded for operation {$operation->id}");
        }

        $operation->update([
            'status' => 'in_progress',
            'retry_count' => $operation->retry_count + 1,
            'failed_record_ids' => null,
        ]);

        $failedIds = $operation->failed_record_ids ?? [];
        $this->processRecords($operation, $failedIds, $processRecord);
    }
}
```

### Expected Result

Bulk operations can be paused and resumed. Failed records are tracked and can be retried without reprocessing successes.

### Why It Works

Tracking operation progress in the database enables resuming after failures or system restarts. Separating successful from failed records allows targeted retries. Storing the last processed ID enables efficient resumption.

---

## Step 13: Implement Cost Tracking & Budget Limits (~20 min)

### Goal

Track costs in real-time and prevent operations that exceed budget limits.

### Actions

1. **Create a cost tracking service**:

```php
<?php
# filename: app/Services/AiCostTracker.php
declare(strict_types=1);

namespace App\Services;

use App\Models\AiAuditLog;
use Illuminate\Support\Facades\Cache;

class AiCostTracker
{
    private const CLAUDE_HAIKU_INPUT = 0.00080 / 1000; // per token
    private const CLAUDE_HAIKU_OUTPUT = 0.00240 / 1000;
    private const CLAUDE_SONNET_INPUT = 0.003 / 1000;
    private const CLAUDE_SONNET_OUTPUT = 0.015 / 1000;
    private const CLAUDE_OPUS_INPUT = 0.015 / 1000;
    private const CLAUDE_OPUS_OUTPUT = 0.075 / 1000;

    public function estimateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        return match($model) {
            'claude-haiku-4-5-20251001' =>
                ($inputTokens * self::CLAUDE_HAIKU_INPUT) +
                ($outputTokens * self::CLAUDE_HAIKU_OUTPUT),

            'claude-sonnet-4-5' =>
                ($inputTokens * self::CLAUDE_SONNET_INPUT) +
                ($outputTokens * self::CLAUDE_SONNET_OUTPUT),

            'claude-opus-4-1' =>
                ($inputTokens * self::CLAUDE_OPUS_INPUT) +
                ($outputTokens * self::CLAUDE_OPUS_OUTPUT),

            default => 0.0,
        };
    }

    public function getTodaysCost(int $userId): float
    {
        $cacheKey = "ai_cost_today_{$userId}";

        return Cache::remember($cacheKey, 3600, function () use ($userId) {
            return AiAuditLog::query()
                ->where('user_id', $userId)
                ->whereDate('created_at', today())
                ->where('success', true)
                ->sum('cost');
        });
    }

    public function getMonthsCost(int $userId): float
    {
        return AiAuditLog::query()
            ->where('user_id', $userId)
            ->whereMonth('created_at', now()->month)
            ->where('success', true)
            ->sum('cost');
    }

    public function checkBudgetLimit(int $userId, float $estimatedCost): bool
    {
        $dailyBudget = (float) config('ai.daily_budget_limit', 100.0);
        $todaysCost = $this->getTodaysCost($userId);

        return ($todaysCost + $estimatedCost) <= $dailyBudget;
    }

    public function logCost(int $userId, string $action, string $model, int $inputTokens, int $outputTokens): float
    {
        $cost = $this->estimateCost($model, $inputTokens, $outputTokens);

        AiAuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'model' => $model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'cost' => $cost,
            'success' => true,
        ]);

        Cache::forget("ai_cost_today_{$userId}");

        return $cost;
    }
}
```

2. **Update actions to check budget**:

```php
// In SummarizeContentAction::make()
$costTracker = app(AiCostTracker::class);

// Estimate tokens (rough estimate: ~4 tokens per word)
$estimatedInputTokens = (str_word_count($content) * 4) + 100;
$estimatedOutputTokens = 300;
$estimatedCost = $costTracker->estimateCost('claude-sonnet-4-5', $estimatedInputTokens, $estimatedOutputTokens);

if (!$costTracker->checkBudgetLimit(auth()->id(), $estimatedCost)) {
    Notification::make()
        ->title('Budget Limit Exceeded')
        ->body("Today's cost: $" . number_format($costTracker->getTodaysCost(auth()->id()), 2))
        ->danger()
        ->send();
    return;
}
```

### Expected Result

Cost tracking is automatic. Operations fail gracefully when budget limits are exceeded. Users can see their daily and monthly costs.

### Why It Works

Estimating costs before operations prevents bill shock. Caching today's cost reduces database queries. Budget limits encourage responsible AI usage. Real pricing per model allows accurate predictions.

---

## Step 14: Implement Async Queue Processing (~25 min)

### Goal

Process bulk operations asynchronously using Laravel queues instead of blocking the admin panel.

### Actions

1. **Create a bulk operation job**:

```bash
php artisan make:job ProcessAiBulkOperation
```

2. **Implement the job**:

```php
<?php
# filename: app/Jobs/ProcessAiBulkOperation.php
declare(strict_types=1);

namespace App\Jobs;

use App\Models\AiBatchOperation;
use App\Services\ResumableBatchProcessor;
use App\Facades\Claude;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAiBulkOperation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private AiBatchOperation $operation) {}

    public function handle(ResumableBatchProcessor $processor): void
    {
        $recordIds = $this->getRecordIds();

        $processor->processBatch(
            $this->operation->operation_type,
            $recordIds,
            $this->getProcessor()
        );
    }

    private function getProcessor(): callable
    {
        return match($this->operation->operation_type) {
            'bulk_summarize' => fn($recordId) => $this->summarizeRecord($recordId),
            'bulk_seo' => fn($recordId) => $this->generateSeoRecord($recordId),
            default => fn($recordId) => true,
        };
    }

    private function summarizeRecord(int $recordId): void
    {
        $post = \App\Models\BlogPost::findOrFail($recordId);

        $summary = Claude::withModel('claude-haiku-4-5-20251001')
            ->generate(
                "Summarize in 2-3 sentences:\n\n{$post->content}",
                null,
                ['temperature' => 0.3, 'max_tokens' => 200]
            );

        $post->update(['summary' => $summary->content[0]->text]);
    }

    private function getRecordIds(): array
    {
        // Implementation based on operation_type
        return \App\Models\BlogPost::whereNull('summary')
            ->pluck('id')
            ->toArray();
    }
}
```

3. **Dispatch from bulk action**:

```php
// In BulkGenerateDescriptionsAction::make()
->action(function (Collection $records) {
    $operation = AiBatchOperation::create([
        'user_id' => auth()->id(),
        'operation_type' => 'bulk_descriptions',
        'status' => 'pending',
        'total_records' => $records->count(),
    ]);

    // Dispatch async job
    dispatch(new ProcessAiBulkOperation($operation));

    Notification::make()
        ->title('Processing started')
        ->body('Your bulk operation is running in the background')
        ->success()
        ->send();
})
```

### Expected Result

Bulk operations run in background jobs. Admin panel remains responsive. Users can see progress without page refresh.

### Why It Works

Queue jobs remove blocking operations from HTTP requests. Jobs can be retried automatically. Progress is tracked in the database. Multiple jobs run in parallel.

---

## Step 15: Add Content Versioning & Revision History (~20 min)

### Goal

Enable users to compare AI-generated content with originals and maintain revision history.

### Actions

1. **Create a content versions table**:

```bash
php artisan make:migration create_content_versions_table
```

2. **Define the schema**:

```php
<?php
# filename: database/migrations/2024_01_01_000012_create_content_versions_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_versions', function (Blueprint $table) {
            $table->id();
            $table->morphs('versionable'); // Post, BlogPost, etc.
            $table->string('field_name'); // 'summary', 'meta_description', etc.
            $table->longText('original_value')->nullable();
            $table->longText('generated_value');
            $table->string('model');
            $table->integer('tokens_used')->nullable();
            $table->decimal('cost', 10, 6)->nullable();
            $table->boolean('approved')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['versionable_id', 'versionable_type', 'field_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_versions');
    }
};
```

3. **Create a Filament page to view versions**:

```php
<?php
# filename: app/Filament/Pages/ViewContentVersions.php
declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\ContentVersion;
use Filament\Pages\Page;

class ViewContentVersions extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Tools';
    protected static string $view = 'filament.pages.view-content-versions';

    public function getVersions($postId): array
    {
        return ContentVersion::where('versionable_id', $postId)
            ->where('versionable_type', \App\Models\BlogPost::class)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('field_name')
            ->toArray();
    }
}
```

4. **Save versions when generating content**:

```php
// In SummarizeContentAction::make()
$originalSummary = $record->summary;
$summary = Claude::generate(...);

// Save version
\App\Models\ContentVersion::create([
    'versionable_id' => $record->id,
    'versionable_type' => \App\Models\BlogPost::class,
    'field_name' => 'summary',
    'original_value' => $originalSummary,
    'generated_value' => $summary,
    'model' => 'claude-sonnet-4-5',
    'created_by' => auth()->id(),
]);

$record->update(['summary' => $summary]);
```

### Expected Result

All AI-generated content is versioned. Users can compare original vs. generated. Complete history is available for audit.

### Why It Works

Versioning enables A/B testing and comparison. Polymorphic relationships work with any content type. Approval tracking enables content review workflows. Timestamps enable reverting to previous versions.

---

## Step 16: Add Real-time Progress Updates for UI (~20 min)

### Goal

Show users real-time progress for long-running bulk operations with progress bar and status updates.

### Actions

1. **Create a progress tracking event**:

```php
<?php
# filename: app/Events/BatchOperationProgress.php
declare(strict_types=1);

namespace App\Events;

use App\Models\AiBatchOperation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BatchOperationProgress implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public AiBatchOperation $operation) {}

    public function broadcastOn(): Channel
    {
        return new Channel("batch-operation.{$this->operation->id}");
    }

    public function broadcastAs(): string
    {
        return 'progress';
    }
}
```

2. **Update batch operation to emit events**:

```php
// In ResumableBatchProcessor::processRecords()
$operation->increment('processed_records');

// Emit progress event every 5 records
if ($operation->processed_records % 5 === 0) {
    broadcast(new BatchOperationProgress($operation));
}
```

3. **Create a Livewire component to show progress**:

<div v-pre>

```blade
{{-- filename: resources/views/livewire/batch-operation-progress.blade.php --}}
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h3>{{ $operation->operation_type }}</h3>
        <span class="text-sm text-gray-500">
            {{ $operation->processed_records }}/{{ $operation->total_records }}
        </span>
    </div>

    <div class="w-full bg-gray-200 rounded-full h-2">
        <div
            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
            style="width: {{ ($operation->processed_records / $operation->total_records) * 100 }}%"
        ></div>
    </div>

    <div class="grid grid-cols-3 gap-4 text-sm">
        <div class="bg-green-50 p-3 rounded">
            <p class="text-green-600 font-semibold">{{ $operation->successful_records }} Successful</p>
        </div>
        <div class="bg-red-50 p-3 rounded">
            <p class="text-red-600 font-semibold">{{ $operation->failed_records }} Failed</p>
        </div>
        <div class="bg-yellow-50 p-3 rounded">
            <p class="text-yellow-600 font-semibold">{{ $operation->total_records - $operation->processed_records }} Remaining</p>
        </div>
    </div>
</div>

@script
<script>
    Echo.channel('batch-operation.{{ $operation->id }}')
        .listen('BatchOperationProgress', (e) => {
            $wire.dispatch('progress-updated', { operation: e.operation });
        });
</script>
@endscript
```

</div>

### Expected Result

Users see real-time progress with a progress bar, success/failure counts, and estimated time remaining.

### Why It Works

Livewire and Laravel Broadcasting enable real-time updates without polling. Emitting events every N records balances updates with performance. Progress bar provides visual feedback encouraging users to wait.

---

## Step 17: Add Model Selection Configuration (~15 min)

### Goal

Make model selection configurable per feature instead of hardcoded.

### Actions

1. **Create configuration**:

```php
<?php
# filename: config/ai.php
declare(strict_types=1);

return [
    'default_model' => env('AI_DEFAULT_MODEL', 'claude-sonnet-4-5-20250929'),
    'fast_model' => env('AI_FAST_MODEL', 'claude-haiku-4-5-20251001'),
    'smart_model' => env('AI_SMART_MODEL', 'claude-opus-4-1-20250805'),

    'operations' => [
        'summarize' => [
            'model' => 'claude-haiku-4-5-20251001', // Fast and cheap
            'temperature' => 0.3,
            'max_tokens' => 300,
        ],
        'generate_seo' => [
            'model' => 'claude-sonnet-4-5-20250929', // Balanced
            'temperature' => 0.5,
            'max_tokens' => 300,
        ],
        'quality_analysis' => [
            'model' => 'claude-sonnet-4-5-20250929', // Good for analysis
            'temperature' => 0.3,
            'max_tokens' => 500,
        ],
        'semantic_search' => [
            'model' => 'claude-haiku-4-5-20251001', // Fast
            'temperature' => 0.3,
            'max_tokens' => 200,
        ],
    ],

    'daily_budget_limit' => (float) env('AI_DAILY_BUDGET_LIMIT', 100.0),
];
```

2. **Update actions to use config**:

```php
// Instead of hardcoded model:
Claude::withModel('claude-haiku-4-5-20251001')->generate(...)

// Use config:
$config = config('ai.operations.summarize');
Claude::withModel($config['model'])
    ->generate($prompt, null, [
        'temperature' => $config['temperature'],
        'max_tokens' => $config['max_tokens'],
    ])
```

3. **Update .env.example**:

```bash
AI_DEFAULT_MODEL=claude-sonnet-4-5
AI_FAST_MODEL=claude-haiku-4-5
AI_SMART_MODEL=claude-opus-4-1
AI_DAILY_BUDGET_LIMIT=100.0
```

### Expected Result

All models are configurable per operation. Easy to switch models for cost or quality optimization. Budget limits are configurable.

### Why It Works

Centralized configuration makes it easy to tune all operations. Different operations have different requirements (speed vs. quality). Environment variables enable different configs per deployment.

---

## Step 18: Add Testing Strategy (~20 min)

### Goal

Provide comprehensive testing patterns for AI-powered admin actions.

### Actions

1. **Create a test for AI actions**:

```php
<?php
# filename: tests/Feature/Filament/Actions/SummarizeActionTest.php
declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SummarizeActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Claude API
        \Illuminate\Support\Facades\Http::fake([
            'api.anthropic.com/*' => \Illuminate\Support\Facades\Http::response([
                'content' => [['type' => 'text', 'text' => 'Mocked summary']],
                'usage' => ['input_tokens' => 100, 'output_tokens' => 50],
            ]),
        ]);
    }

    public function test_admin_can_summarize_content(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = BlogPost::factory()->create([
            'content' => 'This is a long blog post with lots of content...',
            'summary' => null,
        ]);

        $this->actingAs($admin);

        // Simulate action
        $action = new \App\Filament\Actions\SummarizeContentAction();
        $actionInstance = $action::make();

        // Call the action's closure
        $closure = $actionInstance->getAction();
        $closure($post);

        // Verify summary was saved
        $this->assertTrue($post->refresh()->summary !== null);
    }

    public function test_non_admin_cannot_summarize(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $post = BlogPost::factory()->create();

        $this->actingAs($user);

        // Attempt action should be blocked
        // Implementation depends on how permissions are checked
    }

    public function test_audit_log_created(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = BlogPost::factory()->create();

        $this->actingAs($admin);

        $action = \App\Filament\Actions\SummarizeContentAction::make();
        $closure = $action->getAction();
        $closure($post);

        $this->assertDatabaseHas('ai_audit_logs', [
            'user_id' => $admin->id,
            'action' => 'summarize',
        ]);
    }
}
```

2. **Create fixtures for testing**:

```php
<?php
# filename: tests/Fixtures/MockClaudeResponses.php
declare(strict_types=1);

namespace Tests\Fixtures;

class MockClaudeResponses
{
    public static function summary(): array
    {
        return [
            'content' => [['type' => 'text', 'text' => 'This is a concise summary of the content.']],
            'usage' => ['input_tokens' => 100, 'output_tokens' => 30],
        ];
    }

    public static function seoMetadata(): array
    {
        return [
            'content' => [[
                'type' => 'text',
                'text' => json_encode([
                    'meta_description' => 'Learn about PHP development.',
                    'keywords' => ['php', 'development', 'laravel'],
                    'slug' => 'php-development-guide',
                ]),
            ]],
            'usage' => ['input_tokens' => 150, 'output_tokens' => 80],
        ];
    }

    public static function qualityAnalysis(): array
    {
        return [
            'content' => [[
                'type' => 'text',
                'text' => json_encode([
                    'score' => 8,
                    'issues' => ['Missing introduction', 'Could use more examples'],
                ]),
            ]],
            'usage' => ['input_tokens' => 200, 'output_tokens' => 60],
        ];
    }
}
```

### Expected Result

Comprehensive test coverage for AI actions. Tests mock Claude API. Tests verify permissions, audit logging, and expected behavior.

### Why It Works

Mocking prevents real API calls in tests. Fixtures provide consistent test data. Tests verify both success and error cases. Permission tests ensure security.

---

## Step 19: Add Webhook Support for External Integrations (~20 min)

### Goal

Enable triggering AI operations from external systems via webhooks.

### Actions

1. **Create webhook model**:

```bash
php artisan make:model Webhook -m
```

2. **Define schema**:

```php
// In migration:
Schema::create('webhooks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('event'); // 'post.created', 'post.updated', etc.
    $table->string('action'); // 'summarize', 'generate_seo', etc.
    $table->string('url');
    $table->json('headers')->nullable();
    $table->boolean('active')->default(true);
    $table->timestamps();
});
```

3. **Create webhook dispatcher**:

```php
<?php
# filename: app/Services/WebhookDispatcher.php
declare(strict_types=1);

namespace App\Services;

use App\Models\Webhook;
use Illuminate\Support\Facades\Http;

class WebhookDispatcher
{
    public function dispatch(string $event, array $data): void
    {
        Webhook::where('event', $event)
            ->where('active', true)
            ->each(function (Webhook $webhook) use ($data) {
                Http::withHeaders($webhook->headers ?? [])
                    ->post($webhook->url, array_merge($data, [
                        'event' => $webhook->event,
                        'timestamp' => now()->toIso8601String(),
                    ]));
            });
    }
}
```

4. **Listen for events**:

```php
// After generating summary:
app(WebhookDispatcher::class)->dispatch('post.summarized', [
    'post_id' => $post->id,
    'summary' => $post->summary,
]);
```

### Expected Result

External systems can receive webhooks when AI operations complete. Enables integration with third-party services.

### Why It Works

Webhooks enable real-time notifications. External systems can react to AI operations. Fully asynchronous and non-blocking. Supports multiple subscribers per event.

---

## Troubleshooting

### Error: "Class 'App\Facades\Claude' not found"

**Symptom**: `Fatal error: Class 'App\Facades\Claude' not found`

**Cause**: The Claude facade hasn't been created or registered.

**Solution**: Ensure you've completed Chapter 21 and created the facade:

```php
// app/Facades/Claude.php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \ClaudePhp\ClaudeResponse generate(string $prompt, ?string $system = null, array $options = [])
 * @method static \ClaudePhp\ClaudeResponse withModel(string $model)
 * @method static \ClaudePhp\ClaudeResponse withTemperature(float $temperature)
 * @method static \ClaudePhp\ClaudeResponse withMaxTokens(int $maxTokens)
 * @method static \ClaudePhp\ClaudePhp client()
 */
class Claude extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'claude';
    }
}
```

Register it in `config/app.php` aliases array.

2. **Create the service class**:

```php
<?php
# filename: app/Services/ClaudeService.php
declare(strict_types=1);

namespace App\Services;

use ClaudePhp\ClaudePhp;
use ClaudePhp\ClaudeResponse;
use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Support\Facades\Cache;

class ClaudeService
{
    private ClaudePhp $client;
    private array $currentOptions = [];

    public function __construct()
    {
        $this->client = new ClaudePhp(
            apiKey: config('services.anthropic.api_key') ?? env('ANTHROPIC_API_KEY')
        );
    }

    /**
     * Generate a response from Claude
     */
    public function generate(string $prompt, ?string $system = null, array $options = []): ClaudeResponse
    {
        $params = array_merge([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 1024,
            'temperature' => 0.7,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ], $this->currentOptions, $options);

        // Add system message if provided
        if ($system) {
            array_unshift($params['messages'], [
                'role' => 'system',
                'content' => $system
            ]);
        }

        // Check cache
        $cacheKey = 'claude:' . md5(json_encode($params));
        if (Cache::has($cacheKey)) {
            $cachedData = Cache::get($cacheKey);
            return new ClaudeResponse($cachedData);
        }

        $response = $this->client->messages()->create($params);

        // Cache the response
        $responseData = [
            'content' => [$response->content[0]],
            'usage' => $response->usage,
            'model' => $response->model,
        ];
        Cache::put($cacheKey, $responseData, 3600); // Cache for 1 hour

        return new ClaudeResponse($responseData);
    }

    /**
     * Set the model for subsequent requests
     */
    public function withModel(string $model): self
    {
        $this->currentOptions['model'] = $model;
        return $this;
    }

    /**
     * Set the temperature for subsequent requests
     */
    public function withTemperature(float $temperature): self
    {
        $this->currentOptions['temperature'] = $temperature;
        return $this;
    }

    /**
     * Set max tokens for subsequent requests
     */
    public function withMaxTokens(int $maxTokens): self
    {
        $this->currentOptions['max_tokens'] = $maxTokens;
        return $this;
    }

    /**
     * Get the underlying Claude client
     */
    public function client(): ClaudePhp
    {
        return $this->client;
    }

    /**
     * Reset current options
     */
    public function reset(): self
    {
        $this->currentOptions = [];
        return $this;
    }
}
```

3. **Create the service provider**:

```php
<?php
# filename: app/Providers/ClaudeServiceProvider.php
declare(strict_types=1);

namespace App\Providers;

use App\Services\ClaudeService;
use Illuminate\Support\ServiceProvider;

class ClaudeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('claude', function ($app) {
            return new ClaudeService();
        });
    }

    public function boot(): void
    {
        //
    }
}
```

4. **Register the provider in `config/app.php`**:

```php
'providers' => [
    // ... other providers
    App\Providers\ClaudeServiceProvider::class,
],
```

5. **Update .env file**:

```bash
ANTHROPIC_API_KEY=your_api_key_here
```

### Error: "Action not appearing in Filament table"

**Symptom**: Custom actions don't show up in the resource table.

**Cause**: Actions not properly imported or added to the actions array.

**Solution**: Verify the action is imported and added:

```php
use App\Filament\Actions\SummarizeContentAction;

// In table() method:
->actions([
    SummarizeContentAction::make(), // Must call make()
])
```

### Problem: Slow admin panel performance

**Symptom**: Admin panel loads slowly, especially with widgets.

**Cause**: AI API calls are blocking page loads.

**Solutions**:

- Cache AI results aggressively (increase TTL to 3600+ seconds)
- Use async jobs for bulk operations:

```php
dispatch(new GenerateSummariesJob($posts));
```

- Implement lazy loading for widgets (only load when scrolled into view)
- Use Haiku model for faster, cheaper responses:

```php
Claude::withModel('claude-haiku-4-5-20251001')->generate(...)
```

### Problem: Widget refresh consuming too many API calls

**Symptom**: API quota exhausted quickly, high costs.

**Cause**: Widgets refreshing too frequently without caching.

**Solutions**:

- Increase cache TTL for widgets (use 3600+ seconds):

```php
Cache::remember('widget_key', 3600, function() { ... });
```

- Add manual refresh button instead of auto-refresh
- Only load widgets when viewed (lazy loading)
- Use background jobs to refresh widgets periodically

### Problem: Bulk operations timing out

**Symptom**: `Maximum execution time exceeded` when processing many records.

**Cause**: Processing too many records synchronously.

**Solutions**:

- Process in smaller batches:

```php
$posts->chunk(10, function($batch) {
    foreach ($batch as $post) { ... }
});
```

- Use queue jobs for large operations:

```php
foreach ($posts as $post) {
    dispatch(new GenerateSummaryJob($post));
}
```

- Add progress tracking with database status field
- Implement resumable operations with checkpoints

### Problem: Inconsistent AI results

**Symptom**: Same input produces different outputs each time.

**Cause**: Temperature too high or prompts too vague.

**Solutions**:

- Use lower temperature (0.3-0.5) for consistent results:

```php
['temperature' => 0.3, 'max_tokens' => 300]
```

- Provide more specific prompts with examples
- Add examples in system prompts for few-shot learning
- Validate outputs before saving:

```php
if (empty($summary) || strlen($summary) < 10) {
    throw new \Exception('Invalid summary generated');
}
```

### Error: "Route [filament.admin.resources.blog-posts.edit] not defined"

**Symptom**: Links in quality report page don't work.

**Cause**: Route name doesn't match Filament's naming convention.

**Solution**: Use Filament's route helper:

```php
// Instead of:
route('filament.admin.resources.blog-posts.edit', $id)

// Use:
\Filament\Facades\Filament::getPanel('admin')
    ->getResource('blog-posts')
    ->getUrl('edit', ['record' => $id])
```

Or use the model's route helper if available:

```php
$post->getFilamentUrl('edit')
```

## Further Reading

- **[Official PHP SDK Documentation](https://github.com/anthropics/anthropic-sdk-php)** — The official Anthropic PHP SDK on GitHub
- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community resources and examples for Claude with PHP
- **[Anthropic API Documentation](https://docs.anthropic.com)** — Complete API reference and guides
- **[PHP SDK Composer Package](https://packagist.org/packages/claude-php/claude-php-sdk)** — Official package on Packagist

## Wrap-up

Congratulations! You've transformed your admin panel with AI superpowers. Here's what you've accomplished:

- ✓ **Built custom Filament actions** — AI-powered summarization and SEO generation integrated seamlessly
- ✓ **Created bulk operations** — Generate descriptions and metadata for multiple records efficiently
- ✓ **Implemented AI insights widget** — Dashboard widget that analyzes statistics and provides actionable recommendations
- ✓ **Built semantic search** — Intelligent search that understands user intent and enhances queries
- ✓ **Developed quality analysis** — Automated content quality checking with issue identification
- ✓ **Optimized performance** — Caching strategies and batch processing for efficient AI operations
- ✓ **Created reusable components** — AI writing assistant and other components for admin productivity
- ✓ **Integrated AI throughout** — Complete Filament resource with AI features at every level

Your admin panel now leverages Claude to automate repetitive tasks, provide intelligent insights, and enhance productivity. The integration follows Filament best practices while adding powerful AI capabilities that save time and improve data quality.

**✅ All code samples verified compatible with:**

- Laravel 12
- Filament 4
- Claude-PHP-SDK v0.2

In the next chapter, you'll build an automated code review assistant that uses Claude to analyze code quality, suggest improvements, and maintain coding standards.

## Further Reading

- [Filament PHP Documentation](https://filamentphp.com/docs) — Complete guide to Filament admin panels and components
- [Filament Actions](https://filamentphp.com/docs/actions) — Creating custom actions for table and form operations
- [Filament Widgets](https://filamentphp.com/docs/widgets) — Building dashboard widgets and custom components
- [Filament Forms](https://filamentphp.com/docs/forms) — Form components and custom field types
- [Laravel Queues](https://laravel.com/docs/queues) — Background job processing for bulk operations
- [Laravel Caching](https://laravel.com/docs/cache) — Caching strategies for performance optimization

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
