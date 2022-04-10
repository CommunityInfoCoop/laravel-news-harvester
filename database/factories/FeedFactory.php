<?php

namespace CommunityInfoCoop\NewsHarvester\Database\Factories;

use CommunityInfoCoop\NewsHarvester\Models\Feed;
use CommunityInfoCoop\NewsHarvester\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedFactory extends Factory
{
    protected $model = Feed::class;

    public function definition()
    {
        return [
            'name' => $this->faker->bs(),
            'source_id' => Source::factory(),
            'type' => 'RssFeed',
            'location' => $this->faker->url(),
            'check_frequency' => '60',
        ];
    }
}
