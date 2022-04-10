<?php

namespace CommunityInfoCoop\NewsHarvester\Commands;

use Illuminate\Console\Command;

class NewsHarvesterCommand extends Command
{
    public $signature = 'laravel-news-harvester';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
