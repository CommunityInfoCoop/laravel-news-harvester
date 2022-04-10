<?php

namespace CommunityInfoCoop\NewsHarvester\Commands;

use Celd\Opml\Importer;
use CommunityInfoCoop\NewsHarvester\Models\Feed;
use CommunityInfoCoop\NewsHarvester\Models\Source;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

class ImportFeedsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsharvest:import-feeds {import_file : the OPML filename to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import RSS Feeds from an OPML file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filename = $this->argument('import_file');

        if (! file_exists($filename) || ! is_readable($filename)) {
            $this->error('Import file is not readable.');
            return Command::FAILURE;
        }

        try {
            $importer = new Importer(file_get_contents($filename));
            $feedList = $importer->getFeedList();
        } catch (\Exception $e) {
            $this->error('File does not contain valid OPML content');
            return Command::FAILURE;
        }

        $this->info('Processing feeds in file...');

        $feedAddCount = 0;
        $feedExistingCount = 0;

        foreach ($feedList->getItems() as $item) {
            if ($item->getType() === 'category') {
                $this->info('Processing category ' . $item->getTitle());

                foreach ($item->getFeeds() as $feed) {
                    $this->info('Processing feed ' . $feed->getTitle());
                    $feedAdded = $this->maybeAddFeed($feed);
                    $feedAdded ? $feedAddCount++ : $feedExistingCount++;
                }
            }
        }

        $this->info(
            sprintf(
                'Added %d %s and found %d existing %s',
                $feedAddCount,
                Str::plural('feed', $feedAddCount),
                $feedExistingCount,
                Str::plural('feed', $feedExistingCount)
            )
        );

        return 0;
    }

    private function maybeAddFeed(\Celd\Opml\Model\Feed $feed): bool
    {
        if (! $this->feedUrlExists($feed->getXmlUrl())) {
            $source = $this->getOrCreateSource($feed);
            $source->feeds()->create([
                'name' => $feed->getTitle(),
                'type' => 'rss',
                'location' => $feed->getXmlUrl(),
                'check_frequency' => config('news-harvester.feeds.check_frequency'),
            ]);
            return true;
        }
        return false;
    }

    private function feedUrlExists(string $url): bool
    {
        $compare_url = Str::of($url)->trim()->lower();
        return Feed::where('type', '=', 'rss')->where('location', '=', $compare_url)->exists();
    }

    private function getOrCreateSource(\Celd\Opml\Model\Feed $feed): Source
    {
        $source_url = Str::of($feed->getHtmlUrl())->trim()->lower();
        return Source::firstOrCreate(
            ['url' => $source_url],
            ['name' => $feed->getTitle()]
        );
    }
}
