<?php

use CommunityInfoCoop\NewsHarvester\Models\Feed;
use CommunityInfoCoop\NewsHarvester\Models\Source;
use Illuminate\Support\Carbon;

it('can be created for a source', function () {
    $s = Source::factory()->create();

    $s->feeds()->create([
        'name' => 'My RSS feed',
        'type' => 'RSS',
        'location' => 'https://google.com/rss/',
    ]);

    $f = Feed::first();

    $this->assertEquals('RSS', $f->type);
    $this->assertEquals($s->id, $f->source_id);
});

it('is checkable', function () {
    $f = Feed::factory()->create();

    $this->assertContains($f->id, Feed::checkable()->pluck('id'));
});

it('is not checkable when not active', function () {
    $f = Feed::factory()->create(['is_active' => false]);
    $this->assertNotContains($f->id, Feed::checkable()->pluck('id'));
});

it('is not checkable when admin paused', function () {
    $f = Feed::factory()->create(['is_admin_paused' => true]);
    $this->assertNotContains($f->id, Feed::checkable()->pluck('id'));
});

it('is not checkable when recently checked', function () {
    $f = Feed::factory()->create(['last_check_at' => Carbon::now()->subMinutes(5)]);
    $this->assertNotContains($f->id, Feed::checkable()->pluck('id'));
});

it('is not checkable when next check is in the future', function () {
    $f = Feed::factory()->create(['next_check_after' => Carbon::now()->addMinutes(95)]);
    $this->assertNotContains($f->id, Feed::checkable()->pluck('id'));
});

it('has a valid url for rss', function () {
    $f = Feed::factory()->create([
        'type' => 'rss',
        'location' => 'https://google.com/feed/'
    ]);

    $this->assertEquals('https://google.com/feed/', $f->url);
});

it('has a null url for non rss', function () {
    $f = Feed::factory()->create([
        'type' => 'other',
        'location' => '12345'
    ]);
    $this->assertNull($f->url);
});
