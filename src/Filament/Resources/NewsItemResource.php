<?php

namespace CommunityInfoCoop\NewsHarvester\Filament\Resources;

use CommunityInfoCoop\NewsHarvester\Filament\Resources\NewsItemResource\Pages;
use CommunityInfoCoop\NewsHarvester\Filament\Resources\NewsItemResource\RelationManagers;
use CommunityInfoCoop\NewsHarvester\Models\NewsItem;
use CommunityInfoCoop\NewsHarvester\Models\Source;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables\Actions\ButtonAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\MultiSelectFilter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class NewsItemResource extends Resource
{
    protected static ?string $model = NewsItem::class;

    protected static ?string $label = 'Content Curation';

    protected static ?string $pluralLabel = 'Content Curation';

    protected static ?string $navigationLabel = 'Content Curation';

    protected static bool $isGloballySearchable = true;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'content'];
    }

    public static function getGlobalSearchResultUrl(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->url;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Source' => $record->source_info->name,
            'Published' => $record->publish_timestamp_relative,
        ];
    }

    protected static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['feed', 'source']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('')->getStateUsing(function (NewsItem $record) {
                    return optional($record->feed)->type;
                })
                    ->options([
                        'heroicon-o-collection',
                        'heroicon-o-rss' => 'rss',
                        'heroicon-o-chat-alt' => 'facebook_page',
                        'heroicon-o-chat-alt-2' => 'facebook_group',
                    ])
                    ->url(fn (NewsItem $record): string => $record->url)
                    ->openUrlInNewTab()
                    ->visibleFrom('sm'),
                ViewColumn::make('summary')->view('news-harvester::filament.tables.columns.news-item-summary')
                    ->url(fn (NewsItem $record): string => $record->url)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                // Time period
                SelectFilter::make('time_period')
                    ->options([
                        1 => 'Last Hour',
                        24 => 'Last 24 Hours',
                        36 => 'Last 36 Hours',
                        168 => 'Last 7 Days',
                        720 => 'Last 30 Days',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value']) {
                            return $query->where('feed_timestamp', '>=', Carbon::now()->subHours($data['value']));
                        }
                        return $query;
                    })
                    ->default('168'), // default to last 7 days
                // Feed types
                MultiSelectFilter::make('feed_type')
                    ->options(config('news-harvester.select_options.feed_types'))
                    ->query(function (Builder $query, array $data): Builder {
                        $types = $data['values'];
                        if ($types) {
                            return $query
                                ->whereHas('feed', function (Builder $query) use ($types) {
                                    $query->whereIn('harvest_feeds.type', $types);
                                });
                        }
                        return $query;
                    }),
                // Source types
                MultiSelectFilter::make('source_type')
                    ->options(config('news-harvester.select_options.source_types'))
                    ->query(function (Builder $query, array $data): Builder {
                        $types = $data['values'];
                        if ($types) {
                            return $query
                                ->whereHas('feed.source', function (Builder $query) use ($types) {
                                    $query->whereIn('harvest_sources.type', $types);
                                });
                        }
                        return $query;
                    }),
                // Search for a source
                MultiSelectFilter::make('source')
                    ->options(Source::all()->pluck('name', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        $source_ids = $data['values'];
                        if ($source_ids) {
                            return $query
                                ->whereHas('feed.source', function (Builder $query) use ($source_ids) {
                                    $query->whereIn('harvest_sources.id', $source_ids);
                                });
                        }
                        return $query;
                    }),
                // Top Sources Only
                Filter::make('Top Sources Only')
                    ->query(fn (Builder $query): Builder => $query->whereHas('feed.source', function (Builder $query) {
                        return $query->top();
                    }))
                    ->default(),
            ])
            ->defaultSort('feed_timestamp', 'desc')
            ->actions([
                ButtonAction::make('open')
                    ->label('Open')
                    ->url(fn (NewsItem $record): string => $record->url)
                    ->icon('heroicon-o-external-link')
                    ->openUrlInNewTab(),

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsItems::route('/'),
            'create' => Pages\CreateNewsItem::route('/create'),
            'edit' => Pages\EditNewsItem::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['feed.source']);
    }

}
