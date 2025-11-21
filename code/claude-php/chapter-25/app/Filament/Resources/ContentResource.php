<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Models\Content;
use App\Services\ClaudeService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Actions\Action;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->suffixAction(
                        Action::make('generateTitle')
                            ->icon('heroicon-o-sparkles')
                            ->action(function (Forms\Get $get, Forms\Set $set) {
                                $claude = app(ClaudeService::class);
                                $content = $get('body');
                                if ($content) {
                                    $title = $claude->generateTitle($content);
                                    $set('title', $title);
                                }
                            })
                    ),

                Forms\Components\RichEditor::make('body')
                    ->required()
                    ->columnSpanFull()
                    ->suffixActions([
                        Action::make('improve')
                            ->icon('heroicon-o-sparkles')
                            ->action(function (Forms\Get $get, Forms\Set $set) {
                                $claude = app(ClaudeService::class);
                                $improved = $claude->improveContent($get('body'));
                                $set('body', $improved);
                            }),
                        Action::make('summarize')
                            ->icon('heroicon-o-document-text')
                            ->action(function (Forms\Get $get, Forms\Set $set) {
                                $claude = app(ClaudeService::class);
                                $summary = $claude->summarize($get('body'));
                                $set('summary', $summary);
                            }),
                    ]),

                Forms\Components\Textarea::make('summary')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Select::make('category')
                    ->options([
                        'article' => 'Article',
                        'tutorial' => 'Tutorial',
                        'news' => 'News',
                    ]),

                Forms\Components\TagsInput::make('tags')
                    ->suggestions(function (Forms\Get $get) {
                        $claude = app(ClaudeService::class);
                        $body = $get('body');
                        return $body ? $claude->suggestTags($body) : [];
                    }),

                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'Under Review',
                        'published' => 'Published',
                    ])
                    ->default('draft'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'review' => 'warning',
                        'published' => 'success',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'review' => 'Review',
                        'published' => 'Published',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('analyze')
                    ->icon('heroicon-o-magnifying-glass')
                    ->action(function (Content $record) {
                        $claude = app(ClaudeService::class);
                        $analysis = $claude->analyzeContent($record->body);

                        \Filament\Notifications\Notification::make()
                            ->title('Content Analysis')
                            ->body($analysis)
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('checkSeo')
                    ->icon('heroicon-o-chart-bar')
                    ->action(function (Content $record) {
                        $claude = app(ClaudeService::class);
                        $seo = $claude->checkSeo($record->title, $record->body);

                        \Filament\Notifications\Notification::make()
                            ->title('SEO Score: ' . $seo['score'])
                            ->body($seo['suggestions'])
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('generateSummaries')
                        ->icon('heroicon-o-document-text')
                        ->action(function ($records) {
                            $claude = app(ClaudeService::class);
                            foreach ($records as $record) {
                                $summary = $claude->summarize($record->body);
                                $record->update(['summary' => $summary]);
                            }
                        }),

                    Tables\Actions\BulkAction::make('autoTag')
                        ->icon('heroicon-o-tag')
                        ->action(function ($records) {
                            $claude = app(ClaudeService::class);
                            foreach ($records as $record) {
                                $tags = $claude->suggestTags($record->body);
                                $record->update(['tags' => $tags]);
                            }
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
