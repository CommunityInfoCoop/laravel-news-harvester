<?php

namespace CommunityInfoCoop\NewsHarvester\Jobs;

use CommunityInfoCoop\NewsHarvester\FeedHandlers\BaseBulkFeedHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckBulkFeedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $bulkFeedClassName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $bulkFeedClassName)
    {
        $this->bulkFeedClassName = $bulkFeedClassName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $feed_class_base_path = "CommunityInfoCoop\NewsHarvester\FeedHandlers\\";

        $bulk_feed_class_name = Str::ucfirst(Str::camel($this->bulkFeedClassName));
        $bulk_feed_class_path = $feed_class_base_path . $bulk_feed_class_name . '\\' . $bulk_feed_class_name;

        if (class_exists($bulk_feed_class_path)) {
            $bulk_feed_class = new $bulk_feed_class_path();

            if (! $bulk_feed_class instanceof BaseBulkFeedHandler) {
                Log::warning('Feed handler class found but not a bulk handler, exiting.');
                return;
            }

            try {
                $items = $bulk_feed_class->getFeedItems();
            } catch (\Exception $e) {
                report($e);
            }
        } else {
            Log::warning('No feed handler class found for bulk class name ' . $this->bulkFeedClassName);
            return;
        }
    }
}
