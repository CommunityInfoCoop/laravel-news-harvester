<?php

namespace CommunityInfoCoop\NewsHarvester\FeedHandlers\Rss;

use CommunityInfoCoop\NewsHarvester\Exceptions\FeedNotCheckable;
use CommunityInfoCoop\NewsHarvester\FeedHandlers\BaseSingleFeedHandler;
use CommunityInfoCoop\NewsHarvester\FeedHandlers\NewsItemCollection;
use Illuminate\Support\Facades\Log;
use SimplePie_Item;
use Symfony\Component\DomCrawler\Crawler;
use willvincent\Feeds\Facades\FeedsFacade;

class Rss extends BaseSingleFeedHandler
{
    /**
     * @return NewsItemCollection
     * @throws FeedNotCheckable
     */
    public function getFeedItems(): NewsItemCollection
    {
        $newsItems = array();
        $feed_options = array(
            'curl.timeout' => config('news-harvester.feeds.fetch_timeout'),
        );
        $feedReader = FeedsFacade::make($this->feed->url, 0, false, $feed_options);

        // If there was a problem reading the feed, throw an exception and return.
        if ($feedReader->error()) {
            throw new FeedNotCheckable(
                $feedReader->error(),
                0,
                null,
                $this->feed
            );
        }

        // If we ended up at a different URL than the one we intended, note that.
        if ($feedReader->feed_url !== $this->feed->url) {
            Log::notice(sprintf(
                'Feed `%s` is redirected from `%s` to `%s`.',
                $this->feed->name,
                $this->feed->url,
                $feedReader->feed_url
            ));
        }

        foreach ($feedReader->get_items() as $item) {
            if (! $this->newsItemExists($item->get_id())) {
                $newsItems[] = [
                    'title'          => htmlspecialchars_decode(html_entity_decode($item->get_title()), ENT_QUOTES),
                    'url'            => $item->get_permalink(),
                    'external_id'    => $item->get_id(),
                    'feed_timestamp' => $item->get_date(),
                    'content'        => $item->get_description(),
                    'media_url'      => $this->getMediaUrl($item),
                ];
            }
        }

        return NewsItemCollection::make($newsItems);
    }

    protected function getMediaUrl(SimplePie_Item $item): ?string
    {
        $media_url = '';

        if ($item->get_enclosure()) {
            if ($media_url = $item->get_enclosure()->get_thumbnail()) {
                return $media_url;
            } elseif ($media_url = $item->get_enclosure()->get_link()) {
                return $media_url;
            }
        } elseif ($item->get_description()) {
            $crawler =  new Crawler($item->get_description());

            return optional($crawler->filter('img')->first())->attr('src');
        }

        return null;
    }
}
