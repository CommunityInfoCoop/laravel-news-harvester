<?php

namespace CommunityInfoCoop\NewsHarvester\Actions\CrowdTangle;

use ChrisHardie\CrowdtangleApi\Client;
use CommunityInfoCoop\NewsHarvester\Models\Feed;
use CommunityInfoCoop\NewsHarvester\Models\Source;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshFeedsAction
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(config('news-harvester.modules.crowdtangle.api_token'));
    }

    public function execute(): void
    {
        // Every feed needs a source, so we get or create one for importing purposes.
        $source = $this->getSourceForNewFeeds();
        // Get page and group lists for the CrowdTangle account
        $lists = $this->getLists();

        $accounts_total_count = 0;
        $feeds_created_count = 0;

        // For each list, get accounts in the list
        $lists->each(function ($list) use ($source, &$accounts_total_count, &$feeds_created_count) {
            $accounts = $this->client->getAccountsForList($list['id']);

            // For each account, add a feed if it doesn't already exist, leave it alone if it does
            // TODO find and delete feeds/accounts not included in CrowdTangle lists?
            foreach ($accounts as $account) {
                $accounts_total_count++;

                // If there's no feed with this page/group ID of the same type
                if (! Feed::where('location', '=', $account['platformId'])
                    ->where('type', '=', $this->getFeedTypeForAccountType($account['accountType']))
                    ->exists()) {
                    // Then create the feed using the default source for new feeds
                    $source->feeds()->create([
                        'location' => $account['platformId'],
                        'type'     => $this->getFeedTypeForAccountType($account['accountType']),
                        'name'     => $account['name'],
                    ]);

                    $feeds_created_count++;
                }
            }
        });

        Log::debug(sprintf(
            'CrowdTangle refresh: found %d total Facebook accounts, created %s new local feeds.',
            $accounts_total_count,
            $feeds_created_count
        ));
    }

    /**
     * Provide a local version of the getLists API call that filters by list type (LIST)
     * and caches the result for 2 hours so we don't hit the CrowdTangle API too often.
     *
     * @return mixed
     */
    private function getLists(): mixed
    {
        return Cache::remember('crowdtangle_lists', 60 * 60 * 2, function () {
            $lists = $this->client->getLists();
            return collect($lists)->where('type', '=', 'LIST');
        });
    }

    /**
     * @param int $listId
     * @return array
     */
    private function getAccountsForList(int $listId): array
    {
        return $this->client->getAccountsForList($listId);
    }

    private function getFeedTypeForAccountType(string $accountType): string|null
    {
        if (! empty(config('news-harvester.select_options.feed_types')[$accountType])) {
            return $accountType;
        }

        return null;
    }

    /**
     * Find or create a source that new feeds can be assigned to
     * @return Source
     */
    private function getSourceForNewFeeds(): Source
    {
        if (! empty(config('news-harvester.modules.crowdtangle.default_source_id'))) {
            $source = Source::find(config('news-harvester.modules.crowdtangle.default_source_id'));
            if ($source) {
                return $source;
            }
        }
        return Source::firstOrCreate(
            ['name' => 'Default Source for CrowdTangle Feeds'],
            ['internal_notes' => 'Automatically created by CrowdTangle account refresh action']
        );
    }
}
