<?php

namespace CommunityInfoCoop\NewsHarvester\Filament\Widgets;

use CommunityInfoCoop\NewsHarvester\Models\NewsItem;
use Filament\Widgets\LineChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class NewsItemChart extends LineChartWidget
{
    public ?string $filter = '7';

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = null;

    protected function getHeading(): string
    {
        return 'News Items';
    }

    protected function getFilters(): ?array
    {
        return [
            7 => 'Last 7 Days',
            30 => 'Last 30 Days',
            90 => 'Last 90 Days',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $data = Trend::model(NewsItem::class)
            ->between(
                start: now()->subDays((int) $activeFilter),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'News items found',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }
}
