<?php

use CommunityInfoCoop\NewsHarvester\Tests\Database\Factories\UserFactory;
use CommunityInfoCoop\NewsHarvester\Filament\Resources\NewsItemResource;
use CommunityInfoCoop\NewsHarvester\Models\NewsItem;

beforeEach(function () {
    $this->actingAs(UserFactory::new()->create());
});

it('has a News Item index', function () {
    $this->get(NewsItemResource::getUrl('index'))->assertSuccessful();
});

it('can render News Items on index', function () {
    $newData = NewsItem::factory()->create();
    $this->get(NewsItemResource::getUrl('index'))->assertSeeText($newData->title)->assertSeeText($newData->excerpt);
});
