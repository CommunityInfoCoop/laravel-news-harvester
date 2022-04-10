<?php

namespace CommunityInfoCoop\NewsHarvester\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CommunityInfoCoop\NewsHarvester\NewsHarvester
 */
class NewsHarvester extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-news-harvester';
    }
}
