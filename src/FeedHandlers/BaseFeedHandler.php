<?php

namespace CommunityInfoCoop\NewsHarvester\FeedHandlers;

use CommunityInfoCoop\NewsHarvester\Models\Feed;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class BaseFeedHandler
{
    /**
     * @param Feed     $feed
     * @param int|null $new_item_count
     * @return void
     */
    protected function updateLastSuccess(Feed $feed, ?int $new_item_count = null): void
    {
        if (0 < $feed->fail_count) {
            Log::info(sprintf(
                'Updating items for feed `%s` was successful after %d %s.',
                $feed->name,
                $feed->fail_count,
                Str::plural('failure', $feed->fail_count)
            ));
        }

        $feed_update_attributes = [
            'last_succeed_at' => Carbon::now(),
            'fail_count' => 0,
            'next_check_after' => null,
        ];

        if ($new_item_count > 0) {
            $feed_update_attributes['last_new_item_at'] = Carbon::now();
        }

        $feed->update($feed_update_attributes);
    }

    protected function updateLastChecked(array $feed_ids): void
    {
        Feed::whereIn('id', $feed_ids)->update([
            'last_check_at' => now(),
        ]);
    }
}
