<?php

namespace CommunityInfoCoop\NewsHarvester\Commands;

use CommunityInfoCoop\NewsHarvester\Jobs\CheckBulkFeedJob;
use CommunityInfoCoop\NewsHarvester\Jobs\CheckFeedJob;
use CommunityInfoCoop\NewsHarvester\Models\Feed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckBulkFeedsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsharvest:check-bulk-feeds
            {--type= : the type of bulk feeds to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check news harvest bulk feeds for updates';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // If a single bulk feed handle type is specified, run that one
        if ($this->option('type')) {
            CheckBulkFeedJob::dispatch($this->option('type'));
            return self::SUCCESS;
        }

        // Unlike the single feed command where we derive feed types from what's in the DB,
        // here we will manually call the bulk feed handlers that we know might be supported.

        // CrowdTangle
        if (config('news-harvester.modules.crowdtangle.api_token')) {
            CheckBulkFeedJob::dispatch('CrowdTangle');
        }

        return self::SUCCESS;
    }
}
