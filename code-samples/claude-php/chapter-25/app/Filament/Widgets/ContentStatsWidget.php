<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Content;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Content', Content::count())
                ->description('All content items')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('success'),

            Stat::make('Published', Content::where('status', 'published')->count())
                ->description('Live content')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Under Review', Content::where('status', 'review')->count())
                ->description('Pending approval')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Drafts', Content::where('status', 'draft')->count())
                ->description('Work in progress')
                ->descriptionIcon('heroicon-o-pencil')
                ->color('gray'),
        ];
    }
}
