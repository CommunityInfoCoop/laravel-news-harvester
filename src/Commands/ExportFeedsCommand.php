<?php

namespace CommunityInfoCoop\NewsHarvester\Commands;

use Celd\Opml\Importer;
use Celd\Opml\Model\FeedList;
use CommunityInfoCoop\NewsHarvester\Models\Feed;
use Illuminate\Console\Command;

class ExportFeedsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsharvest:export-feeds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export RSS feeds in OPML format';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $feedList = new FeedList();

        $feeds = Feed::where('type', '=', 'rss')->with('source')->get();

        foreach ($feeds as $feed) {
            $export_item = new \Celd\Opml\Model\Feed();
            $export_item->setTitle(sprintf('%s (%s)', $feed->name, $feed->source->name));
            $export_item->setXmlUrl($feed->location);
            $export_item->setType('rss');
            $export_item->setHtmlUrl($feed->source->url);
            $feedList->addItem($export_item);
        }

        $exporter = new Importer();
        echo $exporter->export($feedList);

        return 0;
    }
}
