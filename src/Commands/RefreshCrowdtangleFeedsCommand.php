<?php

namespace CommunityInfoCoop\NewsHarvester\Commands;

use CommunityInfoCoop\NewsHarvester\Actions\CrowdTangle\RefreshFeedsAction;
use Illuminate\Console\Command;

class RefreshCrowdtangleFeedsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsharvest:crowdtangle-feeds-refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Facebook Pages and Groups from CrowdTangle';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(RefreshFeedsAction $refreshFeedsAction)
    {
        if (empty(config('news-harvester.modules.crowdtangle.api_token'))) {
            $this->warn('No CrowdTangle API key configured, exiting.');
            return self::FAILURE;
        }

        $refreshFeedsAction->execute();

        return self::SUCCESS;
    }
}
