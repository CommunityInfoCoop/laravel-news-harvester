<?php

namespace CommunityInfoCoop\NewsHarvester\Filament\Resources\NewsItemResource\Pages;

use CommunityInfoCoop\NewsHarvester\Filament\Resources\NewsItemResource;
use Filament\Resources\Pages\ListRecords;

class ListNewsItems extends ListRecords
{
    protected static string $resource = NewsItemResource::class;
}
