<?php

namespace CommunityInfoCoop\NewsHarvester\Commands;

use CommunityInfoCoop\NewsHarvester\Jobs\CheckFeedJob;
use CommunityInfoCoop\NewsHarvester\Models\Feed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckFeedsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsharvest:check-feeds
        {--id= : the feed ID to check}
        {--type= : the type of feeds to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check news harvest feeds for updates';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        if (! empty($this->option('id'))) {
            $feeds = Feed::where('id', $this->option('id'))->get();
        } elseif ($this->option('type')) {
            $feeds = Feed::where('type', $this->option('type'))->get();
        } else {
            $feeds = Feed::checkable()->orderby('last_check_at')->get();
        }

        Log::debug('Checking ' . $feeds->count() . ' feeds for new items.');

        foreach ($feeds as $feed) {
            CheckFeedJob::dispatch($feed);
        }

        return self::SUCCESS;
    }
}
