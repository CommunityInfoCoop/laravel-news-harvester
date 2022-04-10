<?php

namespace CommunityInfoCoop\NewsHarvester\Filament\Widgets;

use CommunityInfoCoop\NewsHarvester\Models\Feed;
use CommunityInfoCoop\NewsHarvester\Models\NewsItem;
use CommunityInfoCoop\NewsHarvester\Models\Source;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 5;

    protected function getCards(): array
    {
        return [
            Card::make(
                'Last 24h on Starred Feeds',
                NewsItem::where('created_at', '<=', Carbon::now()->subHours(24))
                    ->whereHas('feed', function (Builder $query) {
                        return $query->starred();
                    })
                    ->count()
            ),
            Card::make('Sources', Source::active()->count()),
            Card::make('Feeds', Feed::active()->count()),
            Card::make('Failing Feeds', Feed::failing()->count()),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
