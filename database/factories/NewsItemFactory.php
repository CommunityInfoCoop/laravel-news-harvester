<?php

namespace CommunityInfoCoop\NewsHarvester\Database\Factories;

use CommunityInfoCoop\NewsHarvester\Models\Feed;
use CommunityInfoCoop\NewsHarvester\Models\NewsItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;


class NewsItemFactory extends Factory
{
    protected $model = NewsItem::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'url' => $this->faker->url(),
            'external_id' => $this->faker->slug(),
            'feed_timestamp' => Carbon::now()->subMinutes(30),
            'content' => $this->faker->paragraph(),
            'feed_id' => Feed::factory(),
        ];
    }
}
