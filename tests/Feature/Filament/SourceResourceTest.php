<?php

use CommunityInfoCoop\NewsHarvester\Filament\Resources\SourceResource;
use CommunityInfoCoop\NewsHarvester\Models\Source;
use CommunityInfoCoop\NewsHarvester\Tests\Database\Factories\UserFactory;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->actingAs(UserFactory::new()->create());
});

it('can render create page', function () {
    $this->get(SourceResource::getUrl('create'))->assertSuccessful();
});

it('can create', function () {
    $newData = Source::factory()->make();

    livewire(SourceResource\Pages\CreateSource::class)
        ->set('data.name', $newData->name)
        ->set('data.url', $newData->url)
        ->call('create');

    $this->assertDatabaseHas(\CommunityInfoCoop\NewsHarvester\Models\Source::class, [
        'name' => $newData->name,
        'url' => $newData->url,
    ]);
});
