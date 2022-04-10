<?php

namespace CommunityInfoCoop\NewsHarvester\Jobs;

use CommunityInfoCoop\NewsHarvester\FeedHandlers\BaseBulkFeedHandler;
use CommunityInfoCoop\NewsHarvester\FeedHandlers\BaseSingleFeedHandler;
use CommunityInfoCoop\NewsHarvester\Models\Feed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckFeedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Feed $feed;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $feed_class_base_path = "CommunityInfoCoop\NewsHarvester\FeedHandlers\\";

        $feed_class_name = Str::ucfirst(Str::camel($this->feed->type));
        $feed_class_path = $feed_class_base_path . $feed_class_name . '\\' . $feed_class_name;

        if (class_exists($feed_class_path)) {
            $feed_class = new $feed_class_path($this->feed);

            if (! $feed_class instanceof BaseSingleFeedHandler) {
                Log::warning('Feed handler class found but not a single feed handler, exiting.');
                return;
            }

            try {
                $items = $feed_class->getFeedItems();
                $feed_class->saveFeedItems($items);
            } catch (\Exception $e) {
                report($e);
            }
        } else {
            Log::warning('No feed handler class found for feed ' . $this->feed->name);
        }
    }
}
