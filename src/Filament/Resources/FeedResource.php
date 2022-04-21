<?php

namespace CommunityInfoCoop\NewsHarvester\Filament\Resources;

use App\Filament\Resources\SourceResource\Pages\CreateSource;
use Closure;
use CommunityInfoCoop\NewsHarvester\Filament\Resources\FeedResource\Pages;
use CommunityInfoCoop\NewsHarvester\Filament\Resources\FeedResource\RelationManagers;
use CommunityInfoCoop\NewsHarvester\Jobs\CheckFeedJob;
use CommunityInfoCoop\NewsHarvester\Models\Feed;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\IconButtonAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FeedResource extends Resource
{
    protected static ?string $model = Feed::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-rss';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->placeholder('Name of this feed'),
                BelongsToSelect::make('source_id')->relationship('source', 'name')->searchable()->required(),
                Select::make('type')
                    ->options(config('news-harvester.select_options.feed_types'))
                    ->reactive()
                    ->required()
                    ->default('rss')
                    ->afterStateUpdated(function (Closure $set, $state) {
                        $set('check_frequency', match ($state) {
                            'twitter_account' => 5,
                            'facebook_page' => 720,
                            'facebook_group' => 720,
                            'google_calendar' => 720,
                            default => config('news-harvester.feeds.check_frequency'),
                        });
                    }),
                TextInput::make('location')
                    ->required()
                    ->url(fn (Closure $get) => $get('type') === 'rss')
                    ->label(function (Closure $get) {
                        return match ($get('type')) {
                            'rss' => 'Feed URL',
                            'twitter_account' => 'Twitter Username',
                            'facebook_page' => 'Facebook Page ID',
                            'facebook_group' => 'Facebook Group ID',
                            'google_calendar' => 'Google Calendar ID',
                            default => null,
                        };
                    })
                    ->placeholder(function (Closure $get) {
                        return match ($get('type')) {
                            'rss' => 'https://...',
                            'twitter_account' => 'Jack',
                            'facebook_page' => '123456789',
                            'facebook_group' => '123456789',
                            'google_calendar' => 'calendar@gmail.com',
                            default => null,
                        };
                    })
                    ->prefix(function (Closure $get) {
                        return match ($get('type')) {
                            'twitter_account' => '@',
                            default => null,
                        };
                    })
                    ->helperText(function (Closure $get) {
                        return match ($get('type')) {
                            'facebook_page' => '[Look it up at lookup-id.com](https://lookup-id.com/)',
                            'facebook_group' => '[Look it up at lookup-id.com](https://lookup-id.com/)',
                            default => null,
                        };
                    }),
                Textarea::make('internal_notes'),
                Grid::make()->schema([
                    TextInput::make('check_frequency')->numeric()->minValue(5)->maxValue(10080)
                        ->default(config('news-harvester.feeds.check_frequency'))
                        ->label('Update frequency')
                        ->hint("How often, in minutes, to check the feed."),
                    Checkbox::make('is_active')->default(true)->label('Active?'),
                    Checkbox::make('is_starred')->default(false)->label('Starred?'),
                    Checkbox::make('is_admin_paused')->label('Checks Paused?'),
                ])->columnSpan(1)->columns(1),
                Section::make('Activity')
                    ->schema([
                        TextInput::make('last_check_at')->disabled(),
                        TextInput::make('last_succeed_at')->disabled(),
                        TextInput::make('last_fail_at')->disabled(),
                        TextInput::make('next_check_after')->disabled(),
                        TextInput::make('last_fail_reason')->disabled(),
                        TextInput::make('fail_count')->disabled(),
                    ])
                    ->columns(2)
                    ->collapsed()
                    ->hidden(fn ($livewire): bool => $livewire instanceof Pages\CreateFeed),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->limit(40),
                TextColumn::make('type')->enum(config('news-harvester.select_options.feed_types'))->sortable(),
                TextColumn::make('source.name')->label('Source')->limit(20),
                BadgeColumn::make('last_check_at')->label('Checked')->dateTime()->sortable()
                    ->formatStateUsing(
                        function (?string $state) {
                            if (empty($state)) {
                                return 'Never';
                            } else {
                                return Carbon::parse($state)->shortAbsoluteDiffForHumans();
                            }
                        }
                    )
                    ->colors([
                        'success',
                        'warning' => fn ($state): bool => Carbon::now()->subDays(1)->greaterThan($state),
                        'danger' => fn ($state): bool => Carbon::now()->subDays(3)->greaterThan($state),
                    ]),
                BadgeColumn::make('last_new_item_at')->label('Last New')->dateTime()->sortable()
                    ->formatStateUsing(
                        function (?string $state, Feed $record) {
                            if ($record->fail_count > 0) {
                                return 'Failing';
                            } elseif (empty($state)) {
                                return 'N/A';
                            } else {
                                return Carbon::parse($state)->shortAbsoluteDiffForHumans();
                            }
                        }
                    )
                    ->colors([
                        'success',
                        'warning' => fn ($state): bool => Carbon::now()->subDays(30)
                            ->greaterThan($state),
                        'danger' => fn ($state, Feed $record): bool => $record->fail_count > 0
                        || Carbon::now()->subDays(60)->greaterThan($state),
                    ]),
                BooleanColumn::make('is_active')->label('Active?'),
            ])
            ->prependActions([
                IconButtonAction::make('check')
                    ->label('Check')
                    ->action(fn (Feed $record) =>
                        CheckFeedJob::dispatch($record)
                        && Filament::notify('success', 'Checking Feed'))
                    ->icon('heroicon-o-refresh')
                    ->visible(fn (Feed $record) => $record->type === 'rss'),
            ])
            ->bulkActions([
                BulkAction::make('check')
                    ->label('Check Feed(s) for Updates')
                    ->action(fn (Collection $records) => $records->each(fn ($feed) => CheckFeedJob::dispatch($feed)))
            ])
            ->filters([
                Filter::make('active')->label('Only Active')->default()
                    ->query(fn (Builder $query): Builder => $query->active()),
                Filter::make('failing')->label('Failing')
                    ->query(fn (Builder $query): Builder => $query->failing()),
                Filter::make('stale')->label('Stale')
                    ->query(fn (Builder $query): Builder => $query->stale()),
                SelectFilter::make('type')
                    ->options(config('news-harvester.select_options.feed_types')),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\NewsItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeeds::route('/'),
            'create' => Pages\CreateFeed::route('/create'),
            'edit' => Pages\EditFeed::route('/{record}/edit'),
        ];
    }
}
