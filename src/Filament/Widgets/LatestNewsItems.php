<?php

namespace CommunityInfoCoop\NewsHarvester\Filament\Widgets;

use Closure;
use CommunityInfoCoop\NewsHarvester\Models\NewsItem;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestNewsItems extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = null;

    protected static ?string $heading = 'Latest News';

    protected function getTableQuery(): Builder
    {
        return NewsItem::query()->latest('feed_timestamp')->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('title')
                ->label('')
                ->limit(70)
                ->url(fn (NewsItem $record): string => $record->url)
                ->openUrlInNewTab(),
        ];
    }

    protected function getTableRecordsPerPage(): int
    {
        return 5;
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
