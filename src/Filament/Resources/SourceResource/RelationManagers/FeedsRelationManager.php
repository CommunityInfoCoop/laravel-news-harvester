<?php

namespace CommunityInfoCoop\NewsHarvester\Filament\Resources\SourceResource\RelationManagers;

use App\Filament\Resources\NewsItemResource;
use CommunityInfoCoop\NewsHarvester\Filament\Resources\FeedResource;
use Filament\Forms;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\HasManyRelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\ButtonAction;
use Filament\Tables\Actions\LinkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon;

class FeedsRelationManager extends HasManyRelationManager
{
    protected static string $relationship = 'feeds';

    protected static ?string $recordTitleAttribute = 'name';

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
                TextColumn::make('name')->searchable()->sortable()->limit(40),
                TextColumn::make('type')->enum(config('news-harvester.select_options.feed_types'))->sortable(),
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
                        function (?string $state) {
                            if (empty($state)) {
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
                        'danger' => fn ($state): bool => Carbon::now()->subDays(60)
                            ->greaterThan($state),
                    ]),
                BooleanColumn::make('is_active')->label('Active?'),
            ])
            ->headerActions([
                ButtonAction::make('create')
                    ->label('Create')
                    ->url(fn() => FeedResource::getUrl('create'))
                    ->visible(auth()->user()->can('create_feed')),
            ])
            ->actions([
                LinkAction::make('view')
                    ->url(fn ($record) => FeedResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye')
                    ->visible(fn ($record) => auth()->user()->can('view', $record)),
                LinkAction::make('edit')
                    ->icon('heroicon-o-pencil')
                    ->visible(fn ($record) => auth()->user()->can('update', $record))
                    ->url(fn ($record) => FeedResource::getUrl('edit', ['record' => $record]))
            ])
            ->filters([
                //
            ]);
    }
}
