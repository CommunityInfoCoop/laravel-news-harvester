<?php

namespace CommunityInfoCoop\NewsHarvester\Filament\Resources\NewsItemResource\Pages;

use CommunityInfoCoop\NewsHarvester\Filament\Resources\NewsItemResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

class ListNewsItems extends ListRecords
{
    protected static string $resource = NewsItemResource::class;

    public function boot()
    {
        config(['filament.layout.max_content_width' => '3xl']);
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [25, 50, 100, 200];
    }

    protected function paginateTableQuery(Builder $query): Paginator
    {
        return $query->simplePaginate($this->getTableRecordsPerPage());
    }
}
