<?php

use CommunityInfoCoop\NewsHarvester\Models\Feed;

it('has an RSS feed handler class', function () {
    $feed_class_base_path = "CommunityInfoCoop\NewsHarvester\FeedHandlers\\";
    $this->assertTrue(class_exists($feed_class_base_path . 'Rss\\Rss'));
});

it('has a get feed items method', function () {
    $rss = new CommunityInfoCoop\NewsHarvester\FeedHandlers\Rss\Rss(Feed::factory()->make());
    $this->assertTrue(method_exists($rss, 'getFeedItems'));
});

it('can get news items from a valid feed', function () {
    $f = Feed::factory()->create(['type' => 'rss', 'location' => 'https://chrishardie.com/feed/']);
    $rss = new CommunityInfoCoop\NewsHarvester\FeedHandlers\Rss\Rss($f);
    $result = $rss->getFeedItems();

    expect($result)
        ->toBeInstanceOf(\CommunityInfoCoop\NewsHarvester\FeedHandlers\NewsItemCollection::class)
        ->toBeIterable();

    expect($result->first())
        ->toBeArray()
        ->toHaveKey('external_id');
});

it('throws an exception for an invalid feed', function () {
    $f = Feed::factory()->create(['type' => 'rss', 'location' => 'https://google.com/feed2/']);
    $rss = new CommunityInfoCoop\NewsHarvester\FeedHandlers\Rss\Rss($f);
    $rss->getFeedItems();
})->throws(\CommunityInfoCoop\NewsHarvester\Exceptions\FeedNotCheckable::class);
