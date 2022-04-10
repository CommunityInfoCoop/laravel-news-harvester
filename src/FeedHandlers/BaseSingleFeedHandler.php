<?php

namespace CommunityInfoCoop\NewsHarvester\FeedHandlers;

use CommunityInfoCoop\NewsHarvester\Models\Feed;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class BaseSingleFeedHandler extends BaseFeedHandler
{
    public Feed $feed;

    abstract public function getFeedItems(): NewsItemCollection;

    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
        $this->updateLastChecked([$this->feed->id]);
    }

    /**
     * @param NewsItemCollection $newsItems
     */
    public function saveFeedItems(NewsItemCollection $newsItems): void
    {
        $newsItems->transform(function ($item) {
            // If there was no feed timestamp, set it to the created date of the item
            if (empty($item['feed_timestamp'])) {
                $item['feed_timestamp'] = $item['created_at'];
            }

            // Make sure feed timestamps are Carbon objects
            $item['feed_timestamp'] = Carbon::parse($item['feed_timestamp']);

            // If a feed says a thing happens more than 5 mins from now, set it to now instead
            if ($item['feed_timestamp']->greaterThan(Carbon::now()->addMinutes(5))) {
                $item['feed_timestamp'] = Carbon::now();
            }

            return $item;
        });

        $this->feed->newsItems()->createMany($newsItems);

        $this->updateLastSuccess($this->feed, $newsItems->count());
    }

    /**
     * @param string $external_id
     * @return bool
     */
    public function newsItemExists(string $external_id): bool
    {
        return $this->feed->newsItems()->where('external_id', '=', $external_id)->exists();
    }
}
