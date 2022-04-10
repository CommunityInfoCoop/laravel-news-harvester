<?php

namespace CommunityInfoCoop\NewsHarvester\FeedHandlers\CrowdTangle;

use ChrisHardie\CrowdtangleApi\Client;
use CommunityInfoCoop\NewsHarvester\Exceptions\FeedNotCheckable;
use CommunityInfoCoop\NewsHarvester\FeedHandlers\BaseBulkFeedHandler;
use CommunityInfoCoop\NewsHarvester\FeedHandlers\NewsItemCollection;
use CommunityInfoCoop\NewsHarvester\Models\Feed;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CrowdTangle extends BaseBulkFeedHandler
{
    private Client $client;

    public array $feedTypes = [
        'facebook_page',
        'facebook_group',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client(config('news-harvester.modules.crowdtangle.api_token'));
    }

    /**
     * Get the latest posts from Facebook and, if they don't already exist locally, create news items
     * @throws FeedNotCheckable
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getFeedItems(): void
    {
        try {
            $posts = $this->client->getPosts([
                'timeframe' => config('news-harvester.modules.crowdtangle.post_fetch_timeframe'),
            ]);
        } catch (\Exception $e) {
            // TODO need a different exception handler for bulk? Make feed param optional?
            throw new FeedNotCheckable(
                $e->getMessage(),
                0,
                $e,
                null
            );
        }

        // For each post, if it doesn't already exist, match it up to a feed
        foreach ($posts as $post) {
            $postId = $this->getPostIdFromPlatformId($post['platformId']);
            if (! $this->newsItemExists($postId)) {
                $feed = Feed::where('location', '=', $post['account']['platformId'])->first();
                if ($feed->exists()) {
                    // Make sure feed timestamps are Carbon objects
                    if (empty($post['date'])) {
                        $feed_timestamp = Carbon::now();
                    } else {
                        $feed_timestamp = Carbon::parse($post['date']);
                    }
                    $feed->newsItems()->create([
                        'title'          => $this->getTitle($post),
                        'url'            => $post['postUrl'],
                        'external_id'    => $postId,
                        'feed_timestamp' => $feed_timestamp,
                        'content'        => $post['message'],
                        'media_url'      => $this->getMediaUrl($post),
                    ]);
                    $this->updateLastSuccess($feed, 1);
                }
            }
        }
    }

    /**
     * Convert the concatenated platform ID format `<pageId>_<postID>` into just the post ID
     * @param string $platformId
     * @return int
     */
    private function getPostIdFromPlatformId(string $platformId): int
    {
        return Str::afterLast($platformId, '_');
    }

    /**
     * @param array $post
     * @return string
     */
    private function getTitle(array $post): string
    {
        if (! empty($post['message'])) {
            return Str::limit($post['message'], 75);
        }

        if (! empty($post['description'])) {
            return Str::limit($post['description'], 75);
        }

        if (! empty($post['account']['name'])) {
            return 'A post from ' . $post['account']['name'];
        }

        return 'A Facebook post';
    }

    /**
     * @param array $post
     * @return string|null
     */
    private function getMediaUrl(array $post): string|null
    {
        if (! empty($post['media'][0]) && 'photo' === $post['media'][0]['type']) {
            return $post['media'][0]['url'];
        }
        return null;
    }
}
