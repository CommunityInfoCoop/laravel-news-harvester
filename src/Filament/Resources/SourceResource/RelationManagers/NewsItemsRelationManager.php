<?php

namespace CommunityInfoCoop\NewsHarvester\Filament\Resources\SourceResource\RelationManagers;

use CommunityInfoCoop\NewsHarvester\Models\NewsItem;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\HasManyThroughRelationManager;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\ButtonAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ViewColumn;

class NewsItemsRelationManager extends HasManyThroughRelationManager
{
    protected static string $relationship = 'newsItems';

    protected static ?string $recordTitleAttribute = 'title';

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
                ViewColumn::make('summary')->view('news-harvester::filament.tables.columns.news-item-summary')
                    ->url(fn (NewsItem $record): string => $record->url)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('feed_timestamp', 'desc')
            ->actions([]);
    }
}
