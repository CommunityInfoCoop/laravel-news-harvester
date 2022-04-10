<?php

namespace CommunityInfoCoop\NewsHarvester\Database\Factories;

use CommunityInfoCoop\NewsHarvester\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\CommunityInfoCoop\NewsHarvester\Models\Source>
 */
class SourceFactory extends Factory
{
    protected $model = Source::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'url' => $this->faker->url(),
        ];
    }
}
