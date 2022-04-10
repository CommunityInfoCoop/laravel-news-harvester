<?php

use CommunityInfoCoop\NewsHarvester\Models\Feed;
use CommunityInfoCoop\NewsHarvester\Models\NewsItem;
use CommunityInfoCoop\NewsHarvester\Models\Source;
use Illuminate\Support\Carbon;

it('can be created', function () {
    NewsItem::factory()->create();

    $this->assertCount(1, NewsItem::all());
});

it('can be created for a feed', function () {
    $f = Feed::factory()->has(NewsItem::factory()->count(3))->create();

    $this->assertEquals($f->id, NewsItem::first()->feed->id);
});

it('can be created for a source', function () {
    $s = Source::factory()->has(NewsItem::factory()->count(3))->create();

    $this->assertEquals($s->id, NewsItem::first()->source->id);
});

it('has a publish timestamp that is feed timestamp', function () {
    $t = '2022-01-01 05:00:00';

    $n = NewsItem::factory()->create(['feed_timestamp' => $t]);

    $this->assertEquals($t, $n->publish_timestamp);
});

it('has a publish timestamp that is created at timestamp', function () {
    $t = '2022-01-01 05:00:00';

    $n = NewsItem::factory()->create(['feed_timestamp' => null, 'created_at' => $t]);

    $this->assertEquals($t, $n->publish_timestamp);
});

it('has a relative publish timestamp', function () {
    $t = Carbon::now()->subDays(5);
    $n = NewsItem::factory()->create(['feed_timestamp' => $t]);
    $this->assertEquals('5 days ago', $n->publish_timestamp_relative);
});

it('has source info with feed relationship', function () {
    $f = Feed::factory()->create();
    $n = NewsItem::factory()->for($f)->create();
    $this->assertEquals($f->source->name, $n->source_info->name);
});

it('has source info with direct source relationship', function () {
    $s = Source::factory()->create();
    $n = NewsItem::factory()->for($s)->create(['feed_id' => null]);
    $this->assertEquals($s->name, $n->source_info->name);
});
