<?php

namespace CommunityInfoCoop\NewsHarvester\FeedHandlers;

use CommunityInfoCoop\NewsHarvester\Models\Feed;
use CommunityInfoCoop\NewsHarvester\Models\NewsItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

abstract class BaseBulkFeedHandler extends BaseFeedHandler
{
    // What Feed types does this bulk handler support
    public array $feedTypes;

    abstract public function getFeedItems();

    public function __construct()
    {
        $this->updateLastChecked(
            Feed::whereIn('type', $this->feedTypes)
                ->active()
                ->pluck('id')
                ->toArray()
        );
    }

    /**
     * Check if a news item for the supported types of feeds already exists as a news item
     * @param string $external_id
     * @return bool
     */
    public function newsItemExists(string $external_id): bool
    {
        return NewsItem::where('external_id', '=', $external_id)
            ->whereHas('feed', function (Builder $query) {
                $query->whereIn('type', $this->feedTypes);
            })
            ->exists();
    }
}
