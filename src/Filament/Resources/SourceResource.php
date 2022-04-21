<?php

namespace CommunityInfoCoop\NewsHarvester\Filament\Resources;

use App\Filament\Resources\SourceResource\RelationManagers\NewsItemsRelationManager;
use CommunityInfoCoop\NewsHarvester\Filament\Resources\SourceResource\Pages;
use CommunityInfoCoop\NewsHarvester\Filament\Resources\SourceResource\RelationManagers;
use CommunityInfoCoop\NewsHarvester\Models\Source;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;

class SourceResource extends Resource
{
    protected static ?string $model = Source::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-office-building';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                TextInput::make('url')->url()->label('Website URL'),
                Select::make('type')->options(config('news-harvester.select_options.source_types')),
                Textarea::make('internal_notes'),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('type')->enum(config('news-harvester.select_options.source_types')),
            ])
            ->filters([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\NewsItemsRelationManager::class,
            RelationManagers\FeedsRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSources::route('/'),
            'create' => Pages\CreateSource::route('/create'),
            'edit' => Pages\EditSource::route('/{record}/edit'),
        ];
    }
}
