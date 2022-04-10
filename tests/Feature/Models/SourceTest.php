<?php

use CommunityInfoCoop\NewsHarvester\Models\Source;

it('can be created', function () {
    Source::create([
        'name' => 'Google',
        'url' => 'https://google.com/',
        'type' => 'Custom',
    ]);
    $this->assertEquals('Google', Source::first()->name);
});
